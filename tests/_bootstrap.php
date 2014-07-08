<?php
// This is global bootstrap for autoloading

require __DIR__.'/../vendor/autoload.php';

// TODO: possibly remove this or at least update them to be something sensable
define('ROOTPATH', sys_get_temp_dir());
define('VENDORPATH', sys_get_temp_dir());
define('DOCROOT', sys_get_temp_dir());
