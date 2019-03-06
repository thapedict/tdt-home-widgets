<?php

class TDT_HW_Post_loop extends TDT_HW_Widget_Base {

        protected $id = 'post_loop';

        protected $meta = array(
            array(
                'name' => 'sub_title',
                'type' => 'textarea',
            ),
            array(
                'name' => 'posts_per_page',
                'type' => 'number',
            ),
        );
    }