<?php
/**
 * Set up the single event widget
 *
 * @package Meetup_Widgets
 */

/**
 * VsMeetSingle extends the widget class to create a single-event widget with RSVP functionality.
 */
class VsMeetSingleWidget extends WP_Widget {
	/**
	 * Set up the widget
	 */
	function __construct() {
		parent::__construct(
			'vsm-single-event',
			__( 'Meetup Single Event', 'meetup-widgets' ),
			array(
				'classname'   => 'widget_meetup_single_event',
				'description' => __( 'Display a single event.', 'meetup-widgets' ),
			)
		);
	}

	/**
	 * Display the widget content
	 *
	 * @see WP_Widget::widget
	 */
	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$id    = $instance['id'];
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		if ( $id ) {
			$vsm  = new Meetup_Widget();
			$html = $vsm->get_single_event( $id );
			echo $html;
		}
		echo $args['after_widget'];
	}

	/**
	 * Save the widget settings
	 *
	 * @see WP_Widget::update
	 */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['id']    = strip_tags( $new_instance['id'] );

		return $instance;
	}

	/**
	 * Display the widget settings form
	 *
	 * @see WP_Widget::form
	 */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
			$id    = esc_attr( $instance['id'] );
		} else {
			$title = '';
			$id    = '';
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title:', 'meetup-widgets' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<p><label for="<?php echo $this->get_field_id( 'id' ); ?>">
			<?php _e( 'Event ID:', 'meetup-widgets' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo $id; ?>" />
		</label></p>
	<?php
	}
}
