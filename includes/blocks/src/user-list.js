/**
 * Core WP Dependencies
 *
 * @format
 */
const { __ } = wp.i18n;
const translate = str => __( str, 'meetup-widgets' );

/**
 * Internal Dependencies
 */
import UserListBlock from './components/user-list';

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: translate( 'Meetup.com User List' ),
	description: translate(
		'This is a list of the upcoming events on Meetup.com for the user that created the API key'
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
