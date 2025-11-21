<?php
/**
 * Environment Indicator Class
 *
 * Handles the display and management of the environment indicator in the WordPress admin bar
 *
 * @package DontMessUpProd
 * @since   0.4.0
 */

namespace DontMessUpProd;

use WP_Admin_Bar;

/**
 * Environment Indicator Class
 *
 * Manages environment detection and admin bar display functionality
 */
class Environment_Indicator {
	/**
	 * Cached singleton instance
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Default environment colors
	 *
	 * These names match the WP_ENVIRONMENT_TYPE constant values
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
	 * Environment URLs
	 *
	 * @var array<string, string>
	 */
	private array $environment_urls = [];

	/**
	 * Allowed user logins who can see the indicator
	 *
	 * @var array<string>
	 */
	private array $allowed_users = [];

	/**
	 * Gets the singleton instance of the Environment_Indicator class
	 *
	 * The class bootstraps itself on first access, so including this file is
	 * sufficient to have the indicator available.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === static::$instance ) {
			static::$instance = new static();
			static::$instance->add_hooks();
		}

		return static::$instance;
	}

	/**
	 * Use get_instance() instead
	 */
	private function __construct() {}

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
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance
	 * @return void
	 */
	public function add_environment_indicator_item( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( ! $this->current_user_can_see_indicator() ) {
			return;
		}

		$environment      = $this->get_current_environment();
		$environment_urls = $this->get_environment_urls();

		$wp_admin_bar->add_node(
			[
				'id'         => 'dmup-environment-indicator',
				'title'      => sprintf(
					'<span class="dmup-environment-indicator">%s</span>',
					esc_html( $environment )
				),
				'href'       => false,
				'parent'     => 'top-secondary',
				'menu_title' => __( 'Environment Indicator', 'dont-mess-up-prod' ),
				'meta'       => [
					'class' => 'dmup-environment-' . esc_attr( $environment ),
				],
			]
		);

		if ( ! empty( $environment_urls ) ) {
			foreach ( $environment_urls as $environment_name => $url ) {
				$wp_admin_bar->add_node(
					[
						'id'     => 'dmup-environment-indicator-' . sanitize_key( $environment_name ),
						'title'  => ucwords( esc_html( $environment_name ) ),
						'href'   => esc_url( $url ),
						'parent' => 'dmup-environment-indicator',
						'meta'   => [
							'class'  => 'dmup-nav-external',
							'target' => '_blank',
							'rel'    => 'noopener',
						],
					]
				);
			}
		}
	}

	/**
	 * Determines the current environment
	 *
	 * Checks the the site URL against a configurable list of environment URLs first,
	 * then falls back to checking to the WP_ENVIRONMENT_TYPE constant if defined
	 *
	 * @return string The current environment type (e.g., 'local', 'staging', 'production')
	 */
	public function get_current_environment(): string {
		$environment_urls = $this->get_environment_urls();
		$current_url      = get_site_url();

		foreach ( $environment_urls as $env => $url ) {
			if ( str_contains( $current_url, $url ) ) {
				return $env;
			}
		}

		if ( defined( 'WP_ENVIRONMENT_TYPE' ) ) {
			$wp_environment_type = WP_ENVIRONMENT_TYPE;
			if ( $wp_environment_type ) {
				return $wp_environment_type;
			}
		}

		$default_message = __( 'No Environment Set', 'dont-mess-up-prod' );

		return apply_filters( 'dmup_no_environment_set_message', $default_message );
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
		return apply_filters( 'dmup_environment_urls', $this->environment_urls );
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
				display: inline-block;
				font-weight: bold;
				padding: 0 15px;	
				height:100%;
				text-transform: capitalize;
			}

			<?php foreach ( $colors as $environment => $color ) : ?>
				#wpadminbar .dmup-environment-<?php echo esc_attr( $environment ); ?> {
					background-color: <?php echo esc_attr( $color ); ?>;
				}
			<?php endforeach; ?>

		</style>
		<?php

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
