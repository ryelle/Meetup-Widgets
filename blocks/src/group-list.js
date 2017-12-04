const { __ } = wp.i18n;

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: __( 'Meetup.com List', 'meetup-widgets' ),

	icon: 'editor-ul',

	category: 'embed',

	// Remove to make block editable in HTML mode.
	supportHTML: false,

	edit: props => {
		return __( 'Group list here! 🎊', 'meetup-widgets' );
	},

	save: () => {
		return null
	}
};
