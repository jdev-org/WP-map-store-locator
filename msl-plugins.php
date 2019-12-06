<?php
/*
Plugin Name: Map Store Location

Description: simple map plugin
Version: 1.0.0
Author: JDev
License: GPLv3 or later
*/
include('classes/MslPlugin.php');

$msl = new MslPlugin();
$msl->register();


register_activation_hook( __FILE__, array( $msl, 'activate' ) );
register_deactivation_hook( __FILE__, array( $msl, 'deactivate' ) );
