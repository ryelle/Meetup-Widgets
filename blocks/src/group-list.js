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
import { GroupListBlock } from './blocks/group-list';

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: translate( 'Meetup.com List' ),
	description: translate( 'This is a list of events for a given group on Meetup.com' ),

	icon: 'editor-ul',

	category: 'embed',

	// Remove to make block editable in HTML mode.
	supportHTML: false,

	edit: ({ attributes, setAttributes, focus, setFocus }) => {
		// Inject default attributes
		attributes = { ...defaultAttributes, ...attributes };
		getMeetupEvents(attributes);

		const onChangeEditable = field => value =>
			setAttributes({ [field]: value });

		const onFocus = field => focus =>
			setFocus({ ...focus, editable: field });

		const focusedEditable = focus ? focus.editable || 'title' : null;

		const controls = focus && (
			<InspectorControls key="meetup-inspector">
				<TextControl
					label={translate('Group Name')}
					value={attributes.group}
					onChange={onChangeEditable('group')}
				/>
				<RangeControl
					label={translate('Number of event to show')}
					value={attributes.limit}
					onChange={onChangeEditable('limit')}
					min={2}
					max={15}
				/>
			</InspectorControls>
		);

		return [
			controls,
			<div className="meetup-widgets" key="meetup-display">
				<Editable
					tagName="h3"
					placeholder={translate('Upcoming Events')}
					onChange={onChangeEditable('title')}
					focus={focusedEditable === 'title'}
					onFocus={onFocus('title')}
					className="meetup-widgets-title"
					value={attributes.title}
				/>
				<ul className="meetup-widgets-list">
					{attributes.formattedEvents}
				</ul>
			</div>,
		];
	},

	save: ( { attributes } ) => {
		return (
			<div className="meetup-widgets">
				<h3>{ attributes.title }</h3>
				<ul className="meetup-widgets-list">{ attributes.formattedEvents }</ul>
			</div>
		);
	},
};
