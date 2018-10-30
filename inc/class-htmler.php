<?php
/**
 * HTMLER: Safe html (Because writing escaping functions is very tedious)
 *
 *  @package TDT_HW
 *  @author Thapelo Moeti
 *  @version 0.1
 */

/**
 *  WordPress specific html formatter and outputter
 */
class HTMLER {

    /**
     *  Safetly wraps content with tag and escapes everything
     *
     *  @param  string  $tag        the tag to wrap the content with.
     *  @param  string  $content    The content.
     *  @param  array   $attr       An array of attributes.
     *
     *  @return string  html string
     */
    public static function wrap( $tag, $content, $attr = array() ) {
        $attr = self::attr( $attr );

        return sprintf( '<%1$s%2$s>%3$s</%1$s>', $tag, $attr, esc_html( $content ) );
    }

    /**
     *  Wraps content with tag. Content not escaped
     *
     *  @param  string  $tag        the tag to wrap the content with.
     *  @param  string  $content    The content.
     *  @param  array   $attr       An array of attributes.
     *
     *  @return string  html string
     */
    public static function wrap_raw( $tag, $content, $attr = array() ) {
        $attr = self::attr( $attr );

        return sprintf( '<%1$s%2$s>%3$s</%1$s>', $tag, $attr, $content );
    }

    /**
     *  cleans up attributes
     *
     *  @param  array   $attr   array containing attributes.
     *
     *  @return string  $attr   safe attributes string
     */
    public static function attr( array $attr ) {
        if ( $attr ) {
            array_walk( $attr, 'esc_attr' );

            $_attr = array();

            foreach ( $attr as $k => $v ) {
                $_attr[] = preg_replace( '#[^\w-]*#', '', $k ) . '="' . $v . '"';
            }

            $attr = ' ' . implode( ' ', $_attr );
        } else {
            $attr = '';
        }

        return $attr;
    }

    /**
     *  Prints out wrapped content
     *
     *  @see HTMLER::wrap
     *
     *  @param  string  $tag        the tag to wrap the content with.
     *  @param  string  $content    The content.
     *  @param  array   $attr       An array of attributes.
     */
    public static function wrap_e( $tag, $content, $attr = array() ) {
        echo self::wrap( $tag, $content, $attr );
    }

    /**
     *  Prints out unsafe wrapped content
     *
     *  @see HTMLER::wrap_raw
     *
     *  @param  string  $tag        the tag to wrap the content with.
     *  @param  string  $content    The content.
     *  @param  array   $attr       An array of attributes.
     */
    public static function wrap_raw_e( $tag, $content, $attr = array() ) {
        echo self::wrap_raw( $tag, $content, $attr );
    }

    /**
     *  Handling of common HTML tags without expicitly declaring them
     *
     *  @param string $function_name the cunction being called.
     *  @param array $arguments the args passed to the function.
     *
     *  @throws Exception If trying to call invalid tag hooks.
     *
     *  @return string|void string of function doesn't print
     */
    public static function __callStatic( $function_name, $arguments ) {
        // validate tag? Let's assume it's a div
        // expecting: div, div_e, div_raw, div_raw_e
        if ( preg_match( '#^(?<tag>[a-z]+|h[1-6])$#', $function_name ) ) {
            $arguments = array_merge( array( $function_name ), $arguments );

            if ( self::valid_tag( $function_name ) ) {
                return call_user_func_array( 'HTMLER::wrap', $arguments );
            } elseif ( self::valid_nct( $function_name ) ) {
                return call_user_func_array( 'HTMLER::nct', $arguments );
            } else {
                throw new Exception( 'Invalid Tag: ' . $function_name );
            }
        } elseif ( preg_match( '#^(?<tag>[a-z]+|h[1-6])_e$#', $function_name, $match ) ) {
            $arguments = array_merge( array( $match[ 'tag' ] ), $arguments );

            if ( self::valid_tag( $match[ 'tag' ] ) ) {
                call_user_func_array( 'HTMLER::wrap_e', $arguments );
            } elseif ( self::valid_nct( $match[ 'tag' ] ) ) {
                call_user_func_array( 'HTMLER::nct_e', $arguments );
            } else {
                throw new Exception( 'Invalid Tag: ' . $function_name );
            }
        } elseif ( preg_match( '#^(?<tag>[a-z]+|h[1-6])_raw$#', $function_name, $match ) ) {
            if ( self::valid_tag( $match[ 'tag' ] ) ) {
                $arguments = array_merge( array( $match[ 'tag' ] ), $arguments );

                return call_user_func_array( 'HTMLER::wrap_raw', $arguments );
            }
        } elseif ( preg_match( '#^(?<tag>[a-z]+|h[1-6])_raw_e$#', $function_name, $match ) ) {
            if ( self::valid_tag( $match[ 'tag' ] ) ) {
                $arguments = array_merge( array( $match[ 'tag' ] ), $arguments );

                call_user_func_array( 'HTMLER::wrap_raw_e', $arguments );
            }
        } else {
            throw new Exception( "Invalid static function call: HTMLER::{$function_name}" );
        }
    }

    /**
     *  Allowing only a set of html tags
     *
     *  @param string $tag the name of the tag.
     *
     *  @return bool    true if valid
     */
    public static function valid_tag( $tag ) {
        $tags = array( 'a', 'b', 'blockquote', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'i', 'li', 'ol', 'ul', 'small', 'span' );

        return in_array( $tag, $tags );
    }

    /**
     *  Handling non-closing HTML tags
     *
     *  @param string $tag tag name.
     *  @param array $attr a list of attributes.
     *
     *  @return string formatted HTML
     */
    public static function nct( $tag, array $attr = array() ) {
        $attr = self::attr( $attr );

        return sprintf( '<%1$s%2$s />', $tag, $attr );
    }

    /**
     *  Printing of non-closing tags
     *
     *  @param string $tag the tag.
     *  @param array $attr the list of attributes.
     */
    public static function nct_e( $tag, array $attr = array() ) {
        echo self::nct( $tag, $attr );
    }

    /**
     *  Validating the non-closing tags
     *
     *  @param string $tag the tag to validate.
     *
     *  @return bool true if tag is valid, false if not
     */
    public static function valid_nct( $tag ) {
        $tags = array( 'img', 'input', 'hr' );

        return in_array( $tag, $tags );
    }
}