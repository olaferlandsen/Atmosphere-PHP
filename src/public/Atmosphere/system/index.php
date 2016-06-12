<?php
require_once( ATMOSPHERE . 'system/Atmosphere.php');
require_once( ATMOSPHERE . 'system/Environment.php');
require_once( ATMOSPHERE . 'system/Rewrite.php');
$Atmosphere = new \Atmosphere\Atmosphere(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
$Atmosphere->init(__FILE__);