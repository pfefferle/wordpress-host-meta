<?php
/**
 * Test the host-meta document.
 *
 * @package Host_Meta
 */

namespace Host_Meta\Tests;

use Host_Meta\Host_Meta;

/**
 * Test class for the host-meta document.
 *
 * @coversDefaultClass \Host_Meta\Host_Meta
 */
class Test_Host_Meta extends \WP_UnitTestCase {

	/**
	 * The query vars are added.
	 *
	 * @covers ::query_vars
	 */
	public function test_query_vars() {
		$vars = Host_Meta::query_vars( array( 'p' ) );

		$this->assertSame( array( 'p', 'well-known', 'format' ), $vars );
	}

	/**
	 * Both well-known URLs are rewritten, and only those.
	 *
	 * @covers ::rewrite_rules
	 */
	public function test_rewrite_rules() {
		global $wp_rewrite;

		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		Host_Meta::rewrite_rules();
		\flush_rewrite_rules();

		$rules = $wp_rewrite->wp_rewrite_rules();

		$this->assertSame( 'index.php?well-known=host-meta', $rules['^\.well-known/host-meta/?$'] );
		$this->assertSame( 'index.php?well-known=host-meta.json', $rules['^\.well-known/host-meta\.json/?$'] );
	}

	/**
	 * The default data links the feeds, RSD and the REST API.
	 *
	 * @covers ::generate_default_content
	 */
	public function test_generate_default_content() {
		$host_meta = Host_Meta::generate_default_content();

		$this->assertSame( \site_url(), $host_meta['subject'] );

		$rels = \wp_list_pluck( $host_meta['links'], 'rel' );
		$this->assertContains( 'alternate', $rels );
		$this->assertContains( 'EditURI', $rels );
		$this->assertContains( 'https://api.w.org/', $rels );

		$api = \wp_list_filter( $host_meta['links'], array( 'rel' => 'https://api.w.org/' ) );
		$this->assertSame( \get_rest_url(), \reset( $api )['href'] );
	}

	/**
	 * The RSD URL is not HTML encoded, so it is valid in JSON.
	 *
	 * @covers ::generate_default_content
	 */
	public function test_generate_default_content_raw_urls() {
		$host_meta = Host_Meta::generate_default_content();

		$rsd = \wp_list_filter( $host_meta['links'], array( 'rel' => 'EditURI' ) );
		$this->assertStringNotContainsString( '&#038;', \reset( $rsd )['href'] );
		$this->assertStringEndsWith( 'xmlrpc.php?rsd', \reset( $rsd )['href'] );
	}

	/**
	 * The site icon and the privacy policy are only linked when they exist.
	 *
	 * @covers ::generate_default_content
	 */
	public function test_generate_default_content_optional_links() {
		$rels = \wp_list_pluck( Host_Meta::generate_default_content()['links'], 'rel' );
		$this->assertNotContains( 'icon', $rels );
		$this->assertNotContains( 'privacy-policy', $rels );

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		\update_option( 'wp_page_for_privacy_policy', $page_id );

		// An attachment record is enough, the file itself is never read.
		$attachment_id = self::factory()->attachment->create(
			array(
				'post_mime_type' => 'image/png',
				'file'           => 'icon.png',
			)
		);
		\update_option( 'site_icon', $attachment_id );

		$links = Host_Meta::generate_default_content()['links'];

		$privacy = \wp_list_filter( $links, array( 'rel' => 'privacy-policy' ) );
		$this->assertSame( \get_permalink( $page_id ), \reset( $privacy )['href'] );

		$icon = \wp_list_filter( $links, array( 'rel' => 'icon' ) );
		$this->assertSame( 'image/png', \reset( $icon )['type'] );
		$this->assertSame( \get_site_icon_url( 512 ), \reset( $icon )['href'] );
	}

	/**
	 * A filtered site icon without an attachment gets no type.
	 *
	 * @covers ::generate_default_content
	 */
	public function test_generate_default_content_filtered_icon() {
		\add_filter(
			'get_site_icon_url',
			function () {
				return 'https://example.com/icon.png';
			}
		);

		$icon = \wp_list_filter( Host_Meta::generate_default_content()['links'], array( 'rel' => 'icon' ) );
		$icon = \reset( $icon );

		$this->assertSame( 'https://example.com/icon.png', $icon['href'] );
		$this->assertArrayNotHasKey( 'type', $icon );
	}

	/**
	 * Calling the filter directly still returns the defaults.
	 *
	 * @covers ::generate_default_content
	 */
	public function test_host_meta_filter_has_defaults() {
		$host_meta = \apply_filters( 'host_meta', array() );

		$this->assertSame( \site_url(), $host_meta['subject'] );
		$this->assertContains( 'https://api.w.org/', \wp_list_pluck( $host_meta['links'], 'rel' ) );
	}

	/**
	 * All JRD members are converted to XRD elements.
	 *
	 * @covers ::jrd_to_xrd
	 */
	public function test_jrd_to_xrd() {
		$xrd = Host_Meta::jrd_to_xrd(
			array(
				'subject'    => 'https://example.com/?a=1&b=2',
				'aliases'    => array( 'acct:user@example.com' ),
				'properties' => array(
					'http://example.com/ns/name' => 'Tom & Jerry',
					'http://example.com/ns/nil'  => null,
				),
				'links'      => array(
					array(
						'rel'     => 'lrdd',
						'type'    => 'application/xrd+xml',
						'aliases' => array( 'https://example.com/not-allowed' ),
					),
					array(
						'rel'    => 'author',
						'href'   => 'https://example.com/about',
						'titles' => array(
							'default' => 'About',
							'de'      => 'Über',
						),
					),
				),
			)
		);

		$this->assertStringContainsString( '<Subject>https://example.com/?a=1&amp;b=2</Subject>', $xrd );
		$this->assertStringContainsString( '<Alias>acct:user@example.com</Alias>', $xrd );
		$this->assertStringNotContainsString( 'not-allowed', $xrd );
		$this->assertStringContainsString( '<Property type="http://example.com/ns/name">Tom &amp; Jerry</Property>', $xrd );
		$this->assertStringContainsString( '<Property type="http://example.com/ns/nil" xsi:nil="true" />', $xrd );
		$this->assertStringContainsString( '<Link rel="lrdd" type="application/xrd+xml" />', $xrd );
		$this->assertStringContainsString( '<Link rel="author" href="https://example.com/about">', $xrd );
		$this->assertStringContainsString( '<Title>About</Title>', $xrd );
		$this->assertStringContainsString( '<Title xml:lang="de">Über</Title>', $xrd );
	}

	/**
	 * The XRD document is well-formed, also with extra namespaces.
	 *
	 * @covers ::print_xrd
	 */
	public function test_print_xrd() {
		$ns = function () {
			echo ' xmlns:foo="http://example.com/foo"';
		};
		\add_action( 'host_meta_ns', $ns );

		$xrd = \get_echo( array( Host_Meta::class, 'print_xrd' ), array( Host_Meta::generate_default_content() ) );

		$doc = new \DOMDocument();
		$this->assertTrue( $doc->loadXML( $xrd ) );
		$root = $doc->documentElement; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->assertSame( 'XRD', $root->localName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->assertSame( 'http://example.com/foo', $root->lookupNamespaceURI( 'foo' ) );
		$this->assertSame( \site_url(), $doc->getElementsByTagName( 'Subject' )->item( 0 )->textContent );
	}

	/**
	 * The requested format is rendered, other well-known URIs are ignored.
	 *
	 * @covers ::parse_request
	 */
	public function test_parse_request() {
		$rendered = array();
		$callback = function ( $format ) use ( &$rendered ) {
			$rendered[] = $format;
		};
		\add_action( 'host_meta_render', $callback );

		// Remove the renderers, they exit.
		\remove_all_actions( 'host_meta_render_xrd' );
		\remove_all_actions( 'host_meta_render_jrd' );

		foreach ( array( 'host-meta', 'host-meta.json', 'webfinger' ) as $well_known ) {
			$wp             = new \WP();
			$wp->query_vars = array( 'well-known' => $well_known );

			Host_Meta::parse_request( $wp );
		}

		$this->assertSame( array( 'xrd', 'jrd' ), $rendered );
	}

	/**
	 * The filter starts with the default links and keeps data from early callbacks.
	 *
	 * @covers ::parse_request
	 */
	public function test_parse_request_filter() {
		$data = null;

		\add_filter(
			'host_meta',
			function ( $host_meta ) {
				$host_meta['links'][] = array( 'rel' => 'early' );
				return $host_meta;
			},
			-1
		);
		\add_action(
			'host_meta_render',
			function ( $format, $host_meta ) use ( &$data ) {
				$data = $host_meta;
			},
			10,
			2
		);
		\remove_all_actions( 'host_meta_render_xrd' );

		$wp             = new \WP();
		$wp->query_vars = array( 'well-known' => 'host-meta' );
		Host_Meta::parse_request( $wp );

		$rels = \wp_list_pluck( $data['links'], 'rel' );
		$this->assertContains( 'https://api.w.org/', $rels );
		$this->assertContains( 'early', $rels );
	}

	/**
	 * The old global class name still works.
	 */
	public function test_deprecated_class() {
		$this->setExpectedDeprecated( 'Host_Meta' );

		$this->assertTrue( \class_exists( 'Host_Meta' ) );
		$this->assertInstanceOf( Host_Meta::class, new \Host_Meta() );
	}
}
