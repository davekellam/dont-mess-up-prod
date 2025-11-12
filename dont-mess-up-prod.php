<?php
/**
 * Plugin Name:       Don't Mess Up Prod
 * Plugin URI:        https://github.com/davekellam/dont-mess-up-prod
 * Description:       Displays the current environment in the admin bar
 * Version:           0.5.0
 * Requires at least: 6.7
 * Requires PHP:      8.0
 * Author:            Dave Kellam
 * Author URI:        https://davekellam.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dont-mess-up-prod
 *
 * @package           DontMessUpProd
 * @author            Dave Kellam
 * @copyright         2025 Dave Kellam
 */

namespace DontMessUpProd;

define( 'DMUP_VERSION', '0.5.0' );
define( 'DMUP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DMUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load the main class.
require_once DMUP_PLUGIN_DIR . 'includes/class-environment-indicator.php';

/**
 * Initialize the plugin
 */
global $dmup_environment_indicator;
$dmup_environment_indicator = new Environment_Indicator();
