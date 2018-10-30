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
        if ( isset( $_GET[ 'id' ] ) ) {
            $id = (int) $_GET[ 'id' ];

            $this->load_one( $id );
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
     *  Get url to add new page
     *
     *  @return string the add new url
     */
    public function get_edit_url( $id ) {
        $args = array(
            'page' => $this->get_slug(),
            'id' => $id
        );

        return $this->get_admin_url( $args );
    }

    /**
     *  Creating names out of ID
     */
    protected function set_names() {
        if ( ! $this->singular_name )
            $this->singular_name = ucwords( str_replace( '_', ' ', $this->id ) );

        if ( ! $this->plural_name )
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
        $this->the_add_new_button();

        $all = get_posts( array( 'post_type' => $this->id ) );

        if ( $all ) {
            echo '<ul>';
            foreach ( $all as $post ) {
                $url = $this->get_admin_url( array( 'id' => $post->ID ) );
                $link = HTMLER::a( $post->post_title, array( 'href' => $url ) );

                HTMLER::li_raw_e( $link );
            }
            echo '</ul>';
        } else {
            HTMLER::h3_e( __( 'No posts found', 'tdt-hw' ) );
        }
    }

    /**
     *  Load one gui
     *
     *  @param int $id the id (int) of the post to edit (0 if we want to add new post).
     */
    public function load_one( $id ) {
        HTMLER::h1_e( $this->singular_name );

        $html_form = new html_form( array( 'id' => $this->get_slug() ) );

        $fields = array_merge( array( 'ID', 'post_title', 'post_content' ), $this->meta );

        $html_form->add_input( $fields );

        $html_form->update_type( array( 'ID' => 'hidden', 'post_content' => 'textarea' ) );

        if ( empty( $_POST ) ) {
            if ( $id ) {
                $the_post = (array) $this->get_post( $id );
                $the_meta = array();

                foreach ( $this->meta as $m ) {
                    $the_meta[ $m ] = get_post_meta( $id, $m, true );
                }

                $html_form->update_values( $the_post );
                $html_form->update_values( $the_meta );
            }
        } else {
            $submitted = $html_form->get_post();

            $post_args = $this->get_post_args( $submitted );

            $_id = wp_insert_post( $post_args, true );

            if ( is_int( $_id ) ) {
                if ( ! $id ) { // creating a new post
                    $post_args[ 'ID' ] = $id = $_id;
                    $html_form->set_action( $this->get_edit_url( $_id ) );
                }

                $html_form->update_values( $post_args );

                $submitted_meta = array_intersect_key( $submitted, array_flip( $this->meta ) );

                foreach ( $submitted_meta as $k => $v ) {
                    update_post_meta( $id, $k, $v );
                }

                $html_form->update_values( $submitted_meta );
            } else {
                // WP_Error
                // How to debug this??
                // var_dump( $_id );
                HTMLER::h3_e( $_id->get_error_message() );
            }
        }

        if ( $id ) { // An ID means we are not at the and new page
            $this->the_add_new_button();
        }

        $html_form->print_form();
    }

    /**
     *  Get one post
     *
     *  @param int $id the id of the post.
     *
     *  @return WP_Post|false false on failure, or WP_Post on success
     */
    public function get_post( $id ) {
        $post = false;

        $id = intval( $id );

        $args = array(
            'p' => $id,
            'post_type' => $this->id
        );

        $query = new WP_Query( $args );

        if ( $query->found_posts ) {
            $post = $query->post;
        }

        return $post;
    }

    /**
     *  Get some default post args
     *
     *  @param array $args an optional array to merge with default post args.
     *
     *  @return array an array with default post args
     */
    public function get_post_args( array $args = array() ) {
        $default_args = array(
            'ID' => 0,
            'post_title' => '',
            'post_content' => '',
            'post_status' => 'publish',
            'ping_status' => 'closed'
        );

        if ( ! empty( $args ) ) {
            if ( isset( $args[ 'ID' ] ) ) {
                if ( is_numeric( $args[ 'ID' ] ) ) {
                    $args[ 'ID' ] = (int) $args[ 'ID' ];
                } else {
                    unset( $args[ 'ID' ] );
                }
            }

            $_args = array_intersect_key( $args, $default_args );

            $default_args = array_merge( $default_args, $_args );
        }

        $default_args[ 'post_type' ] = $this->id;

        return $default_args;
    }

    /**
     *  Prints outs the add new link
     */
    public function the_add_new_button() {
        $attr = array(
            'href' => $this->get_edit_url( 0 ),
            'class' => 'add-new-button button'
        );
        
        HTMLER::a_e( __( 'Add New', 'tdt-hw' ), $attr );
    }
}
