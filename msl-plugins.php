<?php
/*
Plugin Name: Map Store Location

Description: A plugin to display geographic data in a map.
Version: 1.1.0
Author: JDev
License: GPLv3 or later
Domain Path: /languages
Text Domain: WP-map-store-locator
Author URI: https://jdev.fr
*/
include('classes/MslPlugin.php');

$msl = new MslPlugin();

$msl->register();

$url = plugin_dir_url(__FILE__);
define('MSL_PLUGIN_URL',$url);

register_activation_hook( __FILE__, array( $msl, 'activate' ) );
register_deactivation_hook( __FILE__, array( $msl, 'deactivate' ) );
