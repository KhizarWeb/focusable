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