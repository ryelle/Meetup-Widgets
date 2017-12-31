/** @format */

const { __ } = wp.i18n;

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: __( 'Meetup.com Event', 'meetup-widgets' ),

	icon: 'groups',

	category: 'embed',

	// Remove to make block editable in HTML mode.
	supportHTML: false,

	edit: props => {
		return __( 'Single event here! 🎊', 'meetup-widgets' );
	},

	save: () => {
		return null;
	},
};
