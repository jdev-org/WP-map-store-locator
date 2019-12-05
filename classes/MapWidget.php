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
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.1.1/css/ol.css" type="text/css">
                <style>
                .map {
                    height: 400px;
                    width: 100%;
                }
                </style>
                <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.1.1/build/ol.js"></script>
                <title>OpenLayers example</title>
            </head>
            <body>
                <div id="map" class="map"></div>
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