<?php
/**
 * VsMeetUserList extends the widget class to create an event list for a specific meetup group.
 */
class VsMeetUserListWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::__construct(
			'vsm-user-list',
			__( 'Meetup User Events', 'vsmeet_domain' ),
			array(
				'classname' => 'widget_meetup_user_list',
				'description' => __( 'Display a list of events for a single user.', 'vsmeet_domain' ),
			)
		);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$limit = absint( $instance['limit'] );

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		$vsm = new VsMeetWidget();
		$html = $vsm->get_user_events( $limit );
		echo $html;
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = absint( $new_instance['limit'] );

		// remove caching of old event
		if ( ! empty( $old_instance['id'] ) ) {
			delete_transient( 'vsmeet_user_events_' . $old_instance['id'] );
		}

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
			$limit = absint( $instance['limit'] );
		} else {
			$title = '';
			$limit = 5;
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title:', 'vsmeet_domain' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of events to show:', 'vsmeet_domain' ); ?>
			</label>
			<input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" size='3' />
		</p>
		<p class="description">This widget automatically pulls events from the user who created the API key.</p>
	<?php }
}
