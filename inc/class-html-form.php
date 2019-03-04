<?php
/**
 *  HTML_Form: Deal with HTML forms without the mess of HTML
 *
 *  @package HTML
 *  @author Thapelo Moeti
 *  @version 1.0.1
 */

/**
 * The HTML Form class.
 */
class HTML_Form {

    /**
     *    the id of the form
     *
     *    @var string $id
     */
    private $id = '';

    /**
     *    the submit method of the form
     *
     *    @var string $method
     */
    private $method = 'POST';

    /**
     *    the submit url of the form
     *
     *    @var string $action
     */
    private $action = '';

    /**
     *    if the form uploads files
     *
     *    @var boolean $is_multipart
     */
    private $is_multipart = false;

    /**
     *    holds all form fields
     *
     *    @var array $fields
     */
    private $fields = array();

    /**
     *    holds all valid field types
     *
     *    @var array $valid_field_types
     */
    private $valid_field_types = array();

    /**
     *  holds general field defaults
     *
     *  @var array $field_defaults
     */
    protected $field_defaults = array(
        'name' => '',
        'type' => 'text',
        'value' => '',
        'wrap_field' => '',
    );

    /**
     *  holds label defaults
     *
     *  @var array $label_defaults
     */
    protected $label_defaults = array(
        'label' => '',
        'sr_only' => false,
        'auto_label' => true,
        'placeholder' => '',
        'label_is_placeholder' => true,
    );

    /**
     *    the default properties of the submit button.
     *
     *    @var array $submit_defaults
     */
    protected $submit_defaults = array(
        'name' => 'submit',
        'type' => 'submit',
        'value' => 'Submit',
    );

    /**
     *    holds all input types
     *
     *    @var array $input_types
     */
    protected $input_types = array( 'text', 'hidden', 'email', 'search', 'password', 'url', 'radio', 'checkbox', 'number' );

    /**
     *    the constructor
     *
     *    @param array $options Form setup options.
     */
    public function __construct( array $options = array() ) {
        if( isset( $options[ 'id' ] ) ) {
            $this->id = $options[ 'id' ];
        }

        if( isset( $options[ 'method' ] ) ) {
            $this->method = $options[ 'method' ];
        }

        if( isset( $options[ 'action' ] ) ) {
            $this->action = $options[ 'action' ];
        }

        if( isset( $options[ 'is_multipart' ] ) ) {
            $this->is_multipart = $options[ 'is_multipart' ];
        }

        if( isset( $options[ 'submit_value' ] ) ) {
            $this->submit_defaults[ 'value' ] = $options[ 'submit_value' ];
        }

        $this->add_default_fields();
    }

    /**
     *  When this class is used as a string
     *
     *  @see html_form::get_form
     *
     *  @return string the HTML version of the form
     */
    public function __toString() {
        return $this->get_form();
    }

    /**
     *  Get the HTML string of the form
     *
     *  @return string the HTML version of the form
     */
    public function get_form() {
        if( empty( $this->fields ) )
            return '';

        $form = $this->form_start();

        // fields
        foreach( $this->fields as $f ) {
            $form .= call_user_func_array( $this->valid_field_types[ $f['type'] ][ 'html' ], array( $f ) );
        }

        $form .= $this->form_end();

        return $form;
    }

    /**
     *  Print out the HTML form
     */
    public function print_form() {
        print $this->get_form();
    }

    /**
     *  Add field type.
     *
     *  @param array $properties Field type properties.
     */
    public function add_field_type( array $properties ) {
        $def_prop = array(
            'name' => '',
            'defaults' => '',
            'html' => '',
        );

        if( ! array_diff_key( $def_prop, $properties ) ) {
            $this->valid_field_types[ $properties[ 'name' ] ] = $properties;
        } else {
            trigger_error( 'html_form::add_field_type Invalid properties passed', E_USER_ERROR );
        }
    }

    /**
     *  Remove field type.
     *
     *  @param string $type Field type.
     */
    public function remove_field_type( $type ) {
        if( $this->valid_field_type( $type ) ) {
            // remove fields
            foreach( $this->fields as $k => $v ) {
                if( $v[ 'type' ] === $type ) {
                    unset( $this->fields[ $k ] );
                }
            }

            unset( $this->valid_field_types[ $type ] );
        }
    }

    /**
     *  Add default field types.
     */
    private function add_default_fields() {
        $input = array(
            'name' => 'text',
            'defaults' => array( $this, 'input_defaults' ),
            'html' => array( $this, 'input_html' ),
        );

        $this->add_field_type( $input );

        $other_inputs = array( 'hidden', 'email', 'search', 'password', 'url', 'radio', 'checkbox', 'number' );

        foreach( $other_inputs as $i ) {
            $input[ 'name' ] = $i;

            $this->add_field_type( $input );
        }

        $textarea = array(
            'name' => 'textarea',
            'defaults' => array( $this, 'textarea_defaults' ),
            'html' => array( $this, 'textarea_html' ),
        );

        $this->add_field_type( $textarea );

        $repeat_group = array(
            'name' => 'repeat_group',
            'defaults' => array( $this, 'repeat_group_defaults' ),
            'html' => array( $this, 'repeat_group_html' ),
            'get_post' => array( $this, 'repeat_group_get_post' ),
            'update_value' => array( $this, 'repeat_group_update_value' ),
        );

        $this->add_field_type( $repeat_group );

        $submit = array(
            'name' => 'submit',
            'defaults' => array( $this, 'submit_defaults' ),
            'html' => array( $this, 'submit_html' ),
        );

        $this->add_field_type( $submit );
    }

    /**
     *  Builds up HTML of escaped attributes.
     *
     *  @param array $attr Attributes to escape.
     *
     *  @return string Escaped attributes HTML.
     */
    protected function get_attr( array $attr ) {
        $html = '';

        foreach( $attr as $k => $v ) {
            $v = htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );

            $html .= sprintf( ' %s="%s"', $k, $v );
        }

        return trim( $html );
    }

    /**
     *  Wrap an input field
     *
     *  @param string $wrap_with The tag to wrap with.
     *  @param string $string The input field to wrap.
     *  @param array $attr Attributes to add to the wrapping tag.
     *
     *  @return string Wrapped content
     */
    public function wrap( $wrap_with, $string, array $attr = array() ) {
        $attributes = '';
        if( is_array( $attr ) ) {
            foreach( $attr as $k => $v ) {
                if ( is_int( $k ) )
                    continue;

                $attributes .= sprintf( ' %s="%s"', $k, htmlentities( $v ) );
            }
        }

        $str = sprintf( '<%2$s%3$s>%1$s</%2$s>', $string, $wrap_with, $attributes );

        return $str;
    }

    /**
     *  Provide a unified way to wrap a field.
     *
     *  @param array $field The field properties.
     *  @param string $html The HTML of the field.
     *
     *  @return string The HTML of the wrapped field.
     */
    public function wrap_field( array $field, $html ) {
        $attr = array(
            'id' => "{$field['name']}-field",
            'class' => "field-wrapper type-{$field['type']}",
        );

        return $this->wrap( 'div', $html, $attr );
    }

    /**
     *  Gets the  html label, or just the text
     *
     *  @param array $input The input to extract the label from.
     *  @param bool $text_only whether to return the text only or an html element.
     *
     *  @return string with label text
     */
    public function get_label( array $input, $text_only = false ) {
        $label = '';

        $label_defaults = array(
            'label' => '',
            'sr_only' => false,
            'auto_label' => true
        );

        $input = array_merge( $label_defaults, $input );

        // filter to only fields that can have labels
        if( in_array( $input[ 'type' ], array( 'hidden', 'submit', 'reset' ) ) )
            return $label;

        // filter to only fields that can have labels
        if( in_array( $input[ 'type' ], array( 'radio', 'checkbox' ) ) )
            $input[ 'sr_only' ] = false;

        if( empty( $input[ 'label' ] ) && $input[ 'auto_label' ] ) {
            $label = preg_replace( '/[^a-zA-Z0-9]/i', ' ', $input[ 'name' ] );
            $label = trim( ucwords( $label ) );
        }else if( ! empty( $input[ 'label' ] ) ) {
            $label = $input[ 'label' ];
        }

        if( $text_only )
            return $label;

        // html label
        $attr = array();

        if( $input[ 'sr_only' ] ) {
            $attr[ 'class' ] = 'sr-only';
        }

        $label = $this->wrap( 'label', $label, $attr );

        return $label;
    }

    /**
     *    Gets the placeholder text
     *
     *    @param array $input Properties to use to get placeholder.
     *
     *    @return string with placeholder text
     */
    public function get_placeholder( array $input ) {
        $placeholder = '';

        // filter out only fields that can't have placeholders
        if( in_array( $input[ 'type' ], array( 'hidden', 'submit', 'reset', 'radio', 'checkbox' ) ) ) {
            return $placeholder;
        }

        if( ! empty( $input[ 'placeholder' ] ) ) {
            $placeholder = sprintf( ' placeholder="%s"', $input[ 'placeholder' ] );
        } else if( $input[ 'label_is_placeholder' ] ) {
            $placeholder = sprintf( ' placeholder="%s"', $this->get_label( $input, true )  );
        }

        return $placeholder;
    }

    /**
     *    Adds an input to the form
     *
     *    @param string|array $input Name or array with input properties.
     *
     *    @return html_form $this
     */
    public function add_field( $input ) {
        if( is_string( $input ) ) {
            $this->fields[ $input ] = array_merge( $this->input_defaults, array( 'name' => $input ) );
        } else if( is_array( $input ) ) {
            // we assume you are passing multiple inputs
            // types of arrays to expect
            // 1. array( 'username', 'password' );
            // 2. array( array( 'name' => 'username' ), array( 'name' => 'password' ) )
            // 3. array( 'username' => array( 'required' => true ), 'password' => array( 'type' => 'password', 'required' => true ) );
            foreach( $input as $k => $v ) {
                if( is_int( $k ) ) { // 1
                    if( is_string( $v ) ) {
                        $v = array( 'name' => $v );
                    }
                } else if( is_string( $k ) ) { // 3
                    if( is_array( $v ) ) {
                        $v[ 'name' ] = $k; // potential conflict resolution (array key must have precedence)
                    } else {
                        // if you aren't passing an array, what are you doing?
                        trigger_error( 'html_form::add_field - Expecting an array' );
                    }
                }

                if( ! isset( $v[ 'name' ] ) ) {
                    // we don't have a name? throw an error maybe?
                    trigger_error( 'html_form::add_field - No name set', E_USER_ERROR );
                    continue;
                }

                if( ! isset( $v[ 'type' ] ) ) {
                    $v[ 'type' ] = 'text';
                }

                if( $this->valid_field_type( $v[ 'type' ] ) ) {
                    $this->fields[ $v[ 'name' ] ] = $this->field_defaults( $v[ 'type' ], $v );
                } else {
                    trigger_error( 'html_form::add_field - Invalid field type ' . "({$v['type']})", E_USER_ERROR );
                }
            }
        }
    }

    /**
     *  Remove a field from the form.
     *
     *  @param string $name The name of the field.
     */
    public function remove_field( $name ) {
        if( isset( $this->fields[ $name ] ) ) {
            unset( $this->fields[ $name ] );
        } // else: throw an error?
    }

    /**
     *  Check if type is valid.
     *
     *  @param string $type The type to check against.
     *
     *  @return bool True when valid, false when not.
     */
    public function valid_field_type( $type ) {
        return isset( $this->valid_field_types[ $type ] );
    }

    /**
     * Check if form has a field with that name.
     *
     *  @param string $name The name of the field.
     *
     *  @return bool True when form has field, false when not.
     */
    public function has_field( $name ) {
        return isset( $this->fields[ $name ] );
    }

    /**
     *  Return field defaults.
     *
     *  @param string $type The type of field.
     *  @param string|array $options Options to pass to the appropriate field's default function.
     *
     *  @return string array with field defaults.
     */
    public function field_defaults( $type, $options ) {
        if( ! is_array( $options ) ) {
            $options = array(
                'name' => (string) $options,
            );
        }

        if( $this->valid_field_type( $type ) ) {
            return call_user_func_array( $this->valid_field_types[ $type ][ 'defaults' ], array( $options ) );
        } else {
            trigger_error( "html_form::field_defaults Invalid field type ({$type})", E_USER_ERROR );
        }
    }

    /**
     *  Prints out a field html
     *
     *  @param string|array $field_name String as the ID of field
     */
    public function print_field( $field_name ) {
        if( $this->has_field( $field_name ) ) {
            print $this->html_field( $field_name );
        } else {
            trigger_error( 'html_form::print_field - Invalid field name' );
        }
    }

    /**
     *  Gets form field from $_POST or $_GET
     *
     *  @return array|false Array with matching form fields or false on failure
     */
    public function get_post() {
        $form_fields = array();

        foreach( $this->fields as $f ) {
            extract( $f );

            if( isset( $this->valid_field_types[ $type ][ 'get_post' ] ) ) {
                $form_fields[ $name ] = call_user_func_array( $this->valid_field_types[ $type ][ 'get_post' ], array( $name ) );
            } else {
                $form_fields[ $name ] = $this->get_field_post( $name );
            }
        }

        return $form_fields;
    }

    /**
     *  Get a form field
     *
     *  @param string $name Name of field to return.
     *
     *  @return array|bool With fields properties, false on failure
     */
    public function get_field( $name ) {
        if( isset( $this->fields[ $name ] ) ) {
            return $this->fields[ $name ];
        } else {
            return false;
        }
    }

    /**
     *  Get the HTML string of a given field
     *
     *  @param array $field Field properties
     *
     *  @return HTML string
     */
    public function get_field_html( array $field ) {
        return call_user_func_array( $this->valid_field_types[ $field['type'] ][ 'html' ], array( $field ) );
    }

    /**
     *  Get the submitted value of a field
     *
     *  @param string $name The name of the field.
     *
     *  @return string The value of the field
     */
    public function get_field_post( $name ) {
        return $_POST[ $name ];
    }

    /**
     *  Get all form fields
     *
     *  @return array With all fields properties
     */
    public function get_all_fields() {
        return $this->fields;
    }

    /**
     *  Checks if is valid input type
     *
     *  @param string $type Type to check agains't valid types.
     *
     *  @return bool true if a vaild type, false on failure.
     */
    public function is_input_type( $type ) {
        return in_array( $type, $this->input_types );
    }

    /**
     *  Update an input type
     *
     *  @param array $key_values The key value pair of fields to update.
     */
    public function update_type( array $key_values ) {
        foreach( $key_values as $k => $v ) {
            if( isset( $this->fields[ $k ] ) ) {
                $field = $this->fields[ $k ];

                if( $field[ 'type' ] == $v ) {
                    continue;
                }

                $field[ 'type' ] = $v;

                if( $this->is_input_type( $v ) ) {
                    $this->fields[ $k ] = $this->input_defaults( $field );
                } else if( $v == 'textarea' ) {
                    $this->fields[ $k ] = $this->textarea_defaults( $field );
                }
            }
        }
    }

    /**
     *  Update form field values
     *
     *  @param array $key_values The key value pairs of fields to update.
     */
    public function update_values( array $key_values ) {
        foreach ( $key_values as $k => $v ) {
            if( $this->has_field( $k ) ) {
                $field = $this->get_field( $k );

                // Incase any fields wants to format/filter a value before setting it
                if( ! empty( $this->valid_field_types[ $field['type'] ][ 'update_value' ] ) ) {
                    $v = call_user_func_array( $this->valid_field_types[ $field['type'] ][ 'update_value' ], array( $k, $v ) );
                }

                $this->fields[ $k ][ 'value' ] = $v;
            }
        }
    }

    /**
     *  Returns new input properties after merging options with defaults
     *
     *  @param string|array The options to merge with defaults.
     *
     *  @return array With new input properties.
     */
    public function input_defaults( $options ) {
        $defaults = array(
            'repeat' => 0,
        );

        $defaults = array_merge( $defaults, $this->label_defaults );

        // since we might be handling different types of inputs
        $input_type = 'text';
        if( isset( $options[ 'type' ] ) && $this->is_input_type( $options[ 'type' ] ) ) {
            $input_type = $options[ 'type' ];
        }

        return $this->parse_args( $defaults, $options, $input_type );
    }

    /**
     *  Get post value from input field.
     *
     *  @param string $name Field name.
     *
     *  @return string|array Field value.
     */
    public function input_get_post( $name ) {
        $post = '';

        $f = $this->get_field( $name );

        if( isset( $f[ 'repeat' ] ) && $f[ 'repeat' ] ) {
            for( $x = 0; $x < $f[ 'repeat' ]; $x++ ) {
                if( $f[ 'type' ] == 'checkbox' ) {
                    if( isset( $_POST[ $name ][ $x ] ) )
                        $post[ $name ][ $x ] = true;
                    else
                        $post[ $name ][ $x ] = false;
                }

                if( isset( $_POST[ $name ][ $x ] ) ) {
                    $post[ $name ][ $x ] = $_POST[ $name ][ $x ];
                } else {
                    $post[ $name ][ $x ] = null;
                }
            }
        } else {
            if( $f[ 'type' ] == 'checkbox' ) {
                if( isset( $_POST[ $name ] ) )
                    $post[ $name ] = true;
                else
                    $post[ $name ] = false;
            }

            if( isset( $_POST[ $name ] ) ) {
                $post[ $name ] = $_POST[ $name ];
            } else {
                $post[ $name ] = null;
            }
        }

        return $post[ $name ];
    }

    /**
    *  Get HTML string of an input
    *
    *  @param array|string $input properties to use when building the input.
    *
    *  @return string the HTML version of the input
    */
    public function input_html( $input ) {
        $input = $this->input_defaults( $input );

        $string = '';

        if( $input[ 'repeat' ] ){
            if( substr( $input[ 'name' ], -2 ) != '[]' )
                $input[ 'name' ] .= '[]';
        }

        $name = sprintf( 'name="%1$s" id="%1$s"', $input[ 'name' ] );
        $type = sprintf( ' type="%s"', $input[ 'type' ] );
        $required = empty( $input[ 'required' ] ) ? '': ' required="required"';
        $placeholder = $this->get_placeholder( $input );

        // label
        $string .= $this->get_label( $input );

        if( $input[ 'repeat' ] ) {
            for( $x = 0; $x < $input[ 'repeat' ]; $x++ ) {
                $value = empty( $input[ 'value' ][ $x ] ) ? '': ( $input[ 'type'] == 'checkbox' ? ' checked': sprintf( ' value="%s"', htmlspecialchars( $input[ 'value' ][ $x ] ) ) );
                $string .= sprintf( '<input %s%s%s%s%s />', $name, $type, $value, $placeholder, $required );
            }
        } else {
            $value = empty( $input[ 'value' ] ) ? '': ( $input[ 'type'] == 'checkbox' ? ' checked': sprintf( ' value="%s"', htmlspecialchars( $input[ 'value' ] ) ) );
            $string .= sprintf( '<input %s%s%s%s%s />', $name, $type, $value, $placeholder, $required );
        }

        if( $input[ 'wrap_field' ] && $input[ 'type' ] !== 'hidden' ) {
            $string = $this->wrap_field( $input, $string );
        }

        return $string;
    }

    /**
     *  Get HTML string of an textarea
     *
     *  @param array $textarea properties to use when building the textarea.
     *
     *  @return string the HTML version of the textarea
     */
    public function textarea_html( $textarea ) {
        $textarea = $this->textarea_defaults( $textarea );

        $string = '';

        if( $textarea[ 'repeat' ] ) {
            if( substr( $textarea[ 'name' ], -2 ) != '[]' )
                $textarea[ 'name' ] .= '[]';
        }

        $name = sprintf( 'name="%1$s" id="%1$s"', $textarea[ 'name' ] );
        $required = empty( $textarea[ 'required' ] ) ? '': ' required="required"';
        $cols = sprintf( ' cols="%s" rows="%s"', $textarea[ 'cols' ], $textarea[ 'rows' ] );
        $placeholder = $this->get_placeholder( $textarea );

        // label
        $string .= $this->get_label( $textarea );

        if( $textarea[ 'repeat' ] ) {
            for( $x = 0; $x < $textarea[ 'repeat' ]; $x++ ) {
                $value = empty( $textarea[ 'value' ][ $x ] ) ? '': htmlspecialchars( $textarea[ 'value' ][ $x ] );
                $string .= sprintf( '<textarea %s%s%s%s>%s</textarea>', $name, $placeholder, $required, $cols, $value );
            }
        } else {
            $value = empty( $textarea[ 'value' ] ) ? '': htmlspecialchars( $textarea[ 'value' ] );
            $string .= sprintf( '<textarea %s%s%s%s>%s</textarea>', $name, $placeholder, $required, $cols, $value );
        }

        if( $textarea[ 'wrap_field' ] ) {
            $string = $this->wrap_field( $textarea, $string );
        }

        return $string;
    }

    /**
     *  Returns new textarea properties after merging options with defaults
     *
     *  @param string|array $options The options to merge with defaults.
     *
     *  @return array With new textarea properties.
     */
    public function textarea_defaults( $options ) {
        $defaults = array(
            'rows' => 5,
            'cols' => 30,
            'repeat' => 0,
        );

        $defaults = array_merge( $defaults, $this->label_defaults );

        $defaults = $this->parse_args( $defaults, $options, 'textarea' );

        // some error checking
        $defaults[ 'cols' ] = intval( $defaults[ 'cols' ] );
        $defaults[ 'rows' ] = intval( $defaults[ 'rows' ] );

        return $defaults;
    }

    /**
     *  Get the submit button defaults.
     *
     *  @param mixed $options Submit properties.
     *
     *  @return array Submit defaults.
     */
    public function submit_button_defaults( $options ) {
        $defaults = array();

        return $this->parse_args( $defaults, $options, 'submit' );
    }

    /**
     *  Get HTML of submit button.
     *
     *  @param mixed $field Submit button properties.
     *
     *  @return string HTML of the submit button.
     */
    public function submit_button_html( $field ) {
        $field = $this->submit_button_defaults( $field );

        $name = sprintf( 'name="%1$s" id="%1$s"', $field[ 'name' ] );
        $type = sprintf( ' type="%s"', $field[ 'type' ] );
        $value = sprintf( ' value="%s"', htmlspecialchars( $field[ 'value' ], ENT_QUOTES, 'UTF-8' ) );

        $html = sprintf( '<input %s%s%s />', $name, $type, $value );

        if( $field[ 'wrap_field' ] ) {
            $html = $this->wrap_field( $field, $html );
        }

        return $html;
    }

    /**
     *  Starting the HTML version of the form
     *
     *  @return string The opening part of the part.
     */
    public function form_start() {
        $form = '<form';

        if( $this->id )
            $form .= sprintf( ' id="%s"', $this->id );

        if( $this->action )
            $form .= sprintf( ' action="%s"', $this->action );

        $form .= sprintf( ' method="%s"', $this->method );

        if( $this->is_multipart )
            $form .= ' enctype="multipart/form-data"';

        $form .= '>';

        if( $this->id )
            $form .= sprintf( '<input type="hidden" name="_form_id" value="%s" />', $this->id );

        return $form;
    }

    /**
     *  Closing off the HTML form
     *
     *  @return string The closing form HTML with the submit button.
     */
    public function form_end() {
        $submit = $this->submit_button_html( $this->submit_defaults );

        return $submit . '</form>';
    }

    /**
     *  Set the form id.
     *
     *  @param string $value The new form ID.
     */
    public function set_id( $value ) {
        $this->id = $value;
    }

    /**
     *  Set the form submit action (submit url)
     *
     *  @param string $value The url to submit to.
     */
    public function set_action( $value ) {
        $this->action = $value;
    }

    /**
     *  Set the form submit method
     *
     *  @param string $value POST or GET method.
     */
    public function set_method( $value ) {
        if( strtolower( $value ) === 'post' || strtolower( $value ) === 'get' ) {
            $this->method = $value;
        }
    }

    /**
     *  Extract field names from a given array
     *
     *  @param array $fields The array to extract from.
     *  @param bool $as_keys Set field names as array keys.
     *
     *  @return array List of field names.
     */
    public function get_field_names( array $fields = array(), $as_keys = false ) {
        if( empty( $fields ) ) {
            $fields = $this->fields;
        } else {
            $fields = $this->array_field_fixer( $fields, array( 'name' => '' ) );
        }

        $names = array();

        foreach( $fields as $k => $v ) {
            $names[] = $v[ 'name' ];
        }

        if( $as_keys ) {
            $_names = array_flip( $names );
            $names = array();

            foreach( $_names as $k => $v ) {
                $names[ $k ] = '';
            }
        }

        return $names;
    }

    /**
     *  Fixes an array to an expected format.
     *
     *  @param array $fields The array with fields to transform.
     *  @param array $expected_kv How each element in the field is expected.
     *
     *  @return array An array of fixed fields.
     */
    public function array_field_fixer( array $fields, array $expected_kv = array() ) {
        $fixed_fields = array();

        if( empty( $expected_kv ) ) {
            $expected_kv = array(
                'name' => '',
                'type' => 'text',
                'value' => ''
            );
        }

        foreach( $fields as $k => $v ) {
            if( ! is_array( $v ) ) {
                if( is_string( $k ) ) {
                    $v = $k;
                }

                $fixed_fields[ $k ] = array_merge( $expected_kv, array( 'name' => $v ) );
            } else {
                $fixed_fields[ $k ] = array_merge( $expected_kv, $v );
            }
        }

        return $fixed_fields;
    }

    /**
     *  Merge defaults with options.
     *
     *  @param array $defaults The defaults to merge with field defaults.
     *  @param mixed $options Options to merge with defaults.
     *  @param string $type Field type.
     *
     *  @return array Merged defaults.
     */
    public function parse_args( array $defaults, $options, $type ) {
        $defaults = array_merge( $this->field_defaults, $defaults );

        if ( ! is_array( $options ) ) {
            $options = array(
                'name' => (string) $options,
            );
        }

        $options = array_intersect_key( $options, $defaults );

        $options[ 'type' ] = $type;

        return array_merge( $defaults, $options );
    }

    /**
     *  Get defaults for repeat group
     *
     *  @param array $options Field properties to merge with defaults.
     *
     *  @return array Field properties
     */
    protected function repeat_group_defaults( array $options ) {
        $defaults = array(
            'repeat' => 3,
            'fields' => array()
        );

        $defaults = array_merge( $this->field_defaults, $defaults );

        $options = array_intersect_key( $options, $defaults );

        $options[ 'type' ] = 'repeat_group';

        return array_merge( $defaults, $options );
    }

    /**
        *  Get HTML for repeat group field
        *
        *  @param array $field Field properties.
        *
        *  @return string HTML string
        */
    protected function repeat_group_html( array $field ) {
        $field = $this->repeat_group_defaults( $field );

        extract( $field );

        $html = '';

        $fields = $this->array_field_fixer( $fields );

        if ( ! is_array( $value ) ) {
            $value = array();
        }

        $value = $this->repeat_group_fix_values( $fields, $value, $repeat );

        $fieldsets = $form_field_names = $form_field_labels = array();

        // Append group name to field names
        foreach ( $fields as $k => $v ) {
            $new_name = "{$name}-{$v['name']}[]";

            $form_field_labels[ $v['name'] ] = $this->get_label( $v, true );

            $form_field_names[ $v['name'] ] = $new_name;
        }

        foreach ( $value as $k => $v ) {
            $group_html = '';

            foreach ( $fields as $f ) {
                $f_name = $f[ 'name' ];

                $f[ 'value' ] = $v[ $f_name ];
                $f[ 'label' ] = $form_field_labels[ $f_name ];
                $f[ 'name' ] = $form_field_names[ $f_name ];

                $group_html .= $this->get_field_html( $f );
            }

            $class = array(
                'class' => "repeat-group-group"
            );

            $group_html = $this->wrap( 'div', $group_html, $class );

            $fieldsets[] = $group_html;
        }

        $html = implode( $fieldsets );

        $attr = array(
            'id' => $name,
            'class' => 'repeat-group-wrap'
        );

        return $this->wrap( 'div', $html, $attr );
    }

    /**
        *  Format a value to required value.
        *
        *  @param string $field_name The name of the field.
        *  @param array $value The value.
        *
        *  @return array Of formatted the value.
        */
    protected function repeat_group_update_value( $field_name, array $value ) {
        $field = $this->get_field( $field_name );

        $value = $this->repeat_group_fix_values( $field[ 'fields' ], $value, $field[ 'repeat' ] );

        return $value;
    }

    /**
        *  Get the post value of the field.
        *
        *  @param string $field_name The name of the field name.
        *
        *  @return array formatted value.
        */
    protected function repeat_group_get_post( $field_name ) {
        $values = $field_names = $fn_wgn = array(); // fn_wgn: field names with group name

        $field = $this->get_field( $field_name );

        $fields = $field[ 'fields' ];

        $field_names = $this->get_field_names( $fields );

        $group = array_flip( $field_names );

        // add group name to fields
        $fields_count = count( $field_names );
        for ( $x = 0; $x < $fields_count; $x++ ) {
            $fn_wgn[ $x ] = "{$field_name}-{$field_names[$x]}";
        }

        // And then extract
        $_values = array_intersect_key( $_POST, array_flip( $fn_wgn ) );

        foreach ( $_values as $k => $v ) {
            $values[ str_replace( "{$field_name}-", '', $k ) ] = $v;
        }

        $fields_count = count( $values[ $field_names[0] ] );

        if ( -1 !== $field[ 'repeat' ] && $fields_count !== $field[ 'repeat' ] ) {
            $values = array_fill( 0, $repeat, $group );
        } else {
            $_values = array();

            for ( $x = 0; $x < $field['repeat']; $x++ ) {
                foreach ( $field_names as $name ) {
                    $_values[ $x ][ $name ] = $values[ $name ][ $x ];
                }
            }

            $values = $_values;
        }

        return $values;
    }

    /**
        *  Makes sure we are getting the expected values.
        *
        *  @param array $fields The fields of the repeat group.
        *  @param array $values The values to match to the fields.
        *  @param int $repeat The number of times to repeat the fields.
        *
        *  @return array The expected repeat group value.
        */
    protected function repeat_group_fix_values( array $fields, array $values, $repeat ) {
        $field_names = $this->get_field_names( $fields, true );

        $group = $this->get_field_names( $fields, true );

        $expected_count = -1 !== $repeat && count( $values ) === $repeat;

        $first = reset( $values );

        $expected_group = is_array( $first ) && ! array_diff_key( $first, $group );

        if ( ! $expected_count || ! $expected_group ) {
            $values = array_fill( 0, $repeat, $group );
        }

        return $values;
    }
}
