<?php
/**
 * Domain Path: /languages
 * Text Domain: WP-map-store-locator
 */
require_once('admin/MslAdmin.php');
require_once('widgets/MslWidget.php');

class MslPlugin
{
    
    public $plugin_url;
    
    /**
     * Constructor
     */
    function __construct($plugin_path) {
        $this->plugin_url = $plugin_path;
    }
    
    /**
     * Call to register and init plugin.
     */
    function register() {
        load_plugin_textdomain( 'WP-map-store-locator', false, $this->plugin_url.'languages/' );
        // add admin page
        $admin = new MslAdmin($this->plugin_url);
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
        $msl_widget = new MslWidget($this->plugin_url);
        $msl_widget->register();
    }
}