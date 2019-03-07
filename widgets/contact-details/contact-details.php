<?php

/**
 *  Some WidgetBase class
 */
class TDT_HW_Contact_Details extends TDT_HW_Widget_Base {

    /**
     *  Widget ID
     *
     *  @var string
     */
    protected $id = 'contact_details';

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
        'email',
        'facebook',
        'twitter',
        'instagram',
        'phone',
        array(
            'name' => 'physical_address',
            'type' => 'textarea',
        ),
        array(
            'name' => 'postal_address',
            'type' => 'textarea',
        ),
    );
}
