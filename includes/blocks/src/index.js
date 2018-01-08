/** @format */

const { registerBlockType } = wp.blocks;
import userListOptions from './user-list';
import groupListOptions from './group-list';

import './style.css';

registerBlockType( 'meetup-widgets/user-list', userListOptions );
registerBlockType( 'meetup-widgets/group-list', groupListOptions );
