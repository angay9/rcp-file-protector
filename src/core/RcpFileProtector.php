<?php

namespace RcpFileProtector\Core;

use RcpFileProtector\Core\Admin\Helpers\Htaccess;
use RcpFileProtector\Core\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RcpFileProtector 
{
    protected $view;

    protected $adminSettings;

    public function __construct()
    {
        $this->view = new View(
            __DIR__ . '/../../views'
        );

        $this->adminSettings = new Settings(
            $this->view
        );
    }

    public function init()
    {
        $this->adminSettings->init();
    }

    public function onPluginActivated()
    {
        $htaccessHelper = new Htaccess();

        $htaccessHelper->addRules(RCP_FILE_PROTECTOR_RELATIVE_ROOT_URL);
    }

    public function onPluginDeactivated()
    {
        $htaccessHelper = new Htaccess();

        $htaccessHelper->removeRules();
    }

}