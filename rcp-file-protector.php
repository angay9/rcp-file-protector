<?php

/**
 * Plugin Name: File Protector For Restrict Content Pro
 * Plugin URI: https://andriy.space
 * Description: Protect your files from unauthorized access
 * Version: 0.0.1
 * Author: Andriy Haydash
 * Author URI: https://andriy.space
 * 
 */

use RcpFileProtector\Core\RcpFileProtector;

require_once __DIR__ . '/bootstrap.php';

// Plugin Initializer
$rcpFileProtector = $rcpFileProtectorContainer->make(RcpFileProtector::class);
add_action('init', [$rcpFileProtector, 'init']);

// Activation/Deactivation Hooks
register_activation_hook(__FILE__, [$rcpFileProtector, 'onPluginActivated']);
register_deactivation_hook(__FILE__, [$rcpFileProtector, 'onPluginDeactivated']);
