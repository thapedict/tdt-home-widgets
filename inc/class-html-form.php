<?php


class html_form {

    /**
     *    the id of the form
     *    @var string
     */
    private $id = '';

    /**
     *    the submit method of the form
     *    @var string
     */
    private $method = 'POST';

    /**
     *    the submit url of the form
     *    @var string
     */
    private $action = '';

    /**
     *    if the form uploads files
     *    @var boolean
     */
    private $is_multipart = false;

    /**
     *    holds all form fields
     *    @var array
     */
    private $fields = array();

    /**
     *    holds all input types
     *    @var array
     */
    private $input_types = array( 'text', 'hidden','email', 'search', 'password', 'url', 'radio', 'checkbox', 'number' );

    /**
     *    the default properties of the input fields
     *    @var array
     */
    private $input_defaults = array(
                            'name' => '',
                            'type' => 'text',
                            'value' => '',
                            'required' => false,
                            'label' => '',
                            'sr_only' => false,
                            'auto_label' => true,
                            'placeholder' => '',
                            'label_is_placeholder'=> true,
                            'wrap_field' => true
                        );

    /**
     *    the default properties of the textarea fields
     *
     *    NOTE: Upon initialization (to avoid repetition), they will extend (be joined with) the input defaults
     *
     *    @var array
     */
    private $textarea_defaults = array(
                            'type' => 'textarea',
                            'rows' => 5,
                            'cols' => 20
                        );

    /**
     *    the default properties of the submit button
     *
     *    NOTE: Upon initialization (to avoid repetition), they will extend (be joined with) the input defaults
     *
     *    @var array
     */
    private $submit_defaults = array(
                            'name' => 'submit',
                            'type' => 'submit',
                            'value' => 'Submit'
                        );

    /**
     *    the constructor
     *    @param array with form options
     */
    public function __construct( array $options = array() ) {
        if( isset( $options[ 'id' ] ) )
            $this->id = $options[ 'id' ];

        if( isset( $options[ 'method' ] ) )
            $this->method = $options[ 'method' ];

        if( isset( $options[ 'action' ] ) )
            $this->action = $options[ 'action' ];

        if( isset( $options[ 'is_multipart' ] ) )
            $this->is_multipart = $options[ 'is_multipart' ];

        $input_defaults = $textarea_defaults = array();

        // input defaults
        $_input_defaults = array();
        if( isset( $options[ 'input_defaults' ] ) && is_array( $options[ 'input_defaults' ] ) )
            $_input_defaults = array_intersect_key( $options[ 'input_defaults' ], $this->input_defaults );

        $this->input_defaults = array_merge( $this->input_defaults, $_input_defaults );

        // label
        if( isset( $options[ 'sr_only' ] ) )
            $this->input_defaults[ 'sr_only' ] = $options[ 'sr_only' ];

        if( isset( $options[ 'wrap_fields' ] ) )
            $this->input_defaults[ 'wrap_field' ] = $options[ 'wrap_fields' ];

        // textarea defaults
        $this->textarea_defaults = array_merge( $this->input_defaults, $this->textarea_defaults );

        $_textarea_defaults = array();
        if( isset( $options[ 'textarea_defaults' ] ) && is_array( $options[ 'textarea_defaults' ] ) )
            $_textarea_defaults = array_intersect_key( $options[ 'textarea_defaults' ], $this->textarea_defaults );

        $this->textarea_defaults = array_merge( $this->textarea_defaults, $_textarea_defaults );

        // submit defaults
        $this->submit_defaults = array_merge( $this->input_defaults, $this->submit_defaults );

        if( isset( $options[ 'submit_value' ] ) )
            $this->submit_defaults[ 'value' ] = $options[ 'submit_value' ];


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
            switch( $f[ 'type' ] ) {
                case in_array( $f[ 'type'], $this->input_types ):
                    $form .= $this->get_input( $f );
                break;

                case 'textarea':
                    $form .= $this->get_textarea( $f );
                break;
            }
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
     *  Get HTML string of an input
     *
     *  @param array $input properties to use when building the input.
     *
     *  @return string the HTML version of the input
     */
    public function get_input( array $input ) {
        $string = '';

        if( isset( $input[ 'repeat' ] ) && is_int( $input[ 'repeat' ] ) ) {
            $repeat = $input[ 'repeat' ];
        } else {
            $repeat = 0;
        }

        if( $repeat ){
            if( substr( $input[ 'name' ], -2 ) != '[]' )
                $input[ 'name' ] .= '[]';
        }

        $name = sprintf( 'name="%1$s" id="%1$s"', $input[ 'name' ] );
        $type = sprintf( ' type="%s"', $input[ 'type' ] );
        $required = empty( $input[ 'required' ] ) ? '': ' required="required"';
        $placeholder = $this->get_placeholder( $input );

        // label
        $string .= $this->get_label( $input );

        if( $repeat ) {
            for( $x = 0; $x < $input[ 'repeat' ]; $x++ ) {
                $value = empty( $input[ 'value' ][ $x ] ) ? '': ( $input[ 'type'] == 'checkbox' ? ' checked': sprintf( ' value="%s"', htmlspecialchars( $input[ 'value' ][ $x ] ) ) );
                $string .= sprintf( '<input %s%s%s%s%s />', $name, $type, $value, $placeholder, $required );
            }
        } else {
            $value = empty( $input[ 'value' ] ) ? '': ( $input[ 'type'] == 'checkbox' ? ' checked': sprintf( ' value="%s"', htmlspecialchars( $input[ 'value' ] ) ) );
            $string .= sprintf( '<input %s%s%s%s%s />', $name, $type, $value, $placeholder, $required );
        }

        if( $input[ 'wrap_field' ] && $input[ 'type' ] != 'hidden' )
            $string = $this->wrap( 'div', $string );

        return $string;
    }
    
    /**
     *  Get HTML string of an textarea
     *
     *  @param array $textarea properties to use when building the textarea.
     *
     *  @return string the HTML version of the textarea
     */
    public function get_textarea( array $textarea ) {
        $string = '';

        if( isset( $textarea[ 'repeat' ] ) && is_int( $textarea[ 'repeat' ] ) ) {
            $repeat = $textarea[ 'repeat' ];
        } else {
            $repeat = 0;
        }

        if( $repeat ){
            if( substr( $textarea[ 'name' ], -2 ) != '[]' )
                $textarea[ 'name' ] .= '[]';
        }

        $name = sprintf( 'name="%1$s" id="%1$s"', $textarea[ 'name' ] );
        $required = empty( $textarea[ 'required' ] ) ? '': ' required="required"';
        $cols = sprintf( ' cols="%s" rows="%s"', $textarea[ 'cols' ], $textarea[ 'rows' ] );
        $placeholder = $this->get_placeholder( $textarea );

        // label
        $string .= $this->get_label( $textarea );

        if( $repeat ) {
            for( $x = 0; $x < $textarea[ 'repeat' ]; $x++ ) {
                $value = empty( $textarea[ 'value' ][ $x ] ) ? '': htmlspecialchars( $textarea[ 'value' ][ $x ] );
                $string .= sprintf( '<textarea %s%s%s%s>%s</textarea>', $name, $placeholder, $required, $cols, $value );
            }
        } else {
            $value = empty( $textarea[ 'value' ] ) ? '': htmlspecialchars( $textarea[ 'value' ] );
            $string .= sprintf( '<textarea %s%s%s%s>%s</textarea>', $name, $placeholder, $required, $cols, $value );
        }

        if( $textarea[ 'wrap_field' ] )
            $string = $this->wrap( 'div', $string );

        return $string;
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
    public function wrap( $wrap_with, $string, $attr = null ) {
        $attributes = '';
        if( is_array( $attr ) ) {
            foreach( $attr as $k => $v ) {
                if ( is_int( $k ) )
                    continue;

                $attributes .= sprintf( ' %s="%s"', $k, htmlentities( $v ) );
            }
        } else if ( is_string( $attr ) ) {
            $attributes = $attr;
        }

        $str = sprintf( '<%2$s%3$s>%1$s</%2$s>', $string, $wrap_with, $attributes );

        return $str;
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
        $classes = array();

        if( $input[ 'sr_only' ] )
            $classes[] = 'sr-only';

        if( empty( $classes ) )
            $classes = '';
        else
            $classes = sprintf( ' class="%s"', implode( ' ', $classes ) );

        $label = $this->wrap( 'label', $label, $classes );

        return $label;
    }

    /**
     *    Gets the placeholder text
     *
     *    @param array $input Properties to use to get placeholder.
     *
     *    @return string with placeholder text
     */
    function get_placeholder( array $input ) {
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
    public function add_input( $input ) {
        if( is_string( $input ) ) {
            $this->fields[ $input ] = array_merge( $this->input_defaults, array( 'name' => $input ) );
        } else if( is_array( $input ) ) {
            // we assume you are passing multiple inputs
            // types of arrays to expect
            // 1. array( 'username', 'password' );
            // 2. array( array( 'name' => 'username' ), array( 'name' => 'password' ) )
            // 3. array( 'username' => array( 'required' => true ), 'password' => array( 'type' => 'password', 'required' => true ) );
            foreach( $input as $k => $v ) {
                if( is_int( $k ) ) { // 1, 2
                    if( is_string( $v ) ) { // 1
                        $this->fields[ $v ] = array_merge( $this->input_defaults, array( 'name' => $v ) );
                    }  else if ( is_array( $v ) ) { // 2
                        if( isset( $v[ 'name' ] ) ) {
                            if( isset( $v[ 'type' ] ) && $v[ 'type' ] == 'textarea' ) {
                                $this->fields[ $v[ 'name' ] ] = $this->textarea_defaults( $v );
                            } else {
                                $this->fields[ $v[ 'name' ] ] = $this->input_defaults( $v );
                            }
                        }
                    }
                } else if( is_string( $k ) ) { // 3
                    if( is_array( $v ) ) {
                        $v[ 'name' ] = $k;

                        if( isset( $v[ 'type' ] ) && $v[ 'type' ] == 'textarea' ) {
                            $this->fields[ $k ] = $this->textarea_defaults( $v );
                        } else {
                            $this->fields[ $k ] = $this->input_defaults( $v );
                        }
                    } else {
                        $v = array( 'name' => $k );

                        $this->fields[ $k ] = $this->input_defaults( $v );
                    }
                }
            }
        }

        return $this;
    }

    /**
     *  Prints out an input html
     *
     *  @param string|array $input String as the ID of input or array with new input properties
     */
    public function print_input( $input ) {
        $args = func_get_args();

        foreach( $args as $input ) {
            if( is_string( $input ) ) {
                foreach( $this->fields as $f ) {
                    if( $f[ 'name' ] == $input ) {
                        $this->print_input( $f );
                        break;
                    }
                }
            } elseif( is_array( $input ) ) {
                if( $input[ 'type' ] == 'textarea' )
                    print $this->get_textarea( $input );
                else
                    print $this->get_input( $input );
            }
        }
    }

    /**
     *  Gets form field from $_POST or $_GET
     *
     *  @return array|false Array with matching form fields or false on failure
     */
    public function get_post() {
        $post = array();

        foreach( $this->fields as $f ) {
            $name = $f[ 'name' ];

            if( isset( $f[ 'repeat' ] ) && $f[ 'repeat' ] ) {
                for( $x = 0; $x < $f[ 'repeat' ]; $x++ ) {
                    if( $f[ 'type' ] == 'checkbox' ) {
                        if( isset( $_POST[ $name ][ $x ] ) )
                            $post[ $name ][ $x ] = true;
                        else
                            $post[ $name ][ $x ] = false;

                        continue;
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

                    continue;
                }

                if( isset( $_POST[ $name ] ) ) {
                    $post[ $name ] = $_POST[ $name ];
                } else {
                    $post[ $name ] = null;
                }
            }
        }

        return $post;
    }

    /**
     *  Get a form field
     *
     *  @param string $id ID of field to return.
     *
     *  @return array|bool With fields properties, false on failure
     */
    public function get_field( $id ) {
        if( isset( $this->fields[ $id ] ) ) {
            return $this->fields[ $id ];
        } else {
            return false;
        }
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
        foreach( $key_values as $k => $v ) {
            foreach( $this->fields as $fk => $fv ) {
                if( $fv[ 'name' ] == $k ) {
                    $this->fields[ $fk ][ 'value' ] = $v;
                }
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
        if( is_string( $options ) )
            $options = array( 'name' => $options );

        if( ! is_array( $options ) )
            return;
        
        $options = array_intersect_key( $options, $this->input_defaults );

        return array_merge( $this->input_defaults, $options );
    }

    /**
     *  Returns new textarea properties after merging options with defaults
     *
     *  @param string|array The options to merge with defaults.
     *
     *  @return array With new textarea properties.
     */
    public function textarea_defaults( $options ) {
        if( is_string( $options ) )
            $options = array( 'name' => $options );

        if( ! is_array( $options ) )
            return;
        
        $options = array_intersect_key( $options, $this->textarea_defaults );

        unset( $options[ 'type' ] );

        return array_merge( $this->textarea_defaults, $options );
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
        $submit = $this->get_input( $this->submit_defaults );

        return $submit . '</form>';
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
}
