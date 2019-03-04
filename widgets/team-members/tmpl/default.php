
<div id="<?php echo "post-{$post->ID}"; ?>" class="hw-widget team-members">
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
?>
    <div id="members" class="team-members-loop ts-ns-3">
        <?php
            $members = $post->members;

            foreach ( $members as $member ) {
                print '<div class="member">';
                    printf( '<h3 id="title">%s</h3>', esc_html( $member[ 'names' ] ) );
                    printf( '<p id="position">%s</p>', esc_html( $member[ 'position' ] ) );
                    printf( '<div id="avatar"><img src="%s" /></div>', esc_url( $member[ 'avatar' ] ) );

                    print '<div class="social-links align-center">';
                        if( ! empty( $member[ 'link_facebook' ] ) ) {
                            printf( '<a class="social-media-link" id="facebook" href="%s"><i class="fab fa-facebook"></i></a>',
                                        esc_url( $member[ 'link_facebook' ] ) );
                        }
                        if( ! empty( $member[ 'link_twitter' ] ) ) {
                            printf( '<a class="social-media-link" id="twitter" href="%s"><i class="fab fa-twitter"></i></a>',
                                        esc_url( $member[ 'link_twitter' ] ) );
                        }
                        if( ! empty( $member[ 'link_linkedin' ] ) ) {
                            printf( '<a class="social-media-link" id="linkedin" href="%s"><i class="fab fa-linkedin"></i></a>',
                                        esc_url( $member[ 'link_linkedin' ] ) );
                        }
                    print '</div>';

                    printf( '<p id="bio">%s</p>', esc_html( $member[ 'bio' ] ) );
                print '</div>';
            }
        ?>
    </div>
</div>
