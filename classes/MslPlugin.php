<?php
require('admin/Admin.php');

require('widgets/FootMapWidget.php');
require('widgets/PageMapWidget.php');

//require('widgets/FooterMapWidget.php');

class MslPlugin
{
    
    public $plugin;
    
    function __construct() {
        $this->plugin = plugin_basename( __FILE__ );
    }
    
    // trigger with class init
    function register() {
        $admin = new Admin();
        $admin->register();

        $this->activateWidgets();
    }

	function activate() {
		// flush rewrite rules
		flush_rewrite_rules();
	}

	function deactivate() {
		// flush rewrite rules
		flush_rewrite_rules();
    }

    function activateWidgets() {
        // footer widget
        $page_map_widget = new FootMapWidget(
            'Footer map widget',
            'Footer map locator',
            null,
            true,           
        );
        $page_map_widget->register();
        // page widget
        $page_map_widget = new PageMapWidget(
            'Map page widget',
            'Map page locator',
            null,
            true,           
        );
        $page_map_widget->register();
    }
}