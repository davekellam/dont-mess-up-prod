<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the WordPress testing environment for the plugin.
 *
 * @package DontMessUpProd\Tests
 */

// Load the plugin
require_once __DIR__ . '/../includes/class-environment-indicator.php';

// Mock WordPress functions and classes
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		// Mock implementation
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		global $wp_filters;
		
		if ( ! isset( $wp_filters[ $tag ] ) ) {
			$wp_filters[ $tag ] = [];
		}
		
		if ( ! isset( $wp_filters[ $tag ][ $priority ] ) ) {
			$wp_filters[ $tag ][ $priority ] = [];
		}
		
		$wp_filters[ $tag ][ $priority ][] = $function_to_add;
		
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		global $wp_filters;
		
		if ( ! isset( $wp_filters[ $tag ] ) ) {
			return $value;
		}
		
		foreach ( $wp_filters[ $tag ] as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_callable( $callback ) ) {
					$value = call_user_func( $callback, $value );
				}
			}
		}
		
		return $value;
	}
}

if ( ! function_exists( 'wp_get_environment_type' ) ) {
	function wp_get_environment_type() {
		return 'production';
	}
}

if ( ! function_exists( 'get_site_url' ) ) {
	function get_site_url() {
		return 'https://example.com';
	}
}

if ( ! function_exists( 'is_admin_bar_showing' ) ) {
	function is_admin_bar_showing() {
		return true;
	}
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return true;
	}
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	function wp_get_current_user() {
		return (object) [
			'ID' => 1,
			'user_login' => 'test_user',
			'user_email' => 'test@example.com',
		];
	}
}

if ( ! function_exists( 'user_can' ) ) {
	function user_can( $user, $capability ) {
		return true;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'plugins_url' ) ) {
	function plugins_url( $path = '', $plugin = '' ) {
		return 'https://example.com/wp-content/plugins' . $path;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {
		// Mock implementation
		return true;
	}
}

if ( ! function_exists( 'wp_add_inline_style' ) ) {
	function wp_add_inline_style( $handle, $style ) {
		// Mock implementation
		return true;
	}
}

// Mock WP_Admin_Bar class
if ( ! class_exists( 'WP_Admin_Bar' ) ) {
	class WP_Admin_Bar {
		public function add_node( $args ) {
			// Mock implementation
			return true;
		}
	}
}

// Global filters storage for testing
$GLOBALS['wp_filters'] = [];

// Mock WP_UnitTestCase for WordPress testing
if ( ! class_exists( 'WP_UnitTestCase' ) ) {
	abstract class WP_UnitTestCase extends PHPUnit\Framework\TestCase {
		protected function setUp(): void {
			// Reset global filters before each test
			$GLOBALS['wp_filters'] = [];
		}
	}
}