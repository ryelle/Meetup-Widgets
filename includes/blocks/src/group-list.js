/** @format */
/**
 * Core WP Dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal Dependencies
 */
import GroupListBlock from './components/group-list';

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: __( 'Meetup.com List', 'meetup-widgets' ),
	description: __(
		'This is a list of events for a given group on Meetup.com',
		'meetup-widgets'
	),
	icon: 'groups',
	category: 'embed',
	supports: {
		anchor: true,
		html: false,
	},
	edit: GroupListBlock,
	save: () => null,
};
