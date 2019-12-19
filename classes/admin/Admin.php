<?php
/**
 * Domain Path: /languages
 */

class Admin
{
    public $options = array();

    public function __construct() {
        $this->plugin_path= plugin_dir_url( dirname( __FILE__ ) );
    }

    /**
     * Register method as init
     */
	public function register() {       
        add_action("admin_menu", array( $this, 'add_new_menu_items' ));
        add_action("admin_init", array( $this, 'view_section' ));
        add_action("admin_init", array( $this, 'overlay_section' ));
        add_action("admin_init", array( $this, 'data_section' ));
        add_action("admin_init", array( $this, 'search_section' ));
    }

    /**
     * Define admin main page
     */
    public function add_new_menu_items() {
        add_menu_page(
            'Map Store Locator',
            'Msl',
            'manage_options',
            'msl_plugin',
            array( $this,
            'admin_page' ),
            'dashicons-location',
            110
        );
    }
    /**
     * Front page
     */
    public function admin_page(){
        ?>
            <div class="wrap">
                <div id="icon-options-general" class="icon32"></div>
                <h1>Map Store Locator - Administration</h1>
                <?php settings_errors(); ?>
                <form method="post" action="options.php">
                    <?php
                        //add_settings_section callback is displayed here. For every new section we need to call settings_fields.                        
                        settings_fields("msl_settings");
                        // all the add_settings_field callbacks is displayed here
                        do_settings_sections("msl_plugin");
                        // Add the submit button to serialize the options
                        submit_button(__('Save', 'WP-map-store-locator'));
                    ?>         
                </form>
            </div>
        <?php
    }

    
    /**
     * View section
     * View is link to map and define default map view options
     */
    function view_section() {
        // create section
        add_settings_section("default_view_section", __( 'Map options', 'WP-map-store-locator' ), array($this,"def_section_view"), "msl_plugin");
        // set center field
        add_settings_field("msl_default_coordinates", __( 'Coordinates', 'WP-map-store-locator' ), array($this,"def_field_coordinates"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "msl_default_coordinates");
        // set zoom field
        add_settings_field("msl_default_zoom", __( 'Zoom (1 - 20)', 'WP-map-store-locator' ), array($this,"def_field_zoom"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "msl_default_zoom");
        // url to open page
        add_settings_field("msl_open_page",__( 'Link to open', 'WP-map-store-locator' ), array($this,"msl_open_page"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "msl_open_page");        
    }

    // section description text
    function def_section_view() {
        ?>
            <div style="border-bottom: 1px solid black;">
                <?php echo __("Default map view options.", "WP-map-store-locator") ?>
            </div>
            </br>
            <?php echo __("Get coordinates in EPSG:3857 (lon, lat) from ", "WP-map-store-locator") ?>
            <a href="https://app.dogeo.fr/Projection/#/point-to-coords" target="_blank">
                <?php echo __("Dogeo web site. ", "WP-map-store-locator") ?>
            </a>
        <?php
    }

    function def_field_coordinates() {
        ?>
            <input type="text" name="msl_default_coordinates" id="msl_default_coordinates" value="<?php echo get_option('msl_default_coordinates'); ?>" />
        <?php
    }

    function def_field_zoom() {
        ?>
            <input type="number" name="msl_default_zoom" id="msl_default_zoom" value="<?php echo get_option('msl_default_zoom'); ?>" />
        <?php
    }

    function msl_open_page() {
        ?>
            <input type="text" name="msl_open_page" id="msl_data_png3_url" value="<?php echo get_option('msl_open_page'); ?>" />
        <?php
    }

    /**
     * Overlay section
     * contain owner main store, warehouse or headquarter description
     */
    function overlay_section() {
        // create section
        add_settings_section("overlay_section", __("Popup options","WP-map-store-locator"), array($this,"def_section_overlay"), "msl_plugin");
        // set overlay title
        add_settings_field("msl_overlay_title",__( 'Title', 'WP-map-store-locator' ), array($this,"def_msl_overlay_title"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "msl_overlay_title");
        // set overlay text
        add_settings_field("msl_overlay_text", __("Text", "WP-map-store-locator"), array($this,"def_msl_overlay_text"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "msl_overlay_text");
        // overlay marker
        add_settings_field("msl_overlay_marker",  __("Marker Icon", "WP-map-store-locator"), array($this,"def_msl_overlay_marker"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "msl_overlay_marker");
        // overlay marker size
        add_settings_field("msl_overlay_marker_size", __("Marker size (0-1)", "WP-map-store-locator"), array($this,"msl_overlay_marker_size"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "msl_overlay_marker_size");                
        // overlay html content
        add_settings_field("msl_overlay_html",__("HTML content", "WP-map-store-locator"), array($this,"def_msl_overlay_html"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "msl_overlay_html");
    }

    // section description text
    function def_section_overlay() {
        ?>
            <div style="border-bottom: 1px solid black;">
                <?php echo __("Default popup options.", "WP-map-store-locator") ?>
            </div>      
        <?php
    }

    function def_msl_overlay_title(){
        ?>
            <input type="text" name="msl_overlay_title" id="msl_overlay_title" value="<?php echo get_option('msl_overlay_title'); ?>" />
        <?php
    }

    function def_msl_overlay_text() {
        ?>
            <input type="text" name="msl_overlay_text" id="msl_overlay_text" value="<?php echo get_option('msl_overlay_text'); ?>" />
        <?php
    }

    function def_msl_overlay_marker() {
        ?>
            <input type="text" name="msl_overlay_marker" id="msl_overlay_marker" value="<?php echo get_option('msl_overlay_marker'); ?>" />
        <?php
    }

    function msl_overlay_marker_size() {
        ?>
            <input type="number" step="0.01" name="msl_overlay_marker_size" id="msl_overlay_marker_size" value="<?php echo get_option('msl_overlay_marker_size'); ?>" />
        <?php
    }

    function def_msl_overlay_html() {
        ?>
            <input type="text" name="msl_overlay_html" id="msl_overlay_html" value="<?php echo get_option('msl_overlay_html'); ?>" />
        <?php
    }

    /**
     * Data section
     * Contain all params to display data to map
     */
    function data_section () {
        add_settings_section("data_section", __("Data options", "WP-map-store-locator"), array($this,"section_data_title"), "msl_plugin");
        // data file url input
        add_settings_field("msl_data_file_url", __( 'URL', 'WP-map-store-locator' ), array($this,"msl_data_file_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_file_url");

        add_settings_field("msl_data_size", __( "Marker size (0-1)", 'WP-map-store-locator' ), array($this,"msl_data_size"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_size");

        add_settings_field("msl_data_png1_type", __( 'Type 1', 'WP-map-store-locator' ), array($this,"msl_data_png1_type"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_png1_type");

        add_settings_field("msl_data_png1_url", __( 'Image 1', 'WP-map-store-locator' ), array($this,"msl_data_png1_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_png1_url");

        add_settings_field("msl_data_png2_type", __( 'Type 2', 'WP-map-store-locator' ), array($this,"msl_data_png2_type"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_png2_type");

        add_settings_field("msl_data_png2_url", __( 'Image 2', 'WP-map-store-locator' ), array($this,"msl_data_png2_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_png2_url");

        add_settings_field("msl_data_png3_type", __( 'Type 3', 'WP-map-store-locator' ), array($this,"msl_data_png3_type"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_png3_type");

        add_settings_field("msl_data_png3_url", __( 'Image 3', 'WP-map-store-locator' ), array($this,"msl_data_png3_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "msl_data_png3_url");
    }

    // section description text
    function section_data_title() {
        ?>
            <div style="border-bottom: 1px solid black;">
               <?php echo __("Options to display data on the map.", "WP-map-store-locator") ?>
            </div>
            </br>
            <span><?php echo __("Display data with projection ", "WP-map-store-locator") ?><a href="http://epsg.io/4326" target="_blank">EPSG:4326.</a></span>
            </br>
            </br>
            <?php echo __("Please, use SVG or PNG as icons.", "WP-map-store-locator") ?>
        <?php
    }

    function msl_data_file_url() {
        ?>
            <input type="text" name="msl_data_file_url" id="msl_data_file_url" value="<?php echo get_option('msl_data_file_url'); ?>" />
        <?php
    }

    function msl_data_size() {
        ?>
            <input type="number" step="0.01" name="msl_data_size" id="msl_data_size" value="<?php echo get_option('msl_data_size'); ?>" />
        <?php
    }

    function msl_data_png1_url() {
        ?>
            <input type="text" name="msl_data_png1_url" id="msl_data_png1_url" value="<?php echo get_option('msl_data_png1_url'); ?>" />
        <?php
        if(get_option('msl_data_png1_url')) {
            ?>
                </br>
                </br>
                <img src="<?php echo get_option('msl_data_png1_url'); ?>">
            <?php
        }        
    }

    function msl_data_png2_url() {
        ?>
            <input type="text" name="msl_data_png2_url" id="msl_data_png2_url" value="<?php echo get_option('msl_data_png2_url'); ?>" />
        <?php
        if(get_option('msl_data_png2_url')) {
            ?>
                </br>
                </br>
                <img src="<?php echo get_option('msl_data_png2_url'); ?>">
            <?php
        }
    }
    
    function msl_data_png3_url() {
        ?>
            <input type="text" name="msl_data_png3_url" id="msl_data_png3_url" value="<?php echo get_option('msl_data_png3_url'); ?>" />
        <?php
        if(get_option('msl_data_png3_url')) {
            ?>
                </br>
                </br>
                <img src="<?php echo get_option('msl_data_png3_url'); ?>">
            <?php
        }
    }

    function msl_data_png1_type() {
        ?>
            <input type="text" name="msl_data_png1_type" id="msl_data_png1_type" value="<?php echo get_option('msl_data_png1_type'); ?>" />
        <?php
    }

    function msl_data_png2_type() {
        ?>
            <input type="text" name="msl_data_png2_type" id="msl_data_png2_type" value="<?php echo get_option('msl_data_png2_type'); ?>" />
        <?php
    }
    
    function msl_data_png3_type() {
        ?>
            <input type="text" name="msl_data_png3_type" id="msl_data_png3_type" value="<?php echo get_option('msl_data_png3_type'); ?>" />
        <?php
    }

    /**
     * Data section
     * Contain all params to display data to map
     */
    function search_section () {
        add_settings_section("search_section", __("Search options", "WP-map-store-locator"), array($this,"section_search"), "msl_plugin");
        // search icon url
        add_settings_field("msl_marker_search_url", __( 'Marker URL', 'WP-map-store-locator' ), array($this,"msl_marker_search_url"), "msl_plugin", "search_section");
        register_setting("msl_settings", "msl_marker_search_url");
        // search icon size
        add_settings_field("msl_marker_search_size", __( "Marker size (0-1)", 'WP-map-store-locator' ), array($this,"msl_marker_search_size"), "msl_plugin", "search_section");
        register_setting("msl_settings", "msl_marker_search_size");
    }    

    // section description text
    function section_search() {
        ?>
            <div style="border-bottom: 1px solid black;">
               <?php echo __("Search options", "WP-map-store-locator") ?>
            </div>
            </br>
            <?php echo __("Please, use SVG or PNG as icons.", "WP-map-store-locator") ?>            
        <?php
    }
    
    function msl_marker_search_url() {
        ?>
            <input type="text" name="msl_marker_search_url" id="msl_marker_search_url" value="<?php echo get_option('msl_marker_search_url'); ?>" />
        <?php
        if(get_option('msl_marker_search_url')) {
            ?>
                </br>
                </br>
                <img src="<?php echo get_option('msl_marker_search_url'); ?>">
            <?php
        }
    }
    function msl_marker_search_size() {
        ?>
            <input type="number" step="0.01" name="msl_marker_search_size" id="msl_marker_search_size" value="<?php echo get_option('msl_marker_search_size'); ?>" />
        <?php
    }
}