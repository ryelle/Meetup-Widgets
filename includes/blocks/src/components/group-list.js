/** @format */
/**
 * External Dependencies
 */
import { stringify } from 'qs';

/**
 * Internal Dependencies
 */
const runTemplate = require( TEMPLATE_DIRECTORY + '/meetup-list.hbs' );

/**
 * Core WP Dependencies
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { RichText, InspectorControls } = wp.editor;
const {
	Dashicon,
	Placeholder,
	PanelBody,
	RangeControl,
	Spinner,
	SelectControl,
	TextControl,
	ToggleControl,
	withAPIData,
} = wp.components;

class GroupListBlock extends Component {
	constructor() {
		super( ...arguments );
		this.onChangeEditable = this.onChangeEditable.bind( this );
		this.onChangeToggle = this.onChangeToggle.bind( this );
		this.renderEventsList = this.renderEventsList.bind( this );
	}

	onChangeEditable( field ) {
		return value => this.props.setAttributes( { [ field ]: value } );
	}

	onChangeToggle( field ) {
		return () =>
			this.props.setAttributes( {
				[ field ]: ! this.props.attributes[ field ],
			} );
	}

	renderEventsList() {
		const { attributes, events = {} } = this.props;
		const { isLoading, error, data = [] } = events;

		/* eslint-disable yoda */
		if ( error && error.status > 200 ) {
			let message = __( 'There was an error loading the API for this block', 'meetup-widgets' );
			if ( error.resposeJSON && error.resposeJSON.message ) {
				message = error.resposeJSON.message;
			}

			return (
				<Placeholder label={ message }>
					<Dashicon icon="warning" />
				</Placeholder>
			);
		}
		/* eslint-enable yoda */

		if ( isLoading ) {
			return (
				<Placeholder icon="editor-list" label={ __( 'Fetching Events…', 'meetup-widgets' ) }>
					<Spinner />
				</Placeholder>
			);
		}

		const vars = {
			attributes,
			events: data,
			hide_title: true, // title is editable here, so we hide it in the final template.
			show_events: !! data.length,
			show_events_description: !! data.length && attributes.show_description,
		};

		return { __html: runTemplate( vars ) };
	}

	render() {
		const { attributes, isSelected, groups: { data = [] } } = this.props;

		const groupOptions = data.map( group => ( {
			label: group.name,
			value: group.urlname,
		} ) );
		groupOptions.unshift( {
			label: __( 'Select a group…', 'meetup-widgets' ),
			value: '',
		} );

		const controls = (
			<InspectorControls key="meetup-inspector">
				<PanelBody title={ __( 'Meetup.com Settings', 'meetup-widgets' ) }>
					<SelectControl
						label={ __( 'Meetup Group', 'meetup-widgets' ) }
						value={ attributes.group }
						options={ groupOptions }
						onChange={ this.onChangeEditable( 'group' ) }
					/>
					<ToggleControl
						label={ __( 'Show description', 'meetup-widgets' ) }
						checked={ !! attributes.show_description }
						onChange={ this.onChangeToggle( 'show_description' ) }
					/>
					<RangeControl
						label={ __( 'Number of events to show', 'meetup-widgets' ) }
						value={ attributes.per_page }
						onChange={ this.onChangeEditable( 'per_page' ) }
						min={ 1 }
						max={ 15 }
					/>
					<TextControl
						label={ __( 'Text to display when there are no upcoming events', 'meetup-widgets' ) }
						value={ attributes.placeholder }
						onChange={ this.onChangeEditable( 'placeholder' ) }
					/>
				</PanelBody>
			</InspectorControls>
		);

		const list = this.renderEventsList();
		const { title } = attributes;

		return [
			controls,
			<div className="meetup-widgets" key="meetup-display">
				{ ( ( title && title.length > 0 ) || isSelected ) && (
					<RichText
						tagName="h3"
						placeholder={ __( 'Upcoming Events', 'meetup-widgets' ) }
						onChange={ this.onChangeEditable( 'title' ) }
						className="meetup-widgets-title"
						value={ title }
					/>
				) }
				{ list.__html ? <div dangerouslySetInnerHTML={ this.renderEventsList() } /> : list }
			</div>,
		];
	}
}

export default withAPIData( props => {
	const { group, per_page = 3 } = props.attributes;
	const queryString = stringify( { per_page } );
	return {
		events: group ? `/meetup/v1/events/${ group }?${ queryString }` : {},
		groups: '/meetup/v1/groups/self',
	};
} )( GroupListBlock );
