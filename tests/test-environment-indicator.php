<?php
/**
 * Environment Indicator Tests
 *
 * Tests for the Environment_Indicator class and its filters.
 *
 * @package DontMessUpProd\Tests
 */

use DontMessUpProd\Environment_Indicator;

/**
 * Environment Indicator Test Class
 */
class Test_Environment_Indicator extends WP_UnitTestCase {

	/**
	 * Environment Indicator instance
	 *
	 * @var Environment_Indicator
	 */
	private $indicator;

	/**
	 * Set up test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->indicator = Environment_Indicator::get_instance();
	}

	/**
	 * Test dmup_environment_colors filter
	 */
	public function test_dmup_environment_colors_filter() {
		// Get default colors
		$default_colors = $this->indicator->get_default_colors();
		
		// Test without filter
		$colors = $this->indicator->get_environment_colors();
		$this->assertEquals( $default_colors, $colors );

		// Add filter to modify colors
		add_filter( 'dmup_environment_colors', function( $colors ) {
			$colors['local'] = '#ff0000';
			$colors['custom'] = '#00ff00';
			return $colors;
		} );

		// Test with filter
		$filtered_colors = $this->indicator->get_environment_colors();
		$this->assertEquals( '#ff0000', $filtered_colors['local'] );
		$this->assertEquals( '#00ff00', $filtered_colors['custom'] );
		$this->assertNotEquals( $default_colors['local'], $filtered_colors['local'] );
	}

	/**
	 * Test dmup_environment_urls filter
	 */
	public function test_dmup_environment_urls_filter() {
		// Test without filter
		$urls = $this->indicator->get_environment_urls();
		$this->assertEquals( [], $urls );

		// Add filter to set URLs
		add_filter( 'dmup_environment_urls', function( $urls ) {
			return [
				'local' => 'localhost',
				'staging' => 'staging.example.com',
			];
		} );

		// Test with filter
		$filtered_urls = $this->indicator->get_environment_urls();
		$this->assertEquals( 'localhost', $filtered_urls['local'] );
		$this->assertEquals( 'staging.example.com', $filtered_urls['staging'] );
	}

	/**
	 * Test dmup_allowed_users filter
	 */
	public function test_dmup_allowed_users_filter() {
		// Test without filter
		$users = $this->indicator->get_allowed_users();
		$this->assertEquals( [], $users );

		// Add filter to set allowed users
		add_filter( 'dmup_allowed_users', function( $users ) {
			return [ 'admin_user', 'developer_user' ];
		} );

		// Test with filter
		$filtered_users = $this->indicator->get_allowed_users();
		$this->assertEquals( [ 'admin_user', 'developer_user' ], $filtered_users );
	}

	/**
	 * Test dmup_minimum_capability filter
	 */
	public function test_dmup_minimum_capability_filter() {
		// Use reflection to test private method
		$reflection = new ReflectionClass( $this->indicator );
		$method = $reflection->getMethod( 'get_minimum_capability' );
		$method->setAccessible( true );

		// Test without filter
		$capability = $method->invoke( $this->indicator );
		$this->assertEquals( 'publish_posts', $capability );

		// Add filter to modify capability
		add_filter( 'dmup_minimum_capability', function( $capability ) {
			return 'manage_options';
		} );

		// Test with filter
		$filtered_capability = $method->invoke( $this->indicator );
		$this->assertEquals( 'manage_options', $filtered_capability );

		// Test disabling capability check
		add_filter( 'dmup_minimum_capability', function( $capability ) {
			return false;
		} );

		$disabled_capability = $method->invoke( $this->indicator );
		$this->assertFalse( $disabled_capability );
	}

	/**
	 * Test get_environment_color method
	 */
	public function test_get_environment_color() {
		// Test with default colors
		$color = $this->indicator->get_environment_color( 'local' );
		$this->assertEquals( '#6c757d', $color );

		// Test with unknown environment (should return local color)
		$unknown_color = $this->indicator->get_environment_color( 'unknown' );
		$this->assertEquals( '#6c757d', $unknown_color );

		// Test with filtered colors
		add_filter( 'dmup_environment_colors', function( $colors ) {
			$colors['local'] = '#ff0000';
			return $colors;
		} );

		$filtered_color = $this->indicator->get_environment_color( 'local' );
		$this->assertEquals( '#ff0000', $filtered_color );
	}

	/**
	 * Test get_current_environment method
	 */
	public function test_get_current_environment() {
		// Test without URLs (should return wp_get_environment_type)
		$environment = $this->indicator->get_current_environment();
		$this->assertEquals( 'production', $environment );

		// Test with URLs that match current site
		add_filter( 'dmup_environment_urls', function( $urls ) {
			return [
				'local' => 'example.com',
				'staging' => 'staging.example.com',
			];
		} );

		$matched_environment = $this->indicator->get_current_environment();
		$this->assertEquals( 'local', $matched_environment );
	}
}