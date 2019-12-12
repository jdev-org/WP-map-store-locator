<?php
/*
Plugin Name: WP-map-store-locator
Description: un plugin de test pour l’ajout d’un menu admin WordPress
Author: JDev
Version: 0.1
*/
// map store locator class
class msl extends WP_Widget {
    // constructor
    function __construct() {
        parent::__construct(
            'msl',
            esc_html__( 'Map Store Locator', 'textdomain' ),
            array( 'description' => esc_html__( 'Display stores or customers on a map', 'textdomain' ), )
        );
    }
 
    // display and layout
    public function widget( $args, $instance ) {
        extract($args);
        // HTML before widget
        echo $before_widget;
        print_r($instance);
        // get mapTitle from instane
        echo $before_title.$instance["mapTitle"].$after_title;
        ?>
            <!doctype html>
            <html lang="en">

            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/lib/ol-6.1.1/css/ol.css'?>">
                <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/lib/bootstrap-4.3.1/css/bootstrap.min.css'?>"> 
                <title>Map Store Locator</title>
                <style>
                    .ol-attribution.ol-logo-only,
                    .ol-attribution.ol-uncollapsible {
                        max-width: calc(100% - 3em) !important;
                        height: 1.5em !important;
                    }
                    .ol-control button,
                    .ol-attribution,
                    .ol-scale-line-inner {
                        font-family: 'Lucida Grande', Verdana, Geneva, Lucida, Arial, Helvetica, sans-serif !important;
                    }
                </style>
            </head>

            <body>
                <div class="input-group">
                    <input type="text" class="form-control pl-1 pt-0 basic" placeholder="Enter address..." aria-label="Recipient's username" aria-describedby="basic-addon2"/>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">Clear</button>
                    </div>
                </div>
                <div id="map" style="width: 600px; height: 400px;"></div>
                <div id="popup" class="ol-popup">
                    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
                    <div id="popup-content"></div>
                </div>
                <script src="<?= plugins_url() . '/WP-map-store-locator/lib/ol-6.1.1/js/ol.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/lib/jquery-3.4.1/jquery-3.4.1.min.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/lib/popper.js-1.14.7/popper.min.js'?>"></script>                
                <script src="<?= plugins_url() . '/WP-map-store-locator/lib/bootstrap-4.3.1/js/bootstrap.min.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/lib/autocomplete/dist/latest/bootstrap-autocomplete.js'?>"></script>
                <script>
                    // values from php wordpress
                    var mapDefaultCenter = <?= json_encode($instance["mapCenterXY"]) ?> || '-385579.42,6244601.85';
                    var mapDefaultZoom = <?= json_encode($instance["mapCenterZoom"]) ?> || 7;
                    var mapDefaultSearchZoom = <?= json_encode($instance["mapSearchZoom"]) ?> || 7;
                    var dataUrl = <?= json_encode(get_option('data_file_url'));?> || '';
                    var map;
                    // behavior for autocomplete research
                    $('.basic').autoComplete({
                        resolver: 'custom',
                        events: {
                            search: function (qry, callback) {
                                var xhr = new XMLHttpRequest();
                                xhr.open('GET', 'https://nominatim.openstreetmap.org/search?q='+ qry + '&format=json');
                                xhr.onload = function() {
                                    if (xhr.status === 200 && xhr.responseText) {
                                        var response = xhr.responseText.length ? JSON.parse(xhr.responseText) : null;
                                        if(response) {
                                            callback(response);
                                        }
                                    }
                                    else {
                                        console.log('fail request');
                                    }
                                };
                                xhr.send();
                            }
                        },
                        formatResult: function(item) {
                            return {
                                value: JSON.stringify(item),
                                text: item.display_name,
                                html: [
                                    item.display_name
                                ]
                            };
                        }
                    });
                    // zoom on search result
                    $('.basic').on('autocomplete.select', (evt,value) => {
                        var xy = [value.lon, value.lat];
                        var reprojPoint = ol.proj.fromLonLat(xy);
                        console.log(map);
                        map.getView().setCenter(reprojPoint);
                        map.getView().setZoom(mapDefaultSearchZoom);
                        //map.getView().setCenter([0,0]);
                    });
                    // set default center view
                    var ddvCoordinates = [588206.82,5621639.47];
                    var splitCenter = mapDefaultCenter.split(',');
                    var x = parseFloat(splitCenter[0]);
                    var y = parseFloat(splitCenter[1]);
                    mapDefaultCenter = [x,y];
                    // for map attribution
                    var attribution = new ol.control.Attribution({
                        collapsible: false
                    });
                    // create map
                    map = new ol.Map({
                        controls: ol.control.defaults({ attribution: false }).extend([attribution]),
                        layers: [
                            new ol.layer.Tile({
                                source: new ol.source.OSM()
                            })
                        ],
                        target: 'map',
                        view: new ol.View({
                            center: mapDefaultCenter,
                            zoom: mapDefaultZoom
                        })
                    });
                    
                    // create and add layers
                    var layer = new ol.layer.Vector({
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
        // HTML after widget
        echo $after_widget;
    }
    function update($new_values, $old_values) {
        // keep values to compare or, and modify between old and news
        return $new_values;
    }
    function form($instance) {
        ?>
            <p>
                <label for="<?php echo $this->get_field_id("mapTitle"); ?>">Title: </label>
                <input placeholder="Default map title" type="text" name="<?php echo $this->get_field_name("mapTitle"); ?>" id="<?php echo $this->get_field_id("mapTitle"); ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id("mapCenterXY"); ?>">Center: </label>
                <input placeholder="Default view coordinates" type="text" name="<?php echo $this->get_field_name("mapCenterXY"); ?>" id="<?php echo $this->get_field_id("mapCenterXY"); ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id("mapCenterZoom"); ?>">Default zoom: </label>
                <input placeholder="Inital zoom level" type="integer" name="<?php echo $this->get_field_name("mapCenterZoom"); ?>" id="<?php echo $this->get_field_id("mapCenterZoom"); ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id("mapSearchZoom"); ?>">Search zoom: </label>
                <input placeholder="Search zoom level" type="integer" name="<?php echo $this->get_field_name("mapSearchZoom"); ?>" id="<?php echo $this->get_field_id("mapSearchZoom"); ?>"/>
            </p>
        <?php
    }
}
// call actions
add_action( 'widgets_init', 'register_msl' );
add_action('init', 'load_scripts');


function load_scripts() {
    wp_enqueue_script('jquery');   
}
function register_msl() {
    register_widget( 'msl' );
}