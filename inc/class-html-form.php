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
     *    NOTE: Upon initialization(to avoid repetition), they will extend (be joined with) the input defaults
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
     *    NOTE: Upon initialization(to avoid repetition), they will extend (be joined with) the input defaults
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
    function __construct( array $options = array() ) {
        if( isset( $options[ 'id' ] ) )
            $this->id = $options[ 'id' ];

        if( isset( $options[ 'method' ] ) )
            $this->method = $options[ 'method' ];

        if( isset( $options[ 'is_multipart' ] ) )
            $this->is_multipart = $options[ 'is_multipart' ];

        $input_defaults = $textarea_defaults = array();


        // label
        if( isset( $options[ 'sr_only' ] ) )
            $this->input_defaults[ 'sr_only' ] = $options[ 'sr_only' ];

        // input defaults
        if( isset( $options[ 'wrap_fields' ] ) )
            $this->input_defaults[ 'wrap_field' ] = $options[ 'wrap_fields' ];

        if( isset( $options[ 'input_defaults' ] ) && is_array( $options[ 'input_defaults' ] ) )
            $input_defaults = $options[ 'input_defaults' ];

        $this->input_defaults = array_merge( $this->input_defaults, $this->input_defaults, $input_defaults );

        // textarea defaults
        if( isset( $options[ 'textarea_defaults' ] ) && is_array( $options[ 'textarea_defaults' ] ) )
            $textarea_defaults = $options[ 'textarea_defaults' ];

        $this->textarea_defaults = array_merge( $this->input_defaults, $this->textarea_defaults, $textarea_defaults );

        // submit defaults
        $this->submit_defaults = array_merge( $this->input_defaults, $this->submit_defaults );

        if( isset( $options[ 'submit_value' ] ) )
            $this->submit_defaults[ 'value' ] = $options[ 'submit_value' ];


    }

    public function __toString() {
        return $this->get_form();
    }

    /**
     *
     */
    function get_form() {
        if( empty( $this->fields ) )
            return '';

        $form = $this->form_start();

        // fields
        foreach( $this->fields as $f ) {
            switch( $f[ 'type' ] ) {
                case in_array( $f[ 'type'], array( 'text', 'hidden','email', 'search', 'password', 'url', 'radio', 'checkbox', 'number' ) ):
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
     *
     */
    function print() {
        print $this->get_form();
    }

    /**
     *
     */
    function get_input( array $input ) {
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
     *
     */
    function get_textarea( array $textarea ) {
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
     *
     */
    function wrap( $wrap_with, $string, $attr = null ) {
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
     *    gets the  html label, or just the text
     *    @param input array
     *    @return string with label text
     */
    function get_label( array $input, $text_only = false ) {
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
     *    gets the placeholder text
     *    @param input array
     *    @return string with placeholder text
     */
    function get_placeholder( array $input ) {
        $placeholder = '';

        // filter to only fields that can have placeholders
        if( in_array( $input[ 'type' ], array( 'hidden', 'submit', 'reset', 'radio', 'checkbox' ) ) )
            return $placeholder;

        if( ! empty( $placeholder ) )
            $placeholder = sprintf( ' placeholder="%s"', $input[ 'placeholder' ] );
        else if( $input[ 'label_is_placeholder' ] )
            $placeholder = sprintf( ' placeholder="%s"', $this->get_label( $input, true )  );

        return $placeholder;
    }

    /**
     *    adds an input to the form
     *    @param    input name or array with input properties
     *    @return $this
     */
    function add_input( $input ) {
        if( is_string( $input ) ) {
            $this->fields[] = array_merge( $this->input_defaults, array( 'name' => $input ) );
        } else if( is_array( $input ) ) {
            // types of arrays to expect
            // 1. array( 'name', 'password' );
            // 2. array( array( 'name' => 'name' ), array( 'name' => 'password' ) )
            // 3. array( 'name' => 'name', 'required' => true );
            // because the input name can be the array key
            // 4. array( 'name' => array( 'required' => true ) );
            // 5. array( 'name' => array( 'required' => true ), 'password' => array( 'required' => true ) );
            foreach( $input as $k => $v ) {
                if( is_int( $k ) ) { // 1, 2
                    if( is_string( $v ) ) // 1
                        $this->fields[] = array_merge( $this->input_defaults, array( 'name' => $v ) );
                    else if ( is_array( $v ) ) { // 2
                        if( isset( $v[ 'name' ] ) ) {
                            if( isset( $v[ 'type' ] ) && $v[ 'type' ] == 'textarea' )
                                $this->fields[] = array_merge( $this->textarea_defaults, $v );
                            else
                                $this->fields[] = array_merge( $this->input_defaults, $v );
                        } else {
                            if( class_exists( 'tbt_debug' ) )
                                tbt_debug()->log( 'Error: No Name Set' );
                        }
                    }
                } else { // 3, 4, 5
                    if( is_string( $v ) ) { // we assume it's 3
                        if( isset( $input[ 'name' ] ) ) {
                            if( isset( $input[ 'type' ] ) && $input[ 'type' ] == 'textarea' )
                                $this->fields[] = array_merge( $this->textarea_defaults, $input );
                            else
                                $this->fields[] = array_merge( $this->input_defaults, $input );
                        }

                        break; // stop looping through the properties
                    } else if( is_array( $v ) ) { // 4, 5
                        if( ! isset( $v[ 'name' ] ) )
                            $v[ 'name' ] = $k;

                        if( isset( $v[ 'type' ] ) && $v[ 'type' ] == 'textarea' )
                            $this->fields[] = array_merge( $this->textarea_defaults, $v );
                        else
                            $this->fields[] = array_merge( $this->input_defaults, $v );
                    }
                }
            }
        }

        return $this;
    }

    function print_input( $input ) {
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

    function get_post() {
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

    function is_input_type( $type ) {
        return in_array( $type, $this->input_types );
    }

    function update_type( array $key_values ) {
        $args = func_get_args();

        if( count( $args ) == 1 ) {
            foreach( $key_values as $k => $v ) {
                foreach( $this->fields as $fk => $fv ) {
                    if( $fv[ 'name' ] == $k ) {
                        $this->fields[ $fk ][ 'type' ] = $v;

                        if( $this->is_input_type( $v ) )
                            $this->fields[ $fk ] = array_merge( $this->input_defaults, $this->fields[ $fk ] );

                        if( $v == 'textarea' )
                            $this->fields[ $fk ] = array_merge( $this->textarea_defaults, $this->fields[ $fk ] );
                    }
                }
            }
        } else {
            foreach( $key_values as $k ) {
                foreach( $this->fields as $fk => $fv ) {
                    if( $fv[ 'name' ] == $k ) {
                        $this->fields[ $fk ][ 'type' ] = $args[ 1 ];

                        if( $this->is_input_type( $args[ 1 ] ) )
                            $this->fields[ $fk ] = array_merge( $this->input_defaults, $this->fields[ $fk ] );

                        if( $args[ 1 ] == 'textarea' )
                            $this->fields[ $fk ] = array_merge( $this->textarea_defaults, $this->fields[ $fk ] );
                    }
                }
            }
        }
    }

    function update_values( array $key_values ) {
        foreach( $key_values as $k => $v ) {
            foreach( $this->fields as $fk => $fv ) {
                if( $fv[ 'name' ] == $k ) {
                    $this->fields[ $fk ][ 'value' ] = $v;
                }
            }
        }
    }

    function input_defaults( $options ) {
        if( is_string( $options ) )
            $options = array( 'name' => $options );

        if( ! is_array( $options ) )
            return;

        return array_merge( $this->input_defaults, $options );
    }

    function textarea_defaults( $options ) {
        if( is_string( $options ) )
            $options = array( 'name' => $options );

        if( ! is_array( $options ) )
            return;

        return array_merge( $this->textarea_defaults, $options );
    }

    function form_start() {
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

    function form_end() {
        $submit = $this->get_input( $this->submit_defaults );

        return $submit . '</form>';
    }
}
