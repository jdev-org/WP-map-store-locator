<?php
/**
 * Domain Path: /languages
 * Text Domain: WP-map-store-locator
 */
require_once('admin/MslAdmin.php');
require_once('widgets/MslWidget.php');

class MslPlugin
{
    
    /**
     * Constructor
     */
    function __construct() {}
    
    /**
     * Call to register and init plugin.
     */
    function register() {
        // load text domain to translate
        load_plugin_textdomain( 'WP-map-store-locator', false, 'WP-map-store-locator/languages/' );
        // add admin page
        $admin = new MslAdmin();
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
     * When plugin is deactivate.
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