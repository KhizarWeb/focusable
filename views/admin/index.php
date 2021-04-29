<?php

add_filter('wpsf_register_settings_focusable_settings_general', 'focusable_tabless_settings');

/**
 * Tabless example
 */
function focusable_tabless_settings($focusable_settings)
{
    // General Settings section
    $focusable_settings[] = array(
        'section_id' => 'general',
        'section_title' => 'General Settings',
        'section_description' => 'Some intro description about this section.',
        'section_order' => 5,
        'fields' => array(
            array(
				'id'      => 'transition',
				'title'   => 'Transition',
				'desc'    => 'Smoothly transition the ring while navigation.',
				'type'    => 'checkbox',
				'default' => 1,
			),
        ),
    );

    return $focusable_settings;
}
