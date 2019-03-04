<?php
/**
 *  Team Members Widget Base
 *
 *  @package TDT_HW
 *  @version 1.0
 */

/**
 *  Team Memeber base class
 */
class TDT_HW_Team_Members extends TDT_HW_Widget_Base {
    /**
     *  Widget ID
     *
     *  @var string
     */
    protected $id = 'team_members';

    /**
     *  Widget Metas
     *
     *  @var array
     */
    protected $meta = array(
        array(
            'name' => 'sub_title',
            'type' => 'textarea'
        ),
        array(
            'name' => 'members',
            'type' => 'repeat_group',
            'repeat' => 5,
            'fields' => array(
                'names',
                'position',
                array(
                    'name' => 'avatar',
                    'type' => 'image_picker'
                ),
                array(
                    'name' => 'bio',
                    'type' => 'textarea'
                ),
                'link_facebook',
                'link_twitter',
                'link_linkedin'
            )
        )
    );
}