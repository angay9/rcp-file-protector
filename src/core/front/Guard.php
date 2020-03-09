<?php

namespace RcpFileProtector\Core\Front;

use RcpFileProtector\Core\Front\Exceptions\FileNotFoundException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class uses the solution inspired by this thread
 * https://gist.github.com/hakre/1552239
 */
class Guard 
{
	/**
	 * Boolean flag to check if Guard needs to be unguarded
	 *
	 * @var boolean
	 */
	protected $unguard = false;

	/**
	 * File reader
	 *
	 * @var RcpFileProtector\Core\Front\FileReader
	 */
	protected $fileReader;

	/**
	 * Constructor
	 *
	 * @param RcpFileProtector\Core\Front\FileReader $fileReader
	 */
	public function __construct(FileReader $fileReader)
	{
		$this->fileReader = $fileReader;
	}

	/**
	 * Get file reader
	 *
	 * @return RcpFileProtector\Core\Front\FileReader
	 */
	public function getFileReader()
	{
		return $this->fileReader;
	}

	/**
	 * Set file reader
	 *
	 * @param FileReader $fileReader
	 * @return void
	 */
	public function setFileReader(FileReader $fileReader)
	{
		$this->fileReader = $fileReader;
	}

	/**
	 * Protect requests
	 *
	 * @return void
	 */
	public function protect() 
	{
		// Get file from request
		$file = $this->getFileFromRequest();

		// Check if file arg exists
        $this->verifyRequest($file);

		if ($this->unguard === true) {
			
			$this->processRequest($file);
			
			return;
		}

		// Get user memberships
		$userMemberships = $this->getUserMemberships();
		$userMemberships = apply_filters('rcp-file-protector/front/guard/user_memberships', $userMemberships);

		// Get protection levels
		$protectionLevels = stripslashes_deep(
			get_option('rcp_file_protector_protection_levels', [])
		);

		$protectionLevels = apply_filters('rcp-file-protector/front/guard/protection_levels', $protectionLevels);

		$isAllowed = $this->checkAccess($protectionLevels, $userMemberships, $file);
		$isAllowed = apply_filters('rcp-file-protector/front/guard/is_allowed_access', $isAllowed);
		
		if (! $isAllowed && ! is_super_admin()) {
			do_action('rcp-file-protector/front/guard/before_abort');

			$this->abort();	
		}

		do_action('rcp-file-protector/front/guard/before_request_processed');
		
		$this->processRequest($file);
	}

	/**
	 * Verify if request is valid
	 *
	 * @param string $file
	 * @return void
	 */
	protected function verifyRequest($file)
	{
		if (! $file) {
			$this->abort();
		}
	}

	/**
	 * Get filename from request
	 *
	 * @return string|null
	 */
	protected function getFileFromRequest() 
	{
		return isset($_GET['file']) ? 
			filter_var($_GET['file'], FILTER_SANITIZE_STRING) 
			: 
			null
		;
	}

	/**
	 * Match url
	 *
	 * @param string $pattern
	 * @param string $url
	 * @param boolean $isRegex
	 * @return boolean
	 */
	protected function matchUrl($pattern, $url, $isRegex = false) 
	{

		if ($isRegex) {
			return preg_match("/$pattern/", $url);
		}
	
		// Make sure that string starts with "/"
		$pattern = '/' . ltrim($pattern, '/');
	
		// return $pattern === $url;
		return strpos($url, $pattern) !== false;
	}

	/**
	 * Get user memberships
	 *
	 * @return array
	 */
	protected function getUserMemberships()
	{
		$userMemberships = [];

		// No one is logged in, so memberships are an empty array
		if (! is_user_logged_in()) {
			return $userMemberships;
		}

		// Get RCP Customer using current user id
		$customer = rcp_get_customer_by_user_id(
			get_current_user_id()
		);

		if ($customer) {
			$customerMemberships = $customer->get_memberships([
				'status' => 'active',
				'fields' => ['id']
			]);

			$userMemberships = array_map(function ($membership) {
				return $membership->id;
			}, $customerMemberships);
		}

		return $userMemberships;
	}

	/**
	 * Check user access to file
	 *
	 * @param array $protectionLevels
	 * @param array $userMemberships
	 * @param string $file
	 * @return void
	 */
	public function checkAccess(array $protectionLevels, array $userMemberships, $file)
	{
		$isAllowed = true;

		foreach ($protectionLevels as $level) {
			$pattern = $level['url'];

			if ($this->matchUrl($pattern, $file, $level['isRegex'])) {
				
				$isAllowed = is_user_logged_in() && count(
					array_intersect($level['memberships'], $userMemberships)
				) > 0;
			}
		}

		return $isAllowed;
	}

	/**
	 * Abort request
	 *
	 * @param integer $code
	 * @param string $message
	 * @return void
	 */
	protected function abort($code = 404, $message = '404. File not found.') 
	{
		status_header($code);
		wp_die($message);
	}

	/**
	 * Try to read and return file
	 *
	 * @param string $file
	 * @return void
	 */
	protected function processRequest($file)
	{	
		try {
			
			$this->fileReader->read($file);

		} catch (FileNotFoundException $e) {
			$this->abort(
				$e->getCode(),
				$e->getMessage()
			);
		}
	}

	/**
	 * Unguard the guard
	 *
	 * @param boolean $unguard
	 * @return void|boolean
	 */
	public function unguard($unguard = null)
	{
		if ($unguard === null) {
			return $this->unguard;
		}

		// Convert to boolean and assign
		$this->unguard = !! $unguard;
	}

}
