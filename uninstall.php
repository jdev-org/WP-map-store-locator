<?php

/**
 * Trigger this file on Plugin uninstall
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}



/**
 * Remove all options
 */

delete_option('msl_data_file_url');
delete_option('msl_data_png1_type');
delete_option('msl_data_png2_type');
delete_option('msl_data_png3_type');
delete_option('msl_data_png1_url');
delete_option('msl_data_png2_url');
delete_option('msl_data_png3_url');
delete_option('msl_data_size');
delete_option('msl_default_coordinates');
delete_option('msl_default_zoom');
delete_option('msl_open_page');
delete_option('msl_marker_search_url');
delete_option('msl_marker_search_size');
delete_option('msl_marker_search_extent');
delete_option('msl_marker_search_bias');
delete_option('msl_overlay_html');
delete_option('msl_overlay_marker');
delete_option('msl_overlay_marker_size');
delete_option('msl_overlay_text');
delete_option('msl_overlay_title');