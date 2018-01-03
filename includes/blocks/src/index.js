/** @format */

const { registerBlockType } = wp.blocks;
import singleWidgetOptions from './single';
import groupWidgetOptions from './group-list';

import './style.css';

registerBlockType( 'meetup-widgets/single', singleWidgetOptions );
registerBlockType( 'meetup-widgets/group-list', groupWidgetOptions );
