<?php
require_once('admin/Admin.php');

require_once('widgets/MapWidget.php');
require_once('widgets/BaseWidgets.php');
require_once('widgets/Msl.php');

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
        $msl_widget = new MslWidget();
        $msl_widget->register();
    }
}