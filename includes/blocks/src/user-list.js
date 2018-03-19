/** @format */
/**
 * Core WP Dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal Dependencies
 */
import UserListBlock from './components/user-list';

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: __( 'Meetup.com User List', 'meetup-widgets' ),
	description: __(
		'This is a list of the upcoming events on Meetup.com for the user that created the API key',
		'meetup-widgets'
	),
	icon: 'groups',
	category: 'embed',
	supports: {
		anchor: true,
		html: false,
	},
	edit: UserListBlock,
	save: () => null,
};
