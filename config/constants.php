<?php

defined('RCP_FILE_PROTECTOR_ROOT_URL') 
    or 
define(
    'RCP_FILE_PROTECTOR_ROOT_URL', 
    plugins_url('', realpath(__DIR__ .'/../rcp-file-protector.php') )
);


defined('RCP_FILE_PROTECTOR_RELATIVE_ROOT_URL') 
    or 
define(
    'RCP_FILE_PROTECTOR_RELATIVE_ROOT_URL', 
    rtrim(wp_make_link_relative(RCP_FILE_PROTECTOR_ROOT_URL), '/')
);