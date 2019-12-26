<?php

namespace RcpFileProtector\Core\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class uses the solution inspired by this thread
 * https://gist.github.com/hakre/1552239
 */

class Guard 
{
	public function protect() 
	{
		// Get file from request
		$file = $this->getFileFromRequest();

		// Check if file arg exists
		$this->verifyRequest($file);

		// Get user memberships
		$userMemberships = $this->getUserMemberships();

		// Get protection levels
		$protectionLevels = stripslashes_deep(
			get_option('rcp_file_protector_protection_levels', [])
		);

		$isAllowed = $this->checkAccess($protectionLevels, $userMemberships, $file);

		if (! $isAllowed && ! is_super_admin()) {
			$this->abort();
		}

		$this->processRequest($file);
	}

	protected function verifyRequest($file)
	{
		if (! $file) {
			$this->abort();
		}
	}

	protected function getFileFromRequest() 
	{
		return isset($_GET['file']) ? 
			filter_var($_GET['file'], FILTER_SANITIZE_STRING) 
			: 
			null
		;
	}

	protected function match_url($pattern, $url, $isRegex = false) 
	{

		if ($isRegex) {
			// var_dump("/$pattern/", $url, preg_match("/$pattern/", $url));
			// wp_die(1);
			return preg_match("/$pattern/", $url);
		}
	
		// Make sure that string starts with "/"
		$pattern = '/' . ltrim($pattern, '/');
	
		// return $pattern === $url;
		return strpos($url, $pattern) !== false;
	}

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

	public function checkAccess($protectionLevels, $userMemberships, $file)
	{
		$isAllowed = true;

		foreach ($protectionLevels as $level) {
			$pattern = $level['url'];

			if ($this->match_url($pattern, $file, $level['isRegex'])) {
				
				$isAllowed = is_user_logged_in() && count(
					array_intersect($level['memberships'], $userMemberships)
				) > 0;
			}
		}

		return $isAllowed;
	}

	protected function abort($code = 404, $message = '404. File not found.') 
	{
		status_header($code);
		wp_die($message);
	}

	protected function processRequest($file)
	{
		list($basedir) = array_values(
			array_intersect_key(
				wp_upload_dir(), array('basedir' => 1)
			)
		) + array(NULL);
		
		$file =  rtrim($basedir,'/').'/'.str_replace('..', '', !is_null($file) ? $file : '');
		$file = realpath($file);
		
		if (!$basedir || !is_file($file)) {
			$this->abort();
		}

		$mime = wp_check_filetype($file);
		if( false === $mime[ 'type' ] && function_exists( 'mime_content_type' ) )
			$mime[ 'type' ] = mime_content_type( $file );
		if( $mime[ 'type' ] )
			$mimetype = $mime[ 'type' ];
		else
			$mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );
		header( 'Content-Type: ' . $mimetype ); // always send this
		if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) )
			header( 'Content-Length: ' . filesize( $file ) );
		$last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
		$etag = '"' . md5( $last_modified ) . '"';
		header( "Last-Modified: $last_modified GMT" );
		header( 'ETag: ' . $etag );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );
		// Support for Conditional GET
		$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;
		if( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
		$client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
		// If string is empty, return 0. If not, attempt to parse into a timestamp
		$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;
		// Make a timestamp for our most recent modification...
		$modified_timestamp = strtotime($last_modified);
		if ( ( $client_last_modified && $client_etag )
			? ( ( $client_modified_timestamp >= $modified_timestamp) && ( $client_etag == $etag ) )
			: ( ( $client_modified_timestamp >= $modified_timestamp) || ( $client_etag == $etag ) )
			) {
			status_header( 304 );
			exit;
		}

		// If we made it this far, just serve the file
		readfile( $file );

	}

}
