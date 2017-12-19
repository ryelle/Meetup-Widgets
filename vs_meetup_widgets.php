<?php
/**
 * VsMeetWidget
 * All widget info for VsMeet (including widgets themselves)
 */

class VsMeetWidget extends VsMeet {
	private $api_url   = 'http://api.meetup.com/';
	private $base_url  = 'http://api.meetup.com/2/events/';
	protected $api_key = '';

	public function __construct() {
		$options       = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];

		parent::__construct();
	}

	/**
	 * Given arguments & a transient name, grab data from the events API
	 *
	 * @param  $args       array   Query params to send to events.json call
	 * @param  $transient  string  The transient name (if empty, no transient stored)
	 *
	 * @return  array  Event data (single event or list)
	 */
	public function get_data( $args, $transient = '' ) {
		if ( $transient ) {
			$event = get_transient( $transient );
		}

		$defaults = array(
			'key' => $this->api_key,
		);

		if ( false === $event ) {
			$args           = wp_parse_args( $args, $defaults );
			$url            = add_query_arg( $args, $this->base_url );
			$event_response = wp_remote_get( $url );
			if ( is_wp_error( $event_response ) ) {
				if ( WP_DEBUG ) {
					echo 'Something went wrong!';
					var_dump( $event_response );
				}
				return false;
			}
			$event = json_decode( $event_response['body'] );
			// Single events only return first result
			if ( ! isset( $event->results ) || ! isset( $event->results[0] ) ) {
				return false;
			}

			if ( isset( $args['event_id'] ) ) {
				$event = $event->results[0];
			} else {
				$event = $event->results;
			}

			if ( $transient ) {
				set_transient( $transient, $event, 2 * HOUR_IN_SECONDS );
			}
		}

		return $event;
	}

	/**
	 * Get a single event, with a link to RSVP.
	 *
	 * @param string $id Event ID
	 *
	 * @return string Event details formatted for display in widget
	 */
	public function get_single_event( $id ) {
		global $event;
		$options       = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];
		$out           = '';

		if ( ! empty( $this->api_key ) ) {
			$event = $this->get_data( array( 'event_id' => $id ), 'vsm_single_event_' . $id );
			if ( ! $event ) {
				return;
			}

			ob_start();

			$template = '';
			if ( isset( $event->group ) && isset( $event->group->urlname ) ) {
				$template = $event->group->urlname;
			}
			get_template_part( 'meetup-single', apply_filters( 'vsm_single_template', $template, $event ) );
			$out = ob_get_contents();

			if ( empty( $out ) ) {
				// grab the template included in plugin
				$template = VSMEET_TEMPLATE_DIR . '/meetup-single.php';
				if ( file_exists( $template ) ) {
					load_template( $template, false );
				}
				$out = ob_get_contents();
			}

			ob_end_clean();

		} else {
			if ( is_user_logged_in() ) {
				$out = '<p><a href="' . admin_url( 'options-general.php' ) . '">Please enter an API key</a></p>';
			}
		}
		return $out;
	}

	/**
	 * Get the HTML for a group's events via Meetup API
	 *
	 * @param string $id    Meetup ID or URL name
	 * @param string $limit Number of events to display, default 5.
	 *
	 * @return string Event list formatted for display in widget
	 */
	public function get_group_events( $id, $limit = 5 ) {
		global $events;
		$options       = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];

		if ( ! empty( $this->api_key ) ) {
			$args = array(
				'status' => 'upcoming',
				'page'   => $limit,
			);
			if ( preg_match( '/[a-zA-Z]/', $id ) ) {
				$args['group_urlname'] = $id;
			} else {
				$args['group_id'] = $id;
			}

			$events = $this->get_data( $args, 'vsm_group_events_' . $id . '_' . $limit );
			if ( ! $events ) {
				return;
			}

			ob_start();
			get_template_part( 'meetup-list', 'group' );
			$out = ob_get_contents();

			if ( empty( $out ) ) {
				// grab the template included in plugin
				$template = VSMEET_TEMPLATE_DIR . '/meetup-list.php';
				if ( file_exists( $template ) ) {
					load_template( $template, false );
				}
				$out = ob_get_contents();
			}

			ob_end_clean();

		} else {
			if ( is_user_logged_in() ) {
				$out = '<p><a href="' . admin_url( 'options-general.php' ) . '">Please enter an API key</a></p>';
			}
		}
		return $out;
	}

	// Function name was changed in 2.1, leave this for backwards compatibilty
	function get_list_events( $id, $limit = 5, $deprecated = '' ) {
		$this->get_group_events( $id, $limit );
	}

	/**
	 * Get user's list of events
	 *
	 * @param string $id     User ID
	 * @param string $limit  Number of events to display, default 5.
	 * @param string $rsvp   Only return events with this RSVP status (can only be set to 'yes' in UI)
	 *
	 * @return string Event list formatted for display in widget
	 */
	public function get_user_events( $limit = 5 ) {
		global $events;
		$options       = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];

		if ( ! empty( $this->api_key ) ) {
			$args = array(
				'rsvp' => 'yes',
				'page' => $limit,
			);

			$events = $this->get_data( $args, 'vsm_user_events_' . $limit );
			if ( ! $events ) {
				return;
			}

			ob_start();
			get_template_part( 'meetup-list', 'group' );
			$out = ob_get_contents();

			if ( empty( $out ) ) {
				// grab the template included in plugin
				$template = VSMEET_TEMPLATE_DIR . '/meetup-list.php';
				if ( file_exists( $template ) ) {
					load_template( $template, false );
				}
				$out = ob_get_contents();
			}

			ob_end_clean();

		} else {
			$out = '<p><a href="' . admin_url( 'options-general.php' ) . '">Please enter an API key</a></p>';
		}
		return $out;
	}
}
