<?php
/**
 *  abstract class TDT_HW_Base
 *
 *  @package TDT_HW
 *  @author Thapelo Moeti
 */

/**
 *  Base class of how all the TDT_HW widgets should behave.
 */
abstract class TDT_HW_Widget_Base {
    /**
     * The ID of the Widget
     *
     * @var string $id
     */
    protected $id;

    /**
     * The singular name of the Widget
     *
     * @var string $singular_name
     */
    protected $singular_name;

    /**
     * The plural name of the Widget
     *
     * @var string $plural
     */
    protected $plural_name;

    /**
     *  The constructor
     */
    public function __construct() {
        $this->set_names();
        $this->init();

        // add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Making private properties accessible
     *
     *  @throw Exception Invalid property
     *
     *  @return mixed property value or null
     */
    public function __get( $property ) {
        $properties = get_object_vars( $this );

        if( isset( $properties[ $property ] ) ) {
            return $properties[ $property ];
        } else {
            throw new Exception( 'Trying to access a property that doesn\'t exist' );
            return null;
        }
    }

    /**
     *  Initialize
     */
    public function init() {
        $this->register_post_type();
        $this->register_widget();
        $this->register_shortcode();
        
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     *  Initialize at the backend
     */
    public function admin_menu() {
        add_submenu_page( 'tdt_home_widgets', $this->singular_name, $this->singular_name, 'manage_options', $this->get_slug(), array( $this, 'load_page' ) );
    }

    /**
     *  Figure out if we are loading one or many
     */
    public function load_page() {
        if( isset( $_GET[ 'id' ] ) ) {
            $this->load_one( $_GET[ 'id' ] );
        } else {
            $this->load_all();
        }
    }

    /**
     *  The slug of the widget
     *
     *  @return string  The unique slug
     */
    public function get_slug() {
        return 'hw_' . $this->id;
    }

    /**
     *  Returns the current widget_base url
     *
     *  @param array $args query args to append to the url.
     *
     *  @return string url to current widget_base
     */
    public function get_admin_url( $args = array() ) {
        $_args = array( 'page' => $this->get_slug() );

        if( ! is_array( $args ) )
            $args = array();

        $query_args = array_merge( $args, $_args );

        $url = isset( $_SERVER[ 'HTTPS' ] ) ? 'https://': 'http://';

        $url .= $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '?' . http_build_query( $query_args, 'tdt_hw_' );

        return $url;
    }

    /**
     *  Creating names out of ID
     */
    protected function set_names() {
        if( ! $this->singular_name )
            $this->singular_name = ucwords( str_replace( '_', ' ', $this->id ) );

        if( ! $this->plural_name )
            $this->plural_name = $this->singular_name . 's';
    }

    /**
     *  Register a unique post type for this widget
     */
    protected function register_post_type() {

        $args = array(
            'label' => $this->singular_name,
        );

        $args = (array) apply_filters( 'tdt_hw_rpt_args-' . $this->id, $args );

        register_post_type( $this->get_slug(), $args );
    }

    /**
     *  Register this as a widget
     */
    protected function register_widget() {

    }

    /**
     *  Register as a shortcode
     */
    protected function register_shortcode() {

    }

    /**
     *  Load all posts of this type
     */
    public function load_all() {
        HTMLER::h1_e( $this->plural_name );

        $all = get_posts( array( 'post_type' => $this->id ) );

        if( $all ) {
            echo '<ul>';
            foreach( $all as $post ) {
                $url = $this->get_admin_url( array( 'id' => $post->ID ) );
                $link = HTMLER::a( $post->post_title, array( 'href' => $url ) );

                HTMLER::li_raw_e( $link );
            }
            echo '</ul>';
        }
    }

    /**
     *  Load one gui
     *
     *  @param mixed $id the id (int) of the post to edit, or the null (string) to add new post.
     */
    public function load_one( $id ) {
        $html_form = new html_form( array( 'id' => $this->get_slug() ) );

        $fields = array_merge( array( 'id', 'title', 'content' ), $this->meta );

        $html_form->add_input( $fields );

        $html_form->update_type( array( 'id' => 'hidden', 'content' => 'textarea' ) );

        if( $id == 'null' ) {
            $title = __( 'Add New', 'tdt-hw' );
        } else {
            $id = (int) $id;

            $args = array(
                'p' => $id,
                'post_type' => $this->id
            );

            $query = new WP_Query( $args );

            if ( ! $query->found_posts ) {
                HTMLER::h1_e( 'Invalid Post ID', array( 'class' => 'error' ) );

                HTMLER::a_e( __( 'Back', 'tdt-hw' ), array( 'href' => $this->get_admin_url() ) );
                return;
            } else {
                $post = $query->post;

                $title = __( 'Update', 'tdt-hw' );

                $html_form->update_values(
                                        array(
                                            'id' => $id,
                                            'title' => $post->post_title,
                                            'content' => $post->post_content
                                        )
                                    );
            }
        }

        HTMLER::h1_e( $title );

        $html_form->print();
    }
}
