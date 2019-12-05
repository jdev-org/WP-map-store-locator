<?php
require('Admin.php');
require('MapWidget.php');

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

    function init() {

    }

    function activateWidgets() {
        $map_widget = new MapWidget();
        $map_widget->register();
    }
}