<?php

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
