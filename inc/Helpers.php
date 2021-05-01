<?php

if (!function_exists('focusable_assets')) {
    /**
     * Get assets folder url.
     *
     * @param  string  $path
     * @return string
     */

    function focusable_assets($path)
    {
        if (!$path) {
            return;
        }

        return FOCUSABLE_DIR_URI . '/assets/dist/' . $path;
    }
}

if (!function_exists('wpsf_get_setting')) {
    /**
     * Get a setting from an option group
     *
     * @param string $option_group
     * @param string $section_id May also be prefixed with tab ID
     * @param string $field_id
     *
     * @return mixed
     */
    function wpsf_get_setting($option_group, $section_id, $field_id)
    {
        $options = get_option($option_group . '_settings');
        if (isset($options[$section_id . '_' . $field_id])) {
            return $options[$section_id . '_' . $field_id];
        }

        return false;
    }
}

if (!function_exists('wpsf_delete_settings')) {
    /**
     * Delete all the saved settings from a settings file/option group
     *
     * @param string $option_group
     */
    function wpsf_delete_settings($option_group)
    {
        delete_option($option_group . '_settings');
    }
}
