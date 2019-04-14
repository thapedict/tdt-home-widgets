

<div id="<?php echo "post-{$post->ID}"; ?>" class="hw-widget contact-details">
<?php
    if ( ! empty( $post->post_title ) ) {
        printf( '<h1 id="title">%s</h1>', esc_html( $post->post_title ) );
    }

    if ( ! empty( $post->sub_title ) ) {
        printf( '<p id="sub-title">%s</p>', esc_html( $post->sub_title ) );
    }

    $social_media = array(
        'facebook',
        'twitter',
        'instagram',
    );

    $links = '';

    foreach ( $social_media as $s ) {
        if ( ! empty( $post->$s ) ) {
            $links .= sprintf( '<div class="link-%1$s">
                                    <i class="fab fa-%1$s"></i> <a href="https://%1$s/%2$s">%2$s</a>
                                </div>', $s, esc_attr( $post->$s ) );
        }
    }

    if ( ! empty( $post->email ) ) {
        $links .= sprintf( '<div class="link-email">
                                <i class="fas fa-envelope"></i> <a class="link-email" mailto="%1$s">%1$s</a>
                            </div>', esc_attr( $post->email ) );
    }

    if ( ! empty( $post->phone ) ) {
        $links .= sprintf( '<div class="link-phone">
                                <i class="fas fa-phone"></i> <a class="link-phone" mailto="%1$s">%1$s</a>
                            </div>', esc_attr( $post->phone ) );
    }

    if ( ! empty( $post->phsical_address ) ) {
        $physical_address = explode( "\n", $post->physical_address );

        array_walk( $physical_address, 'esc_html' );

        $physical_address = '<address><span>' . implode( '</span><span>', $physical_address ) . '</span></address>';

        $links .= sprintf( '<div class="link-physical-address"><i class="fas fa-home"></i> %1$s</div>', $physical_address );
    }

    if ( ! empty( $post->postal_address ) ) {
        $postal_address = explode( "\n", $post->postal_address );

        array_walk( $postal_address, 'esc_html' );

        $postal_address = '<address><span>' . implode( '</span><span>', $postal_address ) . '</span></address>';

        $links .= sprintf( '<div class="link-postal-address"><i class="fas fa-envelope"></i> %1$s</div>', $postal_address );
    }

    printf( '<div class="details-loop">%s</div>', $links );
?>
</div>
