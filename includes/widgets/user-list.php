<?php
/**
 * Set up the user list widget
 *
 * @package Meetup_Widgets
 */

/**
 * VsMeetUserList extends the widget class to create an event list for a specific meetup group.
 */
class VsMeetUserListWidget extends WP_Widget {
	/**
	 * Set up the widget
	 */
	function __construct() {
		parent::__construct(
			'vsm-user-list',
			__( 'Meetup User Events', 'meetup-widgets' ),
			array(
				'classname'   => 'widget_meetup_user_list',
				'description' => __( 'Display a list of events for a single user.', 'meetup-widgets' ),
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
		$limit = absint( $instance['limit'] );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$vsm  = new Meetup_Widget();
		$html = $vsm->get_user_events( $limit );
		echo $html;
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
		$instance['limit'] = absint( $new_instance['limit'] );

		// remove caching of old event
		if ( ! empty( $old_instance['id'] ) ) {
			delete_transient( 'vsmeet_user_events_' . $old_instance['id'] );
		}

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
			$limit = absint( $instance['limit'] );
		} else {
			$title = '';
			$limit = 5;
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title:', 'meetup-widgets' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of events to show:', 'meetup-widgets' ); ?>
			</label>
			<input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" size='3' />
		</p>
		<p class="description"><?php _e( 'This widget automatically pulls events from the user who created the API key.', 'meetup-widgets' ); ?></p>
	<?php
	}
}
