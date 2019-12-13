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
    public $isSimpleMap;
    private $instanceWidget;
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
        add_shortcode('msl',array($this,'displayWidget'));
    }

    
    function displayWidget($atts = [], $content = null, $tag = ''){
        $isSimpleMap;
        if (isset($atts['map'])) {
            $isSimpleMap = $atts['map'] === 'simple' ? 'on' : 'off';
        }
        return $this->initHTML(array('msl_simple' => $isSimpleMap));
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
                <link rel="stylesheet" href="<?= plugins_url() . '/WP-map-store-locator/includes/css/msl.css'?>">
                <style>
                .ol-attribution.ol-uncollapsible {
                    display: none !important;
                }
                </style>
            </head>
            <body>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/js/ol.js'?>"></script>
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
    function createMap($isSimpleMode) {
        $this->ID = uniqid();
        $this->locatorId = "locator-" . $this->ID;
        $mapName = 'map' . $this->ID;
        $popupId = "popup-" . $mapName;
        $popupHtml = "popup-content-" . $mapName;
        ?>
            <div id="locator" style="display:none;" style="col-12">
                <input  class="address" placeholder="Saisir une adresse..." type="text">
            </div>
            <div id=<?= $mapName ?> class="col-sm-12 p-0" style="height: 12em;"></div>
            <div id=<?= $popupId ?> class="ol-popup">
                <div id=<?= $popupHtml ?>></div>
            </div>
          
            <script>
            // ids for popup
            var id = <?= json_encode($this->ID);?>;
            var widgetDivId = <?= json_encode($this->ID.'-msl');?>;
            var popupId =  <?= json_encode($popupId);?>;
            var popupHtml = <?= json_encode($popupHtml);?>;
            var listId = 'list-' + id;

            // replace ids for locator
            var locator = document.getElementById('locator');
            if(locator) {
                locator.id = <?= json_encode($this->locatorId);?>;
            }

            // set values from php
            var mapDefaultCenter = <?= json_encode(get_option('default_coordinates'));?> ||'-385579.42,6244601.85';
            var mapDefaultZoom = <?= json_encode(get_option('default_zoom'));?> || 7;
            var isSimpleMap = <?= json_encode($isSimpleMode['msl_simple']);?> === "on";

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

            var <?= $mapName ?> = new ol.Map({
                target: <?= json_encode($mapName);?>,
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
            
            var mapId = <?= json_encode($this->ID);?>;
            var overlayText = <?= json_encode(get_option('overlay_text'));?> || '';
            var overlayMarker = <?= json_encode(get_option('overlay_marker'));?> || '';
            var overlaySize = <?= json_encode(get_option('overlay_marker_size'));?> || 0.8;
            var overlayTitle = <?= json_encode(get_option('overlay_title'));?> || '';
            var overlayHtmlContent = <?= json_encode(get_option('overlay_html'));?> || '';
            var dataUrl = <?= json_encode(get_option('data_file_url'));?> || '';
            var dataSize = <?= json_encode(get_option('data_size'));?> || '';
            var openPageUrl = <?= json_encode(get_option('open_page'));?> || '';
            var img = [
                <?= json_encode(get_option('data_png1_url'));?>,
                <?= json_encode(get_option('data_png2_url'));?>,
                <?= json_encode(get_option('data_png3_url'));?>];
            var types = [
                <?= json_encode(get_option('data_png1_type'));?>,
                <?= json_encode(get_option('data_png2_type'));?>,
                <?= json_encode(get_option('data_png3_type'));?>];
            
            // js values
            var mapDiv;

            var iconFeature = new ol.Feature({
                geometry: new ol.geom.Point(mapDefaultCenter),
            });

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
                <?= $mapName ?>.addLayer(layer);
            }
            
            if(!isSimpleMap) {
                jQuery('#'+ popupId).css('display', 'none');
                // feature select behavior - only one in the map
                <?= $mapName ?>.on('click', evt => {
                    var features = [];
                    var overlay;
                    <?= $mapName ?>.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        features.push(feature);
                    });
                    if(features.length) {
                        // create overlay
                        overlay = new ol.Overlay({
                            element: document.getElementById('popup-' + <?= json_encode($mapName) ?>),
                            autoPan: false,
                            position: evt.coordinate,
                            id: 'overlay-' + <?= json_encode($mapName) ?>
                        });
                        <?= $mapName ?>.addOverlay(overlay);
                        
                        // display popup
                        jQuery('#'+ 'popup-' + <?= json_encode($mapName) ?>).css('display','inline-block');
                        var html = "";
                        // add content html
                        if(!features[0].getProperties().adresse) {
                            html = '<strong>'+ overlayTitle + '</strong>';
                            html += '</br>'+ overlayText;
                        } else {
                            try {
                                var f = features[0].getProperties();
                                html = '<strong>'+ f.nom + '</strong>';
                                html += '</br>'+ f.adresse + ', ' + f.code_postal + ', ' + f.ville;
                                document.getElementById('popup-content-' + <?= json_encode($mapName) ?>).innerHTML = html;
                            } catch (e) {
                                html = "Contactez " + overlayTitle + " pour plus d'informations."
                            }
                        }
                        document.getElementById('popup-content-' + <?= json_encode($mapName) ?>).innerHTML = html;

                    } else {
                        // hide popup
                        if(<?= $mapName ?>.getOverlays().getArray().length) {
                            jQuery('#'+ popupId).css('display', 'none');
                            <?= $mapName ?>.getOverlays().getArray()[0].setPosition(undefined);
                            <?= $mapName ?>.getOverlays().getArray().splice(0, <?= $mapName ?>.getOverlays().getArray().length);
                        }
                    }
                });
            
                /**
                * INPUT BEHAVIOR
                 */
                jQuery('#'+ locator.id).css('display','');
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
                                var crs = response.crs.properties.name || '';
                                var features = [];
                                var res = new ol.format.GeoJSON().readFeatures( response );
                                
                                if(crs != 'EPSG:3857') {
                                    res.forEach(e => {
                                        if(e.getGeometry().getCoordinates().length > 0) {
                                            e.getGeometry().transform(inSrs, toSrs);
                                            features.push(e);
                                        }
                                    });
                                } else {
                                    features = res;
                                }
                                var layer = featuresToLayer(features);
                                <?= $mapName ?>.addLayer(layer)
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
                    
                    if(types.indexOf(cat) > -1 && cat.length === types.length){
                        var imgCat = img[types.indexOf(cat)];
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
                    getJsonLayer(dataUrl,'EPSG:4326', <?= $mapName ?>.getView().getProjection().getCode());
                }

            } else {
                jQuery('#'+ popupId).remove();
                <?= $mapName ?>.on('click', evt => {
                    // open contact page
                    if(openPageUrl) {
                        window.open(openPageUrl);
                    }                    
                })
            }
            </script>
        <?php
    }    
}
