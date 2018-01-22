<?php
/**
 * Set up the group list widget
 *
 * @package Meetup_Widgets
 */

/**
 * VsMeetList extends the widget class to create an event list for a specific meetup group.
 */
class VsMeetListWidget extends WP_Widget {
	/**
	 * Set up the widget
	 */
	function __construct() {
		parent::__construct(
			'vsm-group-list',
			__( 'Meetup List Event', 'meetup-widgets' ),
			array(
				'classname'   => 'widget_meetup_group_list',
				'description' => __( 'Display a list of events.', 'meetup-widgets' ),
			)
		);
	}

	/**
	 * Normalize and sanitize the widget attribute values
	 */
	function get_sanitized_values( $attrs ) {
		return array(
			'title' => isset( $attrs['title'] ) ? strip_tags( $attrs['title'] ) : '',
			'id' => isset( $attrs['id'] ) ? sanitize_title( $attrs['id'] ) : '',
			'limit' => isset( $attrs['limit'] ) ? filter_var( $attrs['limit'], FILTER_VALIDATE_INT ) : 3,
			'show_desc' => isset( $attrs['show_desc'] ) ? filter_var( $attrs['show_desc'], FILTER_VALIDATE_BOOLEAN ) : false,
		);
	}

	/**
	 * Display the widget content
	 *
	 * @see WP_Widget::widget
	 */
	function widget( $args, $instance ) {
		$attrs = $this->get_sanitized_values( $instance );
		$title = apply_filters( 'widget_title', $attrs['title'] );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo Meetup_Widgets_Blocks::render_block_group_list( array(
			'title' => '',
			'group' => $attrs['id'],
			'per_page' => $attrs['limit'],
			'show_description' => $attrs['show_desc'],
			'placeholder' => __( 'No upcoming events.', 'meetup-widgets' ),
		) );
		echo $args['after_widget'];
	}

	/**
	 * Save the widget settings
	 *
	 * @see WP_Widget::update
	 */
	function update( $new_instance, $old_instance ) {
		$new_values = $this->get_sanitized_values( $new_instance );
		return array_merge( $old_instance, $new_values );
	}

	/**
	 * Display the widget settings form
	 *
	 * @see WP_Widget::form
	 */
	function form( $instance ) {
		$attrs = $this->get_sanitized_values( $instance );
		$groups = $this->get_groups_from_api();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:', 'meetup-widgets' ); ?>
			</label>
			<input
				class="widefat"
				type="text"
				id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>"
				value="<?php echo $attrs['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'id' ); ?>">
				<?php _e( 'Meetup Group:', 'meetup-widgets' ); ?>
			</label>
			<select
				class="widefat"
				id="<?php echo $this->get_field_id( 'id' ); ?>"
				name="<?php echo $this->get_field_name( 'id' ); ?>">
				<option disabled>Choose your group…</option>
				<?php foreach ( $groups as $group ) :
					$group_url = strtolower( $group['urlname'] ); ?>
					<option
						value="<?php echo esc_attr( $group_url ); ?>"
						<?php selected( $attrs['id'], $group_url ); ?>>
						<?php echo esc_attr( $group['name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of events to show:', 'meetup-widgets' ); ?>
			</label>
			<input
				type="number"
				id="<?php echo $this->get_field_id( 'limit' ); ?>"
				name="<?php echo $this->get_field_name( 'limit' ); ?>"
				value="<?php echo $attrs['limit']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_desc' ); ?>">
				<input
					type="checkbox"
					id="<?php echo $this->get_field_id( 'show_desc' ); ?>"
					name="<?php echo $this->get_field_name( 'show_desc' ); ?>"
					<?php checked( $attrs['show_desc'] ); ?> />
				<?php _e( 'Show the event description?', 'meetup-widgets' ); ?>
			</label>
		</p>
	<?php
	}

	/**
	 * Get the user's groups list from the API
	 */
	function get_groups_from_api() {
		$request = new WP_REST_Request( 'GET', '/meetup/v1/groups/self' );
		$request->set_header( 'x-mw-nonce', wp_create_nonce( 'meetup-widgets' ) );
		$response = rest_do_request( $request );
		$groups = $response->get_data();

		if ( isset( $groups['code'] ) || count( $groups ) < 1 ) {
			return [];
		}

		return $groups;
	}
}
