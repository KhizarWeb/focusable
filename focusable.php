<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://khizar.info
 * @since             1.0.0
 * @package           Focusable
 *
 * @wordpress-plugin
 * Plugin Name:       Focusable
 * Plugin URI:        https://redoxbird.com/focusable
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Khizar Hasan
 * Author URI:        https://khizar.info
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       focusable
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define('FOCUSABLE_VERSION', '1.0.0');
define('FOCUSABLE_DIR_URI', plugin_dir_url(__FILE__));
define('FOCUSABLE_DIR_PATH', plugin_dir_path(__FILE__));

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')):
    require_once dirname(__FILE__) . '/vendor/autoload.php';
endif;

if (class_exists('Focusable\\Init')):
    Focusable\Init::register_services();
endif;
