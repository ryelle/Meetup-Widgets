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
import GroupListBlock from './blocks/group-list';

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: translate( 'Meetup.com List' ),
	description: translate( 'This is a list of events for a given group on Meetup.com' ),

	icon: 'editor-ul',

	category: 'embed',

	// Remove to make block editable in HTML mode.
	supportHTML: false,

	edit: GroupListBlock,

	save: () => null,
};
