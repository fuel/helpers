<?php
// load the composer autoloader
include './vendor/autoload.php';

// make Arr available in the global namespace
class_alias('Fuel\Common\Arr', 'Arr');

// constants required for the tests
define('DOCROOT', '/this/docroot/');
define('APPSPATH', '/this/apps/path/');
define('VENDORPATH', realpath(__DIR__.'/../../').'/');
