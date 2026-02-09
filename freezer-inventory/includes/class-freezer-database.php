<?php
/**
 * Database operations for Freezer Inventory.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Database {

	const TABLE_NAME = 'freezer_inventory';

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	public static function create_table() {
		global $wpdb;
		$table = self::get_table_name();
		$charset = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
			id varchar(36) NOT NULL,
			name varchar(255) NOT NULL,
			category varchar(100) NOT NULL,
			quantity decimal(10,3) NOT NULL DEFAULT 0,
			unit varchar(50) NOT NULL,
			location varchar(100) NOT NULL,
			date_added datetime NOT NULL,
			notes text,
			PRIMARY KEY (id),
			KEY category (category),
			KEY location (location)
		) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * @param array $args Optional. category, location, search.
	 * @return array
	 */
	public static function get_items( $args = array() ) {
		global $wpdb;
		$table = self::get_table_name();
		$where = array( '1=1' );
		$values = array();

		if ( ! empty( $args['category'] ) ) {
			$where[] = 'category = %s';
			$values[] = $args['category'];
		}
		if ( ! empty( $args['location'] ) ) {
			$where[] = 'location = %s';
			$values[] = $args['location'];
		}
		if ( ! empty( $args['search'] ) ) {
			$where[] = 'LOWER(name) LIKE %s';
			$values[] = '%' . $wpdb->esc_like( strtolower( $args['search'] ) ) . '%';
		}

		$sql = "SELECT id, name, category, quantity, unit, location, date_added, notes FROM $table WHERE " . implode( ' AND ', $where ) . " ORDER BY date_added DESC";
		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}
		$rows = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! $rows ) {
			return array();
		}
		foreach ( $rows as &$row ) {
			$row['quantity'] = (float) $row['quantity'];
			$row['freezer_zone'] = $row['location'];
			$row['date_added'] = date( 'c', strtotime( $row['date_added'] ) );
		}
		return $rows;
	}

	/**
	 * @param array $data name, category, quantity, unit, location, notes.
	 * @return array|WP_Error Item array or error.
	 */
	public static function add_item( $data ) {
		global $wpdb;
		$table = self::get_table_name();
		$id = wp_generate_uuid4();
		$date = current_time( 'mysql' );
		$name = sanitize_text_field( $data['name'] ?? '' );
		$category = sanitize_text_field( $data['category'] ?? '' );
		$quantity = (float) ( $data['quantity'] ?? 0 );
		$unit = sanitize_text_field( $data['unit'] ?? '' );
		$location = sanitize_text_field( $data['location'] ?? $data['freezer_zone'] ?? '' );
		$notes = sanitize_textarea_field( $data['notes'] ?? '' );

		if ( ! $name || ! $category || ! $location ) {
			return new WP_Error( 'missing', __( 'Missing required fields.', 'freezer-inventory' ) );
		}

		$r = $wpdb->insert( $table, array(
			'id'         => $id,
			'name'       => $name,
			'category'   => $category,
			'quantity'   => $quantity,
			'unit'       => $unit,
			'location'   => $location,
			'date_added' => $date,
			'notes'      => $notes,
		), array( '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s' ) );

		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not save item.', 'freezer-inventory' ) );
		}

		return array(
			'id'           => $id,
			'name'         => $name,
			'category'     => $category,
			'quantity'     => $quantity,
			'unit'         => $unit,
			'location'     => $location,
			'freezer_zone' => $location,
			'date_added'   => date( 'c', strtotime( $date ) ),
			'notes'        => $notes,
		);
	}

	/**
	 * @param string $id Item ID.
	 * @return bool|WP_Error
	 */
	public static function delete_item( $id ) {
		global $wpdb;
		$table = self::get_table_name();
		$id = sanitize_text_field( $id );
		$deleted = $wpdb->delete( $table, array( 'id' => $id ), array( '%s' ) );
		if ( $deleted === false ) {
			return new WP_Error( 'db', __( 'Could not delete item.', 'freezer-inventory' ) );
		}
		if ( $deleted === 0 ) {
			return new WP_Error( 'not_found', __( 'Item not found.', 'freezer-inventory' ) );
		}
		return true;
	}

	/**
	 * @param string $id Item ID.
	 * @param array  $data Fields to update (e.g. quantity, name, category, unit, location, notes).
	 * @return array|WP_Error Updated item or error.
	 */
	public static function update_item( $id, $data ) {
		global $wpdb;
		$table = self::get_table_name();
		$id = sanitize_text_field( $id );
		$allowed = array( 'name', 'category', 'quantity', 'unit', 'location', 'notes' );
		$update = array();
		$formats = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}
			if ( $key === 'quantity' ) {
				$update[ $key ] = (float) $data[ $key ];
				$formats[] = '%f';
			} else {
				$update[ $key ] = $key === 'notes' ? sanitize_textarea_field( $data[ $key ] ) : sanitize_text_field( $data[ $key ] );
				$formats[] = '%s';
			}
		}
		if ( isset( $data['freezer_zone'] ) && ! isset( $data['location'] ) ) {
			$update['location'] = sanitize_text_field( $data['freezer_zone'] );
			$formats[] = '%s';
		}
		if ( empty( $update ) ) {
			return self::get_item_by_id( $id );
		}
		$r = $wpdb->update( $table, $update, array( 'id' => $id ), $formats, array( '%s' ) );
		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not update item.', 'freezer-inventory' ) );
		}
		return self::get_item_by_id( $id );
	}

	public static function get_item_by_id( $id ) {
		global $wpdb;
		$table = self::get_table_name();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT id, name, category, quantity, unit, location, date_added, notes FROM $table WHERE id = %s", $id ), ARRAY_A );
		if ( ! $row ) {
			return new WP_Error( 'not_found', __( 'Item not found.', 'freezer-inventory' ) );
		}
		$row['quantity'] = (float) $row['quantity'];
		$row['freezer_zone'] = $row['location'];
		$row['date_added'] = date( 'c', strtotime( $row['date_added'] ) );
		return $row;
	}
}
