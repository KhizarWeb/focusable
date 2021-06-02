<?php

namespace Focusable\Setup;

/**
 * Enqueue.
 */
class Enqueue
{
    /**
     * register default hooks and actions for WordPress
     * @return
     */
    public function register()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

    }
    
    public function enqueue_scripts()
    {
        $version = FOCUSABLE_VERSION;

        if (FOCUSABLE_DEVELOPMENT_MODE) {
            $version = time();
        }

        wp_enqueue_script('ally', FOCUSABLE_DIR_URI . 'assets/vendor/js/ally.min.js', array(), '1.4.1', true);
        wp_enqueue_script('focusable-main', focusable_assets('js/main.js'), array(), $version, true);

        wp_localize_script('focusable-main', 'focusableData', [
            "settings" => [
                "transition" => wpsf_get_setting('focusable_settings_general', 'general', 'transition'),
            ],
        ]);

        wp_enqueue_style('focusable-style', focusable_assets('css/style.css'), array(), $version, 'all');
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('focusable-admin', focusable_assets('css/admin.css'), array(), FOCUSABLE_VERSION, 'all');
    }

}
