<?php

function vs_meetup_widgets_enqueue_block_assets() {
	$dir = dirname( __FILE__ );
	$block_js = 'group-list/block.js';
	$editor_css = 'group-list/editor.css';
	wp_enqueue_script( 'group-list-block', plugins_url( $block_js, __FILE__ ), array(
		'wp-blocks',
		'wp-i18n',
		'wp-element',
	), filemtime( "$dir/$block_js" ) );
	wp_enqueue_style( 'group-list-block', plugins_url( $editor_css, __FILE__ ), array(
		'wp-blocks',
	), filemtime( "$dir/$editor_css" ) );
}
add_action( 'enqueue_block_editor_assets', 'vs_meetup_widgets_enqueue_block_assets' );
