<?php
/**
 *  Call To Action Widget
 *
 *  @package TDT_HW
 *  @author Thapelo Moeti
 */

/**
 * Call To Action
 */
class TDT_HW_Call_To_Action extends TDT_HW_Widget_Base {
    /**
     * The base ID of the Widget
     *
     * @var string $id
     */
    protected $id = 'call_to_action';

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
        'read_more_text',
        'read_more_url',
        array(
            'type' => 'image_picker',
            'name'=> 'featured_image'
            )
    );
}
