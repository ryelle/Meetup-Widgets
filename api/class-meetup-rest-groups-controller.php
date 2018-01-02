<?php
/**
 * REST API Meetup proxy controller
 *
 * Handles requests to the Meetup.com service (bypassing CORS issues)
 *
 * @package  Meetup_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle all group-data fetching
 */
class Meetup_REST_Groups_Controller extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$namespace = 'meetup/v1';
		$base      = 'groups';
		register_rest_route( $namespace, '/' . $base . '/self', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$items = [
			[
				'name' => 'Boston WordPress',
				'urlname' => 'boston-wordpress-meetup',
			], [
				'name' => 'Women Who Code',
				'urlname' => 'Women-Who-Code-Boston',
			],
		];

		if ( ! $items ) {
			return [];
		}
		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$data = array();
		foreach ( $items as $item ) {
			$itemdata = $this->prepare_item_for_response( $item, $request );
			$data[] = $this->prepare_response_for_collection( $itemdata );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item Meetup.com representation of the group.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return array(
			'id' => rand( 0, 20 ),
			'name' => $item['name'],
			'urlname' => $item['urlname'],
		);
	}

	/**
	 * Check permissions for this endpoint.
	 *
	 * Only logged-in users can use this proxy, to prevent anonymous users from
	 * spamming the meetup.com API with the site-owner's API key.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You cannot view the group resource.', 'meetup-widgets' ),
				array(
					'status' => is_user_logged_in() ? 403 : 401,
				)
			);
		}
		return true;
	}
}
