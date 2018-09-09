<?php
/**
 * Plugin Name: host-meta
 * Plugin URI: https://github.com/pfefferle/wordpress-host-meta
 * Description: Host Metadata for WordPress
 * Version: 1.2.3
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: host-meta
 * Domain Path: /languages
 */

register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );


/**
 * Initialize plugin
 */
function host_meta_init() {
	require_once( dirname( __FILE__ ) . '/includes/class-host-meta.php' );

	add_action( 'query_vars', array( 'Host_Meta', 'query_vars' ) );
	add_action( 'parse_request', array( 'Host_Meta', 'parse_request' ), 2 );
	add_action( 'generate_rewrite_rules', array( 'Host_Meta', 'rewrite_rules' ), 1 );

	add_action( 'host_meta_render_jrd', array( 'Host_Meta', 'render_jrd' ), 42, 1 );
	add_action( 'host_meta_render_xrd', array( 'Host_Meta', 'render_xrd' ), 42, 1 );

	add_filter( 'host_meta', array( 'Host_Meta', 'generate_default_content' ), 0, 1 );
}
add_action( 'plugins_loaded', 'host_meta_init' );
