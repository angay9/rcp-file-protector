<?php

namespace RcpFileProtector\Core;

use RcpFileProtector\Core\Admin\Helpers\Htaccess;
use RcpFileProtector\Core\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RcpFileProtector 
{
    /**
     * Admin settings
     *
     * @var RcpFileProtector\Core\Admin\Settings
     */
    protected $adminSettings;

    /**
     * Htaccess helper
     *
     * @var RcpFileProtector\Core\Admin\Helpers\Htaccess
     */
    protected $htaccess;

    /**
     * Constructor
     *
     * @param RcpFileProtector\Core\Admin\Settings $settings
     * @param RcpFileProtector\Core\Admin\Helpers\Htaccess $htaccess
     */
    public function __construct(Settings $settings, Htaccess $htaccess)
    {
        $this->adminSettings = $settings;
        $this->htaccess = $htaccess;
    }

    /**
     * Initialize plugin
     *
     * @return void
     */
    public function init()
    {
        if (!is_plugin_active('restrict-content-pro/restrict-content-pro.php')) {
            return;
        }
        
        $this->adminSettings->init();
    }

    /**
     * On plugin activated
     *
     * @return void
     */
    public function onPluginActivated()
    {
        $this->htaccess->addRules(RCP_FILE_PROTECTOR_RELATIVE_ROOT_URL);
    }

    /**
     * On plugin deactivated
     *
     * @return void
     */
    public function onPluginDeactivated()
    {
        $this->htaccess->removeRules();
    }

    /**
     * Set admin settings
     *
     * @param RcpFileProtector\Core\Admin\Settings $settings
     * @return void
     */
    public function setAdminSettings(Settings $settings)
    {
        $this->adminSettings = $settings;
    }

    /**
     * Get admin settings
     *
     * @return RcpFileProtector\Core\Admin\Settings
     */
    public function getAdminSettings()
    {
        return $this->adminSettings;
    }

    /**
     * Set htaccess helper
     *
     * @param RcpFileProtector\Core\Admin\Helpers\Htaccess $htaccess
     * @return void
     */
    public function setHtaccess(Htaccess $htaccess)
    {
        $this->htaccess = $htaccess;
    }

    /**
     * Get htaccess helpr
     *
     * @return RcpFileProtector\Core\Admin\Helpers\Htaccess
     */
    public function getHtaccess()
    {
        return $this->htaccess;
    }

}