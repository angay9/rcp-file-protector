<?php

/**
 * Plugin Name: File Protector For Restrict Content Pro
 * Plugin URI: https://codeable.io
 * Description: Protect your files from unauthorized access
 * Version: 0.0.1
 * Author: Andriy Haydash
 * Author URI: https://andriy.space
 * 
 */
defined('RCP_FILE_PROTECTOR_ROOT_URL') or define('RCP_FILE_PROTECTOR_ROOT_URL', plugins_url('', __FILE__));

defined('RCP_FILE_PROTECTOR_RELATIVE_ROOT_URL') 
    or 
define('RCP_FILE_PROTECTOR_RELATIVE_ROOT_URL', rtrim(wp_make_link_relative(RCP_FILE_PROTECTOR_ROOT_URL), '/'));

$loader = require __DIR__ . '/vendor/autoload.php';

use RcpFileProtector\Core\Front\Guard;
use RcpFileProtector\Core\RcpFileProtector;

$protector = new RcpFileProtector();

add_action('init', [$protector, 'init']);

register_activation_hook(__FILE__, [$protector, 'onPluginActivated']);
register_deactivation_hook(__FILE__, [$protector, 'onPluginDeactivated']);
