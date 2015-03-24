<?php
global $events;

echo '<ul class="meetup_list">';
foreach ( $events as $event ) {
	printf(
		'<li><a href="%1$s">%2$s</a>; %3$s</li>',
		esc_url( $event->event_url ),
		strip_tags( $event->name ),
		date( 'M d, g:ia', intval( $event->time/1000 + $event->utc_offset/1000 ) )
	);
}
echo '</ul>';
