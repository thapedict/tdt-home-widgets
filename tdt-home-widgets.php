<?php
/**
Plugin Name: TDT Home Widgets
Plugin URI: https://thapedict.co.za/wordpress-plugins/tdt-home-widgets
Description: The only plugin you'll need to make themes designed by TDT Themes look awesome
Author: Thapelo Moeti
Author URI: https://thapedict.co.za/
Version: 0.0.1
Text Domain: tdt-hw
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

if ( ! defined( 'DS' ) ) {
    define( 'DS', DIRECTORY_SEPARATOR, true );
}

define( 'TDT_HW_VERSION', '0.0.1' );
define( 'TDT_HW_PATH', __DIR__, true );
define( 'TDT_HW_WIDGETS_PATH', TDT_HW_PATH . DS . 'widgets', true );

define( 'TDT_HW_CSS_URL', plugin_dir_url( __FILE__ ) . 'assets/css/', true );
define( 'TDT_HW_JS_URL', plugin_dir_url( __FILE__ ) . 'assets/js/', true );


require_once 'autoload.php';

$tdt_hw_main = new TDT_HW_Main();
