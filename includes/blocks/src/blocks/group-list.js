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
const {
	Editable,
	InspectorControls,
	InspectorControls: { RangeControl, SelectControl },
} = wp.blocks;
const { Placeholder, Spinner, withAPIData } = wp.components;

class GroupListBlock extends Component {
	constructor() {
		super( ...arguments );
		this.onChangeEditable = this.onChangeEditable.bind( this );
		this.onFocus = this.onFocus.bind( this );
		this.renderEventsList = this.renderEventsList.bind( this );
	}

	onChangeEditable( field ) {
		return value => this.props.setAttributes( { [ field ]: value } );
	}

	onFocus( field ) {
		return focus => this.props.setFocus( { ...focus, editable: field } );
	}

	renderEventsList() {
		const { attributes, focus, events = {} } = this.props;
		const { isLoading, data = [] } = events;
		const focusedEditable = focus ? focus.editable || 'title' : null;

		console.log( 'events', isLoading, data );
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
		if ( ! data.length ) {
			return (
				<Editable
					tagName="p"
					onChange={ this.onChangeEditable( 'placeholder' ) }
					focus={ 'placeholder' === focusedEditable }
					onFocus={ this.onFocus( 'placeholder' ) }
					className="meetup-widgets-placeholder"
					value={ attributes.placeholder }
				/>
			);
		}

		const renderEvent = item => {
			return (
				<li key={ item.id }>
					{ item.name } { item.date }
				</li>
			);
		};

		return <ul className="meetup-widgets-list">{ data.map( renderEvent ) }</ul>;
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
					label={ translate( 'Group Name' ) }
					value={ attributes.group }
					options={ groupOptions }
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
	const { group, per_page = 3 } = props.attributes;
	const queryString = stringify( { per_page } );
	return {
		events: group ? `/meetup/v1/events/${ group }?${ queryString }` : {},
		groups: '/meetup/v1/groups/self',
	};
} )( GroupListBlock );
