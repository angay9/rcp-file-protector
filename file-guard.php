<?php
/*
 * dl-file.php
 *
 * Protect uploaded files with login.
 * 
 * @link http://wordpress.stackexchange.com/questions/37144/protect-wordpress-uploads-if-user-is-not-logged-in
 * 
 * @author hakre <http://hakre.wordpress.com/>
 * @license GPL-3.0+
 * @registry SPDX
 */

use RcpFileProtector\Core\Front\Guard;

require_once('../../../wp-load.php');

require_once ABSPATH . WPINC . '/formatting.php';
require_once ABSPATH . WPINC . '/capabilities.php';
require_once ABSPATH . WPINC . '/user.php';
require_once ABSPATH . WPINC . '/meta.php';
require_once ABSPATH . WPINC . '/post.php';
require_once ABSPATH . WPINC . '/pluggable.php';
wp_cookie_constants();
ob_end_clean();
ob_end_flush();

require_once __DIR__ . '/bootstrap.php';

$guard = $rcpFileProtectorContainer->get(Guard::class);

// If rcp is not being used - don't check for permissions
if (! is_plugin_active('restrict-content-pro/restrict-content-pro.php')) {
    $guard->unguard(true);
}

$guard->protect();

