=== Don't Mess Up Prod ===
Contributors: eightface
Tags: environment, admin bar, development, debug
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 0.9.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Displays a colored environment indicator in the admin bar.

== Description ==

Don't Mess Up Prod helps developers and content managers quickly identify which environment they're working in by displaying a colored indicator in the WordPress admin bar. Hopefully this prevents messing up production 😅

[Active development is on Github](https://github.com/davekellam/dont-mess-up-prod)

== Installation ==

The plugin can be installed via the search interface, manually or via composer.

Right now, there is no admin UI. To customize colors or configure urls, you need to add filters via code (`functions.php` or a mu-plugin). See the FAQ for examples.


== Frequently Asked Questions ==

= How do I configure which environment I'm in? =

The plugin detects your environment automatically in two ways:

1. **URL Matching** – Configure environment URLs using the `dmup_environment_urls` filter
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
