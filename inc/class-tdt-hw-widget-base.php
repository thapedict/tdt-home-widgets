<?php
/**
 *  abstract class TDT_HW_Base
 *
 *  @package TDT_HW
 *  @author Thapelo Moeti
 */

/**
 *  Base class of how all the TDT_HW widgets (admin backend objects) should behave.
 */
abstract class TDT_HW_Widget_Base {
    /**
     * The ID of the Widget
     *
     * @var string $id
     */
    protected $id;

    /**
     *  The hyphend id
     *
     *  @var string $base_id
     */
    protected $base_id;

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
     *  The admin notices
     *
     *  @var array $notices
     */
    protected $notices = array();

    /**
     *  The constructor
     */
    public function __construct() {
        $this->set_names();
        $this->init();
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
        $this->register_shortcode();

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'tdt_hw_notices', array( $this, 'show_notices' ) );
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
        do_action( 'all_admin_notices' );

        TDT_HW_Main::open_main();

        if ( isset( $_GET[ 'id' ] ) ) {
            $id = (int) $_GET[ 'id' ];

            if ( isset( $_GET[ 'action' ] ) && 'delete' === $_GET[ 'action' ] ) {
                $this->load_delete( $id );
            } else {
                $this->load_one( $id );
            }
        } else {
            $this->load_all();
        }

        TDT_HW_Main::close_main();
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
    public function get_admin_url( array $args = array() ) {
        $_args = array( 'page' => $this->get_slug() );

        if ( ! is_array( $args ) ) {
            $args = array();
        }

        $query_args = array_merge( $args, $_args );

        $url = isset( $_SERVER[ 'HTTPS' ] ) ? 'https://': 'http://';

        $url .= $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '?' . http_build_query( $query_args, 'tdt_hw_' );

        return $url;
    }

    /**
     *  Get url to add or edit post
     *
     *  @return string the edit post url
     */
    public function get_edit_url( $id ) {
        $args = array(
            'page' => $this->get_slug(),
            'id' => $id
        );

        return $this->get_admin_url( $args );
    }

    /**
     *  Get url to delete post
     *
     *  @return string the delete post url
     */
    public function get_delete_url( $id ) {
        $args = array(
            'page' => $this->get_slug(),
            'id' => $id,
            'action' => 'delete'
        );

        return $this->get_admin_url( $args );
    }

    /**
     *  Creating names out of ID
     */
    protected function set_names() {
        if ( ! $this->base_id ) {
            $this->base_id = str_replace( '_', '-', $this->id );
        }

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
    public function register_widget() {
        $widget_class = 'tdt_' . $this->id . '_widget';

        if ( class_exists( $widget_class ) ) {
            register_widget( $widget_class );
        }
    }

    /**
     *  Register as a shortcode
     */
    protected function register_shortcode() {
        add_shortcode( $this->id, array( $this, 'handle_shortcode' ) );
    }

    /**
     *  The shortcode handler
     *
     *  @param array $attr Passed shortcode attributes.
     *
     *  @return string formatted HTML after processing the shortcode
     */
    public function handle_shortcode( $attr, $content, $shortcode ) {
        $default_args = array(
            'id' => null,
            'template' => ''
        );

        $args = wp_parse_args( $attr, $default_args );

        if ( empty( $args[ 'id' ] ) ) {
            return;
        }

        $post = $this->get_post( intval( $args[ 'id' ] ) );

        ob_start();

        if ( $post ) {
            $this->load_widget_template( $post, $args[ 'template' ] );
            $this->include_frontend_scripts( $this->id );
        } else {
            echo '<!-- ', $this->id, ': Post Not Found -->';
        }

        return ob_get_clean();
    }

    /**
     *  Load all posts of this type
     */
    public function load_all() {
        // Check if we are deleting
        if ( ! empty( $_POST ) ) {
            if ( ! empty( $_POST[ 'ID' ] ) && ! empty( $_POST[ '_wp_nonce' ] ) ) {
                $id = (int) $_POST[ 'ID' ];

                $nonce_action = sprintf( 'delete-%s-%s', $this->id, $id );

                if ( wp_verify_nonce( $_POST[ '_wp_nonce' ], $nonce_action ) ) {

                    if ( wp_delete_post( $id ) ) {
                        $this->add_notice( $this->singular_name . __( ' Deleted', 'tdt-hw' ) );
                    } else {
                        $this->add_notice( __( 'Can\'t Delete ', 'tdt-hw' ) . $this->singular_name, 'error' );
                    }
                } else {
                    wp_nonce_ays();
                }
            }
        }

        $this->page_header();

        $all = get_posts( array( 'post_type' => $this->id ) );

        if ( $all ) {
            echo '<table>';

            echo '<tr><th>ID</th><th>Title</th><th>Actions</th></tr>';

            foreach ( $all as $post ) {
                echo '<tr>';

                $edit_url = $this->get_edit_url( $post->ID );
                $edit_link = HTMLER::a( __( 'Edit', 'tdt-hw' ), array( 'href' => $edit_url ) );

                $delete_url = $this->get_delete_url( $post->ID );
                $delete_link = HTMLER::a( __( 'Delete', 'tdt-hw' ), array( 'href' => $delete_url ) );

                HTMLER::td_raw_e( $post->ID );

                HTMLER::td_raw_e( $post->post_title );

                HTMLER::td_raw_e( $edit_link . ' | ' . $delete_link );

                echo '</tr>';
            }
            echo '</table>';
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
        $html_form = new TDT_HW_HTML_Form( array( 'id' => $this->get_slug() ) );

        $auto_fields = array(
            array(
                'name' => 'ID',
                'type' => 'hidden'
            ),
            array(
                'name' => 'post_title',
                'label' => __( 'Title', 'tdt-hw' )
            )
        ) ;

        $fields = array_merge( $auto_fields, $this->meta );

        $html_form->add_field( $fields );

        $widget_metas = $this->get_metas();
        $widget_meta_names = $this->get_metas( true );

        if ( empty( $_POST ) ) {
            if ( $id ) {
                $the_post = (array) $this->get_post( $id );
                $the_meta = array();

                foreach ( $widget_meta_names as $m ) {
                    $the_meta[ $m ] = get_post_meta( $id, $m, true );
                }

                $html_form->update_values( $the_post );
                $html_form->update_values( $the_meta );
            }
        } else {
            $submitted = $html_form->get_post();

            $submitted = stripslashes_deep( $submitted );

            $post_args = $this->get_post_args( $submitted );

            if ( ! empty( $post_args[ 'ID' ] ) ) {
                $_id = wp_update_post( $post_args, true );
            } else {
                $_id = wp_insert_post( $post_args, true );
            }

            if ( is_int( $_id ) ) {
                if ( ! $id ) { // creating a new post
                    $post_args[ 'ID' ] = $id = $_id;
                    $html_form->set_action( $this->get_edit_url( $_id ) );
                }

                $html_form->update_values( $post_args );

                $submitted_meta = array_intersect_key( $submitted, $html_form->get_field_names( $widget_metas, true ) );

                foreach ( $submitted_meta as $k => $v ) {
                    update_post_meta( $id, $k, $v );
                }
                $html_form->update_values( $submitted_meta );

                $this->add_notice( __( 'Updated', 'tdt-hw' ) );
            } else {
                // WP_Error
                // How to debug this??
                // var_dump( $_id );
                $this->add_notice( $_id->get_error_message(), 'error' );
            }
        }

        $add_button = (bool) $id;

        $this->page_header( false, $add_button );

        echo '<div class="col-2-1">';
        $html_form->print_form();
        $this->the_post_details( $id );
        echo '</div>';
    }

    /**
     *  Load delete confirmation page
     *
     *  @param int $id the post ID to delete.
     */
    public function load_delete( $id ) {
        $post = $this->get_post( $id );

        if ( ! $post ) {
            HTMLER::h2_e( __( 'Something Went Wrong!', 'tdt-hw' ) );
            return;
        }

        $args = array(
            'id' => 'delete-form-' . $this->id,
            'action' => $this->get_admin_url(),
            'input_defaults' => array(
                'type' => 'hidden'
            ),
            'submit_value' => __( 'Delete', 'tdt-hw' )
        );

        $html_form = new html_form( $args );

        $nonce_action = sprintf( 'delete-%s-%s', $this->id, $id );

        $inputs = array(
            array(
                'name' => 'ID',
                'value' => $id
            ),
            array(
                'name' => '_wp_nonce',
                'value' => wp_create_nonce( $nonce_action )
            )
        );

        $html_form->add_field( $inputs );

        HTMLER::h2_e( __( 'Are you sure you want to delete this?', 'tdt-hw' ) );

        $filter = array( 'created', 'modified' );

        $this->the_post_details( $id, $filter );

        $html_form->print_form();
    }

    /**
     *  Loading the required widget template
     *
     *  @param WP_Post $post The CPT to use in the template.
     *  @param string $template The template to use.
     */
    public function load_widget_template( $post, $template = '' ) {
        $theme_override = '';
        // check theme overrides
        $theme_folder = trailingslashit( get_template_directory() ) . 'home-widgets' . DIRECTORY_SEPARATOR;

        // 1. We first check if there's a theme override template for that specific post ID
        // 2. Then check for a theme override template with the matching template name
        // 3. Then lastly check if a theme override template with the widget name exists
        if ( file_exists( $theme_folder . "{$this->base_id}-{$post->ID}.php" ) ) {
            require $theme_folder . "{$this->base_id}-{$post->ID}.php";
        } elseif ( file_exists( $theme_folder . "{$this->base_id}-{$template}.php" ) ) {
            require $theme_folder . "{$this->base_id}-{$template}.php";
        } elseif ( file_exists( $theme_folder . $this->base_id . '.php' ) ) {
            require $theme_folder . $this->base_id . '.php';
        } else {
            require TDT_HW_WIDGETS_PATH . DIRECTORY_SEPARATOR . $this->base_id . DIRECTORY_SEPARATOR . 'tmpl/default.php';
        }
    }

    /**
     *  Print out other useful details about a post
     *
     *  @param int $id the post id of the post.
     *  @param array $filter a list of details to show.
     */
    public function the_post_details( $id, array $filter = array() ) {
        if ( ! $id ) {
            return;
        }

        $post = $this->get_post( $id );

        if ( ! $post ) {
            return;
        }

        if( empty( $filter ) ) {
            $filter = array(
                'title',
                'created',
                'modified',
                'delete'
            );
        }

        echo '<div id="post-details">';

        if ( in_array( 'title', $filter ) ) {
            HTMLER::h3_e( __( 'Other Details', 'tdt-hw' ) );
        }

        if ( in_array( 'created', $filter ) ) {
            $the_date = __( 'Date Created: ', 'tdt-hw' ) . $post->post_date;
            HTMLER::div_e( $the_date );
        }

        if ( in_array( 'modified', $filter ) ) {
            $modified_date = __( 'Last Modified: ', 'tdt-hw' ) . $post->post_modified;
            HTMLER::div_e( $modified_date );
        }

        if ( in_array( 'delete', $filter ) ) {
            $attr = array(
                'href' => $this->get_delete_url( $id ),
                'class' => 'button'
            );
            HTMLER::a_e( __( 'Delete', 'tdt-hw' ), $attr );
        }

        echo '</div>';
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
            'ping_status' => 'closed',
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

    /**
     *  Add a notice to display to the user
     *
     *  @param string $message the notice message.
     *  @param string $class the class to append.
     */
    public function add_notice( $message, $class = 'ok' ) {
        $this->notices[] = array(
            'message' => $message,
            'class' => $class
        );
    }

    /**
     *  Showing of all notices
     */
    public function show_notices() {
        if ( ! empty( $this->notices ) ) {
            echo '<div id="admin-notices">';

            foreach ( $this->notices as $notice ) {
                $class = array(
                    'class' => 'notice ' . $notice[ 'class' ]
                );

                HTMLER::div_e( $notice[ 'message' ], $class );
            }

            echo '</div>';
        }
    }

    /**
     *  Shortcut for page header
     *
     *  @param bool $plural Whether to print out the plural name of the widget base.
     *  @param bool $add_button Whether to add the 'Add New' button.
     */
    public function page_header( $plural = false, $add_button = true ) {
        echo ' <header>';

        if ( $plural ) {
            HTMLER::h1_e( $this->plural_name );
        } else {
            HTMLER::h1_e( $this->singular_name );
        }

        if ( $add_button ) {
            $this->the_add_new_button();
        }

        echo '</header>';

        do_action( 'tdt_hw_notices' );
    }

    /**
     *  Extract metas from meta list
     *
     *  @param bool $names_only Whether to only get names.
     *
     *  @return array $list of widget metas.
     */
    public function get_metas( $names_only = false ) {
        $metas = array();

        $html_form = new html_form;

        if ( $names_only ) {
            $metas = $html_form->get_field_names( $this->meta );
        } else {
            $metas = $html_form->array_field_fixer( $this->meta );
        }

        return $metas;
    }

    /**
     *  Include frontend styles and scripts.
     */
    public function include_frontend_scripts() {
        if ( file_exists( $this->get_path() . '/assets/css/main.css' ) ) {
            wp_enqueue_style( $this->base_id, $this->get_url() . 'assets/css/main.css', null, '1.0.1' );
        }
    }

    /**
     *  Get path of current widget.
     *
     *  @return string Current path.
     */
    public function get_path() {
        return TDT_HW_WIDGETS_PATH . DIRECTORY_SEPARATOR . $this->base_id . DIRECTORY_SEPARATOR;
    }

    /**
     *  Get url of current widget.
     *
     *  @return string Current url.
     */
    public function get_url() {
        return TDT_HW_WIDGETS_URL . "{$this->base_id}/";
    }
}
