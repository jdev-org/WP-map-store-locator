<?php
/*
Plugin Name: Map store locator
Description: Allow to insert a simple OpenLayers map into a wp page.
Author: JDev
Version: 0.1
*/

class MapWidget extends WP_Widget
{
    public $options;

    public $widget_ID;

	public $widget_name;

	public $widget_options = array();

	public $control_options = array();

    function __construct() {
        $this->widget_ID = 'msl_map_widget';
        $this->widget_name = 'Map Store Locator Map widget';
        $this->widget_options = array(
            'classname' => $this->widget_ID,
            'description' => $this->widget_name,
            'customize_selective_refresh' => true
        );
        $this->controls_options = array( 'width' => 400, 'height' => 350 );
    }

    function register() {
        parent::__construct( $this->widget_ID, $this->widget_name, $this->widget_options, $this->control_options );
        wp_enqueue_script('jquery');
        add_action('widgets_init', array( $this, 'widgetInit' ) );
    }

    function widgetInit() {
        register_widget( $this );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        ?>
            <!doctype html>
            <html lang="en">
            <head>
                <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/css/ol.css'?>">
                <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/includes/lib/bootstrap-4/css/bootstrap.min.css'?>">
                <style>
                .ol-attribution.ol-uncollapsible {
                    display: none !important;
                }

                </style>
                <title>OpenLayers example</title>
            </head>
            <body>
                <div class="container">
                    <div class="row">                
                        <div id="map" class="col-sm-12 col-md-8 col-lg-6 p-0" style="height: 12em;"></div>
                    </div>
                </div>
                
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/js/ol.js'?>"></script>
                <scrip src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.js"></scrip>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/popper/popper.min.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/bootstrap-4/js/bootstrap.min.js'?>"></script>                
                <script type="text/javascript">
                    // values from php wordpress
                    
                    let mapDefaultCenter = <?= json_encode(get_option('default_coordinates'));?> ||'-385579.42,6244601.85';
                    let mapDefaultZoom = <?= json_encode(get_option('default_zoom'));?> || 7;
                    
                    let mapDefaultSearchZoom = 7;
                    let map;
                    
                    // set default center view
                    let splitCenter = mapDefaultCenter.split(',');
                    let x = parseFloat(splitCenter[0]);
                    let y = parseFloat(splitCenter[1]);
                    mapDefaultCenter = [x,y];
                    
                    map = new ol.Map({
                        target: 'map',
                        layers: [
                            new ol.layer.Tile({
                                source: new ol.source.OSM()
                            })
                        ],
                        view: new ol.View({
                            center: mapDefaultCenter,
                            zoom: mapDefaultZoom
                        })
                    });
                    // create and add layers
                    let layer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: [
                                new ol.Feature({
                                    geometry: new ol.geom.Point(mapDefaultCenter)
                                })
                            ]
                        })
                    });
                    map.addLayer(layer);         
                </script>
            </body>
            </html>     
        <?php
        echo $args['after_widget'];
    }



    /**Contains widgets options html**/
    public function form($instance) {

        ?>
        <!-- form html -->
        <?php
    }    

    /**Catch update values to compare and insert action on modification**/
    public function update( $new_instance, $old_instance) {
        $instance = $old_instance;        
        return $instance;
    }
}