<?php
/*
Plugin Name: host-meta
Plugin URI: http://wordpress.org/extend/plugins/host-meta/
Description: Host Metadata for WordPress (RFC: http://tools.ietf.org/html/rfc6415)
Version: 1.2.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action( 'init', array( 'HostMetaPlugin', 'init' ) );

/**
 * the host-meta class
 *
 * @author Matthias Pfefferle
 */
class HostMetaPlugin {
	/**
	 * init plugin
	 */
	public static function init() {
		add_action( 'query_vars', array( 'HostMetaPlugin', 'query_vars' ) );
		add_action( 'parse_request', array( 'HostMetaPlugin', 'parse_request' ), 2 );
		add_action( 'generate_rewrite_rules', array( 'HostMetaPlugin', 'rewrite_rules' ), 1 );

		add_action( 'host_meta_render_jrd', array( 'HostMetaPlugin', 'render_jrd' ), 42, 1 );
		add_action( 'host_meta_render_xrd', array( 'HostMetaPlugin', 'render_xrd' ), 42, 1 );

		add_filter( 'host_meta', array( 'HostMetaPlugin', 'generate_default_content' ), 0, 1 );
	}

	/**
	 * adds some query vars
	 *
	 * @param array $vars
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'well-known';
		$vars[] = 'format';

		return $vars;
	}

	/**
	 * Add rewrite rules
	 *
	 * @param WP_Rewrite $wp_rewrite
	 */
	public static function rewrite_rules( $wp_rewrite ) {
		$host_meta_rules = array(
			'(.well-known/host-meta.json)' => 'index.php?well-known=host-meta.json',
			'(.well-known/host-meta)' => 'index.php?well-known=host-meta',
		);

		$wp_rewrite->rules = $host_meta_rules + $wp_rewrite->rules;
	}

	/**
	 * renders the output-file
	 *
	 * @param array $wp
	 */
	public static function parse_request( $wp ) {
		// check if "host-meta" param exists
		if ( ! array_key_exists( 'well-known', $wp->query_vars ) ) {
			return;
		}

		if ( 'host-meta' == $wp->query_vars['well-known'] ) {
			$format = 'xrd';
		} elseif ( 'host-meta.json' == $wp->query_vars['well-known'] ) {
			$format = 'jrd';
		} else {
			return;
		}

		$host_meta = apply_filters( 'host_meta', array(), $wp->query_vars );

		do_action( 'host_meta_render', $format, $host_meta, $wp->query_vars );
		do_action( "host_meta_render_{$format}", $host_meta, $wp->query_vars );
	}

	/**
	 * renders the host-meta file in xml
	 *
	 * @param array $host_meta
	 */
	public static function render_xrd( $host_meta ) {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Content-Type: application/xrd+xml; charset=' . get_bloginfo( 'charset' ), true );
		echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0<?php do_action( 'host_meta_ns' ); ?>">
<?php
	echo self::jrd_to_xrd( $host_meta );
	do_action( 'host_meta_xrd' );
?>
</XRD>
<?php
		exit;
	}

	/**
	 * renders the host-meta file in json
	 *
	 * @param array $host_meta
	 */
	public static function render_jrd( $host_meta ) {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ), true );

		echo wp_json_encode( $host_meta );
		exit;
	}

	/**
	 * generates the host-meta base array (and activate filter)
	 *
	 * @param array $host_meta
	 * @return array
	 */
	public static function generate_default_content( $host_meta ) {
		$host_meta = array();
		// add subject
		$host_meta['subject'] = site_url();

		// add feeds
		$host_meta['links'] = array(
			array( 'rel' => 'alternate', 'href' => get_bloginfo( 'atom_url' ), 'type' => 'application/atom+xml' ),
			array( 'rel' => 'alternate', 'href' => get_bloginfo( 'rss2_url' ), 'type' => 'application/rss+xml' ),
			array( 'rel' => 'alternate', 'href' => get_bloginfo( 'rdf_url' ), 'type' => 'application/rdf+xml' ),
		);

		// RSD discovery link
		$host_meta['links'][] = array(
			'rel' => 'EditURI',
			'href' => esc_url( site_url( 'xmlrpc.php?rsd', 'rpc' ) ),
			'type' => 'application/rsd+xml',
		);

		// add WordPress API
		if ( function_exists( 'get_rest_url' ) ) {
			$host_meta['links'][] = array(
				'rel' => 'https://api.w.org/',
				'href' => esc_url( get_rest_url() ),
			);
		}

		return $host_meta;
	}

	/**
	 * recursive helper to generade the xrd-xml from the jrd array
	 *
	 * @param string $host_meta
	 * @return string the genereated XRD file
	 */
	public static function jrd_to_xrd( $host_meta ) {
		$xrd = null;

		foreach ( $host_meta as $type => $content ) {
			// print subject
			if ( 'subject' == $type ) {
				$xrd .= "<Subject>$content</Subject>";
				continue;
			}

			// print aliases
			if ( 'aliases' == $type ) {
				foreach ( $content as $uri ) {
					$xrd .= '<Alias>' . wp_specialchars( $uri ) . '</Alias>';
				}
				continue;
			}

			// print properties
			if ( 'properties' == $type ) {
				foreach ( $content as $type => $uri ) {
					$xrd .= '<Property type="' . wp_specialchars( $type ) . '">' . wp_specialchars( $uri ) . '</Property>';
				}
				continue;
			}

			// print titles
			if ( 'titles' == $type ) {
				foreach ( $content as $key => $value ) {
					if ( 'default' == $key ) {
						$xrd .= '<Title>' . wp_specialchars( $value ) . '</Title>';
					} else {
						$xrd .= '<Title xml:lang="' . wp_specialchars( $key ) . '">' . wp_specialchars( $value ) . '</Title>';
					}
				}
				continue;
			}

			// print links
			if ( 'links' == $type ) {
				foreach ( $content as $links ) {
					$temp = array();
					$cascaded = false;
					$xrd .= '<Link ';

					foreach ( $links as $key => $value ) {
						if ( is_array( $value ) ) {
							$temp[ $key ] = $value;
							$cascaded = true;
						} else {
							$xrd .= wp_specialchars( $key ) . '="' . wp_specialchars( $value ) . '" ';
						}
					}
					if ( $cascaded ) {
						$xrd .= '>';
						$xrd .= self::jrd_to_xrd( $temp );
						$xrd .= '</Link>';
					} else {
						$xrd .= ' />';
					}
				}

				continue;
			}
		}

		return $xrd;
	}
}
