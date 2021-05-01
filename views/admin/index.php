<?php

add_filter('wpsf_register_settings_focusable_settings_general', 'focusable_settings');


function focusable_settings($focusable_settings)
{
    // General Settings section
    $focusable_settings[] = array(
        'section_id' => 'general',
        'section_title' => 'General Settings',
        'section_order' => 5,
        'fields' => array(
            array(
				'id'      => 'transition',
				'title'   => 'Transition',
				'desc'    => 'Smoothly transition the ring while navigation.',
				'type'    => 'checkbox',
				'default' => 0,
			),
        ),
    );

    return $focusable_settings;
}
