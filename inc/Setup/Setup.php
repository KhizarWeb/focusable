<?php

namespace Focusable\Setup;

use Focusable\API\Settings\WordPressSettingsFramework;

class Setup
{

    private $wpsf;

    /**
     * register default hooks and actions for WordPress
     * @return
     */

    public function register()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        $this->wpsf = new WordPressSettingsFramework(FOCUSABLE_DIR_PATH . 'views/admin/index.php', 'focusable_settings_general');

        add_action('admin_menu', array($this, 'add_settings_page'), 20);

        add_filter($this->wpsf->get_option_group() . '_settings_validate', array(&$this, 'validate_settings'));

        add_filter('wpsf_menu_icon_url_focusable_settings_general', function($icon) {
            $icon = focusable_assets('images/icon.png');
            return $icon;
        });

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

    /**
     * Add settings page.
     */
    public function add_settings_page()
    {
        $this->wpsf->add_settings_page(array(
            'page_title' => __('Focusable', 'focusable'),
            'menu_title' => 'Focusable',
            'capability' => 'manage_options',
        ));
    }

    /**
     * Validate settings.
     *
     * @param $input
     *
     * @return mixed
     */
    public function validate_settings($input)
    {
        // Do your settings validation here
        // Same as $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
        return $input;
    }

}
