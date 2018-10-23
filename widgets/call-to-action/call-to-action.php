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
class HW_Call_To_Action extends TDT_HW_Widget_Base {
    /**
     * The base ID of the Widget
     *
     * @var string $id
     */
    protected $id = 'call_to_action';

    protected $meta = array( 'read_more_text', 'read_more_url' );
}
