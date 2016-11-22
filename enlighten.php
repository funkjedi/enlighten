<?php
/*
Plugin Name: Enlighten
Plugin URI: http://github.com/funkjedi/enlighten
Description: Description here.
Version: 1.0.0
Author: funkjedi
Author URI: http://funkjedi.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (file_exists(__DIR__.'/vendor/autoload.php')) {
	require_once __DIR__.'/vendor/autoload.php';
	new \Enlighten\Plugin;
}
