<?php
/**
 *  Main class file
 *
 *  @package TDT_Home_Widgets
 *  @author Thapelo Moeti
 */

/**
 * main loader class
 */
class TDT_HW_Main {

    /**
     * A list of all the widgets
     *
     * @var array $widgets
     */
    private $widgets = array();

    /**
     *  Error list
     *
     *  @var array $error
     */
    private $error = array();

    /**
     *  construct
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     *  running basic plugin stuff
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'all_admin_notices', array( $this, 'admin_notices' ) );
        $this->load_all_widgets();

        add_action( 'admin_init', array( $this, 'admin_init' ) );
    }

    /**
     *  Plugin directory url
     */
    public static function url() {
        return plugin_dir_url( __DIR__ );
    }

    /**
     *  For the admin
     */
    public function admin_init() {
        $this->admin_scripts();
    }

    /**
     *  running basic plugin stuff
     */
    public function admin_menu() {
        $page_title = __( 'TDT Home Widgets', 'tdt-hw' );

        add_menu_page( $page_title, $page_title, 'manage_options', 'tdt_home_widgets', array( $this, 'main_admin_page' ), '', 60 );
    }

    public function admin_scripts() {
        wp_enqueue_script( 'tdt-hw-main', self::url() . '/assets/js/admin.js' );
        wp_enqueue_style( 'tdt-hw-main', self::url() . '/assets/css/admin.css' );
    }

    /**
     *  Main plugin page
     */
    public function main_admin_page() {
        self::open_main();

        do_action( 'all_admin_notices' );

        HTMLER::h1_e( __( 'Home Widgets', 'tdt-hw' ) );

        echo '<ul style="font-size:1.2em">';
        foreach ( $this->widgets as $w ) {
            $url = $w->get_admin_url();
            $link = HTMLER::a( $w->plural_name, array( 'href' => $url ) );
            HTMLER::li_raw_e( $link );
        }
        echo '</ul>';

        self::close_main();
    }

    /**
     *  Admin notices
     */
    public function admin_notices() {
        if ( ! $this->error ) {
            echo '<ul>';
            foreach ( $this->error as $error ) {
                HTMLER::li_e( $error, array( 'class' => 'error' ) );
            }
            echo '</ul>';
        }
    }

    /**
     *  Loading of all widgets
     */
    public function load_all_widgets() {
        $dirscan = new DirScan( TDT_HW_WIDGETS_PATH );

        $widgets = $dirscan->getDirectories();

        foreach ( $widgets as $w ) {
            $name = $w[ 'name' ];

            $main_widget_class = $this->widget_factory( $name );

            if ( $main_widget_class && is_a( $main_widget_class, 'TDT_HW_Widget_Base' ) ) {
                $this->widgets[] = $main_widget_class;
            }
        }
    }

    /**
     *  Creating main widgets class
     *
     *  @param  string  $widget_base  the class to create.
     *
     *  @throws Exception Class not found.
     *
     *  @return false on failure
     */
    public function widget_factory( $widget_base ) {
        $class = str_replace( '-', ' ', $widget_base );
        $class = ucwords( $class );
        $class = str_replace( ' ', '_', $class );
        $class = 'HW_' . $class;

        if( ! class_exists( $class ) ) {
            $class_file = TDT_HW_WIDGETS_PATH . DS . $widget_base . DS . $widget_base . '.php';

            if ( ! file_exists( $class_file ) ) {
                $this->error[] = "Cant load Widget Base file {$widget_base}: {$class_file}";
                return;
            }

            require_once $class_file;
        }

        if ( class_exists( $class ) ) {
            return new $class();
        } else {
            // ehm...
            throw new Exception( __( 'Class not found', 'tdt-hw' ) . ": {$class}" );
        }
    }

    /**
     *  The opening tags of the main section
     */
    public static function open_main() {
        echo '<div id="tdt-hw-admin-page">';
    }

    /**
     *  The closing tags of the main section
     */
    public static function close_main() {
        echo '</div>';
    }
}
