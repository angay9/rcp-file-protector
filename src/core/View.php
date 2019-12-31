<?php

namespace RcpFileProtector\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class View
{
    /**
     * Views folder prefix for child themes to override views
     */
    const OVERRIDE_VIEWS_FOLDER_PREFIX = 'rcp-file-protector';

    /**
     * Views location
     *
     * @var string
     */
    protected $viewsLocation;

    /**
     * Constructor
     *
     * @param string $viewsLocation
     */
    public function __construct($viewsLocation)
    {
        $this->setViewsLocation($viewsLocation);
    }

    /**
     * Set views location
     *
     * @param string $location
     * @return void
     */
    public function setViewsLocation($location)
    {
        $this->viewsLocation = $location;
    }

    /**
     * Get views location
     *
     * @return string
     */
    public function getViewsLocation()
    {
        return $this->viewsLocation;
    }

    /**
     * Render view from a give path
     * and pass vars to the view
     *
     * @param string $file
     * @param array $vars
     * @return
     */
    public function render($file, array $vars = array())
    {
        extract($vars);

        ob_start();
        
        require_once $this->locateFile($file);

        $view = ob_get_clean();

        return $view;
    }

    /**
     * Locate file
     *
     * @param string $file
     * @return string
     */
    protected function locateFile($file)
    {
        $file = ltrim($file, '/');
        $filePath = '';
        $possibleFilePath = static::OVERRIDE_VIEWS_FOLDER_PREFIX . '/' .$file;

        // Look for file in child theme
        if ( file_exists( get_stylesheet_directory() . '/' . $possibleFilePath ) ) {
            $filePath = get_stylesheet_directory() . '/' . $possibleFilePath;
        }

        // Look for file in parent theme
        else if ( file_exists( get_template_directory() . '/' . $possibleFilePath ) ) {
            $filePath = get_template_directory() . '/' . $possibleFilePath;
        }

        // Save the transient and update it every 12 hours
        if ( !empty( $filePath ) ) {
            return $filePath;
        }

        return $this->viewsLocation . '/' . ltrim($file);
    }
}