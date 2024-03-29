<?php

/**
 * Main and uniq widget fot MSL plugin.
 */

class MslWidget extends WP_Widget {
    /**
     * To know if map html page was already loaded
     * Fix and avoid openLayers lib dupplication errors.
     */
    public $isMapReady;
    public $plugin_dir;
    
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct(
            'msl',
            __( 'Map Store Locator', 'WP-map-store-locator' ),
            array( 'description' => __( 'A plugin to display geographic data in a map.', 'WP-map-store-locator' ))
        );
    }

    /**
     * Call to register and init this widget.
     */
    function register() {
        add_action( 'widgets_init', array( $this, 'widgetInit' ) );
        add_action( 'init', array( $this, 'load_scripts' ) );
        add_shortcode( 'msl', array( $this, 'displayWidget' ) );
        add_action( 'wp_enqueue_scripts', array($this,'load_dep' ), 1 );
        
    }

    /**
     * Call by shortcode.
     * Get attributes from shortcode and call map to display into article, page, etc.
     */
    function displayWidget($atts = [], $content = null, $tag = ''){
        ob_start();
        $height = '15em';
        $width = '100%';
        $isSimpleMap;
        if (isset($atts['map'])) {
            $isSimpleMap = $atts['map'] === 'simple' ? 'on' : 'off';
        }
        if(isset($atts['height'])) {
            $height = $atts['height'];
        }
        if(isset($atts['width'])) {
            $width = $atts['width'];
        }
        $this->initHTML(array('msl_simple' => $isSimpleMap, 'msl_height' => $height, 'msl_width' => $width));
        return ob_get_clean();
    }

    /**
     * Init jQuery
     */
    function load_scripts() {
        wp_enqueue_script('jquery');
    }

    /**
     * Load scripts and styles
     */
    function load_dep() {
        wp_enqueue_script('ol_js', MSL_PLUGIN_URL."includes/lib/ol-6.1.1/js/ol.js", null, '6.1.1' );
        wp_enqueue_style( 'ol_css', MSL_PLUGIN_URL."includes/lib/ol-6.1.1/css/ol.css" );
        // use !important to override child theme css
        wp_enqueue_style( 'msl', MSL_PLUGIN_URL."includes/css/msl.css" );        
    }

    /**
     * Init widget
     * - not optional
     * - use by Wordpress
     */
    function widgetInit() {
        register_widget( $this );
    }

    /**
     * Create widget
     * - not optional
     * - use by Wordpress
     */
    public function widget( $args, $instance ) {
        extract($args);
        // HTML before widget
        echo $before_widget;        
        $this->initHTML($instance);
        // HTML after widget
        echo $after_widget;
    }  

    /**
     * Usefull to control or changes some values save by widget admin UI.
     * - use by Wordpress
     */
    function update($new_values, $old_values) {
        return $new_values;
    }

    /**
     * Usefull to create and display widget admin UI.
     * - Use by Wordpress
     */
    function form($instance) {
        ?>
            <!--Checkbox to set if map will be display with data and search input or as a simple map (baselayer and main marker to localize owner) -->
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $instance[ 'msl_simple' ], 'on' ); ?> id="<?php echo $this->get_field_id( 'msl_simple' ); ?>" name="<?php echo $this->get_field_name( 'msl_simple' ); ?>" /> 
                <label for="<?php echo $this->get_field_id( 'msl_simple' ); ?>"><?php echo __( 'Simple map', 'WP-map-store-locator' ) ?></label>
            </p>
            <!--Text field to set map height (px, em, %). Ex: 500px default on : 12em.-->
            <p>
                <label for="<?php echo $this->get_field_id("msl_height"); ?>"><?php echo __( 'Height (px, em, %)', 'WP-map-store-locator' ) ?></label>
                <input value="<?php echo $instance["msl_height"]; ?>" placeholder="<?php echo __( 'Height', 'WP-map-store-locator' ) ?>" type="text" name="<?php echo $this->get_field_name("msl_height"); ?>" id="<?php echo $this->get_field_id("msl_height"); ?>"/>
            </p>
            <!--Text field to set map width (px, em, %). Ex: 50%. Default on : auto.-->
            <p>
                <label for="<?php echo $this->get_field_id("msl_width"); ?>"><?php echo __( 'Width (px, em, %)', 'WP-map-store-locator' ) ?> </label>
                <input value="<?php echo $instance["msl_width"]; ?>" placeholder="<?php echo __( 'Width', 'WP-map-store-locator' ) ?>" type="text" name="<?php echo $this->get_field_name("msl_width"); ?>" id="<?php echo $this->get_field_id("msl_width"); ?>"/>
            </p>            
        <?php
    }


    /**
     * Init map core and load libraries. If already loaded, map is ready and next widgets directly create map.
     * Fix wrong behavior and dupplicate openLayers type errors.
     */
    function initHTML($instance) {
        if($this->isMapReady) {
            $this->createMap($instance);
        } else {
            
            ?>
            <!doctype html>
            <html lang="en">
            <head>
                <style>
                .ol-attribution.ol-uncollapsible {
                    display: none !important;
                }
                </style>
            </head>
            <body>
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
     * @param - array isSimpleMode
     */
    function createMap($isSimpleMode) {
        $this->ID = uniqid();
        $mapName = 'map' . $this->ID;
        $popupId = "popup-" . $mapName;
        $popupHtml = "popup-content-" . $mapName;
        $inputId =  "search-" . $mapName;
        ?>
            <!--Search input with autocompletion-->
            <div class="autocomplete">
                <input class="input-search input-text" id=<?= $inputId ?> type="text" name="photon" placeholder=" <?= __('Enter your adresse to get  the nearest sale point', 'WP-map-store-locator');?>">
            </div>
            <!--OpenLayers map div-->
            <div id=<?= $mapName ?>
                style="
                    height:<?= $isSimpleMode['msl_height'] ? $isSimpleMode['msl_height'] .';': '15em;' ;?>
                    width:<?= $isSimpleMode['msl_width'] ? $isSimpleMode['msl_width'] . ';' : '100%;' ;?>
                    ">
            </div>
            <!--Popup. Will be transform as openLayers Overlay and move to map div-->
            <div id=<?= $popupId ?> class="ol-popup">
                <div id=<?= $popupHtml ?>></div>
            </div>
          
            <script>
            // ids for popup
            var id = <?= json_encode($this->ID);?>;
            var widgetDivId = <?= json_encode($this->ID.'-msl');?>;
            var popupId =  <?= json_encode($popupId);?>;
            var popupHtml = <?= json_encode($popupHtml);?>;
            var inputId = <?= json_encode($inputId);?>;
            
            // set values from php 'options' table.
            var mapDefaultCenter = <?= json_encode(get_option('msl_default_coordinates'));?> ||'-385579.42,6244601.85';
            var mapDefaultZoom = <?= json_encode(get_option('msl_default_zoom'));?> || 7;
            var isSimpleMap = <?= json_encode($isSimpleMode['msl_simple']);?> === "on";
            var overlayText = <?= json_encode(get_option('msl_overlay_text'));?> || '';
            var overlayMarker = <?= json_encode(get_option('msl_overlay_marker'));?> || '';
            var overlaySize = <?= json_encode(get_option('msl_overlay_marker_size'));?> || 0.8;
            var overlayTitle = <?= json_encode(get_option('msl_overlay_title'));?> || '';
            var overlayHtmlContent = <?= json_encode(get_option('msl_overlay_html'));?> || '';
            var dataUrl = <?= json_encode(get_option('msl_data_file_url'));?> || '';
            var dataSize = <?= json_encode(get_option('msl_data_size'));?> || 0.8;
            var openPageUrl = <?= json_encode(get_option('msl_open_page'));?> || '';
            var img = [
                <?= json_encode(get_option('msl_data_png1_url'));?>,
                <?= json_encode(get_option('msl_data_png2_url'));?>,
                <?= json_encode(get_option('msl_data_png3_url'));?>];
            var types = [
                <?= json_encode(get_option('msl_data_png1_type'));?>,
                <?= json_encode(get_option('msl_data_png2_type'));?>,
                <?= json_encode(get_option('msl_data_png3_type'));?>];
            var searchMarker = <?= json_encode(get_option('msl_marker_search_url'));?> || '';
            var searchSize = <?= json_encode(get_option('msl_marker_search_size'));?> || 1;
            var maxResult = <?= json_encode(get_option('msl_marker_search_extent'));?> || 1;
            var biasScale = <?= json_encode(get_option('msl_marker_search_bias'));?> || 1.5;
            var popupIdx = 0;
            var selectedFeatures = [];
            var jsonFeatures;

            /**
            * Convert string coordinates to real array
            * @param val - string to array
            * return coordinate array readable by openLayers
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

            /**
            * Get default center set as string to array.
            */
            mapDefaultCenter = xyStringToArray(mapDefaultCenter);

            /**
            * Create openLayers Map.
            * <?= $mapName ?> allow to create many maps without ids clonficts.
            */
            var <?= $mapName ?> = new ol.Map({
                target: <?= json_encode($mapName);?>,
                layers: [
                    new ol.layer.Tile({
                        source: new ol.source.OSM()
                    })
                ],
                view: new ol.View({
                    center: mapDefaultCenter,
                    zoom: mapDefaultZoom,
                    projection: 'EPSG:3857'
                })
            });
            
            /**
            * Add a point to a map.
            * Point could be search marker or default owner marker.
            * @param xy - array as coordinates
            * @param marker - string url to get marker
            * @param size - number openLayers scale attribute
            * @param mapName - openLayers map object as target
            * @param id - string uniq id
            */
            function addPoint(xy, marker, size, mapName, id) {
                // create point feature
                var iconFeature = new ol.Feature({
                    geometry: new ol.geom.Point(xy),
                    id: id
                });                
                if (marker) { // avoid empty style and no ol.style.icon assertion error
                    // create style
                    var iconStyle = new ol.style.Style({
                        image: new ol.style.Icon({
                            anchor: [0.3, 40],
                            anchorXUnits: 'fraction',
                            anchorYUnits: 'pixels',
                            src: marker,
                            scale: size
                        })
                    });
                    // set style
                    iconFeature.setStyle(iconStyle);
                }                
                // remove layer if already exist
                if(getLayerById(id, mapName)) {
                    var searchLayer = getLayerById(id, mapName);
                    searchLayer.getSource().clear();
                    searchLayer.getSource().addFeature(iconFeature);
                } else {
                    // create layer with uniq id
                    var layer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: [
                                iconFeature
                            ]
                        }),
                        id: id
                    });
                    // add marker to map
                    mapName.addLayer(layer);                    
                }
            }
            
            // create home point to represent owner or main producer, etc.
            addPoint(mapDefaultCenter, overlayMarker, overlaySize, <?= $mapName ?>, 'home-feature');

            /**
             * Return layer if already exist or return false
             * @param id - string uniq layer id to search
             * @param map - openLayers map object as target
             * return false if not find or openLayers layer map object if exist.
             */            
            function getLayerById(id, map) {
                var find = false;
                map.getLayers().forEach(e => {
                    if(e.get('id') === id) {
                        find = e;
                    };
                });
                return find;
            }
            /**
             * Remove overlays from given map
             * @param map - ol.map
             */ 
            function destroyOverlays(map) {
                if(map.getOverlays().getArray().length) {
                    map.getOverlays().getArray()[0].setPosition(undefined);
                    map.getOverlays().getArray().splice(0, map.getOverlays().getArray().length);
                    jQuery('#'+ 'popup-content-' + map.get('target')).empty();
                }
            }
            /**
             * Manage events for fopup arrows buttons
             * @param features - array of features
             * @param map - ol.Map for this map
             */
             function navigPopup(features, map) {
                var maxIdx = features.length-1;
                var idx;
                // only for more than one features - display buttons
                if(maxIdx > 0 & popupIdx != undefined) {
                    document.getElementById('nextPopup').addEventListener('click', function(){
                        idx = popupIdx === maxIdx ? 0 : popupIdx + 1;
                        displayPopup(features, map, idx);
                    });
                    document.getElementById('previousPopup').addEventListener('click', function(){
                        idx = popupIdx === 0 ? maxIdx : popupIdx - 1;
                        displayPopup(features, map, idx);
                    });
                }
             }
            /**
            * Function to generate the full process to destroy, create and set map overlay.
            * This avoid wrong popup behavior and map id's conflict when popup is display into a bad map.
            * @param feature - ol.feature object to get id. This id was set by addPoint function.
            * @param map - target this ol.map object to display popup.
            */
            function displayPopup(features, map, idx) {
                popupIdx = idx;
                var feature = features[idx];
                var html = "";
                var contactMsg = "<?php echo __('Please, contact ', 'WP-map-store-locator')?>" + overlayTitle + "<?php echo __(' to get more details.', 'WP-map-store-locator')?>";
                if(feature.id_ != undefined && feature.id_ === 'search_marker') {
                    // never display popup on search marker without properties.
                    // avoid to display "undefined" values into popup.
                    return;
                }
                /* remove and destroy others overlay. We just need only 
                * one overlay by map that will be update.
                */
                jQuery('#'+ popupId).css('display', 'none');
                destroyOverlays(map);
                // create and add overlay to map
                var overlay = new ol.Overlay({
                    element: document.getElementById('popup-' + map.get('target')),
                    autoPan: false,
                    position: feature.getGeometry().getCoordinates(),
                    id: 'overlay-' + map.get('target')
                });
                map.addOverlay(overlay);
                // display popup initially hidden
                jQuery('#'+ 'popup-' + map.get('target')).css('display','inline-block');
                // add content html
                var props = feature.getProperties();
                if(props.id === 'home-feature') {
                    // display some content if marker is the owner or default retailer marker
                    if(overlayHtmlContent.length > 0) {
                        html = overlayHtmlContent;
                    } else if (overlayTitle.length && overlayText.length) {
                        html = '<strong>'+ overlayTitle + '</strong>';
                        html += '</br>'+ overlayText;
                    }
                } else {
                    // it's a json data feature, we display specific properties.
                    try {
                        if(!props.name) {
                            html = '<strong>'+ props.nom + '</strong>';
                            html += '</br>'+ props.adresse + ', ' + props.code_postal + ', ' + props.ville;
                        } else {
                            html = '<span>'+ props.name + '</span>';
                        }
                        if(features.length > 1) {
                            var lenFeatures = features.length;
                            html += `<br/><span style="text-align:center;">`;
                            html += `<button style="display:inline-block;" id="previousPopup" class="btn popupBtn"><</button>`;
                            html += popupIdx+1 + '/' + lenFeatures;
                            html += `<button id="nextPopup" style="display:inline-block;" class="btn popupBtn">></button></span>`;
                        }
                        document.getElementById('popup-content-' + map.get('target')).innerHTML = html;                                              
                    } catch (e) {
                        // display contact message
                        html = contactMsg;
                    }
                }
                if(html.indexOf('undefined') > -1) {
                    destroyOverlays(map);
                } else if(html.length < 1) {
                    // display contact message
                    html = contactMsg;
                }
                document.getElementById('popup-content-' + map.get('target')).innerHTML = html;
                
                // events for popup navigation buttons
                navigPopup(features, map);
                selectedFeatures = features;
            }
            
            /**
             * Add more UI and functions if simple option is unchecked from admin IHM
             * From shortcode, map='simple' need to be not write.
             */
            if(!isSimpleMap) {
                // display popup
                jQuery('#'+ popupId).css('display', 'none');
                // pre load data from JSON or GeoJSON file
                getJsonLayer(dataUrl, 'EPSG:4326', <?= $mapName ?>);
                // feature select behavior - only one event by map
                <?= $mapName ?>.on('click', evt => {
                    var features = [];
                    var overlay;
                    // get features under click coordinates
                    <?= $mapName ?>.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        features.push(feature);
                    });
                    if(features.length) {
                        // display popup for the first popup only
                        displayPopup(features, <?= $mapName ?>, 0);
                    } else {
                        // hide popup if no features was find. Allow to hide popup on simple map click.
                        if(<?= $mapName ?>.getOverlays().getArray().length) {
                            // hide popup
                            jQuery('#'+ popupId).css('display', 'none');
                            // force destroy all map overlay - fix wrong behavior
                            <?= $mapName ?>.getOverlays().getArray()[0].setPosition(undefined);
                            <?= $mapName ?>.getOverlays().getArray().splice(0, <?= $mapName ?>.getOverlays().getArray().length);
                        }
                    }
                });

                /**
                * Create layer from JSON url and akjax request. Add this layer to map.
                * @param jsonUrl - string to add layer to map from files URL.
                * @param inSrs - string to know incoming srs - not display as UI. Need to be EPSG:4326 for now.
                * @param map - map as target and avoir to display data on the wrong map.
                * @return openLayers ol.layer.vector object
                */
                function getJsonLayer(jsonUrl, inSrs, map) {
                    var layerCustomers;
                    var toSrs = map.getView().getProjection().getCode();
                    var id = 'layer-' + id;
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', jsonUrl);
                    xhr.onload = function() {
                        if (xhr.status === 200 && xhr.responseText) {
                            var response = xhr.responseText.length ? JSON.parse(xhr.responseText) : null;
                            if(response) {
                                var crs = response.crs.properties.name || '';
                                var features = [];
                                var res = new ol.format.GeoJSON().readFeatures( response );
                                var layerId = 'json-customers';
                                
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
                                jsonFeatures = features;
                            }
                        }
                        else {
                            console.log('fail request');
                        }
                    };
                    xhr.send();
                    return layerCustomers;
                }

                /**
                 * Create vector source and layer. And insert given features.
                 * @param features - objects reads as ol.feature from JSON, GeoJSON file.
                 * @param id - string as uniq id.
                 * @return ol.layer.vector object.
                 */
                function featuresToLayer(features, id, map) {
                    // remove layer if already exist
                    if(getLayerById(id,map)) {
                        map.removeLayer(getLayerById(id,map));
                    }
                    // crate layer and add features
                    let vectorSource = new ol.source.Vector({
                        features
                    });
                    const vectorLayer = new ol.layer.Vector({
                        source: vectorSource,
                        id: id,
                        style: getStyle
                    });
                    return vectorLayer;
                }

                /**
                *  Create style to categorize data by type.
                * @param - ol.feature object
                * @return ol.style.Style object.
                */
                function getStyle(feature) {
                    var style;
                    var cat = feature.get('code_categorie');
                    if(!cat && feature.get('styleUrl') && feature.get('styleUrl').indexOf('icon') >= 0) { // from kml
                        cat = feature.get('styleUrl').indexOf('icon-1502') < 0 ? 'CHR' : 'DET';
                    }
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

                /**
                * From features, calculate and zoom in
                * allow a param to adjust zoom out
                * @param - Array of ol.feature object
                * @param - map ol.map targeted
                * @param - int our float number
                */
                function zoomToFeatures(features, map, out) {
                    var extent = (new ol.source.Vector({
                        features: features
                    })).getExtent();
                    map.getView().fit(extent, map.getSize());
                    var zoom = map.getView().getZoom();
                    map.getView().setZoom(zoom-(out ? out : 0));
                }  

                /**
                * Create autocompletion behavior and HTML UI.
                * @param inp - input DOM element targeted
                */
                function autocomplete(inp) {
                    /*the autocomplete function takes two arguments,
                    the text field element and an array of possible autocompleted values:*/
                    var currentFocus;
                    /*execute a function when someone writes in the text field:*/
                    inp.addEventListener("input", function(e) {
                        var a, b, i, val = this.value;
                        /*close any already open lists of autocompleted values*/
                        closeAllLists();
                        if (!val) { return false;}
                        currentFocus = -1;
                        /*create a DIV element that will contain the items (values):*/
                        a = document.createElement("DIV");
                        a.setAttribute("id", this.id + "autocomplete-list");
                        a.setAttribute("class", "autocomplete-items");
                        /*append the DIV element as a child of the autocomplete container:*/
                        this.parentNode.appendChild(a);
                        /*Call API and display responses*/
                        search(val, a);
                    });
                    
                    /**
                    * Execute a function presses a key on the keyboard:
                    */
                    inp.addEventListener("keydown", function(e) {
                        var x = document.getElementById(this.id + "autocomplete-list");
                        if (x) x = x.getElementsByTagName("div");
                        if (e.keyCode == 40) {
                            /*If the arrow DOWN key is pressed,
                            increase the currentFocus variable:*/
                            currentFocus++;
                            /*and and make the current item more visible:*/
                            addActive(x);
                        } else if (e.keyCode == 38) { //up
                            /*If the arrow UP key is pressed,
                            decrease the currentFocus variable:*/
                            currentFocus--;
                            /*and and make the current item more visible:*/
                            addActive(x);
                        } else if (e.keyCode == 13) {
                            /*If the ENTER key is pressed, prevent the form from being submitted,*/
                            e.preventDefault();
                            if (currentFocus > -1) {
                            /*and simulate a click on the "active" item:*/
                            if (x) x[currentFocus].click();
                            }
                        }
                    });

                    /*
                    * Create div to append and display each results
                    * @param results - json parsed from ajax request response
                    * @param parent- DOM parent
                    */
                    function displayList(results, parent) {
                        var b;
                        var options = [];

                        // parse results
                        results.forEach(e => {

                            // create div for each
                            b = document.createElement("DIV");
                            var xy =  e.geometry.coordinates[0] + ',' + e.geometry.coordinates[1];
                            var add = [];

                            // create string content according to nominatim returns
                            var val = 
                                `${e.properties.street ? e.properties.street + ', ': e.properties.name + ', '}` + 
                                `${e.properties.city ? e.properties.city + ', ': ''}` +
                                `${e.properties.state ? e.properties.state + ', ':''}` +
                                `${e.properties.country ? e.properties.country:''}`
                            ;
                            // set popup content and create result marker feature
                            if(xy && val && options.indexOf(val) < 0) {
                                options.push(val);
                                b.innerHTML = "<span>" + val + "</span>";
                                b.innerHTML += "<input type='hidden' value='" + val + "' lonlat='" + xy + "'>";
                                b.innerHTML += "<input type='hidden' value='" + xy + "'>";
                                b.addEventListener("click", function(e) {
                                    /*insert the value for the autocomplete text field:*/
                                    inp.value = this.getElementsByTagName("span")[0].innerHTML
                                    inp.xy = this.getElementsByTagName("input")[1].value;
                                    let center = xyStringToArray(inp.xy);
                                    center = ol.proj.fromLonLat(center);
                                    <?= $mapName ?>.getView().setCenter(center);
                                    addPoint(center, searchMarker, searchSize, <?= $mapName ?>, "search_marker");
                                    
                                    // display nearest point
                                    var vector = featuresToLayer(jsonFeatures, '', <?= $mapName ?>);
                                    var source = vector ? vector.getSource() : '';

                                    // close the list of autocompleted values
                                    closeAllLists();

                                    /**
                                     * Now we search closests features to display around search marker result
                                     */
                                    if(source && maxResult) {
                                        var closestPoints = {};
                                        var closestDist = [];
                                        var resultPoints = [];
                                        var minDists;
                                        // get all distances
                                        source.getFeatures().forEach(e=>{
                                            // create line
                                            var props = e.getProperties();
                                            var line = new ol.geom.LineString([center, e.getGeometry().getCoordinates()]);
                                            // get line length
                                            var lineMeasure = line.getLength();
                                            if(closestDist.indexOf(lineMeasure)<0){
                                                closestDist.push(lineMeasure);
                                                closestPoints[lineMeasure.toString()] = [];
                                            }
                                            closestPoints[lineMeasure.toString()].push(e);
                                        })
                                        
                                        // order list to get closests distances first
                                        closestDist.sort(function(a, b) {
                                            return a - b
                                        });
                                        minDists = closestDist.slice(0,maxResult);

                                        /*  Now, parse layer features
                                            to get features according to distance */
                                        var extentPoint = []
                                        var popupPoints = [];
                                        minDists.forEach(dist => {
                                            closestPoints[dist].forEach(e => {
                                                if(extentPoint.length < maxResult) {
                                                    extentPoint.push(e);
                                                    if(!popupPoints.length) {
                                                        popupPoints.push(e);
                                                    } else {
                                                        var nearestGeom = e.getGeometry().getCoordinates().join('');
                                                        var compareGeom = popupPoints[0].getGeometry().getCoordinates().join('');
                                                        if(nearestGeom === compareGeom) {
                                                            popupPoints.push(e);
                                                        }
                                                    }
                                                }
                                            })
                                        })

                                        // clear layer and addFeatures
                                        vector = featuresToLayer(extentPoint, '', <?= $mapName ?>);
                                        <?= $mapName ?>.addLayer(vector);
                                        // show popup
                                        displayPopup(popupPoints, <?= $mapName ?>, 0);
                                        // show result marker
                                        var markerFeature = getLayerById("search_marker", <?= $mapName ?>).getSource().getFeatures()[0];                                      
                                        // adjust zoom and extent
                                        zoomToFeatures(extentPoint.concat([markerFeature]), <?= $mapName ?>, 1);                                        
                                    }
                                });
                                // append child input to result div
                                parent.appendChild(b);
                                closestPoints = null;
                                closestDist = null;
                                resultPoints = null;
                                minDists = null;
                            }
                        });
                    }

                    /**
                    * Behavior when user input letters. Call Nominatim api and get coordinates for the input text if exist.
                    * @param value - string from user letters input.
                    * @param parent - DOM input element as parent.
                    */
                    function search(value, parent) {
                        if(value &&value.length > 3) {
                            // Ajax request
                            var xhr = new XMLHttpRequest();
                            var url = 'https://photon.komoot.io/api/?limit=5&q='+ value + '&limit=5';
                            url += '&location_bias_scale=' + biasScale;
                            // Add priority from view center
                            var center = <?= $mapName ?>.getView().getCenter();
                            center = ol.proj.transform(center, 'EPSG:3857', 'EPSG:4326');
                            url += `&lon=${center[0]}&lat=${center[1]}`;
                            xhr.open('GET', url);
                            xhr.onload = function() {
                                if (xhr.status === 200 && xhr.responseText) {
                                    var response = xhr.responseText.length ? JSON.parse(xhr.responseText) : null;
                                    if(response) {
                                        displayList(response.features, parent);
                                    }
                                }
                                else {
                                    console.log('fail request');
                                }
                            };
                            xhr.send();
                        }
                    }
                    /**
                    * A function to classify an item as "active":
                    */
                    function addActive(x) {
                        if (!x) return false;
                        /*start by removing the "active" class on all items:*/
                        removeActive(x);
                        if (currentFocus >= x.length) currentFocus = 0;
                        if (currentFocus < 0) currentFocus = (x.length - 1);
                        /*add class "autocomplete-active":*/
                        x[currentFocus].classList.add("autocomplete-active");
                    }
                    /**
                    * A function to remove the "active" class from all autocomplete items:
                    */
                    function removeActive(x) {
                        for (var i = 0; i < x.length; i++) {
                        x[i].classList.remove("autocomplete-active");
                        }
                    }
                    /**
                    * Close all autocomplete lists in the document
                    */
                    function closeAllLists(elmnt) {
                        /*except the one passed as an argument:*/
                        var x = document.getElementsByClassName("autocomplete-items");
                        for (var i = 0; i < x.length; i++) {
                            if (elmnt != x[i] && elmnt != inp) {
                                x[i].parentNode.removeChild(x[i]);
                            }
                        }
                    }
                    /**
                    * Execute a function when someone clicks in the document:
                    */
                    document.addEventListener("click", function (e) {
                        closeAllLists(e.target);
                    });
                }
                autocomplete(document.getElementById("<?= $inputId ?>"));

            } else {
                // simple map is required
                jQuery('#'+ popupId).remove(); // Hide popup
                jQuery('#'+ "<?= $inputId ?>").remove(); // Remove input search
                <?= $mapName ?>.on('click', evt => {
                    // Open contact page
                    if(openPageUrl) {
                        window.location.replace(openPageUrl);
                    }                    
                })
            }
            </script>
        <?php
    }    
}
