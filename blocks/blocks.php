<?php
/**
 * Initialize Gutenberg block support
 *
 * @package Meetup_Widgets
 * @since 3.0.0
 */

/**
 * Add Gutenberg block JS & CSS to the editor
 */
function vs_meetup_widgets_enqueue_block_assets() {
	$dir = dirname( __FILE__ );
	$js_file = 'http://localhost:8081/blocks/build/index.js';
	$css_file = 'http://localhost:8081/blocks/build/editor.css';
	$js_version = false;
	$css_version = false;

	if ( ! WP_DEBUG ) {
		$js_file = plugins_url( 'build/index.js', __FILE__ );
		$css_file = plugins_url( 'build/editor.css', __FILE__ );
		$js_version = filemtime( "$dir/build/index.js" );
		$css_version = filemtime( "$dir/build/editor.css" );
	}

	wp_enqueue_script( 'meetup-blocks', $js_file, [ 'wp-blocks', 'wp-i18n', 'wp-element' ], $js_version );
	wp_enqueue_style( 'meetup-blocks', $css_file, [ 'wp-blocks' ], $css_version );
}
add_action( 'enqueue_block_editor_assets', 'vs_meetup_widgets_enqueue_block_assets' );
