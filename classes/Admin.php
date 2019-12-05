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
        add_action("admin_init", array( $this, 'default_view_section' ));
        add_action("admin_init", array( $this, 'default_overlay_section' ));
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

    function setOptions() {

    }

    function saveOptions() {

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
     * View is link to map and define default map view options
     */
    function default_view_section() {
        // create section
        add_settings_section("default_view_section", "Map option", array($this,"def_section_view"), "msl_plugin");
        // set center field
        add_settings_field("default_coordinates", "Coordinates", array($this,"def_field_coordinates"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "default_coordinates");
        // set zoom field
        add_settings_field("default_zoom", "Zoom", array($this,"def_field_zoom"), "msl_plugin", "default_view_section");
        register_setting("msl_settings", "default_zoom");
    }

    /**
     * Overlay contain owner main store, warehouse or headquarter description
     */
    function default_overlay_section() {
        // create section
        add_settings_section("overlay_section", "Popup options", array($this,"def_section_overlay"), "msl_plugin");
        // set overlay location coordinates
        add_settings_field("overlay_coordinates", "Location", array($this,"def_overlay_coordinates"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_coordinates");
        // set overlay title
        add_settings_field("overlay_title", "Title", array($this,"def_overlay_title"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_title");
        // set overlay picture
        add_settings_field("overlay_picture", "Picture", array($this,"def_overlay_pict"), "msl_plugin", "overlay_section");
        register_setting("msl_settings", "overlay_picture");
    }

    function def_section_view() {
        echo "Default map view options.";
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

    function def_section_overlay() {
        echo "Default popup options.";
    }

    function def_overlay_title(){
        ?>
            <input type="text" name="overlay_title" id="overlay_title" value="<?php echo get_option('overlay_title'); ?>" />
        <?php
    }
    function def_overlay_coordinates(){
        ?>
            <input type="number" name="overlay_coordinates" id="overlay_coordinates" value="<?php echo get_option('overlay_coordinates'); ?>" />
        <?php
    }
    function def_overlay_pict() {
        ?>
            <input type="text" name="overlay_picture" id="overlay_picture" value="<?php echo get_option('overlay_picture'); ?>" />
        <?php
    }
}