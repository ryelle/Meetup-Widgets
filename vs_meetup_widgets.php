<?php
/**
 * VsMeetWidget
 * All widget info for VsMeet (including widgets themselves)
 */

class VsMeetWidget extends VsMeet {
	private $req_url = 'http://api.meetup.com/oauth/request/';
	private $authurl = 'http://www.meetup.com/authorize/';
	private $acc_url = 'http://api.meetup.com/oauth/access/';
	private $api_url = 'http://api.meetup.com/';
	private $callback_url = '';
	private $base_url = 'http://api.meetup.com/2/events/';

	private $key = '';
	private $secret = '';
	protected $api_key = '';

	public function __construct() {
		$options = get_option( 'vs_meet_options' );
		$this->key = $options['vs_meetup_key'];
		$this->secret = $options['vs_meetup_secret'];
		$this->api_key = $options['vs_meetup_api_key'];
		$this->callback_url = admin_url( 'admin-ajax.php' ) . '?action=meetup_event';

		parent::__construct();

		// add login function to ajax requests
		add_action( 'wp_ajax_nopriv_meetup_event', array( $this, 'meetup_event_popup' ) );
		add_action( 'wp_ajax_meetup_event', array( $this, 'meetup_event_popup' ) );
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
			$args = wp_parse_args( $args, $defaults );
			$url = add_query_arg( $args, $this->base_url );
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
	 * Get a single event, with a link to RSVP (OAuth, new tiny window).
	 *
	 * @param string $id Event ID
	 *
	 * @return string Event details formatted for display in widget
	 */
	public function get_single_event( $id ) {
		global $event;
		$options = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];
		$out = '';

		if ( ! empty( $this->api_key ) ) {
			$event = $this->get_data( array( 'event_id' => $id ), 'vsm_single_event_'.$id );
			if ( ! $event ) {
				return;
			}

			ob_start();

			// We want the callback URL in the template, passing it in via the global $event is easiest.
			$event->callback_url = $this->callback_url;
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
	 * @param string  $id    Meetup ID or URL name
	 * @param string  $limit Number of events to display, default 5.
	 *
	 * @return string Event list formatted for display in widget
	 */
	public function get_group_events( $id, $limit = 5 ) {
		global $events;
		$options = get_option( 'vs_meet_options' );
		$this->api_key = $options['vs_meetup_api_key'];

		if ( ! empty( $this->api_key ) ) {
			$args = array(
				'status' => 'upcoming',
				'page' => $limit,
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
	 * @param string  $id     User ID
	 * @param string  $limit  Number of events to display, default 5.
	 * @param string  $rsvp   Only return events with this RSVP status (can only be set to 'yes' in UI)
	 *
	 * @return string Event list formatted for display in widget
	 */
	public function get_user_events( $limit = 5 ) {
		global $events;
		$options = get_option( 'vs_meet_options' );
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

	/**
	 * Create the event RSVP popup
	 */
	function meetup_event_popup() {
		session_start();
		$header = '<html dir="ltr" lang="en-US">
			<head>
				<meta charset="UTF-8" />
				<meta name="viewport" content="width=device-width" />
				<title>RSVP to a Meetup</title>
				<link rel="stylesheet" type="text/css" media="all" href="' . get_bloginfo( 'stylesheet_url' ) . '" />
				<style>
					.button {
						padding:3%;
						color:white;
						background-color:#B03C2D;
						border-radius:3px;
						display:block;
						font-weight:bold;
						width:40%;
						float:left;
						text-align:center;
					}
					.button.no {
						margin-left:8%;
					}
				</style>
			</head>
			<body>
				<div id="page" class="hfeed meetup event" style="padding:15px;">';
		if ( array_key_exists( 'event', $_GET ) ) {
			$_SESSION['event'] = $_GET['event'];
		}
		if ( ! array_key_exists( 'state', $_SESSION ) ) {
			$_SESSION['state'] = 0;
		}
		// In state=1 the next request should include an oauth_token.
		// If it doesn't go back to 0
		if ( ! isset( $_GET['oauth_token'] ) && 1 == $_SESSION['state'] ) {
			$_SESSION['state'] = 0;
		}
		try {
			$oauth = new OAuth( $this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION );
			$oauth->enableDebug();

			if ( ! isset( $_GET['oauth_token'] ) && ! $_SESSION['state'] ) {
				$request_token_info = $oauth->getRequestToken( $this->req_url );
				$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
				$_SESSION['state'] = 1;
				header( sprintf(
					'Location: %s?oauth_token=%s&oauth_callback=%s',
					$this->authurl,
					$request_token_info['oauth_token'],
					$this->callback_url
				) );
				exit;
			} else if ( 1 == $_SESSION['state'] ) {
				$oauth->setToken( $_GET['oauth_token'], $_SESSION['secret'] );
				$verifier = ( array_key_exists( 'verifier', $_GET ) ) ? $_GET['verifier'] : null;
				$access_token_info = $oauth->getAccessToken( $this->acc_url, null, $verifier );
				$_SESSION['state'] = 2;
				$_SESSION['token'] = $access_token_info['oauth_token'];
				$_SESSION['secret'] = $access_token_info['oauth_token_secret'];
			}

			$oauth->setToken( $_SESSION['token'], $_SESSION['secret'] );
			if ( array_key_exists( 'rsvp', $_GET ) ) { // button has been pressed.
				//send the RSVP.
				if ( 'yes' == $_GET['rsvp'] ) {
					$oauth->fetch( "{$this->api_url}/rsvp", array( 'event_id' => $_SESSION['event'], 'rsvp' => 'yes' ), OAUTH_HTTP_METHOD_POST );
				} else {
					$response = $oauth->fetch( "{$this->api_url}/rsvp", array( 'event_id' => $_SESSION['event'], 'rsvp' => 'no' ), OAUTH_HTTP_METHOD_POST );
				}
				$rsvp = json_decode( $oauth->getLastResponse() );

				echo $header;
				echo '<h1 style="padding:20px 0 0;"><a>' . $rsvp->description . '</a></h1>';
				echo '<p>' . $rsvp->details . '.</p>';
				exit;
			} else {
				// Get event info to display here.
				$oauth->fetch( "{$this->api_url}/2/events?event_id=" . $_SESSION['event'] );
				$event = json_decode( $oauth->getLastResponse() );
				$event = $event->results[0];
				$out  = '<h1 id="site-title" style="padding:20px 0 0;"><a target="_blank" href="'.$event->event_url.'">'.$event->name.'</a></h1>';
				$out .= '<p style="text-align:justify;">'.$event->description.'</p>';
				$out .= '<p><span class="rsvp-count">'.$event->yes_rsvp_count.' '._n( 'attendee', 'attendees', $event->yes_rsvp_count ).'</span></p>';
				if ( null !== $event->venue ) {
					$venue = $event->venue->name.' '.$event->venue->address_1 . ', ' . $event->venue->city . ', ' . $event->venue->state;
					$out .= "<h3 class='event_location'>Location: <a href='http://maps.google.com/maps?q=$venue+%28".$event->venue->name."%29&z=17' target='_blank'>$venue</a></h3>";
				} else {
					$out .= "<p class='event_location'>Location: TBA</p>";
				}
				$out .= '<h2>'.date( 'F d, Y @ g:i a', intval( $event->time / 1000 + $event->utc_offset / 1000 ) ).'</h2>';

				echo $header . $out;

				$oauth->fetch( "{$this->api_url}/rsvps?event_id=" . $_SESSION['event'] );
				$rsvps = json_decode( $oauth->getLastResponse() );
				$oauth->fetch( "{$this->api_url}/members?relation=self" );
				$me = json_decode( $oauth->getLastResponse() );
				$my_id = $me->results[0]->id;
				foreach ( $rsvps->results as $user ) {
					if ( $my_id == $user->member_id ) {
						echo "<h3 style='padding:20px 0 0; font-weight:normal; font-size:16px'>Your RSVP: <strong>{$user->response}</strong></h3>";
						echo '<p>You can change your RSVP below.</p>';
					}
				}

				echo "<h1 style='padding:20px 0 0; font-weight:bold; font-size:22px'>RSVP: </h1>";
				echo "<p style='font-size:.9em'>Please RSVP at meetup.com if you're bringing someone.</p>";
				echo "<a class='button yes' href='{$this->callback_url}&rsvp=yes'>Yes</a>";
				echo "<a class='button no' href='{$this->callback_url}&rsvp=no'>No</a>";
				echo "<p style='clear:both'></p>";
				//echo "<pre>".print_r($event,true)."</pre>";
				exit;
			}
		} catch ( OAuthException $E ) {
			echo $header;
			echo "<h1 class='entry-title'>There was an error processing your request. Please try again.</h1>";
			if ( WP_DEBUG ) {
				echo '<pre>' . print_r( $E, true ) . '</pre>';
			}
		}
		unset( $_SESSION['state'] );
		unset( $_SESSION['event'] );
		echo '</div> </body> </html>';
	}
}
