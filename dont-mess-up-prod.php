<?php
/**
 * Plugin Name:       Don't Mess Up Prod
 * Plugin URI:        https://github.com/davekellam/dont-mess-up-prod
 * Description:       Displays the current environment in the admin bar
 * Version:           0.8.1
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

define( 'DONT_MESS_UP_PROD_VERSION', '0.8.1' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-environment-indicator.php';
Environment_Indicator::get_instance();
