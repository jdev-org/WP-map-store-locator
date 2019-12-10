<?php
require_once('admin/Admin.php');

require_once('widgets/MapWidget.php');

class MslPlugin
{
    
    public $plugin;
    
    function __construct() {
        $this->plugin = plugin_basename( __FILE__ );
    }
    
    // trigger with class init
    function register() {
        // add admin page
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
        // create maion map widget
        $map_widget = new MapWidget(
            'Map widget',
            'Map',
            null,
            true,
        );
        $map_widget->register();

        // footer widget
        /*$foot_map_widget = new FootMapWidget(
            'Footer map widget',
            'Footer map locator',
            null,
            true,           
        );
        $foot_map_widget->register();*/
        
        // page widget

        /*$page_map_widget = new PageMapWidget(
            'Map page widget',
            'Map page locator',
            null,
            true,           
        );
        $page_map_widget->register();*/
    }
}