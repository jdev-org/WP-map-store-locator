<?php

require_once('BaseWidgets.php');

class MapWidget extends BaseWidgets {
    
    /**
     * @override
     */
    function htmlComponent($instance) {
        $this->map($instance);
    }

    function map($instance) {
        $this->$mapReady = false;
        $this->setId();
        /**
         * Create map or load html page
         */
        if($this->mapReady) {
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
                <title>OpenLayers example</title>
            </head>
            <body>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/ol-6.1.1/js/ol.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/popper/popper.min.js'?>"></script>
                <script src="<?= plugins_url() . '/WP-map-store-locator/includes/lib/bootstrap-4/js/bootstrap.min.js'?>"></script>
            </body>
            </html>     
            <?php
            $this->mapReady = true;
            // now, create map
            $this->createMap($instance);
        }
    }

    /**
     * Render map as component
     */
    function createMap($instance) {
        $this->ID = uniqid();
        $this->simple = $instance["wp_isSimpleMap"];
        
        ?>
            <div class="container">
                <div class="row">                
                    <div id=<?= json_encode($this->ID);?> class="col-sm-12 p-0" style="height: 12em;"></div>
                </div>
            </div>
            <div id="popup-<?= json_encode($this->ID);?>" class="ol-popup">
                <div id="popup-content-<?= json_encode($this->ID);?>"></div>
            </div>
            <script>
            var mapDefaultCenter = <?= json_encode(get_option('default_coordinates'));?> ||'-385579.42,6244601.85';
            var mapDefaultZoom = <?= json_encode(get_option('default_zoom'));?> || 7;

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
            var isSimple = <?= json_encode($instance);?>;
            if(isSimple) {
                var mapId = <?= json_encode($this->ID);?>;
                var popupId = 'popup-' + mapId;
                var popupHtml = 'popup-content-' + mapId;
                var overlayText = <?= json_encode(get_option('overlay_text'));?> || '';
                var overlayMarker = <?= json_encode(get_option('overlay_marker'));?> || '';
                var overlayTitle = <?= json_encode(get_option('overlay_title'));?> || '';
                var overlayHtmlContent = <?= json_encode(get_option('overlay_html'));?> || '';
                var dataUrl = <?= json_encode(get_option('data_file_url'));?> || '';
                
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
                            src: overlayMarker
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
                var content = document.getElementById(popupHtml);
                
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
                    var features = [];
                    var coordinate = evt.coordinate;
                    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        features.push(feature);
                    });
                    if (features.length > 0 && content) {
                        showPopup(mapDefaultCenter);
                    } else {
                        hidePopup();
                    }
                });

            }
            </script>
        <?php
    }

    /**
     * @override
     */
    function form($instance) {
        ?>  
            <p>
                <input id="<?php esc_attr( $this->get_field_id( 'wp_isSimpleMap' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wp_isSimpleMap' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox ); ?> />
                <label for="<?php esc_attr( $this->get_field_id( 'wp_isSimpleMap' ) ); ?>"><?php _e( 'wp_isSimpleMap', 'text_domain' ); ?></label>
            </p>            
            <p>
                <label for="<?php echo $this->get_field_id("mapCenterXY"); ?>">Center: </label>
                <input placeholder="Default view coordinates" type="text" name="<?php echo $this->get_field_name("mapCenterXY"); ?>" id="<?php echo $this->get_field_id("mapCenterXY"); ?>"/>
            </p>            
        <?php
    }
}