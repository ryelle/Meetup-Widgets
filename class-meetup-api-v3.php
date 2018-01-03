<?php
/**
 * Handle v3 API endpoints
 *
 * @package Meetup_Widgets
 */

/**
 * Class container for requesting meetup.com data from the v3 endpoints
 */
class Meetup_API_V3 {
	/**
	 * The URL used to fetch event data.
	 *
	 * @var string $base_url
	 */
	private $base_url  = 'https://api.meetup.com';

	/**
	 * Holds the user's Meetup.com API key
	 *
	 * @var string $api_key
	 */
	protected $api_key = '';

	/**
	 * Set our API key by getting it from the database
	 */
	public function __construct() {
		$options       = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];
	}

	/**
	 * Given arguments & a transient name, grab data from the events API
	 *
	 * @param string $group      Group name used to fetch events.
	 * @param array  $args       Query params to send to events call.
	 * @param string $transient  The transient name (if empty, no transient stored).
	 * @return array Event data (list of events)
	 */
	public function get_events( $group = false, $args = [], $transient = '' ) {
		if ( ! $group ) {
			return new WP_Error( 'undefined_group', __( 'Requested group name missing.', 'meetup-widgets' ) );
		}
		$data = false;
		if ( $transient ) {
			$data = get_transient( $transient );
		}

		$defaults = array(
			'key' => $this->api_key,
		);

		if ( false === $data ) {
			$args = wp_parse_args( $args, $defaults );
			$url  = sprintf( '%s/%s/events', $this->base_url, $group );
			$url  = add_query_arg( $args, $url );
			$events_response = wp_remote_get( $url );
			if ( is_wp_error( $events_response ) ) {
				return $events_response;
			}
			$data = json_decode( $events_response['body'] );
			if ( isset( $data->errors ) ) {
				$err = array_shift( $data->errors );
				return new WP_Error( $err->code, $err->message );
			}
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'response_error', __( 'Response is not formatted correctly', 'meetup-widgets' ) );
			}

			if ( $transient ) {
				set_transient( $transient, $data, 2 * HOUR_IN_SECONDS );
			}
		}

		return $data;
	}

	/**
	 * Given arguments & a transient name, grab data from the events API
	 *
	 * @param array  $args       Query params to send to events call.
	 * @param string $transient  The transient name (if empty, no transient stored).
	 * @return array Event data (list of events)
	 */
	public function get_self_events( $args = [], $transient = '' ) {
		$data = false;
		if ( $transient ) {
			$data = get_transient( $transient );
		}

		$defaults = array(
			'key' => $this->api_key,
		);

		if ( false === $data ) {
			$args = wp_parse_args( $args, $defaults );
			$url  = sprintf( '%s/self/events', $this->base_url );
			$url  = add_query_arg( $args, $url );
			$events_response = wp_remote_get( $url );
			if ( is_wp_error( $events_response ) ) {
				return $events_response;
			}
			$data = json_decode( $events_response['body'] );
			if ( isset( $data->errors ) ) {
				$err = array_shift( $data->errors );
				return new WP_Error( $err->code, $err->message );
			}
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'response_error', __( 'Response is not formatted correctly', 'meetup-widgets' ) );
			}

			if ( $transient ) {
				set_transient( $transient, $data, 2 * HOUR_IN_SECONDS );
			}
		}

		return $data;
	}

	/**
	 * Given arguments & a transient name, grab data from the events API
	 *
	 * @param string $group      The parent group name.
	 * @param string $event      The event ID to fetch.
	 * @param string $transient  The transient name (if empty, no transient stored).
	 * @return array Event data (single event)
	 */
	public function get_event( $group = false, $event = false, $transient = '' ) {
		if ( ! $group ) {
			return new WP_Error( 'undefined_group', __( 'Requested group name missing.', 'meetup-widgets' ) );
		}
		if ( ! $event ) {
			return new WP_Error( 'undefined_event', __( 'Requested event ID missing.', 'meetup-widgets' ) );
		}
		$data = false;
		if ( $transient ) {
			$data = get_transient( $transient );
		}

		$args = array(
			'key' => $this->api_key,
		);

		if ( false === $data ) {
			$url = sprintf( '%s/%s/events/%s', $this->base_url, $group, $event );
			$url = add_query_arg( $args, $url );
			$event_response = wp_remote_get( $url );
			if ( is_wp_error( $event_response ) ) {
				return $event_response;
			}
			$data = json_decode( $event_response['body'] );
			if ( isset( $data->errors ) ) {
				$err = array_shift( $data->errors );
				return new WP_Error( $err->code, $err->message );
			}

			if ( $transient ) {
				set_transient( $transient, $data, 2 * HOUR_IN_SECONDS );
			}
		}

		return $data;
	}

	/**
	 * Given arguments & a transient name, grab data from the groups API
	 *
	 * @param string $transient  The transient name (if empty, no transient stored).
	 * @return array Event data (list of events)
	 */
	public function get_self_groups( $transient = '' ) {
		$data = false;
		if ( $transient ) {
			$data = get_transient( $transient );
		}

		$args = array(
			'key'  => $this->api_key,
			'page' => 200,
		);

		if ( false === $data ) {
			$url = sprintf( '%s/self/groups', $this->base_url );
			$url = add_query_arg( $args, $url );
			$groups_response = wp_remote_get( $url );
			if ( is_wp_error( $groups_response ) ) {
				return $groups_response;
			}
			$data = json_decode( $groups_response['body'] );
			if ( isset( $data->errors ) ) {
				$err = array_shift( $data->errors );
				return new WP_Error( $err->code, $err->message );
			}
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'response_error', __( 'Response is not formatted correctly', 'meetup-widgets' ) );
			}

			if ( $transient ) {
				set_transient( $transient, $data, 2 * HOUR_IN_SECONDS );
			}
		}

		return $data;
	}

}
