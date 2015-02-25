<?php
// This is global bootstrap for autoloading

require __DIR__.'/../vendor/autoload.php';

class_alias('Fuel\Common\Arr', 'Arr');

define('ROOTPATH', realpath(__DIR__.'/../'));
define('DOCROOT', __DIR__);
define('VENDORPATH', realpath(__DIR__.'/../vendor/'));
