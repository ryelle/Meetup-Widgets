( function( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
	wp.blocks.registerBlockType( 'meetup-widgets/group-list', {
		title: __( 'Meetup Widgets', 'meetup-widgets' ),

		icon: 'editor-ul',

		category: 'embed',

		// Remove to make block editable in HTML mode.
		supportHTML: false,

		edit: function( props ) {
			return el(
				'p',
				{ className: props.className },
				__( 'Replace with your content!', 'meetup-widgets' )
			);
		},

		save: function() {
			return el(
				'p',
				{},
				__( 'Replace with your content!', 'meetup-widgets' )
			);
		}
	} );
} )(
	window.wp
);
