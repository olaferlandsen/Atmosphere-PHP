<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ATMOSPHERE',        dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Atmosphere'.DIRECTORY_SEPARATOR);
define('ATMOSPHERE_ROOT',   dirname(__FILE__).DIRECTORY_SEPARATOR);
define('ATMOSPHERE_SYSTEM', dirname(__FILE__).DIRECTORY_SEPARATOR.'Atmosphere'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR);
define('ATMOSPHERE_SERVER_NAME',    $_SERVER["SERVER_NAME"]);
include_once(ATMOSPHERE_SYSTEM . 'index.php');
