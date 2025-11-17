# Don't Mess Up Prod

This plugin displays a colored environment indicator in the WordPress admin bar to help developers and content managers quickly identify which environment they're working in. This helps prevent accidentally making changes to production sites when you think you're working on staging or development.

## Installation

1. Upload the plugin files to `/wp-content/plugins/dont-mess-up-prod/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure the plugin using filters (see Configuration section below)

## Configuration

The plugin can be configured using WordPress filters. The recommended approach is to create a mu-plugin file in `/wp-content/mu-plugins/` with your configuration.

### Example Configuration (mu-plugin)

Create a file `/wp-content/mu-plugins/dmup-config.php`:

```php
<?php
/**
 * Plugin Name: Don't Mess Up Prod Configuration
 */

/**
 * Configure minimum capability for the environment indicator
 *
 * By default, the plugin only shows to explicitly allowed users.
 * Use this filter to enable role-based access.
 *
 * @param string|false $capability Current capability setting.
 * @return string|false Modified capability setting.
 */
function dmup_set_minimum_capability( $capability ) {
    // Enable for Author level and above
    return 'publish_posts';
}
add_filter( 'dmup_minimum_capability', 'dmup_set_minimum_capability' );

/**
 * Configure allowed users for the environment indicator
 *
 * Add specific user logins that should see the environment indicator
 * regardless of their role level.
 *
 * @param array $users Current allowed users array.
 * @return array Modified allowed users array.
 */
function dmup_set_allowed_users( $users ) {
    // Add specific user logins here
    $project_users = [
        'developer-name',
        'content-manager-name',
    ];

    return array_merge( $users, $project_users );
}
add_filter( 'dmup_allowed_users', 'dmup_set_allowed_users' );

/**
 * Configure environment URLs for your project
 *
 * Customize the URLs used to detect different environments
 *
 * @param array $urls Current environment URLs array.
 * @return array Modified environment URLs array.
 */
function dmup_set_environment_urls( $urls ) {
    return [
        'local'       => 'http://yourproject.local',
        'development' => 'https://dev.yourproject.com',
        'staging'     => 'https://staging.yourproject.com',
        'production'  => 'https://yourproject.com',
    ];
}
add_filter( 'dmup_environment_urls', 'dmup_set_environment_urls' );

/**
 * Configure environment colors for your project
 *
 * Customize the colors used for each environment
 *
 * @param array $colors Current environment colors array.
 * @return array Modified environment colors array.
 */
function dmup_set_environment_colors( $colors ) {
    return [
        'local'       => '#17a2b8', // blue
        'development' => '#6f42c1', // purple
        'staging'     => '#ffc107', // yellow
        'production'  => '#dc3545', // red
    ];
}
add_filter( 'dmup_environment_colors', 'dmup_set_environment_colors' );
```

## Environment Detection

The plugin detects the current environment using this priority order:

1. **WP_ENVIRONMENT_TYPE constant** - Set in `wp-config.php`:

   ```php
   define( 'WP_ENVIRONMENT_TYPE', 'staging' );
   ```

2. **URL matching** - Compares current site URL against the configured environment URLs

3. **Fallback** - Defaults to 'production' if no match is found

## License

GPL-2.0-or-later
