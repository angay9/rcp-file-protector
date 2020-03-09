<?php

use RcpFileProtector\Core\Admin\Helpers\Htaccess;
use RcpFileProtector\Core\Admin\Settings;
use RcpFileProtector\Core\Autoload\Psr4Autoloader;
use RcpFileProtector\Core\DI\Container;
use RcpFileProtector\Core\Front\FileReader;
use RcpFileProtector\Core\Front\Guard;
use RcpFileProtector\Core\View;

// Constants
require __DIR__ . '/config/constants.php';
$viewConfig = require_once __DIR__ . '/config/view.php';

// Autoload
require_once __DIR__ . '/src/core/autoload/Psr4Autoloader.php';
$config = require_once __DIR__ . '/config/autoload.php'; 

$loader = new Psr4Autoloader();
$loader->setNamespacesConfig($config);
$loader->register();

// Register dependencies
$rcpFileProtectorContainer = new Container();

$rcpFileProtectorContainer->set(View::class, function($container) use ($viewConfig) {
    return new View($viewConfig['dir']);
});

$rcpFileProtectorContainer->set(Htaccess::class, Htaccess::class);
$rcpFileProtectorContainer->set(Settings::class, Settings::class);
$rcpFileProtectorContainer->set(FileReader::class, FileReader::class);
$rcpFileProtectorContainer->set(Guard::class, Guard::class);
