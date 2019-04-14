<?php
/**
 *    Default Call To Action Front-End View
 *    @since 0.1
 */

// ---------- +++ ---------- //

?>
<div id="<?php echo "post-{$post->ID}"; ?>" class="hw-widget call-to-action">
<?php
    $bg_style = '';

    if ( $post->image ) {
        $bg_style = sprintf( 'style="background-image:url(%s);background-size:cover;"', esc_url( $post->image ) );
    }
?>
    <div class="cta-wrap" <?php echo $bg_style; ?>>
        <div id="cta-text-wrap" class="align-center">
        <?php
            $class = array(
                'class' => 'cta-title',
            );
            DPE_HTMLER::h2_e( $post->post_title, $class );

            $class[ 'class' ] = 'cta-content';
            DPE_HTMLER::p_e( $post->post_content, $class );

            if ( ! empty( $post->read_more_text ) && ! empty( $post->read_more_url ) ) {
                printf( '<div class="cta-action"><a href="%s" class="button" id="action-button">%s</a></div>',
                esc_url( $post->read_more_url ), esc_html( $post->read_more_text ) );
            }
        ?>
        </div>
    </div>
</div>
