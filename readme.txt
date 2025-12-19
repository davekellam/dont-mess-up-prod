=== Don't Mess Up Prod ===
Contributors: eightface
Tags: environment, admin bar, development, debug
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 0.9.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Displays a colored environment indicator in the admin bar.

== Description ==

Don't Mess Up Prod helps developers and content managers quickly identify which environment they're working in by displaying a colored indicator in the WordPress admin bar. Hopefully this prevents messing up production 😅

== Installation ==

**Automatic Installation:**

1. Go to Plugins > Add New in your WordPress admin
2. Search for "Don't Mess Up Prod"
3. Click "Install Now" and then "Activate"

**Manual Installation:**

1. Upload the plugin files to `/wp-content/plugins/dont-mess-up-prod/`
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= How do I configure which environment I'm in? =

The plugin detects your environment automatically in two ways:

1. **URL Matching** – Configure environment URLs using the `dmup_environment_urls` filter (see Configuration below)
2. **WP_ENVIRONMENT_TYPE** – Set this constant in your `wp-config.php`:

`define( 'WP_ENVIRONMENT_TYPE', 'staging' );`

= How do I customize the colors? =

Add a filter in your theme's `functions.php` or an mu-plugin:

`
add_filter( 'dmup_environment_colors', function( $colors ) {
    return [
        'local'       => '#17a2b8', // blue
        'development' => '#6f42c1', // purple
        'staging'     => '#ffc107', // yellow
        'production'  => '#dc3545', // red
    ];
} );
`

= How do I control who can see the indicator? =

By default, users with the `publish_posts` capability can see it. Customize this:

**Role-based access:**

`
add_filter( 'dmup_minimum_capability', function() {
    return 'edit_posts'; // or 'manage_options', etc.
} );
`

**Specific users only:**

`
add_filter( 'dmup_allowed_users', function( $users ) {
    return array_merge( $users, [ 'johndoe', 'janedoe' ] );
} );
`

= How do I add environment URLs for quick switching? =

Configure environment URLs to show a menu with links to other environments:

`
add_filter( 'dmup_environment_urls', function() {
    return [
        'local'       => 'http://yourproject.local',
        'development' => 'https://dev.yourproject.com',
        'staging'     => 'https://staging.yourproject.com',
        'production'  => 'https://yourproject.com',
    ];
} );
`

== Screenshots ==

1. Production environment indicator (red) in the admin bar
2. Staging environment indicator (green) with environment switcher menu
3. Development environment indicator (purple)
4. Local environment indicator (grey)
5. Staging environment indicator with environment switcher menu

== Changelog ==

= 0.9.1 =
* Initial wordpress.org release

= 0.9.0 =
* Refactored to use external stylesheet with CSS variables and enqueue system
* Added CSS custom property support

= 0.8.1 =
* Added WordPress Playground blueprints for live demos

= 0.8.0 =
* Color-coded environment indicators
* Environment switcher menu
* Customizable via WordPress filters
* Role-based and user-based visibility controls

== Upgrade Notice ==

= 0.9.1 =
WordPress.org deployment automation and updated Playground blueprints.

= 0.9.0 =
Added stylesheet instead of inline css

== Configuration ==

Add filters via `functions.php` or a mu-plugin

`
<?php
/**
 * Plugin Name: Don't Mess Up Prod Configuration
 */

// Set minimum capability
add_filter( 'dmup_minimum_capability', function() {
    return 'publish_posts';
} );

// Add specific allowed users
add_filter( 'dmup_allowed_users', function( $users ) {
    return array_merge( $users, [
        'developer-name',
        'content-manager-name',
    ] );
} );

// Configure environment URLs
add_filter( 'dmup_environment_urls', function() {
    return [
        'local'       => 'http://yourproject.local',
        'development' => 'https://dev.yourproject.com',
        'staging'     => 'https://staging.yourproject.com',
        'production'  => 'https://yourproject.com',
    ];
} );

// Customize colors
add_filter( 'dmup_environment_colors', function() {
    return [
        'local'       => '#6c757d', // grey
        'development' => '#6f42c1', // purple
        'staging'     => '#28a745', // green
        'production'  => '#dc3545', // red
    ];
} );
`
