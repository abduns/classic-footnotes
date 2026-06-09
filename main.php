<?php
/**
 * Plugin Name: Classic Footnotes
 * Description: A general footnote and reference system for adding inline source markers and a collected reference list to WordPress content.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Abdun Syakuur
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: classic-footnotes
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('CSFN_VERSION', '1.0.0');
define('CSFN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CSFN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CSFN_PLUGIN_FILE', __FILE__);

// Include required class files.
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-settings.php';
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-favicon.php';
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-store.php';
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-renderer.php';
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-shortcode.php';
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-content.php';
require_once CSFN_PLUGIN_DIR . 'includes/class-csfn-editor.php';

// Initialize the plugin.
add_action('plugins_loaded', 'csfn_init_plugin');

function csfn_init_plugin()
{
    new CSFN_Settings();
    new CSFN_Shortcode();
    new CSFN_Content();
    new CSFN_Editor();
}
