<?php
/*
 * Plugin Name: Meetup Widgets
 * Description: Add widgets displaying information from Meetup.com
 * Version: 2.2.1
 * Author: Kelly Dwan
 * Author URI: http://redradar.net
 * Plugin URI: http://redradar.net/category/plugins/meetup-widgets/
 * License: GPL2
 * Date: 01.06.2016
 */

if ( ! defined( 'VSMEET_TEMPLATE_DIR' ) ) {
	define( 'VSMEET_TEMPLATE_DIR', dirname( __FILE__ ) . '/templates/' );
}

/**
 * If the class exists we've declared it in another Meetup integration plugin,
 * and should not do so twice.
 */
if ( ! class_exists( 'VsMeet' ) ) :

	class VsMeet {
		/* global variables */
		private $api_url   = 'http://api.meetup.com/';
		protected $api_key = '';

		public function __construct( $var = '' ) {
			$options       = get_option( 'vs_meet_options' );
			$this->api_key = $options['vs_meetup_api_key'];

			register_activation_hook( __FILE__, array( $this, 'install' ) );

			// TODO deal with translations.
			// load_plugin_textdomain('vsmeet_domain');
			// add admin page & options
			add_filter( 'admin_init', array( $this, 'register_fields' ) );
		}

		public function install() {
			// nothing here yet, as there's really nothing to 'install' that isn't covered by __construct
		}

		function register_fields() {
			register_setting( 'general', 'vs_meet_options', array( $this, 'validate' ) );

			add_settings_section( 'vs_meet', 'Meetup API Settings', array( $this, 'setting_section_vs_meetup' ), 'general' );

			add_settings_field( 'vs_meetup_api_key', sprintf( '<label for="vs_meetup_api_key">%s</label>', __( 'Meetup API Key:', 'vsmeet_domain' ) ), array( $this, 'setting_vs_meetup_api_key' ), 'general', 'vs_meet' );

		}

		function setting_section_vs_meetup() {
			printf(
				'<p>%s</p>',
				sprintf(
					__( 'To use this plugin, you need to a meetup.com API key. You can find your API key at the <a href="">&ldquo;Getting An API Key&rdquo;</a> page. Click the lock next to the input field, then copy the contents of the input into the Meetup API field below.', 'vsmeet_domain' ),
					'https://secure.meetup.com/meetup_api/key/'
				)
			);
		}

		function setting_vs_meetup_api_key() {
			$options = get_option( 'vs_meet_options' );
			printf( '<input id="vs_meetup_api_key" name="vs_meet_options[vs_meetup_api_key]" size="40" type="text" value="%s" />', esc_attr( $options['vs_meetup_api_key'] ) );
		}

		/**
		 * Sanitize and validate input.
		 *
		 * @param array $input an array to sanitize
		 * @return array a valid array.
		 */
		public function validate( $input ) {
			$output = array();
			if ( preg_match( '/^[a-zA-Z0-9]{0,40}$/i', $input['vs_meetup_api_key'] ) ) {
				$output['vs_meetup_api_key'] = $input['vs_meetup_api_key'];
			}
			return $output;
		}
	}

endif;

require_once( 'vs_meetup_widgets.php' );

/**
 * Initialize Meetup Widgets
 */
function meetup_widgets_start() {
	$vsmw = new VsMeetWidget();

} add_action( 'init', 'meetup_widgets_start' );

require_once( 'widgets/single.php' );
add_action(
	'widgets_init', function() {
		return register_widget( 'VsMeetSingleWidget' );
	}
);

require_once( 'widgets/group-list.php' );
add_action(
	'widgets_init', function() {
		return register_widget( 'VsMeetListWidget' );
	}
);

require_once( 'widgets/user-list.php' );
add_action(
	'widgets_init', function() {
		return register_widget( 'VsMeetUserListWidget' );
	}
);
