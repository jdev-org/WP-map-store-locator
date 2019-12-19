<?php
/**
 * Domain Path: /languages
 * Text Domain: WP-map-store-locator
 */
require_once('admin/Admin.php');
require_once('widgets/Msl.php');

class MslPlugin
{
    
    public $plugin;
    
    /**
     * Constructor
     */
    function __construct() {
        $this->plugin = plugin_basename( __FILE__ );
    }
    
    /**
     * Call to register and init plugin.
     */
    function register() {
        load_plugin_textdomain( 'WP-map-store-locator', false, 'WP-map-store-locator/languages/' );
        // add admin page
        $admin = new Admin();
        $admin->register();

        $this->activateWidgets();
    }

    /**
     * When plugin is activate.
     */
	function activate() {
		// flush rewrite rules
		flush_rewrite_rules();
	}

    /**
     * When plugin is seactivate.
     */
	function deactivate() {
		// flush rewrite rules
		flush_rewrite_rules();
    }

    /**
     * Activate widgets.
     */
    function activateWidgets() {
        $msl_widget = new MslWidget();
        $msl_widget->register();
    }
}