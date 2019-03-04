<?php
/**
 * TDT_HW_HTML_Form
 *
 *  @package TDT_HW
 *  @author Thapelo Moeti
 *  @version 0.1
 */

/**
 *  HTML_Form class extender to support WordPress controls
 */
class TDT_HW_HTML_Form extends HTML_Form {

    /**
     *  Tells if the form has a media selector input field.
     *
     *  @var bool $has_media_button
     */
    protected $has_media_button = false;

    /**
     *  Set whether all icons html is printed or not.
     *
     *  @var bool $printed_all_icons_html
     */
    private static $printed_all_icons_html = false;

    /**
     *  Extend the class through constructor.
     *
     *  @param array $options Form init options.
     */
    public function __construct( array $options = array() ) {
        $image_picker = array(
            'name' => 'image_picker',
            'defaults' => array( $this, 'image_picker_defaults' ),
            'html' => array( $this, 'image_picker_html' ),
        );

        $this->add_field_type( $image_picker );

        $icon_picker = array(
            'name' => 'icon_picker',
            'defaults' => array( $this, 'icon_picker_defaults' ),
            'html' => array( $this, 'icon_picker_html' ),
        );

        $this->add_field_type( $icon_picker );

        parent::__construct( $options );
    }

    /**
     *  Get's defaults for Icon Picker
     *
     *  @param array|string $options Options to merge with defaults.
     *
     *  @return array Image Picker defaults.
     */
    protected function image_picker_defaults( $options ) {
        $defaults = array(
            'auto_label' => true,
        );

        if ( ! is_array( $options ) ) {
            $options = array(
                'name' => $options,
            );
        }

        $defaults = array_merge( $this->field_defaults, $defaults );

        $options = array_intersect_key( $options, $defaults );

        $options[ 'type' ] = 'image_picker';

        return array_merge( $defaults, $options );
    }

    /**
     *  Get HTML for image_picker field
     *
     *  @param array $field Field properties.
     *
     *  @return string HTML string
     */
    protected function image_picker_html( array $field ) {
        $field = $this->image_picker_defaults( $field );

        $attr = array(
            'class' => 'tdt-hw-image-picker button',
            'for' => $field[ 'name' ],
        );

        $html = HTMLER::a( $this->get_label( $field, true ), $attr );

        // If we have a value, let's try and get the thumbnail
        $thumbnail = empty( $field[ 'value' ] ) ? '' : $this->get_thumbnail_url( $field[ 'value' ] );

        $attr = array(
            'id' => "preview-{$field['name']}",
            'src' => $thumbnail,
        );

        $html .= HTMLER::img( $attr );

        $hidden_input = array_merge( $field, array( 'type' => 'hidden' ) );
        $html .= $this->get_field_html( $hidden_input );

        // always wrap
        $html = $this->wrap_field( $field, $html );

        $this->include_scripts();

        return $html;
    }

    /**
     *  Get's defaults for Icon Picker
     *
     *  @param array|string $options Options to merge with defaults.
     *
     *  @return array with defaults
     */
    protected function icon_picker_defaults( $options ) {
        $defaults = array(
            'auto_label' => true,
        );

        return $this->parse_args( $defaults, $options, 'icon_picker' );
    }

    /**
     *  Get HTML for icon_picker field
     *
     *  @param array $field Field properties.
     *
     *  @return string HTML string
     */
    protected function icon_picker_html( array $field ) {
        $field = $this->icon_picker_defaults( $field );

        // button
        $attr = array(
            'class' => 'tdt-hw-icon-picker button',
            'for' => $field[ 'name' ],
        );

        $html = HTMLER::a( $this->get_label( $field, true ), $attr );

        $icon = HTMLER::i( '', array( 'class' => $field[ 'value' ] ) );

        // icon
        $attr = array(
            'id' => "preview-{$field['name']}",
            'class' => 'icon-preview',
        );

        $html .= HTMLER::span_raw( $icon, $attr );

        $hidden_input = array_merge( $field, array( 'type' => 'hidden' ) );

        $hidden_input = $this->field_defaults( 'hidden', $hidden_input );

        $html .= $this->get_field_html( $hidden_input );

        // always wrap
        $html = $this->wrap_field( $field, $html );

        $this->has_media_button = true;

        return $html;
    }

    /**
     *  Get HTML of icon picker all icon list.
     *
     *  @return string
     */
    public function icon_picker_all_icons_html() {
        if ( self::$printed_all_icons_html ) {
            return;
        }

        self::$printed_all_icons_html = true;

        $icons = file_get_contents( TDT_HW_PATH . '/assets/js/icons.json' );

        $icons = json_decode( $icons, true );

        $html = '';

        foreach ( $icons as $k => $v ) {
            $style = 'fab';

            if ( in_array( 'solid', $v[ 'styles' ] ) ) {
                $style = 'fas';
            } elseif ( in_array( 'regular', $v[ 'styles' ] ) ) {
                $style = 'far';
            }

            $html .= sprintf( '<i class="%s fa-%s"></i>', $style, $k );
        }

        $attr = array(
            'id' => 'tdt-hw-icon-picker-dialog',
            'class' => 'fa-icons icon-picker-icons',
        );

        $html = $this->wrap( 'div', $html, $attr );

        $html .= '<div id="tdt-hw-icon-picker-lightbox"></div>';

        return $html;
    }

    /**
     *  Add scripts to work with WordPress.
     */
    public function include_scripts() {
        wp_enqueue_media();

        wp_enqueue_style( 'fontawesome', TDT_HW_CSS_URL . 'all.min.css', array(), '5.2.0' );
    }

    /**
     *  Include the necessary scripts and HTML when the form is required.
     *
     *  @return string Form HTML.
     */
    public function get_form() {
        $html = parent::get_form();

        if ( $this->has_media_button ) {
            $this->include_scripts();
            $html .= $this->icon_picker_all_icons_html();
        }

        return $html;
    }

    /**
     *  Get a url to what would normally be the thumbnail url.
     *
     *  @param string $url The url to format.
     *
     *  @return string The thumbnail url.
     */
    private function get_thumbnail_url( $url ) {
        $index = strripos( $url, '.' );
        $name = substr( $url, 0, $index );
        $ext = substr( $url, $index );

        return "{$name}-150x150$ext";
    }
}
