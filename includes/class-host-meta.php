<?php
/**
 * Host_Meta class.
 *
 * @package Host_Meta
 */

namespace Host_Meta;

/**
 * Serves the host-meta document, as XRD and as JRD.
 *
 * @see https://www.rfc-editor.org/rfc/rfc6415
 */
class Host_Meta {

	/**
	 * Hook the plugin into WordPress.
	 */
	public static function init() {
		\add_filter( 'query_vars', array( static::class, 'query_vars' ) );
		\add_action( 'parse_request', array( static::class, 'parse_request' ), 2 );
		\add_action( 'init', array( static::class, 'rewrite_rules' ), 1 );

		\add_action( 'host_meta_render_jrd', array( static::class, 'render_jrd' ), 42 );
		\add_action( 'host_meta_render_xrd', array( static::class, 'render_xrd' ), 42 );

		\add_filter( 'host_meta', array( static::class, 'generate_default_content' ), 0 );
	}

	/**
	 * Add the query vars.
	 *
	 * @param array $vars The query vars.
	 *
	 * @return array The updated query vars.
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'well-known';
		$vars[] = 'format';

		return $vars;
	}

	/**
	 * Add the rewrite rules.
	 */
	public static function rewrite_rules() {
		\add_rewrite_rule( '^\.well-known/host-meta\.json/?$', 'index.php?well-known=host-meta.json', 'top' );
		\add_rewrite_rule( '^\.well-known/host-meta/?$', 'index.php?well-known=host-meta', 'top' );
	}

	/**
	 * Render the host-meta document, if it was requested.
	 *
	 * @param \WP $wp The WordPress request object.
	 */
	public static function parse_request( $wp ) {
		if ( ! \array_key_exists( 'well-known', $wp->query_vars ) ) {
			return;
		}

		if ( 'host-meta' === $wp->query_vars['well-known'] ) {
			$format = 'xrd';
		} elseif ( 'host-meta.json' === $wp->query_vars['well-known'] ) {
			$format = 'jrd';
		} else {
			return;
		}

		/**
		 * Filters the host-meta data.
		 *
		 * @param array $host_meta  The host-meta data, in JRD format.
		 * @param array $query_vars The query vars of the request.
		 */
		$host_meta = \apply_filters( 'host_meta', array(), $wp->query_vars );

		/**
		 * Fires before the host-meta document is rendered.
		 *
		 * @param string $format     The format, `xrd` or `jrd`.
		 * @param array  $host_meta  The host-meta data.
		 * @param array  $query_vars The query vars of the request.
		 */
		\do_action( 'host_meta_render', $format, $host_meta, $wp->query_vars );

		/**
		 * Fires to render the host-meta document in the requested format.
		 *
		 * The dynamic part of the hook name is the format, `xrd` or `jrd`.
		 *
		 * @param array $host_meta  The host-meta data.
		 * @param array $query_vars The query vars of the request.
		 */
		\do_action( "host_meta_render_{$format}", $host_meta, $wp->query_vars );
	}

	/**
	 * Render the host-meta document as XRD.
	 *
	 * @param array $host_meta The host-meta data.
	 */
	public static function render_xrd( $host_meta ) {
		\header( 'Access-Control-Allow-Origin: *' );
		\header( \sprintf( 'Content-Type: application/xrd+xml; charset=%s', \get_bloginfo( 'charset' ) ), true );

		self::print_xrd( $host_meta );
		exit;
	}

	/**
	 * Render the host-meta document as JRD.
	 *
	 * @param array $host_meta The host-meta data.
	 */
	public static function render_jrd( $host_meta ) {
		\header( 'Access-Control-Allow-Origin: *' );

		\wp_send_json( $host_meta );
	}

	/**
	 * Print the full XRD document.
	 *
	 * @param array $host_meta The host-meta data.
	 */
	public static function print_xrd( $host_meta ) {
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';

		/**
		 * Fires inside the root element, to add more namespaces.
		 *
		 * Callbacks echo the namespace with a leading space,
		 * for example ` xmlns:foo="https://example.com/ns"`.
		 */
		\do_action( 'host_meta_ns' );

		echo '>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The values are escaped in jrd_to_xrd().
		echo self::jrd_to_xrd( $host_meta );

		/**
		 * Fires before the closing tag, to add more elements.
		 *
		 * Callbacks echo escaped XRD elements.
		 */
		\do_action( 'host_meta_xrd' );

		echo '</XRD>';
	}

	/**
	 * Add the default host-meta data.
	 *
	 * The default links are added in front of the existing ones, data from
	 * earlier callbacks is kept.
	 *
	 * @param array $host_meta The host-meta data.
	 *
	 * @return array The host-meta data with the defaults.
	 */
	public static function generate_default_content( $host_meta = array() ) {
		$defaults = array(
			'subject' => \site_url(),
			'links'   => array(
				array(
					'rel'  => 'alternate',
					'href' => \get_bloginfo( 'atom_url' ),
					'type' => 'application/atom+xml',
				),
				array(
					'rel'  => 'alternate',
					'href' => \get_bloginfo( 'rss2_url' ),
					'type' => 'application/rss+xml',
				),
				array(
					'rel'  => 'alternate',
					'href' => \get_bloginfo( 'rdf_url' ),
					'type' => 'application/rdf+xml',
				),
				array(
					'rel'  => 'EditURI',
					'href' => \esc_url_raw( \site_url( 'xmlrpc.php?rsd', 'rpc' ) ),
					'type' => 'application/rsd+xml',
				),
				array(
					'rel'  => 'https://api.w.org/',
					'href' => \esc_url_raw( \get_rest_url() ),
				),
			),
		);

		$site_icon_url = \get_site_icon_url( 512 );
		if ( $site_icon_url ) {
			$icon = array(
				'rel'  => 'icon',
				'href' => \esc_url_raw( $site_icon_url ),
			);

			// The URL can come from a filter, without an attachment behind it.
			$mime_type = \get_post_mime_type( (int) \get_option( 'site_icon' ) );
			if ( $mime_type ) {
				$icon['type'] = $mime_type;
			}

			$defaults['links'][] = $icon;
		}

		$privacy_policy_url = \get_privacy_policy_url();
		if ( $privacy_policy_url ) {
			$defaults['links'][] = array(
				'rel'  => 'privacy-policy',
				'href' => \esc_url_raw( $privacy_policy_url ),
				'type' => 'text/html',
			);
		}

		$host_meta = (array) $host_meta;

		if ( ! empty( $host_meta['links'] ) ) {
			$defaults['links'] = \array_merge( $defaults['links'], (array) $host_meta['links'] );
		}

		return \array_merge( $defaults, \array_diff_key( $host_meta, array( 'links' => true ) ) );
	}

	/**
	 * Convert JRD data to XRD elements.
	 *
	 * @param array $host_meta The host-meta data, in JRD format.
	 *
	 * @return string The XRD elements.
	 */
	public static function jrd_to_xrd( $host_meta ) {
		$xrd = '';

		foreach ( $host_meta as $type => $content ) {
			switch ( $type ) {
				case 'subject':
					$xrd .= \sprintf( '<Subject>%s</Subject>', \esc_xml( $content ) );
					break;

				case 'aliases':
					foreach ( (array) $content as $uri ) {
						$xrd .= \sprintf( '<Alias>%s</Alias>', \esc_xml( $uri ) );
					}
					break;

				case 'properties':
					foreach ( (array) $content as $property => $value ) {
						if ( null === $value ) {
							$xrd .= \sprintf( '<Property type="%s" xsi:nil="true" />', \esc_attr( $property ) );
						} else {
							$xrd .= \sprintf( '<Property type="%1$s">%2$s</Property>', \esc_attr( $property ), \esc_xml( $value ) );
						}
					}
					break;

				case 'titles':
					foreach ( (array) $content as $lang => $title ) {
						if ( 'default' === $lang ) {
							$xrd .= \sprintf( '<Title>%s</Title>', \esc_xml( $title ) );
						} else {
							$xrd .= \sprintf( '<Title xml:lang="%1$s">%2$s</Title>', \esc_attr( $lang ), \esc_xml( $title ) );
						}
					}
					break;

				case 'links':
					foreach ( (array) $content as $link ) {
						$attributes = '';
						$children   = array();

						foreach ( $link as $key => $value ) {
							if ( 'titles' === $key || 'properties' === $key ) {
								$children[ $key ] = $value;
							} elseif ( \is_scalar( $value ) ) {
								$attributes .= \sprintf( ' %1$s="%2$s"', \esc_attr( $key ), \esc_attr( $value ) );
							}
						}

						if ( $children ) {
							$xrd .= \sprintf( '<Link%1$s>%2$s</Link>', $attributes, self::jrd_to_xrd( $children ) );
						} else {
							$xrd .= \sprintf( '<Link%s />', $attributes );
						}
					}
					break;
			}
		}

		return $xrd;
	}
}
