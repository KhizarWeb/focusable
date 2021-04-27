<?php

namespace Focusable\Setup;

class Setup
{
    /**
     * register default hooks and actions for WordPress
     * @return
     */
    public function register()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
    }

    public static function activate()
    {

    }

    public static function deactivate()
    {

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Focusable_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */

    public function set_locale()
    {

        load_plugin_textdomain(
            'focusable',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }
}
