
<div id="<?php echo "post-{$post->ID}"; ?>" class="hw-widget testimonials">
<?php
    $attr = array(
        'class' => '',
    );

    // Title
    $attr[ 'class' ] = 'main-title align-center';
    DPE_HTMLER::h2_e( $post->post_title, $attr );

    // Sub-title
    $attr[ 'class' ] = 'sub-title align-center';
    DPE_HTMLER::h5_e( $post->sub_title, $attr );

    $testimonials = $post->messages;

    echo '<div class="items-loop">';
    foreach ( $testimonials as $t ) {
        $html = '';

        $__attr = array(
            'id' => 'image',
            'src' => $t[ 'image' ],
            'class' => 'img-circle',
        );
        $html .= HTMLER::img( $__attr );

        $__attr = array();

        $__attr[ 'id' ] = 'name';
        $html .= HTMLER::h4( $t[ 'name' ], $__attr );

        $__attr[ 'id' ] = 'company';
        $html .= HTMLER::h6( $t[ 'company' ], $__attr );

        $__attr[ 'id' ] = 'message';
        $html .= HTMLER::p( $t[ 'message' ], $__attr );

        echo '<div class="testimonial">', $html, '</div>';
    }
    echo '</div>';
?>
</div>
