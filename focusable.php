<?php

/**
 * @link              https://khizar.info/#/focusable
 * @since             1.0.0
 * @package           Focusable
 *
 * @wordpress-plugin
 * Plugin Name:       Focusable - Focus Ring On Any Element
 * Plugin URI:        https://redoxbird.com/products/focusable
 * Description:       Make your website instantly more accessible! Focusable restores and enhances the visible focus ring for keyboard users, ensuring everyone can navigate your site with confidence.
 * Version:           2.0.0
 * Author:            Khizar Hasan
 * Author URI:        https://redoxbird.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       focusable
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

include_once plugin_dir_path(__FILE__) . 'inc/WordPressSettingsFramework.php';

define('FOCUSABLE_VERSION', '1.3.0');
define('FOCUSABLE_DIR_URI', plugin_dir_url(__FILE__));
define('FOCUSABLE_DIR_PATH', plugin_dir_path(__FILE__));
define('FOCUSABLE_DEVELOPMENT_MODE', false);


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

add_action('wp_enqueue_scripts', 'focusable_enqueue_scripts');

function focusable_enqueue_scripts() {
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


if (!function_exists('focusable_register_setup')) {
    function focusable_register_setup() {
        static $wpsf = null;
        register_activation_hook(__FILE__, 'focusable_activate');
        register_deactivation_hook(__FILE__, 'focusable_deactivate');

        if (!$wpsf) {
            $wpsf = new WordPressSettingsFramework(FOCUSABLE_DIR_PATH . 'views/admin/index.php', 'focusable_settings_general');
        }

        add_action('admin_menu', function() use (&$wpsf) {
            focusable_add_settings_page($wpsf);
        }, 20);

        add_filter($wpsf->get_option_group() . '_settings_validate', 'focusable_validate_settings');

        add_filter('wpsf_menu_icon_url_focusable_settings_general', function($icon) {
            $icon = focusable_assets('images/icon.png');
            return $icon;
        });
    }
}

if (!function_exists('focusable_activate')) {
    function focusable_activate() {
        // Activation logic here
    }
}

if (!function_exists('focusable_deactivate')) {
    function focusable_deactivate() {
        // Deactivation logic here
    }
}

if (!function_exists('focusable_set_locale')) {
    function focusable_set_locale() {
        load_plugin_textdomain(
            'focusable',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}

if (!function_exists('focusable_add_settings_page')) {
    function focusable_add_settings_page($wpsf) {
        $wpsf->add_settings_page(array(
            'page_title' => __('Focusable', 'focusable'),
            'menu_title' => 'Focusable',
            'capability' => 'manage_options',
        ));
    }
}

if (!function_exists('focusable_validate_settings')) {
    function focusable_validate_settings($input) {
        // Do your settings validation here
        // Same as $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
        return $input;
    }
}

add_action('plugins_loaded', 'focusable_register_setup');

