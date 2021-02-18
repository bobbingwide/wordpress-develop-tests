<?php
/**
 * A set of unit tests for functions in wp-includes/general-template.php
 *
 * @group template
 * @group site_icon
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-site-icon.php';

class Tests_General_Template extends WP_UnitTestCase {
	protected $wp_site_icon;
	public $site_icon_id;
	public $site_icon_url;

	public $custom_logo_id;
	public $custom_logo_url;

	function setUp() {
		parent::setUp();

		$this->wp_site_icon = new WP_Site_Icon();
	}

	function tearDown() {
		global $wp_customize;
		$this->_remove_custom_logo();
		$this->_remove_site_icon();
		$wp_customize = null;

		parent::tearDown();
	}

	/**
	 * @group site_icon
	 */
	function test_get_site_icon_url() {
		$this->assertEmpty( get_site_icon_url() );

		$this->_set_site_icon();
		$this->assertEquals( $this->site_icon_url, get_site_icon_url() );

		$this->_remove_site_icon();
		$this->assertEmpty( get_site_icon_url() );
	}

	/**
	 * @group site_icon
	 */
	function test_site_icon_url() {
		$this->expectOutputString( '' );
		site_icon_url();

		$this->_set_site_icon();
		$this->expectOutputString( $this->site_icon_url );
		site_icon_url();
	}

	/**
	 * @group site_icon
	 */
	function test_has_site_icon() {
		$this->assertFalse( has_site_icon() );

		$this->_set_site_icon();
		$this->assertTrue( has_site_icon() );

		$this->_remove_site_icon();
		$this->assertFalse( has_site_icon() );
	}

	/**
	 * @group site_icon
	 * @group multisite
	 * @group ms-required
	 */
	function test_has_site_icon_returns_true_when_called_for_other_site_with_site_icon_set() {
		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );
		$this->_set_site_icon();
		restore_current_blog();

		$this->assertTrue( has_site_icon( $blog_id ) );
	}

	/**
	 * @group site_icon
	 * @group multisite
	 * @group ms-required
	 */
	function test_has_site_icon_returns_false_when_called_for_other_site_without_site_icon_set() {
		$blog_id = $this->factory->blog->create();

		$this->assertFalse( has_site_icon( $blog_id ) );
	}

	/**
	 * @group site_icon
	 */
	function test_wp_site_icon() {
		$this->expectOutputString( '' );
		wp_site_icon();

		$this->_set_site_icon();
		$output = array(
			sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( get_site_icon_url( 32 ) ) ),
			sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( get_site_icon_url( 192 ) ) ),
			sprintf( '<link rel="apple-touch-icon" href="%s" />', esc_url( get_site_icon_url( 180 ) ) ),
			sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( get_site_icon_url( 270 ) ) ),
			'',
		);
		$output = implode( "\n", $output );

		$this->expectOutputString( $output );
		wp_site_icon();
	}

	/**
	 * @group site_icon
	 */
	function test_wp_site_icon_with_filter() {
		$this->expectOutputString( '' );
		wp_site_icon();

		$this->_set_site_icon();
		$output = array(
			sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( get_site_icon_url( 32 ) ) ),
			sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( get_site_icon_url( 192 ) ) ),
			sprintf( '<link rel="apple-touch-icon" href="%s" />', esc_url( get_site_icon_url( 180 ) ) ),
			sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( get_site_icon_url( 270 ) ) ),
			sprintf( '<link rel="apple-touch-icon" sizes="150x150" href="%s" />', esc_url( get_site_icon_url( 150 ) ) ),
			'',
		);
		$output = implode( "\n", $output );

		$this->expectOutputString( $output );
		add_filter( 'site_icon_meta_tags', array( $this, '_custom_site_icon_meta_tag' ) );
		wp_site_icon();
		remove_filter( 'site_icon_meta_tags', array( $this, '_custom_site_icon_meta_tag' ) );
	}

	/**
	 * @group site_icon
	 * @ticket 38377
	 */
	function test_customize_preview_wp_site_icon_empty() {
		global $wp_customize;
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );

		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->register_controls();
		$wp_customize->start_previewing_theme();

		$this->expectOutputString( '<link rel="icon" href="/favicon.ico" sizes="32x32" />' . "\n" );
		wp_site_icon();
	}

	/**
	 * @group site_icon
	 * @ticket 38377
	 */
	function test_customize_preview_wp_site_icon_dirty() {
		global $wp_customize;
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );

		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->register_controls();
		$wp_customize->start_previewing_theme();

		$attachment_id = $this->_insert_attachment();
		$wp_customize->set_post_value( 'site_icon', $attachment_id );
		$wp_customize->get_setting( 'site_icon' )->preview();
		$output = array(
			sprintf( '<link rel="icon" href="%s" sizes="32x32" />', esc_url( wp_get_attachment_image_url( $attachment_id, 32 ) ) ),
			sprintf( '<link rel="icon" href="%s" sizes="192x192" />', esc_url( wp_get_attachment_image_url( $attachment_id, 192 ) ) ),
			sprintf( '<link rel="apple-touch-icon" href="%s" />', esc_url( wp_get_attachment_image_url( $attachment_id, 180 ) ) ),
			sprintf( '<meta name="msapplication-TileImage" content="%s" />', esc_url( wp_get_attachment_image_url( $attachment_id, 270 ) ) ),
			'',
		);
		$output = implode( "\n", $output );
		$this->expectOutputString( $output );
		wp_site_icon();
	}

	/**
	 * Builds and retrieves a custom site icon meta tag.
	 *
	 * @since 4.3.0
	 *
	 * @param $meta_tags
	 * @return array
	 */
	function _custom_site_icon_meta_tag( $meta_tags ) {
		$meta_tags[] = sprintf( '<link rel="apple-touch-icon" sizes="150x150" href="%s" />', esc_url( get_site_icon_url( 150 ) ) );

		return $meta_tags;
	}

	/**
	 * Sets a site icon in options for testing.
	 *
	 * @since 4.3.0
	 */
	function _set_site_icon() {
		if ( ! $this->site_icon_id ) {
			add_filter( 'intermediate_image_sizes_advanced', array( $this->wp_site_icon, 'additional_sizes' ) );
			$this->_insert_attachment();
			remove_filter( 'intermediate_image_sizes_advanced', array( $this->wp_site_icon, 'additional_sizes' ) );
		}

		update_option( 'site_icon', $this->site_icon_id );
	}

	/**
	 * Removes the site icon from options.
	 *
	 * @since 4.3.0
	 */
	function _remove_site_icon() {
		delete_option( 'site_icon' );
	}

	/**
	 * Inserts an attachment for testing site icons.
	 *
	 * @since 4.3.0
	 */
	function _insert_attachment() {
		$filename = DIR_TESTDATA . '/images/test-image.jpg';
		$contents = file_get_contents( $filename );

		$upload              = wp_upload_bits( wp_basename( $filename ), null, $contents );
		$this->site_icon_url = $upload['url'];

		// Save the data.
		$this->site_icon_id = $this->_make_attachment( $upload );
		return $this->site_icon_id;
	}

	/**
	 * @group custom_logo
	 *
	 * @since 4.5.0
	 */
	function test_has_custom_logo() {
		$this->assertFalse( has_custom_logo() );

		$this->_set_custom_logo();
		$this->assertTrue( has_custom_logo() );

		$this->_remove_custom_logo();
		$this->assertFalse( has_custom_logo() );
	}

	/**
	 * @group custom_logo
	 * @group multisite
	 * @group ms-required
	 */
	function test_has_custom_logo_returns_true_when_called_for_other_site_with_custom_logo_set() {
		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );
		$this->_set_custom_logo();
		restore_current_blog();

		$this->assertTrue( has_custom_logo( $blog_id ) );
	}

	/**
	 * @group custom_logo
	 * @group multisite
	 * @group ms-required
	 */
	function test_has_custom_logo_returns_false_when_called_for_other_site_without_custom_logo_set() {
		$blog_id = $this->factory->blog->create();

		$this->assertFalse( has_custom_logo( $blog_id ) );
	}

	/**
	 * @group custom_logo
	 *
	 * @since 4.5.0
	 */
	function test_get_custom_logo() {
		$this->assertEmpty( get_custom_logo() );

		$this->_set_custom_logo();
		$custom_logo = get_custom_logo();
		$this->assertNotEmpty( $custom_logo );
		$this->assertInternalType( 'string', $custom_logo );

		$this->_remove_custom_logo();
		$this->assertEmpty( get_custom_logo() );
	}

	/**
	 * @group custom_logo
	 * @group multisite
	 * @group ms-required
	 */
	function test_get_custom_logo_returns_logo_when_called_for_other_site_with_custom_logo_set() {
		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );

		$this->_set_custom_logo();

		$custom_logo_attr = array(
			'class'   => 'custom-logo',
			'loading' => false,
		);

		// If the logo alt attribute is empty, use the site title.
		$image_alt = get_post_meta( $this->custom_logo_id, '_wp_attachment_image_alt', true );
		if ( empty( $image_alt ) ) {
			$custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
		}

		$home_url = get_home_url( $blog_id, '/' );
		$image    = wp_get_attachment_image( $this->custom_logo_id, 'full', false, $custom_logo_attr );
		restore_current_blog();

		$expected_custom_logo = '<a href="' . $home_url . '" class="custom-logo-link" rel="home">' . $image . '</a>';
		$this->assertEquals( $expected_custom_logo, get_custom_logo( $blog_id ) );
	}

	/**
	 * @group custom_logo
	 *
	 * @since 4.5.0
	 */
	function test_the_custom_logo() {
		$this->expectOutputString( '' );
		the_custom_logo();

		$this->_set_custom_logo();

		$custom_logo_attr = array(
			'class'   => 'custom-logo',
			'loading' => false,
		);

		// If the logo alt attribute is empty, use the site title.
		$image_alt = get_post_meta( $this->custom_logo_id, '_wp_attachment_image_alt', true );
		if ( empty( $image_alt ) ) {
			$custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
		}

		$image = wp_get_attachment_image( $this->custom_logo_id, 'full', false, $custom_logo_attr );

		$this->expectOutputString( '<a href="http://' . WP_TESTS_DOMAIN . '/" class="custom-logo-link" rel="home">' . $image . '</a>' );
		the_custom_logo();
	}

	/**
	 * @group custom_logo
	 * @ticket 38768
	 */
	function test_the_custom_logo_with_alt() {
		$this->_set_custom_logo();

		$image_alt = 'My alt attribute';

		update_post_meta( $this->custom_logo_id, '_wp_attachment_image_alt', $image_alt );

		$image = wp_get_attachment_image(
			$this->custom_logo_id,
			'full',
			false,
			array(
				'class'   => 'custom-logo',
				'loading' => false,
			)
		);

		$this->expectOutputString( '<a href="http://' . WP_TESTS_DOMAIN . '/" class="custom-logo-link" rel="home">' . $image . '</a>' );
		the_custom_logo();
	}

	/**
	 * Sets a site icon in options for testing.
	 *
	 * @since 4.5.0
	 */
	function _set_custom_logo() {
		if ( ! $this->custom_logo_id ) {
			$this->_insert_custom_logo();
		}

		set_theme_mod( 'custom_logo', $this->custom_logo_id );
	}

	/**
	 * Removes the site icon from options.
	 *
	 * @since 4.5.0
	 */
	function _remove_custom_logo() {
		remove_theme_mod( 'custom_logo' );
	}

	/**
	 * Inserts an attachment for testing custom logos.
	 *
	 * @since 4.5.0
	 */
	function _insert_custom_logo() {
		$filename = DIR_TESTDATA . '/images/test-image.jpg';
		$contents = file_get_contents( $filename );
		$upload   = wp_upload_bits( wp_basename( $filename ), null, $contents );

		// Save the data.
		$this->custom_logo_url = $upload['url'];
		$this->custom_logo_id  = $this->_make_attachment( $upload );
		return $this->custom_logo_id;
	}

	/**
	 * Test get_the_modified_time
	 *
	 * @ticket 37059
	 *
	 * @since 4.6.0
	 */
	function test_get_the_modified_time_default() {
		$details = array(
			'post_date'     => '2016-01-21 15:34:36',
			'post_date_gmt' => '2016-01-21 15:34:36',
		);
		$post_id = $this->factory->post->create( $details );
		$post    = get_post( $post_id );

		$GLOBALS['post'] = $post;

		$expected = '1453390476';
		$format   = 'G';
		$actual   = get_the_modified_time( $format );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test get_the_modified_time failures are filtered
	 *
	 * @ticket 37059
	 *
	 * @since 4.6.0
	 */
	function test_get_the_modified_time_failures_are_filtered() {
		// Remove global post object.
		$GLOBALS['post'] = null;

		$expected = 'filtered modified time failure result';
		add_filter( 'get_the_modified_time', array( $this, '_filter_get_the_modified_time_failure' ) );
		$actual = get_the_modified_time();
		$this->assertEquals( $expected, $actual );
		remove_filter( 'get_the_modified_time', array( $this, '_filter_get_the_modified_time_failure' ) );
	}

	function _filter_get_the_modified_time_failure( $the_time ) {
		$expected = false;
		$actual   = $the_time;
		$this->assertEquals( $expected, $actual );

		if ( false === $the_time ) {
			return 'filtered modified time failure result';
		}
		return $the_time;
	}

	/**
	 * Test get_the_modified_time with post_id parameter.
	 *
	 * @ticket 37059
	 *
	 * @since 4.6.0
	 */
	function test_get_the_modified_date_with_post_id() {
		$details  = array(
			'post_date'     => '2016-01-21 15:34:36',
			'post_date_gmt' => '2016-01-21 15:34:36',
		);
		$post_id  = $this->factory->post->create( $details );
		$format   = 'Y-m-d';
		$expected = '2016-01-21';
		$actual   = get_the_modified_date( $format, $post_id );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test get_the_modified_date
	 *
	 * @ticket 37059
	 *
	 * @since 4.6.0
	 */
	function test_get_the_modified_date_default() {
		$details = array(
			'post_date'     => '2016-01-21 15:34:36',
			'post_date_gmt' => '2016-01-21 15:34:36',
		);
		$post_id = $this->factory->post->create( $details );
		$post    = get_post( $post_id );

		$GLOBALS['post'] = $post;

		$expected = '2016-01-21';
		$format   = 'Y-m-d';
		$actual   = get_the_modified_date( $format );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test get_the_modified_date failures are filtered
	 *
	 * @ticket 37059
	 *
	 * @since 4.6.0
	 */
	function test_get_the_modified_date_failures_are_filtered() {
		// Remove global post object.
		$GLOBALS['post'] = null;

		$expected = 'filtered modified date failure result';
		add_filter( 'get_the_modified_date', array( $this, '_filter_get_the_modified_date_failure' ) );
		$actual = get_the_modified_date();
		$this->assertEquals( $expected, $actual );
		remove_filter( 'get_the_modified_date', array( $this, '_filter_get_the_modified_date_failure' ) );
	}

	function _filter_get_the_modified_date_failure( $the_date ) {
		$expected = false;
		$actual   = $the_date;
		$this->assertEquals( $expected, $actual );

		if ( false === $the_date ) {
			return 'filtered modified date failure result';
		}
		return $the_date;
	}

	/**
	 * Test get_the_modified_time with post_id parameter.
	 *
	 * @ticket 37059
	 *
	 * @since 4.6.0
	 */
	function test_get_the_modified_time_with_post_id() {
		$details  = array(
			'post_date'     => '2016-01-21 15:34:36',
			'post_date_gmt' => '2016-01-21 15:34:36',
		);
		$post_id  = $this->factory->post->create( $details );
		$format   = 'G';
		$expected = '1453390476';
		$actual   = get_the_modified_time( $format, $post_id );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @ticket 38253
	 * @group ms-required
	 */
	function test_get_site_icon_url_preserves_switched_state() {
		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );

		$expected = $GLOBALS['_wp_switched_stack'];

		get_site_icon_url( 512, '', $blog_id );

		$result = $GLOBALS['_wp_switched_stack'];

		restore_current_blog();

		$this->assertSame( $expected, $result );
	}

	/**
	 * @ticket 38253
	 * @group ms-required
	 */
	function test_has_custom_logo_preserves_switched_state() {
		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );

		$expected = $GLOBALS['_wp_switched_stack'];

		has_custom_logo( $blog_id );

		$result = $GLOBALS['_wp_switched_stack'];

		restore_current_blog();

		$this->assertSame( $expected, $result );
	}

	/**
	 * @ticket 38253
	 * @group ms-required
	 */
	function test_get_custom_logo_preserves_switched_state() {
		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );

		$expected = $GLOBALS['_wp_switched_stack'];

		get_custom_logo( $blog_id );

		$result = $GLOBALS['_wp_switched_stack'];

		restore_current_blog();

		$this->assertSame( $expected, $result );
	}

	/**
	 * @ticket 43590
	 */
	function test_wp_no_robots() {
		// Simulate private site (search engines discouraged).
		update_option( 'blog_public', '0' );
		$actual_private = get_echo( 'wp_no_robots' );
		$this->assertSame( "<meta name='robots' content='noindex,nofollow' />\n", $actual_private );

		// Simulate public site.
		update_option( 'blog_public', '1' );
		$actual_public = get_echo( 'wp_no_robots' );
		$this->assertSame( "<meta name='robots' content='noindex,follow' />\n", $actual_public );
	}

	/**
	 * @ticket 40969
	 */
	function test_get_header_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Header/' );

		// The `get_header()` function must not return anything
		// due to themes in the wild that may echo its return value.
		$this->assertNull( get_header() );
	}

	/**
	 * @ticket 40969
	 */
	function test_get_footer_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Footer/' );

		// The `get_footer()` function must not return anything
		// due to themes in the wild that may echo its return value.
		$this->assertNull( get_footer() );
	}

	/**
	 * @ticket 40969
	 */
	function test_get_sidebar_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Sidebar/' );

		// The `get_sidebar()` function must not return anything
		// due to themes in the wild that may echo its return value.
		$this->assertNull( get_sidebar() );
	}

	/**
	 * @ticket 40969
	 */
	function test_get_template_part_returns_nothing_on_success() {
		$this->expectOutputRegex( '/Template Part/' );

		// The `get_template_part()` function must not return anything
		// due to themes in the wild that echo its return value.
		$this->assertNull( get_template_part( 'template', 'part' ) );
	}

	/**
	 * @ticket 40969
	 */
	function test_get_template_part_returns_false_on_failure() {
		$this->assertFalse( get_template_part( 'non-existing-template' ) );
	}

	/**
	 * @ticket 21676
	 */
	function test_get_template_part_passes_arguments_to_template() {
		$this->expectOutputRegex( '/{"foo":"baz"}/' );

		get_template_part( 'template', 'part', array( 'foo' => 'baz' ) );
	}
}
