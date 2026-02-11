<?php
/**
 * Plugin Name: Freezer Inventory Manager
 * Plugin URI: https://github.com/proadmin/Freezer
 * Description: Manage your freezer inventory with categories, locations, partial quantities, and PDF export.
 * Version: 2.0.10
 * Author: Freezer Inventory
 * Author URI: ''
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: freezer-inventory
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'FREEZER_INVENTORY_VERSION', '2.0.10' );
define( 'FREEZER_INVENTORY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FREEZER_INVENTORY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-database.php';
require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-rest.php';
require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-admin.php';
require_once FREEZER_INVENTORY_PLUGIN_DIR . 'includes/class-freezer-updater.php';

register_activation_hook( __FILE__, function() {
	Freezer_Database::create_table();
	Freezer_Database::migrate_locations();
	Freezer_Database::migrate_freezers();
	Freezer_Database::migrate_item_names();
	Freezer_Database::migrate_categories();
	Freezer_Database::migrate_strip_location_prefixes();
} );

// Upgrade existing installs.
add_action( 'plugins_loaded', function() {
	$installed = get_option( 'freezer_inventory_db_version', '0' );
	if ( version_compare( $installed, '2.5.0', '<' ) ) {
		Freezer_Database::create_table();
		Freezer_Database::migrate_locations();
		Freezer_Database::migrate_freezers();
		Freezer_Database::migrate_item_names();
		Freezer_Database::migrate_categories();
		Freezer_Database::migrate_strip_location_prefixes();
		update_option( 'freezer_inventory_db_version', '2.5.0' );
	}
} );

Freezer_Updater::init( __FILE__ );

add_action( 'rest_api_init', array( 'Freezer_Rest', 'register_routes' ) );
add_action( 'admin_menu', array( 'Freezer_Admin', 'add_menu' ) );
add_action( 'admin_enqueue_scripts', array( 'Freezer_Admin', 'enqueue_assets' ) );
add_shortcode( 'freezer_inventory', array( 'Freezer_Admin', 'shortcode' ) );
