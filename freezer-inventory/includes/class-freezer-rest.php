<?php
/**
 * REST API for Freezer Inventory.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Rest {

	public static function register_routes() {
		$namespace = 'freezer-inventory/v1';

		// Items.
		register_rest_route( $namespace, '/items', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_items' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'category' => array( 'type' => 'string', 'required' => false ),
					'location' => array( 'type' => 'string', 'required' => false ),
					'search'   => array( 'type' => 'string', 'required' => false ),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'add_item' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'name'         => array( 'type' => 'string', 'required' => true ),
					'category'     => array( 'type' => 'string', 'required' => true ),
					'quantity'     => array( 'type' => 'number', 'required' => true ),
					'unit'         => array( 'type' => 'string', 'required' => true ),
					'location_id'  => array( 'type' => 'integer', 'required' => false ),
					'freezer_zone' => array( 'type' => 'string', 'required' => false ),
					'location'     => array( 'type' => 'string', 'required' => false ),
					'preparation'  => array( 'type' => 'string', 'required' => false ),
					'notes'        => array( 'type' => 'string', 'required' => false ),
					'date_added'   => array( 'type' => 'string', 'required' => false ),
					'freezer'      => array( 'type' => 'string', 'required' => false ),
					'shelf'        => array( 'type' => 'string', 'required' => false ),
					'bin'          => array( 'type' => 'string', 'required' => false ),
				),
			),
		) );
		register_rest_route( $namespace, '/items/(?P<id>[a-f0-9\-]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_item' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array( 'id' => array( 'required' => true, 'type' => 'string' ) ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_item' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'id'           => array( 'required' => true, 'type' => 'string' ),
					'quantity'     => array( 'type' => 'number' ),
					'name'         => array( 'type' => 'string' ),
					'category'     => array( 'type' => 'string' ),
					'unit'         => array( 'type' => 'string' ),
					'location'     => array( 'type' => 'string' ),
					'location_id'  => array( 'type' => 'integer' ),
					'freezer_zone' => array( 'type' => 'string' ),
					'preparation'  => array( 'type' => 'string' ),
					'notes'        => array( 'type' => 'string' ),
					'date_added'   => array( 'type' => 'string' ),
				),
			),
		) );
		register_rest_route( $namespace, '/items/import-csv', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'import_csv' ),
			'permission_callback' => array( __CLASS__, 'check_permission' ),
		) );
		register_rest_route( $namespace, '/items/export-csv', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'export_csv' ),
			'permission_callback' => array( __CLASS__, 'check_permission' ),
		) );

		// Locations.
		register_rest_route( $namespace, '/locations', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_locations' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'add_location' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'freezer' => array( 'type' => 'string', 'required' => true ),
					'shelf'   => array( 'type' => 'string', 'required' => true ),
					'bin'     => array( 'type' => 'string', 'required' => true ),
				),
			),
		) );
		register_rest_route( $namespace, '/locations/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_location' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'id'      => array( 'required' => true, 'type' => 'integer' ),
					'freezer' => array( 'type' => 'string' ),
					'shelf'   => array( 'type' => 'string' ),
					'bin'     => array( 'type' => 'string' ),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_location' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array( 'id' => array( 'required' => true, 'type' => 'integer' ) ),
			),
		) );

		// Freezers.
		register_rest_route( $namespace, '/freezers', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_freezers' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'add_freezer' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'name' => array( 'type' => 'string', 'required' => true ),
				),
			),
		) );
		register_rest_route( $namespace, '/freezers/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( __CLASS__, 'delete_freezer' ),
			'permission_callback' => array( __CLASS__, 'check_permission' ),
			'args'                => array( 'id' => array( 'required' => true, 'type' => 'integer' ) ),
		) );

		// Item Names.
		register_rest_route( $namespace, '/item-names', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_item_names' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'add_item_name' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'name' => array( 'type' => 'string', 'required' => true ),
				),
			),
		) );
		register_rest_route( $namespace, '/item-names/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( __CLASS__, 'delete_item_name' ),
			'permission_callback' => array( __CLASS__, 'check_permission' ),
			'args'                => array( 'id' => array( 'required' => true, 'type' => 'integer' ) ),
		) );

		// PDF.
		register_rest_route( $namespace, '/inventory/pdf', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_pdf' ),
			'permission_callback' => array( __CLASS__, 'check_permission' ),
		) );
	}

	public static function check_permission( $request ) {
		return current_user_can( 'manage_options' );
	}

	// ------------------------------------------------------------------
	// Items
	// ------------------------------------------------------------------

	public static function get_items( $request ) {
		$args = array(
			'category' => $request->get_param( 'category' ),
			'location' => $request->get_param( 'location' ),
			'search'   => $request->get_param( 'search' ),
		);
		$items = Freezer_Database::get_items( $args );
		return new WP_REST_Response( $items, 200 );
	}

	public static function add_item( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		if ( empty( $params['location'] ) && ! empty( $params['freezer_zone'] ) ) {
			$params['location'] = $params['freezer_zone'];
		}
		$result = Freezer_Database::add_item( $params );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}
		return new WP_REST_Response( $result, 201 );
	}

	public static function delete_item( $request ) {
		$id     = $request['id'];
		$result = Freezer_Database::delete_item( $id );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 404 );
		}
		return new WP_REST_Response( array( 'message' => 'Item deleted successfully' ), 200 );
	}

	public static function update_item( $request ) {
		$id     = $request['id'];
		$params = $request->get_json_params() ?: $request->get_body_params();
		if ( isset( $params['freezer_zone'] ) && ! isset( $params['location_id'] ) ) {
			$params['location'] = $params['freezer_zone'];
		}
		$result = Freezer_Database::update_item( $id, $params );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 404 );
		}
		return new WP_REST_Response( $result, 200 );
	}

	// ------------------------------------------------------------------
	// CSV import / export
	// ------------------------------------------------------------------

	public static function import_csv( $request ) {
		$files = $request->get_file_params();
		if ( empty( $files['file']['tmp_name'] ) ) {
			return new WP_REST_Response( array( 'error' => 'No file uploaded.' ), 400 );
		}
		$handle = fopen( $files['file']['tmp_name'], 'r' );
		if ( ! $handle ) {
			return new WP_REST_Response( array( 'error' => 'Could not read file.' ), 400 );
		}
		$header = fgetcsv( $handle );
		if ( ! $header ) {
			fclose( $handle );
			return new WP_REST_Response( array( 'error' => 'Empty CSV file.' ), 400 );
		}
		$header = array_map( 'strtolower', array_map( 'trim', $header ) );

		$items = array();
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) !== count( $header ) ) {
				continue;
			}
			$item = array_combine( $header, $row );
			// Resolve location_id from freezer/shelf/bin columns.
			if ( ! empty( $item['freezer'] ) && ! empty( $item['shelf'] ) ) {
				$loc_id = Freezer_Database::find_or_create_location( $item['freezer'], $item['shelf'], $item['bin'] ?? '' );
				if ( $loc_id ) {
					$item['location_id'] = $loc_id;
				}
			} elseif ( ! empty( $item['location'] ) || ! empty( $item['freezer_zone'] ) ) {
				$loc_str = ! empty( $item['location'] ) ? $item['location'] : $item['freezer_zone'];
				if ( ! isset( $item['freezer_zone'] ) ) {
					$item['freezer_zone'] = $loc_str;
				}
			}
			$items[] = $item;
		}
		fclose( $handle );

		if ( empty( $items ) ) {
			return new WP_REST_Response( array( 'error' => 'No valid rows found in CSV.' ), 400 );
		}
		$count = Freezer_Database::replace_all_items( $items );
		return new WP_REST_Response( array( 'imported' => $count ), 200 );
	}

	public static function export_csv( $request ) {
		$items  = Freezer_Database::get_items( array() );
		$output = fopen( 'php://temp', 'r+' );
		fputcsv( $output, array( 'Name', 'Category', 'Quantity', 'Unit', 'Freezer', 'Shelf', 'Bin', 'Preparation', 'Date Added', 'Notes' ) );
		foreach ( $items as $item ) {
			fputcsv( $output, array(
				$item['name'],
				$item['category'],
				$item['quantity'],
				$item['unit'],
				$item['freezer'] ?? '',
				$item['shelf'] ?? '',
				$item['bin'] ?? '',
				$item['preparation'] ?? '',
				$item['date_added'],
				$item['notes'] ?? '',
			) );
		}
		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );
		return new WP_REST_Response( array(
			'csv'      => $csv,
			'filename' => 'freezer-inventory-' . date( 'Y-m-d' ) . '.csv',
		), 200 );
	}

	// ------------------------------------------------------------------
	// Locations
	// ------------------------------------------------------------------

	public static function get_locations( $request ) {
		$locations = Freezer_Database::get_locations();
		$counts    = Freezer_Database::location_item_counts();
		foreach ( $locations as &$loc ) {
			$loc['item_count'] = isset( $counts[ $loc['id'] ] ) ? $counts[ $loc['id'] ] : 0;
		}
		return new WP_REST_Response( $locations, 200 );
	}

	public static function add_location( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		$result = Freezer_Database::add_location( $params );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}
		return new WP_REST_Response( $result, 201 );
	}

	public static function update_location( $request ) {
		$id     = (int) $request['id'];
		$params = $request->get_json_params() ?: $request->get_body_params();
		$result = Freezer_Database::update_location( $id, $params );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}
		return new WP_REST_Response( $result, 200 );
	}

	public static function delete_location( $request ) {
		$id     = (int) $request['id'];
		$result = Freezer_Database::delete_location( $id );
		if ( is_wp_error( $result ) ) {
			$code = $result->get_error_code() === 'in_use' ? 409 : 404;
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), $code );
		}
		return new WP_REST_Response( array( 'message' => 'Location deleted' ), 200 );
	}

	// ------------------------------------------------------------------
	// Freezers
	// ------------------------------------------------------------------

	public static function get_freezers( $request ) {
		$freezers = Freezer_Database::get_freezers();
		$counts   = Freezer_Database::freezer_location_counts();
		foreach ( $freezers as &$f ) {
			$f['location_count'] = isset( $counts[ $f['name'] ] ) ? $counts[ $f['name'] ] : 0;
		}
		return new WP_REST_Response( $freezers, 200 );
	}

	public static function add_freezer( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		$result = Freezer_Database::add_freezer( $params['name'] ?? '' );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}
		return new WP_REST_Response( $result, 201 );
	}

	public static function delete_freezer( $request ) {
		$id     = (int) $request['id'];
		$result = Freezer_Database::delete_freezer( $id );
		if ( is_wp_error( $result ) ) {
			$code = $result->get_error_code() === 'in_use' ? 409 : 404;
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), $code );
		}
		return new WP_REST_Response( array( 'message' => 'Freezer deleted' ), 200 );
	}

	// ------------------------------------------------------------------
	// Item Names
	// ------------------------------------------------------------------

	public static function get_item_names( $request ) {
		$names  = Freezer_Database::get_item_names();
		$counts = Freezer_Database::item_name_inventory_counts();
		foreach ( $names as &$n ) {
			$n['item_count'] = isset( $counts[ $n['name'] ] ) ? $counts[ $n['name'] ] : 0;
		}
		return new WP_REST_Response( $names, 200 );
	}

	public static function add_item_name( $request ) {
		$params = $request->get_json_params() ?: $request->get_body_params();
		$result = Freezer_Database::add_item_name( $params['name'] ?? '' );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}
		return new WP_REST_Response( $result, 201 );
	}

	public static function delete_item_name( $request ) {
		$id     = (int) $request['id'];
		$result = Freezer_Database::delete_item_name( $id );
		if ( is_wp_error( $result ) ) {
			$code = $result->get_error_code() === 'in_use' ? 409 : 404;
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), $code );
		}
		return new WP_REST_Response( array( 'message' => 'Item name deleted' ), 200 );
	}

	// ------------------------------------------------------------------
	// PDF
	// ------------------------------------------------------------------

	public static function get_pdf( $request ) {
		$items = Freezer_Database::get_items( array() );
		$html  = Freezer_Admin::get_print_html( $items );
		return new WP_REST_Response( array( 'html' => $html ), 200 );
	}
}
