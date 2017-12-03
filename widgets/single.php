<?php
/**
 * VsMeetSingle extends the widget class to create a single-event widget with RSVP functionality.
 */
class VsMeetSingleWidget extends WP_Widget {
	/** constructor */
	function VsMeetSingleWidget() {
		parent::__construct( false, $name = __( 'Meetup Single Event', 'vsmeet_domain' ), array( 'description' => __( 'Display a single event.', 'vsmeet_domain' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$id = $instance['id'];
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( $id ) {
			$vsm = new VsMeetWidget();
			$html = $vsm->get_single_event( $id );
			echo $html;
		}
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['id'] = strip_tags( $new_instance['id'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
			$id = esc_attr( $instance['id'] );
		} else {
			$title = '';
			$id = '';
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title:', 'vsmeet_domain' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<p><label for="<?php echo $this->get_field_id( 'id' ); ?>">
			<?php _e( 'Event ID:', 'vsmeet_domain' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo $id; ?>" />
		</label></p>
	<?php }
} // class VsMeetSingleWidget
