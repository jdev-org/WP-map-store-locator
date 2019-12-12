<?php
/*
Plugin Name: WP-map-store-locator
Description: un plugin de test pour l’ajout d’un menu admin WordPress
Author: JDev
Version: 0.1
*/
// map store locator class
class MslWidget extends WP_Widget {
    public $isMapReady;
    // constructor
    function __construct() {
        parent::__construct(
            'msl',
            esc_html__( 'Map Store Locator', 'textdomain' ),
            array( 'description' => esc_html__( 'Display stores or customers on a map', 'textdomain' ), )
        );
    }
    function register() {
        add_action('widgets_init', array( $this, 'widgetInit' ) );
        add_action('init', array($this, 'load_scripts'));
    }
    
    function load_scripts() {
        wp_enqueue_script('jquery');   
    }

    function widgetInit() {
        register_widget( $this );
    }
 
    // display and layout
    public function widget( $args, $instance ) {
        extract($args);
        // HTML before widget
        echo $before_widget;
        print_r($instance);
        $this->initHTML($instance);
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
                <input class="checkbox" type="checkbox" <?php checked( $instance[ 'msl_simple' ], 'on' ); ?> id="<?php echo $this->get_field_id( 'msl_simple' ); ?>" name="<?php echo $this->get_field_name( 'msl_simple' ); ?>" /> 
                <label for="<?php echo $this->get_field_id( 'msl_simple' ); ?>">Simple Map</label>
            </p>            
        <?php
    }


    /**
     * Create map or load html page if not loaded
     */
    function initHTML($instance) {
        if($this->isMapReady) {
            $this->createMap($instance);
        } else {
            
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
            </head>
            <body>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/js/ol.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/popper/popper.min.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/bootstrap-4/js/bootstrap.min.js'?>"></script>
            </body>
            </html>     
            <?php
            $this->isMapReady = true;
            // now, create map
            $this->createMap($instance);
        }
    }

    /**
     * Create map if openLayers and others lib are loaded
     */
    function createMap($instance) {
        $this->ID = uniqid();
        $this->popupId = "popup-" . $this->ID;
        $this->popupContentId = "popup-content-" . $this->ID;
        $this->locatorId = "locator-" . $this->ID;
        ?>
            <div class="container">
                <div class="row">                
                    <div id="locator" class="col-12 p-0">
                        <input class="p-2" placeholder="Saisir une adresse..." type="text">
                    </div>
                    <div id=<?= json_encode($this->ID);?> class="col-sm-12 p-0" style="height: 12em;"></div>
                </div>

            </div>                
            </div>
            <div id="popup" class="ol-popup">
            </div>
            <script>
            // replace ids for popup
            var id = <?= json_encode($this->ID);?>;
            var popupId =  <?= json_encode($this->popupId);?>;
            var popup = document.getElementById("popup");
            popup.id = popupId;
            document.getElementById(popupId).innerHTML = `<div id="popup-content"></div>`; // avoid popup-content deletation when id changed
            var listId = 'list-' + id;

            // replace ids for locator
            var locator = document.getElementById('locator');
            if(locator) {
                locator.id = <?= json_encode($this->locatorId);?>;
            }

            // set values from php
            var mapDefaultCenter = <?= json_encode(get_option('default_coordinates'));?> ||'-385579.42,6244601.85';
            var mapDefaultZoom = <?= json_encode(get_option('default_zoom'));?> || 7;
            var isSimpleMap = <?= json_encode($instance['msl_simple']);?> === "on";

            /**
            * Convert string coordinates to real array
            */
            function xyStringToArray(val) {
                let arrXY;
                if(val.replace(/\s/g, '').length > 0) {
                    let splitCenter = val.split(',');
                    let x = parseFloat(splitCenter[0]);
                    let y = parseFloat(splitCenter[1]);
                    arrXY = [x,y];
                    return  arrXY;
                }
            }
            // icon and style
            mapDefaultCenter = xyStringToArray(mapDefaultCenter);

            var map = new ol.Map({
                target: <?= json_encode($this->ID);?>,
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

            // if advanced map we display all elements
            if(isSimpleMap) {
                var mapId = <?= json_encode($this->ID);?>;
                var popupHtml = 'popup-content-' + <?= json_encode($this->ID);?>;
                var overlayText = <?= json_encode(get_option('overlay_text'));?> || '';
                var overlayMarker = <?= json_encode(get_option('overlay_marker'));?> || '';
                var overlaySize = <?= json_encode(get_option('overlay_marker_size'));?> || 0.8;
                var overlayTitle = <?= json_encode(get_option('overlay_title'));?> || '';
                var overlayHtmlContent = <?= json_encode(get_option('overlay_html'));?> || '';
                var dataUrl = <?= json_encode(get_option('data_file_url'));?> || '';
                var dataSize = <?= json_encode(get_option('data_size'));?> || '';
                var png1 = <?= json_encode(get_option('data_png1_url'));?> || '';
                var png2 = <?= json_encode(get_option('data_png2_url'));?> || '';
                var png3 = <?= json_encode(get_option('data_png3_url'));?> || '';
                var dataType1 = <?= json_encode(get_option('data_png1_type'));?> || '';
                var dataType2 = <?= json_encode(get_option('data_png2_type'));?> || '';
                var dataType3 = <?= json_encode(get_option('data_png3_type'));?> || '';
                var img = [png1,png2,png3];
                var types = [dataType1,dataType2,dataType3];
                
                // js values
                var mapDiv;

                var iconFeature = new ol.Feature({
                    geometry: new ol.geom.Point(mapDefaultCenter),
                });
                // popup creation                  
                var overlay = new ol.Overlay({
                    element: document.getElementById(popupId),
                    autoPan: false,
                    offset: [0, -50]
                });
                // insert overlay to map                    
                map.setProperties('overlays',[overlay])


                if (overlayMarker ) { // avoid empty style and no ol.style.icon assertion error
                    var iconStyle = new ol.style.Style({
                        image: new ol.style.Icon({
                            anchor: [0.3, 40],
                            anchorXUnits: 'fraction',
                            anchorYUnits: 'pixels',
                            src: overlayMarker,
                            scale: overlaySize
                        })
                    });
                
                    iconFeature.setStyle(iconStyle);
                    // create and add layers
                    var layer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: [
                                iconFeature
                            ]
                        })
                    });
                    // add marker to map
                    map.addLayer(layer);
                }

                // Popup behavior on marker clic
                var content = function () { 
                    return document.getElementById(popupHtml);
                };
                
                /**
                * Hide popup if user clic out of marker
                 */
                function hidePopup(){
                    overlay.setPosition(undefined);                        
                }

                /**
                * show popup if user clic on marker
                 */
                function showPopup(xy) {
                    overlay.setPosition(xy);
                    if(overlayHtmlContent) {
                        content.innerHTML =  overlayHtmlContent;
                    } else {
                        document.getElementById(popupHtml).innerHTML = `
                        <div class="card m-2">
                            <div class="card-body mb-2 p-2">
                                <h6 class="card-title overlay-title mb-2"><strong>` + overlayTitle + `</strong>
                                </h6></n>
                                <span>`+ overlayText + `</span>
                            </div>
                        </div>`;
                    }
                }
                // feature select behavior - only one in the map
                map.on('click', evt => {
                    var features = [];
                    var coordinate = evt.coordinate;
                    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        features.push(feature);
                    });
                    if (features.length > 0 && document.getElementById(popupHtml)) {
                        showPopup(mapDefaultCenter);
                    } else {
                        hidePopup();
                    }
                });

                /**
                * INPUT BEHAVIOR
                 */
                function createSelection(options) {
                    jQuery('#'+ locator.id + ' > datalist').remove();
                    var optionsHTML = ['<datalist id="' + listId + '">'];
                    options.forEach(e=>{
                        var xy =  e.lon + ',' + e.lat;
                        var val = '';
                        if(e.address.county) {
                            val = e.address.county + ', ' + e.address.postcode + ' (' + e.address.country_code + ')'; 
                        } else if (e.address.country && e.address.state){
                            val = e.address.state + ', ' + e.address.country; 
                        }
                        
                        if(xy && val) {
                            optionsHTML.push('<option onclick="console.log(this)" location="'+ xy +'" value="' + val + '">');
                        }
                        
                    });
                    optionsHTML.push('</datalist>');
                    return optionsHTML.join('');
                }
                var input = jQuery('#'+ locator.id + ' > input');
                input.attr('list',listId);
                input.on('keyup', function(e){
                    jQuery('#'+ locator.id + ' > datalist').remove();
                    let el = this;
                    if(this.value && this.value.length > 3 && !jQuery('#'+listId).length) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', 'https://nominatim.openstreetmap.org/search?q='+ this.value + '&format=json&addressdetails=1&limit=5');
                        xhr.onload = function() {
                            if (xhr.status === 200 && xhr.responseText) {
                                var response = xhr.responseText.length ? JSON.parse(xhr.responseText) : null;
                                if(response) {
                                    jQuery('#'+ locator.id).append(createSelection(response)); 
                                }
                            }
                            else {
                                console.log('fail request');
                            }
                        };
                        xhr.send();
                    }
                });
                input.on('select', function(e){
                    jQuery('#'+ locator.id + ' > datalist').remove();
                })
                input.on('focus', function(e){
                    jQuery('#'+ locator.id + ' > datalist').remove();
                })

                /**
                * JSON READER
                */
                function getJsonLayer(jsonUrl, inSrs, toSrs) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', jsonUrl);
                    xhr.onload = function() {
                        if (xhr.status === 200 && xhr.responseText) {
                            var response = xhr.responseText.length ? JSON.parse(xhr.responseText) : null;
                            if(response) {
                                var features = [];
                                var res = new ol.format.GeoJSON().readFeatures( response );
                                res.forEach(e => {
                                    if(e.getProperties().latitude || e.getProperties().longitude) {
                                        e.getGeometry().transform(inSrs, toSrs);
                                        features.push(e);
                                    }
                                });
                                var layer = featuresToLayer(features);
                                map.addLayer(layer)
                            }
                        }
                        else {
                            console.log('fail request');
                        }
                    };
                    xhr.send();
                }

                function featuresToLayer(features, name) {
                    let vectorSource = new ol.source.Vector({
                        features
                    });
                    const vectorLayer = new ol.layer.Vector({
                        source: vectorSource,
                        name: name,
                        id: name,
                        style: getStyle
                    });
                    return vectorLayer;
                }

                function getStyle(feature) {
                    var style;
                    var cat = feature.get('code_categorie');
                    if(cat === 'ASS') {
                        console.log(cat);
                    }
                    
                    if(types.indexOf(cat) > -1 && cat.length === types.length){
                        var imgCat = img[types.indexOf(cat)];
                        console.log(imgCat);
                        style = new ol.style.Style({
                            image: new ol.style.Icon({
                                src: imgCat,
                                scale: dataSize
                            }),
                        })
                    }
                    return [style];                    
                }

                if(dataUrl) {
                    getJsonLayer(dataUrl,'EPSG:4326', map.getView().getProjection().getCode());
                }

            }
            </script>
        <?php
    }    
}
