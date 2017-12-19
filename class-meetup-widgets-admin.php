<?php
/**
 * Handles any admin view functionality
 *
 * @package Meetup_Widgets
 * @since 3.0.0
 */

if ( ! class_exists( 'Meetup_Widgets_Admin' ) ) {
	/**
	 * Set up the Settings section to save Meetup.com API key
	 */
	class Meetup_Widgets_Admin {
		/**
		 * Initialize the settings screen
		 */
		public function __construct() {
			// TODO deal with translations.
			add_filter( 'admin_init', array( $this, 'register_fields' ) );
		}

		/**
		 * Register the section in Settings -> General
		 */
		function register_fields() {
			register_setting( 'general', 'vs_meet_options', array( $this, 'validate' ) );

			add_settings_section( 'vs_meet', 'Meetup API Settings', array( $this, 'setting_section_vs_meetup' ), 'general' );

			add_settings_field( 'vs_meetup_api_key', sprintf( '<label for="vs_meetup_api_key">%s</label>', __( 'Meetup API Key:', 'vsmeet_domain' ) ), array( $this, 'setting_vs_meetup_api_key' ), 'general', 'vs_meet' );

		}

		/**
		 * Display the API key directions
		 */
		function setting_section_vs_meetup() {
			printf(
				'<p>%s</p>',
				sprintf(
					__( 'To use this plugin, you need to a meetup.com API key. You can find your API key at the <a href="">&ldquo;Getting An API Key&rdquo;</a> page. Click the lock next to the input field, then copy the contents of the input into the Meetup API field below.', 'vsmeet_domain' ),
					'https://secure.meetup.com/meetup_api/key/'
				)
			);
		}

		/**
		 * Display an input for the API key
		 */
		function setting_vs_meetup_api_key() {
			$options = get_option( 'vs_meet_options' );
			printf( '<input id="vs_meetup_api_key" name="vs_meet_options[vs_meetup_api_key]" size="40" type="text" value="%s" />', esc_attr( $options['vs_meetup_api_key'] ) );
		}

		/**
		 * Sanitize and validate input
		 *
		 * @param array $input an array to sanitize.
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
} // End if().
