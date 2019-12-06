<?php
/*
Plugin Name: Map Store Location

Description: A plugin to display geographic data in a map.
Version: 1.0.0
Author: JDev
License: GPLv3 or later
Domain Path: /languages
*/
include('classes/MslPlugin.php');

$msl = new MslPlugin();
$msl->register();


register_activation_hook( __FILE__, array( $msl, 'activate' ) );
register_deactivation_hook( __FILE__, array( $msl, 'deactivate' ) );
