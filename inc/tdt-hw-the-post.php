<?php

/**
 *  Because the_date doesn't print the same date more than once
 */
function tdt_hw_post_the_date() {
    echo get_the_date();
}

/**
 * Because the_category doesn't print enough.
 *
 *  @param bool $print Optional. Whether to print or return.
 *
 *  @return string When $print is set to false, the categories HTML will be returned.
 */
function tdt_hw_post_the_category( $print = true ) {
    $cats = get_the_category();

    if ( $cats ) {
        $str = array();
        foreach ( $cats as $c ) {
            $link = esc_url( get_category_link( $c->term_id ) );
            $name = esc_html( $c->name );
            $title = esc_attr( __('View all in', 'tdt-hw') . " {$name}" );
            $str[] = sprintf(
                '<span class="category">
                    <a href="%1$s" title="%2$s" id="category-%3$s">%4$s</a>
                </span>',
                $link, $title, $c->term_id, $name
            );
        }
        $str = implode( ', ', $str );
    } else {
        $str = '';
    }

    if ( $print ) {
        print $str;
    } else {
        return $str;
    }
}

