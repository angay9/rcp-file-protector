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

require_once(__DIR__ . '/src/core/front/Guard.php');

$guard = new Guard();
$guard->protect();