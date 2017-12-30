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
class Meetup_REST_Group_Controller extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$namespace = 'meetup/v1';
		$base      = 'group';
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\S]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$vsm  = new Meetup_Widget();
		$count = intval( $request['count'] ) || 3;
		$id = $request['id'];
		$args = array(
			'status' => 'upcoming',
			'page'   => $count,
		);
		if ( preg_match( '/^[0-9]+$/', $id ) ) {
			$args['group_id'] = $id;
		} else {
			$args['group_urlname'] = $id;
		}

		// switch to v3?
		$items = $vsm->get_data( $args, 'vsm_group_events_' . $id . '_' . $count );
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
		// get parameters from request
		$params = $request->get_params();
		$item = array(); // do a query, call another class, etc
		$data = $this->prepare_item_for_response( $item, $request );

		// return a response or error based on some conditional
		if ( 1 == 1 ) {
			return new WP_REST_Response( $data, 200 );
		} else {
			return new WP_Error( 'code', __( 'message', 'text-domain' ) );
		}
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
		$link = sprintf(
			'<a href="http://maps.google.com/maps?q=%1$s&z=17">%2$s</a>',
			$venue_str,
			$venue['name']
		);

		return array(
			'name' => $item->name,
			'description' => $item->description,
			'event_url' => $item->event_url,
			'maps_link' => $link,
			'time' => date( 'M d, g:ia', intval( $item->time / 1000 + $item->utc_offset / 1000 ) ),
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
}
