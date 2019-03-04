<?php

class TDT_HW_Featured_Items extends TDT_HW_Widget_Base {
    /**
     * The base ID of the Widget
     *
     * @var string $id
     */
    protected $id = 'featured_items';

    /**
     *  The widget metas
     *
     *  @var array $meta
     */
    protected $meta = array(
        array(
            'name' => 'sub_title',
            'type' => 'textarea'
        ),
        array(
            'name' => 'items',
            'type' => 'repeat_group',
            'repeat' => 3,
            'fields' => array(
                'title',
                array(
                    'name' => 'icon',
                    'type' => 'icon_picker'
                ),
                array(
                    'type' => 'textarea',
                    'name' => 'description'
                ),
                'read_more_text',
                'read_more_url'
            )
        )
    );
}
