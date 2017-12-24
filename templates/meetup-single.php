<?php
/**
 * Template for the single event view
 *
 * @package Meetup_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $event;

if ( isset( $event->time ) ) {
	$date = date( 'F d, Y @ g:i a', intval( $event->time / 1000 + $event->utc_offset / 1000 ) );
} else {
	$date = apply_filters( 'vsm_no_date_text', '' );
}
?>

<h3 class="event-title"><a href="<?php echo esc_url( $event->event_url ); ?>"><?php echo strip_tags( $event->name ); ?></a></h3>
<?php if ( ! empty( $date ) ) : ?>
<p class="event-date"><?php echo $date; ?></p>
<?php endif; ?>
<p class="event-summary"><?php echo wp_trim_words( strip_tags( $event->description ), 20 ); ?></p>
<p class="event-rsvp">
	<span class="rsvp-count">
		<?php printf(
			// Translators: %s is the count of people who have RSVP'd to the event.
			_n( '%s attendee', '%s attendees', $event->yes_rsvp_count, 'meetup-widgets' ),
			number_format_i18n( $event->yes_rsvp_count )
		); ?>
	</span>
	<span class="rsvp-add">
		<a href="<?php echo esc_url( $event->event_url ); ?>">RSVP?</a>
	</span>
</p>

<?php
if ( isset( $event->venue ) ) {
	$venue = $event->venue->name . ' ' . $event->venue->address_1 . ', ' . $event->venue->city . ', ' . $event->venue->state;
	$link = sprintf(
		'<a href="http://maps.google.com/maps?q=%1$s+%28%2$s%29&z=17">%1$s</a>',
		$venue,
		$event->venue->name
	);
	echo '<p class="event-location">';
	// Translators: %s is a link to the location, as it appears on Meetup.com
	printf( __( 'Location: %s', 'meetup-widgets' ), $link );
	echo '</p>';
} else {
	$venue = apply_filters( 'vsm_no_location_text', __( 'Location: TBA', 'meetup-widgets' ) );
	if ( ! empty( $venue ) ) {
		echo "<p class='event-location'>$venue</p>";
	}
}
