
<div id="<?php echo "post-{$post->ID}"; ?>" class="hw-widget post-loop">
    <?php
        DPE_HTMLER::h2_e( $post->post_title, array( 'class' => 'main-title align-center') );

        DPE_HTMLER::p_e( $post->sub_title, array( 'class' => 'sub_title align-center' ) );

        $args = array(
            'posts_per_page' => $post->posts_per_page,
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<div id="posts" class="items-loop">';
            while ( $query->have_posts() ) {
                $query->the_post();
                ?>
                <article id="post-<?php the_id(); ?>" <?php post_class( 'post-excerpt' ); ?>>
                    <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="post-meta">
                        <span class="post-date"><i class="fas fa-calendar"></i> <?php tdt_hw_post_the_date(); ?></span>
                        <span class="post-categories">
                            <i class="fas fa-folder"></i>
                            <?php the_category( ', ' ); ?>
                        </span>
                    </div>
                    <div class="post-excerpt"><?php the_excerpt(); ?></div>
                    <div class="post-read-more">
                        <a href="<?php the_permalink(); ?>" class="button"><?php _e('Read More', 'tdt-hw'); ?></a>
                    </div>
                </article>
                <?php
            }
            echo '</div>';
            wp_reset_postdata();
        }
    ?>
</div>
