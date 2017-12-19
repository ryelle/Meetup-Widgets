<?php
/**
 * VsMeetList extends the widget class to create an event list for a specific meetup group.
 */
class VsMeetListWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::__construct(
			'vsm-group-list',
			__( 'Meetup List Event', 'vsmeet_domain' ),
			array(
				'classname'   => 'widget_meetup_group_list',
				'description' => __( 'Display a list of events.', 'vsmeet_domain' ),
			)
		);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$id    = $instance['id']; // meetup ID or URL name
		$limit = intval( $instance['limit'] );

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( $id ) {
			$vsm  = new VsMeetWidget();
			$html = $vsm->get_group_events( $id, $limit );
			echo $html;
		}
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( preg_match( '/[a-zA-Z]/', $new_instance['id'] ) ) {
			$instance['id'] = sanitize_title( $new_instance['id'] );
		} else {
			$instance['id'] = str_replace( ' ', '', $new_instance['id'] );
		}
		$instance['limit'] = intval( $new_instance['limit'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
			$id    = esc_attr( $instance['id'] ); // -> it's a name if it contains any a-zA-z, otherwise ID
			$limit = intval( $instance['limit'] );
		} else {
			$title = '';
			$id    = '';
			$limit = 5;
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">
			<?php _e( 'Title:', 'vsmeet_domain' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<p><label for="<?php echo $this->get_field_id( 'id' ); ?>">
			<?php _e( 'Group ID:', 'vsmeet_domain' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo $id; ?>" />
		</label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of events to show:', 'vsmeet_domain' ); ?>
			</label>
			<input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" size='3' />
		</p>
	<?php
	}
}
