<?php
/**
 * Plugin Name: Freezer Inventory Manager
 * Plugin URI: https://github.com/your-repo/freezer-inventory
 * Description: Manage your freezer inventory with categories, locations, partial quantities, and PDF export.
 * Version: 1.0.1
 * Author: Freezer Inventory
 * Author URI: ''
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: freezer-inventory
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'FREEZER_INVENTORY_VERSION', '1.0.1' );
define( 'FREEZER_INVENTORY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FREEZER_INVENTORY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-database.php';
require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-rest.php';
require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-admin.php';

register_activation_hook( __FILE__, array( 'Freezer_Database', 'create_table' ) );

add_action( 'rest_api_init', array( 'Freezer_Rest', 'register_routes' ) );
add_action( 'admin_menu', array( 'Freezer_Admin', 'add_menu' ) );
add_action( 'admin_enqueue_scripts', array( 'Freezer_Admin', 'enqueue_assets' ) );
add_shortcode( 'freezer_inventory', array( 'Freezer_Admin', 'shortcode' ) );
