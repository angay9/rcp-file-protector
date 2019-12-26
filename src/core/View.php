<?php

namespace RcpFileProtector\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class View 
{
    protected $viewsLocation;

    public function __construct($viewsLocation)
    {
        $this->setViewsLocation($viewsLocation);
    }

    public function setViewsLocation($location)
    {
        $this->viewsLocation = $location;
    }

    public function getViewsLocation()
    {
        return $this->viewsLocation;
    }

    public function getLocation($filename)
    {
        return $this->viewsLocation . '/' . ltrim($filename);
    }

    /**
     * Render view from a give path
     * and pass vars to the view
     *
     * @param string $viewPath
     * @param array $vars
     * @return
     */
    public function render($viewPath, array $vars = array())
    {
        extract($vars);

        ob_start();
        require_once $this->getLocation($viewPath);

        $view = ob_get_clean();

        return $view;
    }
}