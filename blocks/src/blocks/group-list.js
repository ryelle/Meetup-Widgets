/**
 * External Dependencies
 *
 * @format
 */
import { stringify } from 'qs';

/**
 * Core WP Dependencies
 */
const { __ } = wp.i18n;
const translate = str => __( str, 'meetup-widgets' );
const { Component } = wp.element;
const { Editable, InspectorControls, InspectorControls: { RangeControl, TextControl } } = wp.blocks;
const { Placeholder, Spinner, withAPIData } = wp.components;

class GroupListBlock extends Component {
	constructor() {
		super( ...arguments );
		this.onChangeEditable = this.onChangeEditable.bind( this );
		this.onFocus = this.onFocus.bind( this );
	}

	onChangeEditable( field ) {
		return value => this.props.setAttributes( { [ field ]: value } );
	}

	onFocus( field ) {
		return focus => this.props.setFocus( { ...focus, editable: field } );
	}

	renderEventsList() {
		const { isLoading, data = [] } = this.props.events || {};
		console.log( isLoading, data );
		if ( isLoading ) {
			return (
				<Placeholder icon="editor-list" label={ translate( 'Fetching Events…' ) }>
					<Spinner />
				</Placeholder>
			);
		}
		if ( data.code ) {
			return <p>{ data.message }</p>;
		}
		return (
			<ul className="meetup-widgets-list">
				{ data.map( item => (
					<li key={ item.id }>
						{ item.name } { item.date }
					</li>
				) ) }
			</ul>
		);
	}

	render() {
		const { attributes, focus } = this.props;
		const focusedEditable = focus ? focus.editable || 'title' : null;

		const controls = focus && (
			<InspectorControls key="meetup-inspector">
				<TextControl
					label={ translate( 'Group Name' ) }
					value={ attributes.group }
					onChange={ this.onChangeEditable( 'group' ) }
				/>
				<RangeControl
					label={ translate( 'Number of event to show' ) }
					value={ attributes.per_page }
					onChange={ this.onChangeEditable( 'per_page' ) }
					min={ 2 }
					max={ 15 }
				/>
			</InspectorControls>
		);

		return [
			controls,
			<div className="meetup-widgets" key="meetup-display">
				<Editable
					tagName="h3"
					placeholder={ translate( 'Upcoming Events' ) }
					onChange={ this.onChangeEditable( 'title' ) }
					focus={ 'title' === focusedEditable }
					onFocus={ this.onFocus( 'title' ) }
					className="meetup-widgets-title"
					value={ attributes.title }
				/>
				{ this.renderEventsList() }
			</div>,
		];
	}
}

export default withAPIData( props => {
	const { group, per_page } = props.attributes;
	if ( ! group ) {
		return {};
	}
	const queryString = stringify( { per_page } );
	return {
		events: `/meetup/v1/events/${ group }?${ queryString }`,
	};
} )( GroupListBlock );
