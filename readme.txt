=== Map Store Locator ===
Contributors: jdevsig
Donate link: https://jdev.fr/
Tags: map, OpenStreetMap, CSV, GeoJson, geotag, geolocation, OSM, OpenLayers, Open Layers, Open Street Map,POI, geocode, geotagging, location, store, retaillers, jdev
Requires at least: 4.9
Tested up to: 5.3.2
Stable tag: trunk
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Wordpress plugin to display customers and stores on a map.

== Description ==

This plugins contains two widgets :
- a simple and little one to be display on little place. When you click on this widget, it will load another page (to be choose in admin section)
- the second widget is a full map, based on OpenStreetMap and Nominatim. You will be able to see all retailers and search for place.
The idea of this widget is to have retailers informations stock on a geojson file, and be able to locate the nearest retailers for a customer.


Languages:

* English
* French

Licenses of the maps and place search:

* OpenStreetMap: [OpenStreetMap License](http://wiki.openstreetmap.org/wiki/OpenStreetMap_License)
* Nominatim Usage Policy: [Nominatim Usage Policy](https://operations.osmfoundation.org/policies/nominatim/)


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/msl_plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Map Store Locator screen to configure the plugin
4. Include widget in your theme or use MSL shortcode in a page


== Frequently Asked Questions ==

= Do I need any key or any registration to show maps on my wordpress ? =

No !

= Who can I contact if I want to add some new feature ? =

This is on opensource project, you can contribute to it, or you can contact https://jdev.fr if you want someone to do it for you.


== Changelog ==

= 1.0.0 =
This is the first version.

= 1.1.0 =
Fix widget position
Fix search input width
Fix shortcode param
Fix translation path

= 1.2.0 =
Fix input search width
Insert arrows to change the popup target for many feature at the same coordinates
Display popup at nearest(s) point(s)
Complete translation
Change Nominatim to Photon address API
Fix style for KML layer
Fix some default params values
Add param to config the result numbers
Zoom to results address extent