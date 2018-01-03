<?php
/**
 * Initialize Gutenberg block support
 *
 * @package Meetup_Widgets
 * @since 3.0.0
 */


/**
 * Container for block functionality
 */
class Meetup_Widgets_Blocks {
	/**
	 * The base directory of the blocks
	 *
	 * @var string $dir
	 */
	protected $dir = '';

	/**
	 * Initialize the blocks
	 */
	public function __construct() {
		$this->dir = dirname( dirname( __FILE__ ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_assets' ) );

		register_block_type( 'meetup-widgets/group-list', array(
			'attributes' => array(
				'title' => array(
					'type' => 'string',
					'default' => __( 'Upcoming Events', 'meetup-widgets' ),
				),
				'placeholder' => array(
					'type' => 'string',
					'default' => __( 'No upcoming events.', 'meetup-widgets' ),
				),
				'group' => array(
					'type' => 'string',
				),
				'per_page' => array(
					'type' => 'number',
					'default' => 5,
				),
			),
			'render_callback' => array( $this, 'render_block_group_list' ),
		) );
	}

	/**
	 * Add Gutenberg block JS & CSS to the editor
	 */
	public function enqueue_block_assets() {
		$js_file = 'http://localhost:8081/build/index.js';
		$css_file = 'http://localhost:8081/build/editor.css';
		$js_version = false;
		$css_version = false;

		if ( ! WP_DEBUG ) {
			$js_file = plugins_url( 'build/index.js', dirname( __FILE__ ) );
			$css_file = plugins_url( 'build/editor.css', dirname( __FILE__ ) );
			$js_version = filemtime( "{$this->dir}/build/index.js" );
			$css_version = filemtime( "{$this->dir}/build/editor.css" );
		}

		wp_enqueue_script( 'meetup-blocks', $js_file, [ 'wp-blocks', 'wp-i18n', 'wp-element' ], $js_version );
		wp_enqueue_style( 'meetup-blocks', $css_file, [ 'wp-blocks' ], $css_version );
	}

	/**
	 * Renders the `core/latest-posts` block on server.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with latest posts added.
	 */
	public function render_block_group_list( $attributes ) {
		$request = new WP_REST_Request( 'GET', '/meetup/v1/events/' . $attributes['group'] );
		$request->set_query_params( array(
			'per_page' => $attributes['per_page'],
		) );
		$response = rest_do_request( $request );
		if ( 200 !== $response->get_status() ) {
			return '<h3>' . $attributes['title'] . '</h3>';
		}

		$events = $response->get_data();
		$block_content = [ '<h3>' . $attributes['title'] . '</h3>', '<ul>' ];
		array_push( $block_content, '<p>' . $attributes['group'] . '</p>' );
		foreach ( $events as $event ) {
			$list_item = '<li>' . $event['name'] . ' ' . $event['date'] . '</li>';
			array_push( $block_content, $list_item );
		}
		array_push( $block_content, '</ul>' );

		return implode( "\n", $block_content );
	}
}
