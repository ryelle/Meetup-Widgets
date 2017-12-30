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
 * Handle all group-related event fetching
 */
class Meetup_REST_Events_Controller extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$namespace = 'meetup/v1';
		$base      = 'events';
		register_rest_route( $namespace, '/' . $base . '/self', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_endpoint_args(),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<id>[^/]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_endpoint_args(),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<group_id>[^/]+)/(?P<event_id>[^/]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
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
		$api    = new Meetup_API_V3();
		$params = $request->get_params();
		$count  = intval( $params['count'] );
		$args   = array(
			'status' => 'upcoming',
			'page'   => $count,
		);

		if ( isset( $params['id'] ) ) {
			$id = $params['id'];
			$items = $api->get_events( $id, $args, 'vsm_v3_group_' . $id . '_' . $count );
		} else {
			$items = $api->get_self_events( $args, 'vsm_v3_self_' . $count );
		}

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
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$api    = new Meetup_API_V3();
		$params = $request->get_params();
		$item   = $api->get_event(
			$params['group_id'],
			$params['event_id'],
			'vsm_v3_event_' . $params['group_id'] . '_' . $params['event_id']
		);
		if ( ! $item ) {
			return [];
		}
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$data = $this->prepare_item_for_response( $item, $request );

		// return a response or error based on some conditional
		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item Meetup.com representation of the event.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		$venue = wp_parse_args(
			(array) $item->venue,
			array(
				'name' => '',
				'address_1' => '',
				'address_2' => '',
				'address_3' => '',
				'city' => '',
				'state' => '',
				'country' => '',
			)
		);
		$venue_str = sprintf(
			'%1$s – %2$s, %3$s, %4$s',
			$venue['name'],
			$venue['address_1'],
			$venue['city'],
			$venue['state']
		);

		return array(
			'id' => $item->id,
			'name' => $item->name,
			'description' => $item->description,
			'url' => $item->link,
			'google_maps' => "http://maps.google.com/maps?q={$venue_str}&z=17",
			'date' => date( 'M d, g:ia', intval( $item->time / 1000 + $item->utc_offset / 1000 ) ),
			'raw_date' => $item->time,
			'status' => $item->status,
			'venue' => $venue,
			'venue_display' => $venue_str,
			'yes_rsvp_count' => $item->yes_rsvp_count,
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
				esc_html__( 'You cannot view the post resource.', 'meetup-widgets' ),
				array(
					'status' => is_user_logged_in() ? 403 : 401,
				)
			);
		}
		return true;
	}

	/**
	 * Get the argument schema for this example endpoint.
	 */
	function get_endpoint_args() {
		$args = array();

		// Here we add our PHP representation of JSON Schema.
		$args['count'] = array(
			'description'       => esc_html__( 'Number of events to show.', 'meetup-widgets' ),
			'type'              => 'integer',
			'validate_callback' => 'absint',
			'sanitize_callback' => 'absint',
			'required'          => false,
			'default'           => 3,
		);

		return $args;
	}
}
