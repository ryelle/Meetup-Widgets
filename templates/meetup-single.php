<?php
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
<p class="event-rsvp"><span class="rsvp-count"><?php printf( _n( '%s attendee', '%s attendees', $event->yes_rsvp_count, 'meetup-widgets' ), number_format_i18n( $event->yes_rsvp_count ) ); ?></span>

<span class="rsvp-add"><a href="<?php echo esc_url( $event->event_url ); ?>">RSVP?</a></span></p>

<?php
if ( isset( $event->venue ) ) {
	$venue = $event->venue->name . ' ' . $event->venue->address_1 . ', ' . $event->venue->city . ', ' . $event->venue->state;
	echo "<p class='event-location'>Location: <a href='http://maps.google.com/maps?q=$venue+%28" . $event->venue->name . "%29&z=17'>$venue</a></p>";
} else {
	$venue = apply_filters( 'vsm_no_location_text', 'Location: TBA' );
	if ( ! empty( $venue ) ) {
		echo "<p class='event-location'>$venue</p>";
	}
}
