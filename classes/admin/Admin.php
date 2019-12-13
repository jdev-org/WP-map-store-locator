<?php 

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
                        submit_button('Save');
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
        add_settings_section("default_view_section", "Map option", array($this,"def_section_view"), "msl_plugin");
        // set center field
        add_settings_field("default_coordinates", "Coordinates", array($this,"def_field_coordinates"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "default_coordinates");
        // set zoom field
        add_settings_field("default_zoom", "Zoom", array($this,"def_field_zoom"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "default_zoom");
        // url to open page
        add_settings_field("open_page", "Page to open", array($this,"open_page"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "open_page");        
    }

    // section description text
    function def_section_view() {
        ?>
            <div style="border-bottom: 1px solid black;">
                Default map view options.
            </div>
        <?php
    }

    function def_field_coordinates() {
        ?>
            <input type="text" name="default_coordinates" id="default_coordinates" value="<?php echo get_option('default_coordinates'); ?>" />
        <?php
    }

    function def_field_zoom() {
        ?>
            <input type="number" name="default_zoom" id="default_zoom" value="<?php echo get_option('default_zoom'); ?>" />
        <?php
    }

    function open_page() {
        ?>
            <input type="text" name="open_page" id="data_png3_type" value="<?php echo get_option('open_page'); ?>" />
        <?php
    }

    /**
     * Overlay section
     * contain owner main store, warehouse or headquarter description
     */
    function overlay_section() {
        // create section
        add_settings_section("overlay_section", "Popup options", array($this,"def_section_overlay"), "msl_plugin");
        // set overlay location coordinates
        add_settings_field("overlay_coordinates", "Location", array($this,"def_overlay_coordinates"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_coordinates");
        // set overlay title
        add_settings_field("overlay_title", __( 'Title', 'msl_plugin' ), array($this,"def_overlay_title"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_title");
        // set overlay text
        add_settings_field("overlay_text", "Text", array($this,"def_overlay_text"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_text");
        // overlay marker
        add_settings_field("overlay_marker", "Marker Icon", array($this,"def_overlay_marker"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_marker");
        // overlay marker size
        add_settings_field("overlay_marker_size", "Marker size (0-1)", array($this,"overlay_marker_size"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_marker_size");                
        // overlay html content
        add_settings_field("overlay_html", "HTML Content", array($this,"def_overlay_html"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_html");
    }

    // section description text
    function def_section_overlay() {
        ?>
            <div style="border-bottom: 1px solid black;">
                Default popup options.
            </div>
        <?php
    }

    function def_overlay_title(){
        ?>
            <input type="text" name="overlay_title" id="overlay_title" value="<?php echo get_option('overlay_title'); ?>" />
        <?php
    }
    function def_overlay_coordinates(){
        ?>
            <input type="text" name="overlay_coordinates" id="overlay_coordinates" value="<?php echo get_option('overlay_coordinates'); ?>" />
        <?php
    }
    function def_overlay_text() {
        ?>
            <input type="text" name="overlay_text" id="overlay_text" value="<?php echo get_option('overlay_text'); ?>" />
        <?php
    }

    function def_overlay_marker() {
        ?>
            <input type="text" name="overlay_marker" id="overlay_marker" value="<?php echo get_option('overlay_marker'); ?>" />
        <?php
    }

    function overlay_marker_size() {
        ?>
            <input type="number" step="0.01" name="overlay_marker_size" id="overlay_marker_size" value="<?php echo get_option('overlay_marker_size'); ?>" />
        <?php
    }

    function def_overlay_html() {
        ?>
            <input type="text" name="overlay_html" id="overlay_html" value="<?php echo get_option('overlay_html'); ?>" />
        <?php
    }

    /**
     * Data section
     * Contain all params to display data to map
     */
    function data_section () {
        add_settings_section("data_section", "Data options", array($this,"section_data_title"), "msl_plugin");
        // data file url input
        add_settings_field("data_file_url", __( 'URL', 'msl_plugin' ), array($this,"data_file_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_file_url");

        add_settings_field("data_size", __( 'Size (0-1)', 'msl_plugin' ), array($this,"data_size"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_size");

        add_settings_field("data_png1_type", __( 'Type 1', 'msl_plugin' ), array($this,"data_png1_type"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_png1_type");

        add_settings_field("data_png1_url", __( 'Image 1', 'msl_plugin' ), array($this,"data_png1_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_png1_url");

        add_settings_field("data_png2_type", __( 'Type 2', 'msl_plugin' ), array($this,"data_png2_type"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_png2_type");

        add_settings_field("data_png2_url", __( 'Image 2', 'msl_plugin' ), array($this,"data_png2_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_png2_url");

        add_settings_field("data_png3_type", __( 'Type 3', 'msl_plugin' ), array($this,"data_png3_type"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_png3_type");

        add_settings_field("data_png3_url", __( 'Image 3', 'msl_plugin' ), array($this,"data_png3_url"), "msl_plugin", "data_section");
        register_setting("msl_settings", "data_png3_url");
    }

    // section description text
    function section_data_title() {
        ?>
            <div style="border-bottom: 1px solid black;">
                Options to display data on the map.
            </div>
        <?php
    }

    function data_file_url() {
        ?>
            <input type="text" name="data_file_url" id="data_file_url" value="<?php echo get_option('data_file_url'); ?>" />
        <?php
    }

    function data_size() {
        ?>
            <input type="number" step="0.01" name="data_size" id="data_size" value="<?php echo get_option('data_size'); ?>" />
        <?php
    }

    function data_png1_url() {
        ?>
            <input type="text" name="data_png1_url" id="data_png1_url" value="<?php echo get_option('data_png1_url'); ?>" />
        <?php
    }

    function data_png2_url() {
        ?>
            <input type="text" name="data_png2_url" id="data_png2_url" value="<?php echo get_option('data_png2_url'); ?>" />
        <?php
    }
    
    function data_png3_url() {
        ?>
            <input type="text" name="data_png3_url" id="data_png3_url" value="<?php echo get_option('data_png3_url'); ?>" />
        <?php
    }

    function data_png1_type() {
        ?>
            <input type="text" name="data_png1_type" id="data_png1_type" value="<?php echo get_option('data_png1_type'); ?>" />
        <?php
    }

    function data_png2_type() {
        ?>
            <input type="text" name="data_png2_type" id="data_png2_type" value="<?php echo get_option('data_png2_type'); ?>" />
        <?php
    }
    
    function data_png3_type() {
        ?>
            <input type="text" name="data_png3_type" id="data_png3_type" value="<?php echo get_option('data_png3_type'); ?>" />
        <?php
    }
}