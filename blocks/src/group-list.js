const { __ } = wp.i18n;
const {
	BlockDescription,
	InspectorControls,
	InspectorControls: { TextControl },
} = wp.blocks;

const translate = str => __(str, 'meetup-widgets');

// Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
export default {
	title: translate('Meetup.com List'),

	icon: 'editor-ul',

	category: 'embed',

	// Remove to make block editable in HTML mode.
	supportHTML: false,

	edit: ({ attributes, setAttributes, focus, setFocus }) => {
		console.log(attributes);
		return [
			<div className="">
				<h2>{translate('Text!')}</h2>
			</div>,
			!!focus && (
				<InspectorControls key="inspector">
					<BlockDescription>
						<h3>{translate('Meetup.com ?')}</h3>
					</BlockDescription>
					<TextControl
						label={translate('Group')}
						value={attributes.test}
						onChange={value => setAttributes({ test: value })}
					/>
				</InspectorControls>
			),
		];
	},

	save: () => {
		return null;
	},
};
