<?php

function vs_meetup_widgets_enqueue_block_assets() {
	$dir = dirname( __FILE__ );
	wp_enqueue_script( 'group-list-block', plugins_url( 'build/index.js', __FILE__ ), array(
		'wp-blocks',
		'wp-i18n',
		'wp-element',
	), filemtime( "$dir/build/index.js" ) );
	wp_enqueue_style( 'group-list-block', plugins_url( 'build/editor.css', __FILE__ ), array(
		'wp-blocks',
	), filemtime( "$dir/build/editor.css" ) );
}
add_action( 'enqueue_block_editor_assets', 'vs_meetup_widgets_enqueue_block_assets' );
