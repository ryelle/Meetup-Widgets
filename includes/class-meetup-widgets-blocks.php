<?php
/**
 * Initialize Gutenberg block support
 *
 * @package Meetup_Widgets
 * @since 3.0.0
 */
use Handlebars\Handlebars;

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

		$this->register_block_assets();

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
				'show_description' => array(
					'type' => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render_block_group_list' ),
			'editor_script' => 'meetup-blocks',
			'editor_style' => 'meetup-blocks',
		) );

		register_block_type( 'meetup-widgets/user-list', array(
			'attributes' => array(
				'title' => array(
					'type' => 'string',
					'default' => __( 'My Events', 'meetup-widgets' ),
				),
				'placeholder' => array(
					'type' => 'string',
					'default' => __( 'No upcoming events.', 'meetup-widgets' ),
				),
				'per_page' => array(
					'type' => 'number',
					'default' => 5,
				),
				'show_description' => array(
					'type' => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render_block_user_list' ),
			'editor_script' => 'meetup-blocks',
			'editor_style' => 'meetup-blocks',
		) );

		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_style( 'meetup-blocks' );
		} );
	}

	/**
	 * Add Gutenberg block JS & CSS to the editor
	 */
	public function register_block_assets() {
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

		wp_register_script( 'meetup-blocks', $js_file, [ 'wp-blocks', 'wp-i18n', 'wp-element' ], $js_version );
		wp_register_style( 'meetup-blocks', $css_file, [ 'wp-blocks' ], $css_version );
	}

	/**
	 * Renders the `meetup-widgets/group-list` block on server.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the upcoming events for a given group, to be displayed in a post
	 */
	public function render_block_group_list( $attributes ) {
		// Initialize handlebars
		$loader = new \Handlebars\Loader\FilesystemLoader(
			VSMEET_TEMPLATE_DIR,
			array(
				'extension' => 'hbs',
			)
		);
		$engine = new Handlebars( array(
			'loader' => $loader,
		) );

		$request = new WP_REST_Request( 'GET', '/meetup/v1/events/' . $attributes['group'] );
		$request->set_header( 'x-mw-nonce', wp_create_nonce( 'meetup-widgets' ) );
		$request->set_query_params( array(
			'per_page' => $attributes['per_page'],
		) );
		$response = rest_do_request( $request );
		$events = $response->get_data();

		$has_events = ! isset( $events['code'] ) && count( $events ) > 0;

		$vars = [
			'attributes' => $attributes,
			'events' => $events,
			'hide_title' => false,
			'show_events' => $has_events,
			'show_events_description' => $has_events && $attributes['show_description'],
		];
		$output = $engine->render( 'meetup-list', $vars );

		return $output;
	}

	/**
	 * Renders the `meetup-widgets/user-list` block on server.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the upcoming events for the current API user, to be displayed in a post
	 */
	public function render_block_user_list( $attributes ) {
		// Initialize handlebars
		$loader = new \Handlebars\Loader\FilesystemLoader(
			VSMEET_TEMPLATE_DIR,
			array(
				'extension' => 'hbs',
			)
		);
		$engine = new Handlebars( array(
			'loader' => $loader,
		) );

		$request = new WP_REST_Request( 'GET', '/meetup/v1/events/self' );
		$request->set_header( 'x-mw-nonce', wp_create_nonce( 'meetup-widgets' ) );
		$request->set_query_params( array(
			'per_page' => $attributes['per_page'],
		) );
		$response = rest_do_request( $request );
		$events = $response->get_data();

		$has_events = count( $events ) > 0;

		$vars = [
			'attributes' => $attributes,
			'events' => $events,
			'hide_title' => false,
			'show_events' => $has_events,
			'show_events_description' => $has_events && $attributes['show_description'],
		];
		$output = $engine->render( 'meetup-list', $vars );

		return $output;
	}
}
