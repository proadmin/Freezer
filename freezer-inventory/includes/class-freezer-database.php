<?php
/**
 * Database operations for Freezer Inventory.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Database {

	const TABLE_NAME           = 'freezer_inventory';
	const LOCATIONS_TABLE_NAME = 'freezer_locations';
	const FREEZERS_TABLE_NAME  = 'freezer_freezers';
	const ITEM_NAMES_TABLE     = 'freezer_item_names';

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	public static function get_locations_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::LOCATIONS_TABLE_NAME;
	}

	public static function get_freezers_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::FREEZERS_TABLE_NAME;
	}

	public static function get_item_names_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::ITEM_NAMES_TABLE;
	}

	public static function create_table() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Inventory table.
		$table = self::get_table_name();
		$sql = "CREATE TABLE $table (
			id varchar(36) NOT NULL,
			name varchar(255) NOT NULL,
			category varchar(100) NOT NULL,
			quantity decimal(10,3) NOT NULL DEFAULT 0,
			unit varchar(50) NOT NULL,
			location varchar(100) NOT NULL,
			location_id bigint(20) unsigned DEFAULT NULL,
			date_added datetime NOT NULL,
			notes text,
			PRIMARY KEY (id),
			KEY category (category),
			KEY location (location),
			KEY location_id (location_id)
		) $charset;";
		dbDelta( $sql );

		// Locations table.
		$loc_table = self::get_locations_table_name();
		$sql2 = "CREATE TABLE $loc_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			freezer varchar(100) NOT NULL,
			shelf varchar(100) NOT NULL,
			bin varchar(100) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY freezer_shelf_bin (freezer, shelf, bin)
		) $charset;";
		dbDelta( $sql2 );

		// Freezers table.
		$frz_table = self::get_freezers_table_name();
		$sql3 = "CREATE TABLE $frz_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY name (name)
		) $charset;";
		dbDelta( $sql3 );

		// Item names table.
		$names_table = self::get_item_names_table_name();
		$sql4 = "CREATE TABLE $names_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY name (name)
		) $charset;";
		dbDelta( $sql4 );
	}

	// ------------------------------------------------------------------
	// Location migration
	// ------------------------------------------------------------------

	private static $default_location_map = array(
		'Shelf 1 Bin 1' => array( 'freezer' => 'Main Freezer', 'shelf' => 'Shelf 1', 'bin' => 'Bin 1' ),
		'Shelf 1 Bin 2' => array( 'freezer' => 'Main Freezer', 'shelf' => 'Shelf 1', 'bin' => 'Bin 2' ),
		'Shelf 1 Bin 3' => array( 'freezer' => 'Main Freezer', 'shelf' => 'Shelf 1', 'bin' => 'Bin 3' ),
		'Shelf 2 Bin 1' => array( 'freezer' => 'Main Freezer', 'shelf' => 'Shelf 2', 'bin' => 'Bin 1' ),
		'Shelf 2 Bin 2' => array( 'freezer' => 'Main Freezer', 'shelf' => 'Shelf 2', 'bin' => 'Bin 2' ),
		'Shelf 2 Bulk'  => array( 'freezer' => 'Main Freezer', 'shelf' => 'Shelf 2', 'bin' => 'Bulk' ),
		'Door Shelf 1'  => array( 'freezer' => 'Main Freezer', 'shelf' => 'Door',    'bin' => 'Shelf 1' ),
		'Door Shelf 2'  => array( 'freezer' => 'Main Freezer', 'shelf' => 'Door',    'bin' => 'Shelf 2' ),
	);

	public static function seed_default_locations() {
		global $wpdb;
		$loc_table = self::get_locations_table_name();
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $loc_table" );
		if ( $count > 0 ) {
			return;
		}
		foreach ( self::$default_location_map as $parts ) {
			$wpdb->insert( $loc_table, $parts, array( '%s', '%s', '%s' ) );
		}
	}

	public static function migrate_locations() {
		self::create_table();
		self::seed_default_locations();

		global $wpdb;
		$table     = self::get_table_name();
		$loc_table = self::get_locations_table_name();

		// Match existing inventory rows to location IDs.
		$rows = $wpdb->get_results( "SELECT id, location FROM $table WHERE location_id IS NULL AND location != ''", ARRAY_A );
		if ( ! $rows ) {
			return;
		}
		foreach ( $rows as $row ) {
			$loc_string = $row['location'];
			// Try exact mapping from old defaults.
			if ( isset( self::$default_location_map[ $loc_string ] ) ) {
				$parts = self::$default_location_map[ $loc_string ];
				$loc_id = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM $loc_table WHERE freezer = %s AND shelf = %s AND bin = %s",
					$parts['freezer'], $parts['shelf'], $parts['bin']
				) );
			} else {
				// Try matching "Freezer / Shelf / Bin" format.
				$split = array_map( 'trim', explode( '/', $loc_string ) );
				if ( count( $split ) === 3 ) {
					$loc_id = $wpdb->get_var( $wpdb->prepare(
						"SELECT id FROM $loc_table WHERE freezer = %s AND shelf = %s AND bin = %s",
						$split[0], $split[1], $split[2]
					) );
					if ( ! $loc_id ) {
						// Auto-create.
						$wpdb->insert( $loc_table, array(
							'freezer' => sanitize_text_field( $split[0] ),
							'shelf'   => sanitize_text_field( $split[1] ),
							'bin'     => sanitize_text_field( $split[2] ),
						), array( '%s', '%s', '%s' ) );
						$loc_id = $wpdb->insert_id;
					}
				} else {
					$loc_id = null;
				}
			}
			if ( $loc_id ) {
				$wpdb->update( $table, array( 'location_id' => (int) $loc_id ), array( 'id' => $row['id'] ), array( '%d' ), array( '%s' ) );
			}
		}
	}

	// ------------------------------------------------------------------
	// Location CRUD
	// ------------------------------------------------------------------

	public static function get_locations() {
		global $wpdb;
		$table = self::get_locations_table_name();
		$rows = $wpdb->get_results( "SELECT id, freezer, shelf, bin FROM $table ORDER BY freezer, shelf, bin", ARRAY_A );
		if ( ! $rows ) {
			return array();
		}
		foreach ( $rows as &$row ) {
			$row['id'] = (int) $row['id'];
		}
		return $rows;
	}

	public static function get_location_by_id( $id ) {
		global $wpdb;
		$table = self::get_locations_table_name();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT id, freezer, shelf, bin FROM $table WHERE id = %d", $id ), ARRAY_A );
		if ( ! $row ) {
			return new WP_Error( 'not_found', __( 'Location not found.', 'freezer-inventory' ) );
		}
		$row['id'] = (int) $row['id'];
		return $row;
	}

	public static function add_location( $data ) {
		global $wpdb;
		$table   = self::get_locations_table_name();
		$freezer = sanitize_text_field( $data['freezer'] ?? '' );
		$shelf   = sanitize_text_field( $data['shelf'] ?? '' );
		$bin     = sanitize_text_field( $data['bin'] ?? '' );
		if ( ! $freezer || ! $shelf ) {
			return new WP_Error( 'missing', __( 'Freezer and shelf are required.', 'freezer-inventory' ) );
		}
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE freezer = %s AND shelf = %s AND bin = %s",
			$freezer, $shelf, $bin
		) );
		if ( $exists ) {
			return new WP_Error( 'duplicate', __( 'This location already exists.', 'freezer-inventory' ) );
		}
		$r = $wpdb->insert( $table, array(
			'freezer' => $freezer,
			'shelf'   => $shelf,
			'bin'     => $bin,
		), array( '%s', '%s', '%s' ) );
		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not save location.', 'freezer-inventory' ) );
		}
		return array( 'id' => (int) $wpdb->insert_id, 'freezer' => $freezer, 'shelf' => $shelf, 'bin' => $bin );
	}

	public static function update_location( $id, $data ) {
		global $wpdb;
		$table   = self::get_locations_table_name();
		$id      = (int) $id;
		$update  = array();
		$formats = array();
		foreach ( array( 'freezer', 'shelf', 'bin' ) as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$update[ $key ] = sanitize_text_field( $data[ $key ] );
				$formats[] = '%s';
			}
		}
		if ( empty( $update ) ) {
			return self::get_location_by_id( $id );
		}
		$r = $wpdb->update( $table, $update, array( 'id' => $id ), $formats, array( '%d' ) );
		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not update location.', 'freezer-inventory' ) );
		}
		// Update the location display string on all inventory items referencing this location.
		$loc = self::get_location_by_id( $id );
		if ( ! is_wp_error( $loc ) ) {
			$inv_table   = self::get_table_name();
			$loc_display = $loc['freezer'] . ' / ' . $loc['shelf'] . ' / ' . $loc['bin'];
			$wpdb->update( $inv_table, array( 'location' => $loc_display ), array( 'location_id' => $id ), array( '%s' ), array( '%d' ) );
		}
		return $loc;
	}

	public static function delete_location( $id ) {
		global $wpdb;
		$id        = (int) $id;
		$inv_table = self::get_table_name();
		$count     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $inv_table WHERE location_id = %d", $id ) );
		if ( $count > 0 ) {
			return new WP_Error( 'in_use', sprintf( __( 'Cannot delete: %d item(s) use this location.', 'freezer-inventory' ), $count ) );
		}
		$loc_table = self::get_locations_table_name();
		$deleted   = $wpdb->delete( $loc_table, array( 'id' => $id ), array( '%d' ) );
		if ( $deleted === false ) {
			return new WP_Error( 'db', __( 'Could not delete location.', 'freezer-inventory' ) );
		}
		if ( $deleted === 0 ) {
			return new WP_Error( 'not_found', __( 'Location not found.', 'freezer-inventory' ) );
		}
		return true;
	}

	public static function location_item_counts() {
		global $wpdb;
		$table = self::get_table_name();
		$rows  = $wpdb->get_results( "SELECT location_id, COUNT(*) as cnt FROM $table WHERE location_id IS NOT NULL GROUP BY location_id", ARRAY_A );
		$map   = array();
		foreach ( $rows as $row ) {
			$map[ (int) $row['location_id'] ] = (int) $row['cnt'];
		}
		return $map;
	}

	// ------------------------------------------------------------------
	// Freezer CRUD
	// ------------------------------------------------------------------

	public static function get_freezers() {
		global $wpdb;
		$table = self::get_freezers_table_name();
		$rows = $wpdb->get_results( "SELECT id, name FROM $table ORDER BY name", ARRAY_A );
		if ( ! $rows ) {
			return array();
		}
		foreach ( $rows as &$row ) {
			$row['id'] = (int) $row['id'];
		}
		return $rows;
	}

	public static function add_freezer( $name ) {
		global $wpdb;
		$table = self::get_freezers_table_name();
		$name  = sanitize_text_field( $name );
		if ( ! $name ) {
			return new WP_Error( 'missing', __( 'Freezer name is required.', 'freezer-inventory' ) );
		}
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE name = %s", $name ) );
		if ( $exists ) {
			return new WP_Error( 'duplicate', __( 'This freezer already exists.', 'freezer-inventory' ) );
		}
		$r = $wpdb->insert( $table, array( 'name' => $name ), array( '%s' ) );
		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not save freezer.', 'freezer-inventory' ) );
		}
		return array( 'id' => (int) $wpdb->insert_id, 'name' => $name );
	}

	public static function delete_freezer( $id ) {
		global $wpdb;
		$id        = (int) $id;
		$frz_table = self::get_freezers_table_name();
		$freezer   = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $frz_table WHERE id = %d", $id ) );
		if ( ! $freezer ) {
			return new WP_Error( 'not_found', __( 'Freezer not found.', 'freezer-inventory' ) );
		}
		$loc_table = self::get_locations_table_name();
		$count     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $loc_table WHERE freezer = %s", $freezer ) );
		if ( $count > 0 ) {
			return new WP_Error( 'in_use', sprintf( __( 'Cannot delete: %d location(s) use this freezer.', 'freezer-inventory' ), $count ) );
		}
		$deleted = $wpdb->delete( $frz_table, array( 'id' => $id ), array( '%d' ) );
		if ( $deleted === false ) {
			return new WP_Error( 'db', __( 'Could not delete freezer.', 'freezer-inventory' ) );
		}
		return true;
	}

	public static function freezer_location_counts() {
		global $wpdb;
		$table = self::get_locations_table_name();
		$rows  = $wpdb->get_results( "SELECT freezer, COUNT(*) as cnt FROM $table GROUP BY freezer", ARRAY_A );
		$map   = array();
		foreach ( $rows as $row ) {
			$map[ $row['freezer'] ] = (int) $row['cnt'];
		}
		return $map;
	}

	public static function migrate_freezers() {
		global $wpdb;
		$frz_table = self::get_freezers_table_name();
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $frz_table" );
		if ( $count > 0 ) {
			return;
		}
		$loc_table = self::get_locations_table_name();
		$freezers  = $wpdb->get_col( "SELECT DISTINCT freezer FROM $loc_table WHERE freezer != ''" );
		foreach ( $freezers as $name ) {
			$wpdb->insert( $frz_table, array( 'name' => $name ), array( '%s' ) );
		}
	}

	// ------------------------------------------------------------------
	// Item Names CRUD
	// ------------------------------------------------------------------

	public static function get_item_names() {
		global $wpdb;
		$table = self::get_item_names_table_name();
		$rows = $wpdb->get_results( "SELECT id, name FROM $table ORDER BY name", ARRAY_A );
		if ( ! $rows ) {
			return array();
		}
		foreach ( $rows as &$row ) {
			$row['id'] = (int) $row['id'];
		}
		return $rows;
	}

	public static function add_item_name( $name ) {
		global $wpdb;
		$table = self::get_item_names_table_name();
		$name  = sanitize_text_field( $name );
		if ( ! $name ) {
			return new WP_Error( 'missing', __( 'Item name is required.', 'freezer-inventory' ) );
		}
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE name = %s", $name ) );
		if ( $exists ) {
			return new WP_Error( 'duplicate', __( 'This item name already exists.', 'freezer-inventory' ) );
		}
		$r = $wpdb->insert( $table, array( 'name' => $name ), array( '%s' ) );
		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not save item name.', 'freezer-inventory' ) );
		}
		return array( 'id' => (int) $wpdb->insert_id, 'name' => $name );
	}

	public static function ensure_item_name( $name ) {
		global $wpdb;
		$table = self::get_item_names_table_name();
		$name  = sanitize_text_field( $name );
		if ( ! $name ) {
			return;
		}
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE name = %s", $name ) );
		if ( ! $exists ) {
			$wpdb->insert( $table, array( 'name' => $name ), array( '%s' ) );
		}
	}

	public static function delete_item_name( $id ) {
		global $wpdb;
		$id    = (int) $id;
		$table = self::get_item_names_table_name();
		$name  = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $table WHERE id = %d", $id ) );
		if ( ! $name ) {
			return new WP_Error( 'not_found', __( 'Item name not found.', 'freezer-inventory' ) );
		}
		$inv_table = self::get_table_name();
		$count     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $inv_table WHERE name = %s", $name ) );
		if ( $count > 0 ) {
			return new WP_Error( 'in_use', sprintf( __( 'Cannot delete: %d item(s) use this name.', 'freezer-inventory' ), $count ) );
		}
		$deleted = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		if ( $deleted === false ) {
			return new WP_Error( 'db', __( 'Could not delete item name.', 'freezer-inventory' ) );
		}
		return true;
	}

	public static function item_name_inventory_counts() {
		global $wpdb;
		$table = self::get_table_name();
		$rows  = $wpdb->get_results( "SELECT name, COUNT(*) as cnt FROM $table GROUP BY name", ARRAY_A );
		$map   = array();
		foreach ( $rows as $row ) {
			$map[ $row['name'] ] = (int) $row['cnt'];
		}
		return $map;
	}

	public static function migrate_item_names() {
		global $wpdb;
		$names_table = self::get_item_names_table_name();
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $names_table" );
		if ( $count > 0 ) {
			return;
		}
		$inv_table = self::get_table_name();
		$names = $wpdb->get_col( "SELECT DISTINCT name FROM $inv_table WHERE name != ''" );
		foreach ( $names as $name ) {
			$wpdb->insert( $names_table, array( 'name' => $name ), array( '%s' ) );
		}
	}

	/**
	 * Find or create a location by freezer/shelf/bin.
	 */
	public static function find_or_create_location( $freezer, $shelf, $bin ) {
		global $wpdb;
		$table = self::get_locations_table_name();
		$loc_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE freezer = %s AND shelf = %s AND bin = %s",
			$freezer, $shelf, $bin
		) );
		if ( $loc_id ) {
			return (int) $loc_id;
		}
		$r = $wpdb->insert( $table, array(
			'freezer' => sanitize_text_field( $freezer ),
			'shelf'   => sanitize_text_field( $shelf ),
			'bin'     => sanitize_text_field( $bin ),
		), array( '%s', '%s', '%s' ) );
		return $r !== false ? (int) $wpdb->insert_id : null;
	}

	// ------------------------------------------------------------------
	// Inventory CRUD (updated for location_id)
	// ------------------------------------------------------------------

	/**
	 * @param array $args Optional. category, location_id, freezer, shelf, bin, search.
	 * @return array
	 */
	public static function get_items( $args = array() ) {
		global $wpdb;
		$table     = self::get_table_name();
		$loc_table = self::get_locations_table_name();
		$where     = array( '1=1' );
		$values    = array();

		if ( ! empty( $args['category'] ) ) {
			$where[]  = 'i.category = %s';
			$values[] = $args['category'];
		}
		if ( ! empty( $args['location_id'] ) ) {
			$where[]  = 'i.location_id = %d';
			$values[] = (int) $args['location_id'];
		}
		if ( ! empty( $args['freezer'] ) ) {
			$where[]  = 'l.freezer = %s';
			$values[] = $args['freezer'];
		}
		if ( ! empty( $args['shelf'] ) ) {
			$where[]  = 'l.shelf = %s';
			$values[] = $args['shelf'];
		}
		if ( ! empty( $args['bin'] ) ) {
			$where[]  = 'l.bin = %s';
			$values[] = $args['bin'];
		}
		if ( ! empty( $args['search'] ) ) {
			$where[]  = 'LOWER(i.name) LIKE %s';
			$values[] = '%' . $wpdb->esc_like( strtolower( $args['search'] ) ) . '%';
		}

		$sql = "SELECT i.id, i.name, i.category, i.quantity, i.unit, i.location, i.location_id, i.date_added, i.notes,
				COALESCE(l.freezer, '') AS freezer, COALESCE(l.shelf, '') AS shelf, COALESCE(l.bin, '') AS bin
				FROM $table i LEFT JOIN $loc_table l ON i.location_id = l.id
				WHERE " . implode( ' AND ', $where ) . " ORDER BY i.date_added DESC";
		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}
		$rows = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! $rows ) {
			return array();
		}
		foreach ( $rows as &$row ) {
			$row['quantity']    = (float) $row['quantity'];
			$row['location_id'] = $row['location_id'] ? (int) $row['location_id'] : null;
			if ( $row['freezer'] ) {
				$row['location']     = $row['freezer'] . ' / ' . $row['shelf'] . ' / ' . $row['bin'];
				$row['freezer_zone'] = $row['location'];
			} else {
				$row['freezer_zone'] = $row['location'];
			}
			$row['date_added'] = date( 'c', strtotime( $row['date_added'] ) );
		}
		return $rows;
	}

	/**
	 * @param array $data name, category, quantity, unit, location_id (or location/freezer_zone), notes.
	 * @return array|WP_Error Item array or error.
	 */
	public static function add_item( $data ) {
		global $wpdb;
		$table = self::get_table_name();
		$id    = wp_generate_uuid4();
		$date  = ! empty( $data['date_added'] ) ? date( 'Y-m-d H:i:s', strtotime( $data['date_added'] ) ) : current_time( 'mysql' );
		if ( ! $date ) {
			$date = current_time( 'mysql' );
		}
		$name     = sanitize_text_field( $data['name'] ?? '' );
		$category = sanitize_text_field( $data['category'] ?? '' );
		$quantity = (float) ( $data['quantity'] ?? 0 );
		$unit     = sanitize_text_field( $data['unit'] ?? '' );
		$notes    = sanitize_textarea_field( $data['notes'] ?? '' );

		// Resolve location_id.
		$location_id = null;
		if ( ! empty( $data['location_id'] ) ) {
			$location_id = (int) $data['location_id'];
		} elseif ( ! empty( $data['freezer'] ) && ! empty( $data['shelf'] ) ) {
			$location_id = self::find_or_create_location(
				$data['freezer'],
				$data['shelf'],
				isset( $data['bin'] ) ? $data['bin'] : ''
			);
		}
		// Build display string.
		$location = '';
		if ( $location_id ) {
			$loc = self::get_location_by_id( $location_id );
			if ( ! is_wp_error( $loc ) ) {
				$location = $loc['freezer'] . ' / ' . $loc['shelf'] . ' / ' . $loc['bin'];
			}
		}
		if ( ! $location ) {
			$location = sanitize_text_field( $data['location'] ?? $data['freezer_zone'] ?? '' );
		}

		if ( ! $name || ! $category ) {
			return new WP_Error( 'missing', __( 'Missing required fields.', 'freezer-inventory' ) );
		}

		$insert = array(
			'id'         => $id,
			'name'       => $name,
			'category'   => $category,
			'quantity'   => $quantity,
			'unit'       => $unit,
			'location'   => $location,
			'date_added' => $date,
			'notes'      => $notes,
		);
		$formats = array( '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s' );
		if ( $location_id ) {
			$insert['location_id'] = $location_id;
			$formats[]             = '%d';
		}

		$r = $wpdb->insert( $table, $insert, $formats );
		if ( $r === false ) {
			return new WP_Error( 'db', __( 'Could not save item.', 'freezer-inventory' ) );
		}

		// Auto-add item name to the managed list.
		self::ensure_item_name( $name );

		return self::get_item_by_id( $id );
	}

	/**
	 * @param string $id Item ID.
	 * @return bool|WP_Error
	 */
	public static function delete_item( $id ) {
		global $wpdb;
		$table = self::get_table_name();
		$id    = sanitize_text_field( $id );
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
	 * @param string $id   Item ID.
	 * @param array  $data Fields to update.
	 * @return array|WP_Error Updated item or error.
	 */
	public static function update_item( $id, $data ) {
		global $wpdb;
		$table   = self::get_table_name();
		$id      = sanitize_text_field( $id );
		$allowed = array( 'name', 'category', 'quantity', 'unit', 'location', 'notes' );
		$update  = array();
		$formats = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}
			if ( $key === 'quantity' ) {
				$update[ $key ] = (float) $data[ $key ];
				$formats[]      = '%f';
			} else {
				$update[ $key ] = $key === 'notes' ? sanitize_textarea_field( $data[ $key ] ) : sanitize_text_field( $data[ $key ] );
				$formats[]      = '%s';
			}
		}
		// Handle location_id.
		if ( isset( $data['location_id'] ) ) {
			$loc_id = (int) $data['location_id'];
			$update['location_id'] = $loc_id;
			$formats[] = '%d';
			$loc = self::get_location_by_id( $loc_id );
			if ( ! is_wp_error( $loc ) ) {
				$update['location'] = $loc['freezer'] . ' / ' . $loc['shelf'] . ' / ' . $loc['bin'];
				$formats[] = '%s';
			}
		} elseif ( isset( $data['freezer_zone'] ) && ! isset( $data['location'] ) ) {
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

	/**
	 * Delete all items and insert new ones.
	 */
	public static function replace_all_items( $items ) {
		global $wpdb;
		$table = self::get_table_name();
		$wpdb->query( "DELETE FROM $table" );
		$count = 0;
		foreach ( $items as $data ) {
			$result = self::add_item( $data );
			if ( ! is_wp_error( $result ) ) {
				$count++;
			}
		}
		return $count;
	}

	public static function get_item_by_id( $id ) {
		global $wpdb;
		$table     = self::get_table_name();
		$loc_table = self::get_locations_table_name();
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT i.id, i.name, i.category, i.quantity, i.unit, i.location, i.location_id, i.date_added, i.notes,
			 COALESCE(l.freezer, '') AS freezer, COALESCE(l.shelf, '') AS shelf, COALESCE(l.bin, '') AS bin
			 FROM $table i LEFT JOIN $loc_table l ON i.location_id = l.id
			 WHERE i.id = %s", $id
		), ARRAY_A );
		if ( ! $row ) {
			return new WP_Error( 'not_found', __( 'Item not found.', 'freezer-inventory' ) );
		}
		$row['quantity']    = (float) $row['quantity'];
		$row['location_id'] = $row['location_id'] ? (int) $row['location_id'] : null;
		if ( $row['freezer'] ) {
			$row['location']     = $row['freezer'] . ' / ' . $row['shelf'] . ' / ' . $row['bin'];
			$row['freezer_zone'] = $row['location'];
		} else {
			$row['freezer_zone'] = $row['location'];
		}
		$row['date_added'] = date( 'c', strtotime( $row['date_added'] ) );
		return $row;
	}
}
