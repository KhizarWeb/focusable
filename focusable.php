<?php

/**
 * @link              https://khizar.info/#/focusable
 * @since             1.0.0
 * @package           Focusable
 *
 * @wordpress-plugin
 * Plugin Name:       Focusable
 * Plugin URI:        https://khizar.info/#/focusable
 * Description:       This plugin displays a ring on the focusable elements when navigating through keyboard.
 * Version:           1.3.0
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

define('FOCUSABLE_VERSION', '1.3.0');
define('FOCUSABLE_DIR_URI', plugin_dir_url(__FILE__));
define('FOCUSABLE_DIR_PATH', plugin_dir_path(__FILE__));
define('FOCUSABLE_DEVELOPMENT_MODE', false);

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')):
    require_once dirname(__FILE__) . '/vendor/autoload.php';
endif;

if (class_exists('Focusable\\Init')):
    Focusable\Init::register_services();
endif;
