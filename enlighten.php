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

define('ENLIGHTEN_PLUGIN',     __FILE__);
define('ENLIGHTEN_PLUGIN_DIR', plugin_dir_path(ENLIGHTEN_PLUGIN));

require_once ENLIGHTEN_PLUGIN_DIR . 'vendor/autoload.php';
new \Enlighten\Plugin;
