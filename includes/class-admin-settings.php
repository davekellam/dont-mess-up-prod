<?php
/**
 * Admin Settings Class
 *
 * Handles the admin interface for configuring environment settings
 *
 * @package DontMessUpProd
 * @since   1.0.0
 */

namespace DontMessUpProd;

/**
 * Admin Settings Class
 *
 * Manages the settings page and configuration options for environment colors and URLs
 */
class Admin_Settings {
	/**
	 * Cached singleton instance
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Settings group name
	 *
	 * @var string
	 */
	private const SETTINGS_GROUP = 'dmup_settings';

	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	private const PAGE_SLUG = 'dont-mess-up-prod';

	/**
	 * Environment definitions
	 *
	 * These are currently hardcoded into WordPress with no way to retrieve.
	 *
	 * @var array<string>
	 */
	private array $environments = [
		'local',
		'development',
		'staging',
		'production',
	];

	/**
	 * Gets the singleton instance of the Admin_Settings class
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
	 * Add WordPress hooks
	 *
	 * @return void
	 */
	public function add_hooks(): void {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_filter( 'dmup_environment_colors', [ $this, 'apply_saved_colors' ], 20 );
		add_filter( 'dmup_environment_urls', [ $this, 'apply_saved_urls' ], 20 );
	}

	/**
	 * Add settings page to WordPress admin menu
	 *
	 * @return void
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Don\'t Mess Up Prod Settings', 'dont-mess-up-prod' ),
			__( 'Don\'t Mess Up Prod', 'dont-mess-up-prod' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings and fields
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::SETTINGS_GROUP,
			self::SETTINGS_GROUP,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'default'           => [],
			]
		);

		// Add settings section
		add_settings_section(
			'dmup_environments_section',
			__( 'Environment Configuration', 'dont-mess-up-prod' ),
			[ $this, 'render_section_description' ],
			self::PAGE_SLUG
		);

		// Add fields for each environment
		foreach ( $this->environments as $env ) {
			add_settings_field(
				"dmup_{$env}_settings",
				ucwords( $env ),
				[ $this, 'render_environment_fields' ],
				self::PAGE_SLUG,
				'dmup_environments_section',
				[ 'environment' => $env ]
			);
		}
	}

	/**
	 * Render the settings page
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'dont-mess-up-prod' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		?>
		<p>
			<?php
			esc_html_e(
				'Configure the colors and URLs for each environment. The environment indicator will use these settings to display the appropriate color and provide quick links to other environments.',
				'dont-mess-up-prod'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render environment fields
	 *
	 * Will produce the color picker and URL input for each of the environments
	 *
	 * @param array $args Field arguments containing the environment key
	 * @return void
	 */
	public function render_environment_fields( array $args ): void {
		$env            = $args['environment'];
		$default_colors = Environment_Indicator::get_instance()->get_default_colors();
		$options        = get_option( self::SETTINGS_GROUP, [] );
		$env_options    = $options[ $env ] ?? [];
		$color_value    = $env_options['color'] ?? $default_colors[ $env ];
		$url_value      = $env_options['url'] ?? '';
		$color_id       = "dmup_settings_{$env}_color";
		$url_id         = "dmup_settings_{$env}_url";
		?>
		<div style="margin-bottom: 10px;">
			<label for="<?php echo esc_attr( $color_id ); ?>" style="display: inline-block; width: 80px;">
				<?php esc_html_e( 'Color:', 'dont-mess-up-prod' ); ?>
			</label>
			<input 
				type="color" 
				id="<?php echo esc_attr( $color_id ); ?>" 
				name="<?php echo esc_attr( self::SETTINGS_GROUP ); ?>[<?php echo esc_attr( $env ); ?>][color]" 
				value="<?php echo esc_attr( $color_value ); ?>"
				style="width: 60px; height: 30px; vertical-align: middle;"
			/>
			<code style="margin-left: 10px; vertical-align: middle;"><?php echo esc_html( $color_value ); ?></code>
		</div>
		<div>
			<label for="<?php echo esc_attr( $url_id ); ?>" style="display: inline-block; width: 80px;">
				<?php esc_html_e( 'URL:', 'dont-mess-up-prod' ); ?>
			</label>
			<input 
				type="url" 
				id="<?php echo esc_attr( $url_id ); ?>" 
				name="<?php echo esc_attr( self::SETTINGS_GROUP ); ?>[<?php echo esc_attr( $env ); ?>][url]" 
				value="<?php echo esc_attr( $url_value ); ?>"
				placeholder="<?php esc_attr_e( 'https://example.com', 'dont-mess-up-prod' ); ?>"
				class="regular-text"
			/>
		</div>
		<?php
	}

	/**
	 * Sanitize settings for all environments
	 *
	 * @param array $settings Raw settings array
	 * @return array Sanitized settings array
	 */
	public function sanitize_settings( array $settings ): array {
		$default_colors = Environment_Indicator::get_instance()->get_default_colors();
		$sanitized      = [];

		foreach ( $this->environments as $env ) {
			$env_settings = $settings[ $env ] ?? [];
			$color        = '';
			$url          = '';

			if ( isset( $env_settings['color'] ) ) {
				$color = sanitize_hex_color( $env_settings['color'] );
			}

			if ( isset( $env_settings['url'] ) ) {
				$url = esc_url_raw( $env_settings['url'] );
			}

			// ignore if color is default
			if ( $color && $color !== $default_colors[ $env ] ) {
				$sanitized[ $env ]['color'] = $color;
			}

			// ignore if URL is empty (will silently remove invalid urls though)
			if ( '' !== $url ) {
				$sanitized[ $env ]['url'] = $url;
			}
		}

		return $sanitized;
	}

	/**
	 * Apply saved colors to the environment colors filter
	 *
	 * @param array $colors Default colors array
	 * @return array Modified colors array
	 */
	public function apply_saved_colors( array $colors ): array {
		$options = get_option( self::SETTINGS_GROUP, [] );

		foreach ( $this->environments as $env ) {
			$saved_color = $options[ $env ]['color'] ?? '';
			if ( $saved_color ) {
				$colors[ $env ] = $saved_color;
			}
		}

		return $colors;
	}

	/**
	 * Apply saved URLs to the environment URLs filter
	 *
	 * @param array $urls Default URLs array
	 * @return array Modified URLs array
	 */
	public function apply_saved_urls( array $urls ): array {
		$options = get_option( self::SETTINGS_GROUP, [] );

		foreach ( $this->environments as $env ) {
			$saved_url = $options[ $env ]['url'] ?? '';
			if ( $saved_url ) {
				$urls[ $env ] = $saved_url;
			}
		}

		return $urls;
	}
}
