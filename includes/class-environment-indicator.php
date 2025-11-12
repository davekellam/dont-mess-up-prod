<?php
/**
 * Environment Indicator Class
 *
 * Handles the display and management of the environment indicator in the WordPress admin bar.
 *
 * @package DontMessUpProd
 * @since   0.4.0
 */

namespace DontMessUpProd;

/**
 * Environment Indicator Class
 *
 * Manages environment detection and admin bar display functionality.
 */
class Environment_Indicator {
	/**
	 * Default environment colors
	 *
	 * @var array<string, string>
	 */
	private array $default_colors = [
		'local'       => '#6c757d', // Gray
		'development' => '#6f42c1', // Purple
		'staging'     => '#28a745', // Green
		'production'  => '#dc3545', // Red
	];

	/**
	 * Default environment URLs
	 *
	 * @var array<string, string>
	 */
	private array $default_environment_urls = [
		'local'       => 'http://example.local',
		'development' => 'https://dev.example.com',
		'staging'     => 'https://staging.example.com',
		'production'  => 'https://example.com',
	];

	/**
	 * Allowed user logins who can see the indicator (use filter instead of modifying directly).
	 *
	 * @var array<string>
	 */
	private array $allowed_users = [];

	/**
	 * Setup hooks and default values
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Initialize the environment indicator
	 *
	 * @return void
	 */
	public function add_hooks(): void {
		add_action( 'admin_bar_menu', [ $this, 'add_environment_indicator_item' ], 999 );
		add_action( 'admin_head', [ $this, 'add_styles' ] );
		add_action( 'wp_head', [ $this, 'add_styles' ] );
	}

	/**
	 * Adds an environment indicator item to the WordPress admin bar
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance
	 * @return void
	 */
	public function add_environment_indicator_item( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( ! $this->current_user_can_see_indicator() ) {
			return;
		}

		$environment = $this->get_current_environment();

		$wp_admin_bar->add_node(
			[
				'id'     => 'environment-indicator',
				'title'  => sprintf(
					'<span class="dmup-environment-indicator dmup-environment-%s">%s</span>',
					esc_attr( $environment ),
					esc_html( ucfirst( $environment ) )
				),
				'href'   => false,
				'parent' => 'top-secondary',
			]
		);
	}

	/**
	 * Determines the current environment
	 *
	 * Checks the WP_ENVIRONMENT_TYPE constant first, then falls back to checking
	 * the site URL against a configurable list of environment URLs
	 *
	 * @return string The current environment type (e.g., 'local', 'staging', 'production')
	 */
	public function get_current_environment(): string {
		if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE ) {
			return WP_ENVIRONMENT_TYPE;
		}

		$environment_urls = $this->get_environment_urls();
		$current_url      = get_site_url();

		foreach ( $environment_urls as $env => $url ) {
			if ( str_contains( $current_url, $url ) ) {
				return $env;
			}
		}

		return 'production'; // Default to 'production' if no match is found
	}

	/**
	 * Gets the corresponding color for a given environment
	 *
	 * @param string $environment The environment name
	 * @return string The hex color code
	 */
	public function get_environment_color( string $environment ): string {
		$colors = $this->get_environment_colors();
		return $colors[ $environment ] ?? $colors['local'];
	}

	/**
	 * Gets environment colors with filter support
	 *
	 * @return array<string, string> Environment colors array
	 */
	public function get_environment_colors(): array {
		/**
		 * Filters the environment indicator colors
		 *
		 * @since 0.2.0
		 *
		 * @param array<string, string> $colors Array of environment colors
		 */
		return apply_filters( 'dmup_environment_colors', $this->default_colors );
	}

	/**
	 * Gets environment URLs with filter support
	 *
	 * @return array<string, string> Environment URLs array
	 */
	public function get_environment_urls(): array {
		/**
		 * Filters the environment indicator URLs
		 *
		 * @since 0.2.0
		 *
		 * @param array<string, string> $urls Array of environment URLs
		 */
		return apply_filters( 'dmup_environment_urls', $this->default_environment_urls );
	}

	/**
	 * Gets allowed users with filter support
	 *
	 * @return array<string> Allowed user logins array
	 */
	public function get_allowed_users(): array {
		/**
		 * Filters the users allowed to see the environment indicator
		 *
		 * @since 0.2.0
		 *
		 * @param array<string> $users Array of allowed user logins.
		 */
		return apply_filters( 'dmup_allowed_users', $this->allowed_users );
	}

	/**
	 * Checks if the current user can see the environment indicator
	 *
	 * 1. Check against filtered capability
	 * 2. Check against filtered allowed users list
	 *
	 * @since 0.3.0
	 *
	 * @return bool True if the user can see the indicator, false otherwise
	 */
	private function current_user_can_see_indicator(): bool {
		// Must be logged in
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$current_user = wp_get_current_user();

		// Check if user has the minimum required capability (filterable)
		$minimum_capability = $this->get_minimum_capability();
		if ( $minimum_capability && user_can( $current_user, $minimum_capability ) ) {
			return true;
		}

		// Check against explicitly allowed users (via filter)
		$allowed_users = $this->get_allowed_users();
		if ( ! empty( $allowed_users ) && in_array( $current_user->user_login, $allowed_users, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets the minimum capability required to see the environment indicator
	 *
	 * @since 0.3.0
	 *
	 * @return string|false The capability required, or false to disable capability-based access
	 */
	private function get_minimum_capability() {
		/**
		 * Filters the minimum capability required to see the environment indicator
		 *
		 * @since 0.3.0
		 *
		 * @param string|false $capability The user capability required to see the indicator
		 *                                 Default is false (disabled by default)
		 *                                 Examples: 'publish_posts' (author+), 'edit_posts' (contributor+)
		 */
		return apply_filters( 'dmup_minimum_capability', false );
	}

	/**
	 * Adds styles for the environment indicator
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	public function add_styles(): void {
		if ( ! $this->current_user_can_see_indicator() ) {
			return;
		}

		$colors = $this->get_environment_colors();

		ob_start();
		?>
		<style id="dmup-styles">
			#wpadminbar .dmup-environment-indicator {
				background-color: #333;
				border-radius: 5px;
				color: #fff;
				display: inline-block;
				font-size: 11px;
				font-weight: bold;
				letter-spacing: 0.5px;
				line-height: 15px;
				padding: 2px 10px;
				text-transform: uppercase;
			}

			<?php foreach ( $colors as $environment => $color ) : ?>
				#wpadminbar .dmup-environment-<?php echo esc_attr( $environment ); ?> {
					background-color: <?php echo esc_attr( $color ); ?> !important;
				}
			<?php endforeach; ?>

		</style>
		<?php

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
