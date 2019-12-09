<?php

require_once('BaseWidgets.php');

class FootMapWidget extends BaseWidgets 
{
    /**
     * @override
     */
    function htmlComponent() {
        $this->setId();
        $idEl=$this->getId();
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
                // values from php wordpressgit stat
                let mapDefaultSearchZoom = 7;
                let mapDefaultCenter = <?= json_encode(get_option('default_coordinates'));?> ||'-385579.42,6244601.85';
                let mapDefaultZoom = <?= json_encode(get_option('default_zoom'));?> || 7;
                let overlayText = <?= json_encode(get_option('overlay_text'));?> || '';
                let overlayMarker = <?= json_encode(get_option('overlay_marker'));?> || '';
                let overlayTitle = <?= json_encode(get_option('overlay_title'));?> || '';
                let overlayHtmlContent = <?= json_encode(get_option('overlay_html'));?> || '';
                let dataUrl = <?= json_encode(get_option('data_file_url'));?> || '';
                const ID = <?= json_encode($this->getId());?> || <?= json_encode(uniqid());?>;
                // change all ids
                let mapId = 'map-' + ID;
                let overlayId = 'popup-' + ID;
                document.getElementById('map').id = mapId;
                document.getElementById('popup').id = overlayId;                
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
                    element: document.getElementById(overlayId),
                    autoPan: false,
                    offset: [0, -50]
                });
                map = new ol.Map({
                    target: mapId,
                    layers: [
                        new ol.layer.Tile({
                            source: new ol.source.OSM()
                        })
                    ],
                    overlays: overlayMarker ? [overlay] : [],
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

                // treat data;
                function treatData (jsonData) {
                    console.log(jsonData);
                }
                // get fata from json file request
                let data;
                if (dataUrl) {
                    let ajaxReq = new XMLHttpRequest();
                    ajaxReq.onload = function() {
                        if (this.readyState == 4 && this.status == 200) {                            
                            data = this.responseText ? JSON.parse(this.responseText) : null;
                        }
                    }
                    ajaxReq.open("POST", "distillerieDuVercors:v€rcORs26190@" + dataUrl);
                    //ajaxReq.send(null);
                }
            </script>
        </body>
        </html>     
        <?php
    }
}