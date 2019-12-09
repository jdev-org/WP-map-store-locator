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
        $this->controls_options = array( 'width' => 800, 'height' => 500 );
    }

    function register() {
        parent::__construct( $this->widget_ID, $this->widget_name, $this->widget_options, $this->control_options );        
        add_action('widgets_init', array( $this, 'widgetInit' ) );
        
        add_shortcode('map', array($this,'getMap'));       
    }

    function widgetInit() {
        register_widget( $this );
        wp_enqueue_script( 'jquery' );
    }

    function widget( $args, $instance ) {
        $this->instance = $instance;
        echo $args['before_widget'];
        $this->getMap();
        echo $args['after_widget'];
    }

    function getMap() {
        // generate random id
        $randomId = uniqid();
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/css/ol.css'?>">
            <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/includes/lib/bootstrap-4/css/bootstrap.min.css'?>">
            <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/includes/css/popup.css'?>">
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
                    <!--div-- id="<?= json_encode($randomId);?>" class="col-sm-12 p-0" style="height: 12em;"></!--div-->
                    <div id="map" class="col-sm-12 p-0" style="height: 12em;"></div>
                </div>
            </div>
            <div id="popup" class="ol-popup">
                <div id="popup-content"></div>
            </div>
            
            
            <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/js/ol.js'?>"></script>
            <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/popper/popper.min.js'?>"></script>
            <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/bootstrap-4/js/bootstrap.min.js'?>"></script>                
            <script type="text/javascript">

                // create random id
                let id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                // values from php wordpressgit stat
                let mapDefaultCenter = <?= json_encode(get_option('default_coordinates'));?> ||'-385579.42,6244601.85';
                let mapDefaultZoom = <?= json_encode(get_option('default_zoom'));?> || 7;
                let overlayText = <?= json_encode(get_option('overlay_text'));?> || '';
                let overlayMarker = <?= json_encode(get_option('overlay_marker'));?> || '';
                let overlayTitle = <?= json_encode(get_option('overlay_title'));?> || '';
                let overlayHtmlContent = <?= json_encode(get_option('overlay_html'));?> || '';
                let mapDefaultSearchZoom = 7;
                // set default center view
                let splitCenter = mapDefaultCenter.split(',');
                let x = parseFloat(splitCenter[0]);
                let y = parseFloat(splitCenter[1]);
                mapDefaultCenter = [x,y];
                let map;
                // icon and style
                var iconFeature = new ol.Feature({
                    geometry: new ol.geom.Point(mapDefaultCenter),
                });


                // popup creation                  
                let overlay = new ol.Overlay({
                    element: document.getElementById('popup'),
                    autoPan: false,
                    offset: [0, -50]
                });
                // map creation
                document.getElementById("map").id = id;
                map = new ol.Map({
                    target: id,
                    layers: [
                        new ol.layer.Tile({
                            source: new ol.source.OSM()
                        })
                    ],
                    //overlays: [overlay],
                    view: new ol.View({
                        center: mapDefaultCenter,
                        zoom: mapDefaultZoom
                    })
                });
                // set properties
                if (overlayMarker) { // avoid empty style and no ol.style.icon assertion error
                    var iconStyle = new ol.style.Style({
                        image: new ol.style.Icon({
                            anchor: [0.3, 40],
                            anchorXUnits: 'fraction',
                            anchorYUnits: 'pixels',
                            src: overlayMarker
                        })
                    });
                
                    iconFeature.setStyle(iconStyle);
                    // create and add layers
                    let layer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: [
                                iconFeature
                            ]
                        })
                    });
                    map.addLayer(layer);                    
                    map.setProperties({'overlays': [overlay]});
                }
                // popup behavior
                let content = document.getElementById('popup-content');
                function hidePopup(){
                    overlay.setPosition(undefined);                        
                }
                function showPopup(xy) {
                    overlay.setPosition(xy);
                    if(overlayHtmlContent) {
                        content.innerHTML =  overlayHtmlContent;
                    } else {
                        content.innerHTML = `
                        <div class="card m-2">
                            <div class="card-body mb-2 p-2">
                                <h6 class="card-title overlay-title mb-2"><strong>` + overlayTitle + `</strong>
                                </h6></n>
                                <span>`+ overlayText + `</span>
                            </div>
                        </div>`;
                    }
                }
                // feature slect behavior - only one in the map
                map.on('click', function(evt) {
                    let features = [];
                    let coordinate = evt.coordinate;
                    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        features.push(feature);
                    });
                    if (features.length > 0) {
                        showPopup(mapDefaultCenter);
                    } else {
                        hidePopup();
                    }
                });
            </script>
        </body>
        </html>     
        <?php
        
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