<?php
/**
 * REST API for Freezer Inventory.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Rest {

	public static function register_routes() {
		$namespace = 'freezer-inventory/v1';
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
					'freezer_zone' => array( 'type' => 'string', 'required' => true ),
					'location'     => array( 'type' => 'string', 'required' => false ),
					'notes'        => array( 'type' => 'string', 'required' => false ),
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
					'id'       => array( 'required' => true, 'type' => 'string' ),
					'quantity' => array( 'type' => 'number' ),
					'name'     => array( 'type' => 'string' ),
					'category' => array( 'type' => 'string' ),
					'unit'     => array( 'type' => 'string' ),
					'location' => array( 'type' => 'string' ),
					'freezer_zone' => array( 'type' => 'string' ),
					'notes'    => array( 'type' => 'string' ),
				),
			),
		) );
		register_rest_route( $namespace, '/inventory/pdf', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_pdf' ),
			'permission_callback' => array( __CLASS__, 'check_permission' ),
		) );
	}

	public static function check_permission( $request ) {
		return current_user_can( 'manage_options' );
	}

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
		$id = $request['id'];
		$result = Freezer_Database::delete_item( $id );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 404 );
		}
		return new WP_REST_Response( array( 'message' => 'Item deleted successfully' ), 200 );
	}

	public static function update_item( $request ) {
		$id = $request['id'];
		$params = $request->get_json_params() ?: $request->get_body_params();
		if ( isset( $params['freezer_zone'] ) ) {
			$params['location'] = $params['freezer_zone'];
		}
		$result = Freezer_Database::update_item( $id, $params );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 404 );
		}
		return new WP_REST_Response( $result, 200 );
	}

	public static function get_pdf( $request ) {
		$items = Freezer_Database::get_items( array() );
		$html = Freezer_Admin::get_print_html( $items );
		return new WP_REST_Response( array( 'html' => $html ), 200 );
	}
}
