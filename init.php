<?php defined('SYSPATH') OR die('No direct access allowed.');


if ( !class_exists( 'Less_Parser' ) ) {
	require_once __DIR__ . '/vendor/less.php/lib/Less/Autoloader.php';
	Less_Autoloader::register();
}
