<?php
/**
 * Admin menu and assets for Freezer Inventory.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Admin {

	public static function add_menu() {
		add_menu_page(
			__( 'Freezer Inventory', 'freezer-inventory' ),
			__( 'Freezer Inventory', 'freezer-inventory' ),
			'manage_options',
			'freezer-inventory',
			array( __CLASS__, 'render_page' ),
			'dashicons-products',
			30
		);
		add_submenu_page(
			'freezer-inventory',
			__( 'Locations', 'freezer-inventory' ),
			__( 'Locations', 'freezer-inventory' ),
			'manage_options',
			'freezer-locations',
			array( __CLASS__, 'render_locations_page' )
		);
		add_submenu_page(
			'freezer-inventory',
			__( 'Freezers', 'freezer-inventory' ),
			__( 'Freezers', 'freezer-inventory' ),
			'manage_options',
			'freezer-freezers',
			array( __CLASS__, 'render_freezers_page' )
		);
		add_submenu_page(
			'freezer-inventory',
			__( 'Item Names', 'freezer-inventory' ),
			__( 'Item Names', 'freezer-inventory' ),
			'manage_options',
			'freezer-item-names',
			array( __CLASS__, 'render_item_names_page' )
		);
		add_submenu_page(
			'freezer-inventory',
			__( 'CSV Import / Export', 'freezer-inventory' ),
			__( 'CSV Import / Export', 'freezer-inventory' ),
			'manage_options',
			'freezer-csv',
			array( __CLASS__, 'render_csv_page' )
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( $hook === 'toplevel_page_freezer-inventory' ) {
			self::do_enqueue_assets();
		} elseif ( $hook === 'freezer-inventory_page_freezer-locations' ) {
			self::do_enqueue_locations_assets();
		} elseif ( $hook === 'freezer-inventory_page_freezer-freezers' ) {
			self::do_enqueue_freezers_assets();
		} elseif ( $hook === 'freezer-inventory_page_freezer-item-names' ) {
			self::do_enqueue_item_names_assets();
		} elseif ( $hook === 'freezer-inventory_page_freezer-csv' ) {
			self::do_enqueue_csv_assets();
		}
	}

	public static function do_enqueue_assets() {
		wp_enqueue_style(
			'freezer-inventory-admin',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FREEZER_INVENTORY_VERSION
		);
		wp_enqueue_script(
			'freezer-inventory-admin',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/js/admin.js',
			array(),
			FREEZER_INVENTORY_VERSION,
			true
		);
		wp_localize_script( 'freezer-inventory-admin', 'freezerInventory', array(
			'restUrl'     => rest_url( 'freezer-inventory/v1' ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'locations'   => Freezer_Database::get_locations(),
			'freezers'    => Freezer_Database::get_freezers(),
			'itemNames'   => Freezer_Database::get_item_names(),
		) );
	}

	public static function do_enqueue_locations_assets() {
		wp_enqueue_style(
			'freezer-inventory-admin',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FREEZER_INVENTORY_VERSION
		);
		wp_enqueue_script(
			'freezer-inventory-locations',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/js/locations.js',
			array(),
			FREEZER_INVENTORY_VERSION,
			true
		);
		wp_localize_script( 'freezer-inventory-locations', 'freezerInventory', array(
			'restUrl'  => rest_url( 'freezer-inventory/v1' ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'freezers' => Freezer_Database::get_freezers(),
		) );
	}

	public static function do_enqueue_freezers_assets() {
		wp_enqueue_style(
			'freezer-inventory-admin',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FREEZER_INVENTORY_VERSION
		);
		wp_enqueue_script(
			'freezer-inventory-freezers',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/js/freezers.js',
			array(),
			FREEZER_INVENTORY_VERSION,
			true
		);
		wp_localize_script( 'freezer-inventory-freezers', 'freezerInventory', array(
			'restUrl' => rest_url( 'freezer-inventory/v1' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );
	}

	public static function render_page() {
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/admin-page.php';
	}

	public static function render_locations_page() {
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/locations-page.php';
	}

	public static function render_freezers_page() {
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/freezers-page.php';
	}

	public static function render_item_names_page() {
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/item-names-page.php';
	}

	public static function render_csv_page() {
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/csv-page.php';
	}

	public static function do_enqueue_csv_assets() {
		wp_enqueue_style(
			'freezer-inventory-admin',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FREEZER_INVENTORY_VERSION
		);
		wp_enqueue_script(
			'freezer-inventory-csv',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/js/csv.js',
			array(),
			FREEZER_INVENTORY_VERSION,
			true
		);
		wp_localize_script( 'freezer-inventory-csv', 'freezerInventory', array(
			'restUrl' => rest_url( 'freezer-inventory/v1' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );
	}

	public static function do_enqueue_item_names_assets() {
		wp_enqueue_style(
			'freezer-inventory-admin',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			FREEZER_INVENTORY_VERSION
		);
		wp_enqueue_script(
			'freezer-inventory-item-names',
			FREEZER_INVENTORY_PLUGIN_URL . 'admin/js/item-names.js',
			array(),
			FREEZER_INVENTORY_VERSION,
			true
		);
		wp_localize_script( 'freezer-inventory-item-names', 'freezerInventory', array(
			'restUrl' => rest_url( 'freezer-inventory/v1' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );
	}

	public static function shortcode() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '<p>You do not have permission to view the freezer inventory.</p>';
		}
		self::do_enqueue_assets();
		ob_start();
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/frontend-page.php';
		return ob_get_clean();
	}

	/**
	 * HTML for print/PDF (opened in new window).
	 */
	public static function get_print_html( $items ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Freezer Inventory</title>
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; color: #333; }
				h1 { margin-bottom: 20px; }
				table { width: 100%; border-collapse: collapse; }
				th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
				th { background: #667eea; color: white; }
				tr:nth-child(even) { background: #f9f9f9; }
			</style>
		</head>
		<body>
		<h1>Freezer Inventory</h1>
		<p>Generated on <?php echo esc_html( date_i18n( 'F j, Y g:i a' ) ); ?></p>
		<?php if ( empty( $items ) ) : ?>
			<p>No items in inventory.</p>
		<?php else : ?>
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>Category</th>
						<th>Quantity</th>
						<th>Unit</th>
						<th>Freezer</th>
						<th>Shelf</th>
						<th>Bin</th>
						<th>Raw / Cooked</th>
						<th>Date Added</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item['name'] ); ?></td>
							<td><?php echo esc_html( $item['category'] ); ?></td>
							<td><?php echo esc_html( $item['quantity'] ); ?></td>
							<td><?php echo esc_html( $item['unit'] ); ?></td>
							<td><?php echo esc_html( $item['freezer'] ?? '' ); ?></td>
							<td><?php echo esc_html( $item['shelf'] ?? '' ); ?></td>
							<td><?php echo esc_html( $item['bin'] ?? '' ); ?></td>
							<td><?php echo esc_html( $item['preparation'] ?? '' ); ?></td>
							<td><?php echo esc_html( date( 'Y-m-d', strtotime( $item['date_added'] ) ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
