<?php
/**
 * DPE_HTMLER: Do Not Print Empty Values HTMLER
 *
 *  @package TDT_HW
 *  @author Thapelo Moeti
 *  @version 0.1
 */

/**
 *  Main class
 */
class DPE_HTMLER extends HTMLER {
    /**
     *  Override method to check for empty values before print.
     *
     *  @see HTMLER::__callStatic
     */
    public static function __callStatic( $function_name, $arguments ) {
        if( ! empty( $arguments[ 0 ] ) ) {
            parent::__callStatic( $function_name, $arguments );
        }
    }
}
