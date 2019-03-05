<?php
/**
 *  Testimonials Widget Base
 *
 *  @package TDT_HW
 *  @version 1.0
 */
 
/**
 *  Testimonials class
 */
class TDT_HW_Testimonials extends TDT_HW_Widget_Base {
    /**
     *  Widget ID
     *
     *  @var string
     */
    protected $id = 'testimonials';

    /**
     *  Widget Metas
     *
     *  @var array
     */
    protected $meta = array(
        array(
            'name' => 'sub_title',
            'type' => 'textarea',
        ),
        array(
            'name' => 'messages',
            'type' => 'repeat_group',
            'repeat' => 3,
            'fields' => array(
                'name',
                'company',
                array(
                    'name' => 'message',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'image',
                    'type' => 'image_picker',
                ),
            ),
        )
    );
}
