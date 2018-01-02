<?php
/**
 * Plugin Name: Meetup Widgets
 * Description: Add widgets displaying information from Meetup.com
 * Version: 2.2.1
 * Author: Kelly Dwan
 * Author URI: http://redradar.net
 * Plugin URI: http://redradar.net/category/plugins/meetup-widgets/
 * License: GPL2
 * Date: 01.06.2016
 *
 * @package Meetup_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'VSMEET_TEMPLATE_DIR' ) ) {
	define( 'VSMEET_TEMPLATE_DIR', dirname( __FILE__ ) . '/templates/' );
}

require_once( 'class-meetup-widgets-admin.php' );
require_once( 'class-meetup-widget.php' );
require_once( 'class-meetup-api-v3.php' );
require_once( 'api/class-meetup-rest-events-controller.php' );
require_once( 'api/class-meetup-rest-groups-controller.php' );

/**
 * Initialize Meetup Widgets
 */
function meetup_widgets_start() {
	new Meetup_Widgets_Admin();
	$event_controller = new Meetup_REST_Events_Controller();
	$event_controller->register_routes();

	$group_controller = new Meetup_REST_Groups_Controller();
	$group_controller->register_routes();

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

require_once( 'blocks/blocks.php' );
