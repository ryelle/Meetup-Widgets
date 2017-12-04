const { registerBlockType } = wp.blocks;
import singleWidgetOptions from './single';
import groupWidgetOptions from './group-list';

registerBlockType( 'meetup-widgets/single', singleWidgetOptions );
registerBlockType( 'meetup-widgets/group-list', groupWidgetOptions );
