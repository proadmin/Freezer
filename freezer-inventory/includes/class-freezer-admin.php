<?php
/**
 * Admin menu and assets for Freezer Inventory.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Admin {

	const LOCATIONS = array(
		'Shelf 1 Bin 1',
		'Shelf 1 Bin 2',
		'Shelf 1 Bin 3',
		'Shelf 2 Bin 1',
		'Shelf 2 Bin 2',
		'Shelf 2 Bulk',
		'Door Shelf 1',
		'Door Shelf 2',
	);

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
	}

	public static function enqueue_assets( $hook ) {
		if ( $hook !== 'toplevel_page_freezer-inventory' ) {
			return;
		}
		self::do_enqueue_assets();
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
			'restUrl'   => rest_url( 'freezer-inventory/v1' ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'locations' => self::LOCATIONS,
		) );
	}

	public static function render_page() {
		$locations = self::LOCATIONS;
		include FREEZER_INVENTORY_PLUGIN_DIR . 'admin/views/admin-page.php';
	}

	public static function shortcode() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '<p>You do not have permission to view the freezer inventory.</p>';
		}
		self::do_enqueue_assets();
		ob_start();
		self::render_page();
		return ob_get_clean();
	}

	/**
	 * HTML for print/PDF (opened in new window).
	 *
	 * @param array $items
	 * @return string
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
						<th>Location</th>
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
							<td><?php echo esc_html( $item['location'] ); ?></td>
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
