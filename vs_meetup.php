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

/**
 * If the class exists we've declared it in another Meetup integration plugin,
 * and should not do so twice.
 */
if ( ! class_exists( 'VsMeet' ) ) :

	class VsMeet {
		/* global variables */
		private $req_url = 'http://api.meetup.com/oauth/request/';
		private $authurl = 'http://www.meetup.com/authorize/';
		private $acc_url = 'http://api.meetup.com/oauth/access/';
		private $api_url = 'http://api.meetup.com/';

		private $key = '';
		private $secret = '';
		protected $api_key = '';

		public function __construct( $var = '' ) {
			$options = get_option( 'vs_meet_options' );
			$this->key = $options['vs_meetup_key'];
			$this->secret = $options['vs_meetup_secret'];
			$this->api_key = $options['vs_meetup_api_key'];

			register_activation_hook( __FILE__, array( $this, 'install' ) );

			// TODO deal with translations.
			//load_plugin_textdomain('vsmeet_domain');

			// add admin page & options
			add_filter( 'admin_init' , array( $this, 'register_fields' ) );
		}

		public function install() {
			//nothing here yet, as there's really nothing to 'install' that isn't covered by __construct
		}

		function register_fields() {
			register_setting( 'general', 'vs_meet_options', array( $this, 'validate' ) );

			add_settings_section( 'vs_meet', 'Meetup API Settings', array( $this, 'setting_section_vs_meetup' ), 'general' );

			add_settings_field( 'vs_meetup_key', sprintf( '<label for="vs_meetup_key">%s</label>', __( 'OAuth Meetup Key:' , 'vsmeet_domain' ) ), array( $this, 'setting_vs_meetup_key' ) , 'general', 'vs_meet' );
			add_settings_field( 'vs_meetup_secret', sprintf( '<label for="vs_meetup_secret">%s</label>', __( 'OAuth Meetup Secret:' , 'vsmeet_domain' ) ) , array( $this, 'setting_vs_meetup_secret' ), 'general', 'vs_meet' );

			add_settings_field( 'vs_meetup_api_key', sprintf( '<label for="vs_meetup_api_key">%s</label>', __( 'Meetup API Key:' , 'vsmeet_domain' ) ), array( $this, 'setting_vs_meetup_api_key' ) , 'general', 'vs_meet' );

		}

		function setting_section_vs_meetup() {
			printf(
				'<p>%s</p>',
				sprintf(
					__( 'To use this plugin, you need to create an OAuth consumer. You can do that (or reset your information) by going here: %s', 'vsmeet_domain' ),
					"<a target='_blank' href='http://www.meetup.com/meetup_api/oauth_consumers/'>Your OAuth Consumers</a>"
				)
			);
		}

		function setting_vs_meetup_key() {
			$options = get_option( 'vs_meet_options' );
			printf( '<input id="vs_meetup_key" name="vs_meet_options[vs_meetup_key]" size="40" type="text" value="%s" />', esc_attr( $options['vs_meetup_key'] ) );
		}
		function setting_vs_meetup_secret() {
			$options = get_option( 'vs_meet_options' );
			printf( '<input id="vs_meetup_secret" name="vs_meet_options[vs_meetup_secret]" size="40" type="text" value="%s" />', esc_attr( $options['vs_meetup_secret'] ) );
		}
		function setting_vs_meetup_api_key() {
			$options = get_option( 'vs_meet_options' );
			printf( '<input id="vs_meetup_api_key" name="vs_meet_options[vs_meetup_api_key]" size="40" type="text" value="%s" />', esc_attr( $options['vs_meetup_api_key'] ) );
		}

		/**
		 * Sanitize and validate input.
		 * @param array $input an array to sanitize
		 * @return array a valid array.
		 */
		public function validate( $input ) {
			$output = array();
			if ( preg_match( '/^[a-zA-Z0-9]{0,40}$/i', $input['vs_meetup_key'] ) ) {
				$output['vs_meetup_key'] = $input['vs_meetup_key'];
			}
			if ( preg_match( '/^[a-zA-Z0-9]{0,40}$/i', $input['vs_meetup_secret'] ) ) {
				$output['vs_meetup_secret'] = $input['vs_meetup_secret'];
			}
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
	if ( ! class_exists( 'OAuth' ) ) {
		add_action( 'pre_current_active_plugins', 'vsmw_need_oauth' );
	}
	$vsmw = new VsMeetWidget();

} add_action( 'init', 'meetup_widgets_start' );

// Register widgets
add_action( 'widgets_init', create_function( '', 'return register_widget("VsMeetSingleWidget");' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget("VsMeetListWidget");' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget("VsMeetUserListWidget");' ) );

function vsmw_need_oauth() {
	echo '<div id="message" class="updated"><p>Some functionality of <strong>Meetup Login</strong> requires <strong><a href="http://php.net/manual/en/book.oauth.php">OAuth</a></strong>, which is not detected on this server. RSVPing from this site is disabled - RSVP links will instead bring you to the event on Meetup.com.</p></div>';
}
