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
import GroupListBlock from './components/group-list';

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: translate( 'Meetup.com List' ),
	description: translate( 'This is a list of events for a given group on Meetup.com' ),
	icon: 'groups',
	category: 'embed',
	supports: {
		anchor: true,
		html: false,
	},
	edit: GroupListBlock,
	save: () => null,
};
