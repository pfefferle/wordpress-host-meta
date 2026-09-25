<?php
/**
 * Plugin Name: host-meta
 * Plugin URI: https://github.com/pfefferle/wordpress-host-meta
 * Description: Helps other apps and services find out what your site offers.
 * Version: 1.4.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: host-meta
 *
 * @package Host_Meta
 */

namespace Host_Meta;

\defined( 'ABSPATH' ) || exit;

\define( 'HOST_META_VERSION', '1.4.0' );
\define( 'HOST_META_PLUGIN_DIR', \plugin_dir_path( __FILE__ ) );
\define( 'HOST_META_PLUGIN_FILE', __FILE__ );

require_once HOST_META_PLUGIN_DIR . 'includes/class-host-meta.php';
require_once HOST_META_PLUGIN_DIR . 'includes/deprecated.php';

/**
 * Initialize the plugin.
 */
function plugin_init() {
	Host_Meta::init();
}
\add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin_init' );

/**
 * Add the rewrite rules and flush the old ones.
 */
function activate() {
	Host_Meta::rewrite_rules();
	\flush_rewrite_rules();
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
\register_deactivation_hook( __FILE__, '\flush_rewrite_rules' );
