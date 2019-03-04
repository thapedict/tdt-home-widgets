

<div id="<?php echo "post-{$post->ID}"; ?>" class="hw-widget featured-items">
    <?php
        $class = array(
            'class' => '',
        );

        // Title
        $class['class' ] = 'main-title align-center';
        DPE_HTMLER::h2_e( $post->post_title, $class );

        // Sub-title
        $class['class' ] = 'sub-title align-center';
        DPE_HTMLER::h5_e( $post->sub_title, $class );
    ?>
    <div id="items" class="items-loop">
        <?php
            $items = $post->items;

            $attr = array();

            foreach ( $items as $item ) {
                print '<div class="item">';

                $attr[ 'id' ] = 'title';
                HTMLER::h3_e( $item[ 'title' ], $attr );

                printf( '<span id="icon"><i class="%s"></i></span>', esc_attr( $item[ 'icon' ] ) );

                $attr[ 'id' ] = 'description';
                HTMLER::p_e( $item[ 'description' ], $attr );

                if( ! empty( $item[ 'read_more_text' ] ) && ! empty( $item[ 'read_more_url' ] ) )
                    printf( '<div id="button-wrap"><a id="action-button" class="button" href="%s">%s</a></div>',
                                esc_url( $item[ 'read_more_url' ] ), esc_html( $item[ 'read_more_text' ] ) );
                print '</div>';
            }
        ?>
    </div>
</div>
