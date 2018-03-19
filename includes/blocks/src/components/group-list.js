/**
 * External Dependencies
 *
 * @format
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
const translate = str => __( str, 'meetup-widgets' );
const { Component } = wp.element;
const {
	RichText,
	InspectorControls,
	InspectorControls: { RangeControl, SelectControl, TextControl, ToggleControl },
} = wp.blocks;
const { Dashicon, Placeholder, Spinner, withAPIData } = wp.components;

class GroupListBlock extends Component {
	constructor() {
		super( ...arguments );
		this.onChangeEditable = this.onChangeEditable.bind( this );
		this.onChangeToggle = this.onChangeToggle.bind( this );
		this.onFocus = this.onFocus.bind( this );
		this.renderEventsList = this.renderEventsList.bind( this );
	}

	onChangeEditable( field ) {
		return value => this.props.setAttributes( { [ field ]: value } );
	}

	onChangeToggle( field ) {
		return () => this.props.setAttributes( { [ field ]: ! this.props.attributes[ field ] } );
	}

	onFocus( field ) {
		return focus => this.props.setFocus( { ...focus, editable: field } );
	}

	renderEventsList() {
		const { attributes, events = {} } = this.props;
		const { isLoading, error, data = [] } = events;

		/* eslint-disable yoda */
		if ( error && error.status > 200 ) {
			let message = translate( 'There was an error loading the API for this block' );
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
				<Placeholder icon="editor-list" label={ translate( 'Fetching Events…' ) }>
					<Spinner />
				</Placeholder>
			);
		}

		const vars = {
			attributes,
			events: data,
			hide_title: true, // title is <Editable /> here, so we hide it in the final template.
			show_events: !! data.length,
			show_events_description: !! data.length && attributes.show_description,
		};

		return { __html: runTemplate( vars ) };
	}

	render() {
		const { attributes, focus, groups: { data = [] } } = this.props;
		const focusedEditable = focus ? focus.editable || 'title' : null;

		const groupOptions = data.map( group => ( {
			label: group.name,
			value: group.urlname,
		} ) );
		groupOptions.unshift( {
			label: translate( 'Select a group…' ),
			value: '',
		} );

		const controls = focus && (
			<InspectorControls key="meetup-inspector">
				<SelectControl
					label={ translate( 'Meetup Group' ) }
					value={ attributes.group }
					options={ groupOptions }
					onChange={ this.onChangeEditable( 'group' ) }
				/>
				<ToggleControl
					label={ translate( 'Show description' ) }
					checked={ !! attributes.show_description }
					onChange={ this.onChangeToggle( 'show_description' ) }
				/>
				<RangeControl
					label={ translate( 'Number of events to show' ) }
					value={ attributes.per_page }
					onChange={ this.onChangeEditable( 'per_page' ) }
					min={ 2 }
					max={ 15 }
				/>
				<TextControl
					label={ translate( 'Text to display when there are no upcoming events' ) }
					value={ attributes.placeholder }
					onChange={ this.onChangeEditable( 'placeholder' ) }
				/>
			</InspectorControls>
		);

		const list = this.renderEventsList();

		return [
			controls,
			<div className="meetup-widgets" key="meetup-display">
				<RichText
					tagName="h3"
					placeholder={ translate( 'Upcoming Events' ) }
					onChange={ this.onChangeEditable( 'title' ) }
					focus={ 'title' === focusedEditable }
					onFocus={ this.onFocus( 'title' ) }
					className="meetup-widgets-title"
					value={ attributes.title }
				/>
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
