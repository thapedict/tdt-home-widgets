<?php
/**
 *  The autoload function
 *
 *  @package TDT_Home_Widgets
 *  @author Thapelo Moeti
 */

/**
 *  let's try and autoload the needed classes.
 *
 *  @param string $classname name of class.
 */
function tdt_hw_autoload( $classname ) {
    $classname = strtolower( $classname );

    $inc_folder = plugin_dir_path( __FILE__ ) . 'inc/';

    if( ! file_exists( $inc_folder . $classname . '.php' ) ) {
        $classname = str_replace( '_', '-', $classname );

        if( ! file_exists( $inc_folder . $classname . '.php' ) ) {
            $classname = 'class-' . $classname;
        }
    }

    if( file_exists( $inc_folder . $classname . '.php' ) ) {
        require_once $inc_folder . $classname . '.php';
    }
}
spl_autoload_register( 'tdt_hw_autoload' );
