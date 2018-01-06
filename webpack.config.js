/** @format */

const webpack = require( 'webpack' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );
const path = require( 'path' );

// CSS loader for styles specific to blocks in general.
const blocksCSSPlugin = new ExtractTextPlugin( {
	filename: './build/editor.css',
} );

// Configuration for the ExtractTextPlugin.
const extractConfig = {
	use: [
		{ loader: 'raw-loader' },
		{
			loader: 'postcss-loader',
			options: {
				plugins: [ require( 'autoprefixer' ) ],
			},
		},
	],
};

module.exports = {
	entry: __dirname + '/includes/blocks/src/index.js',
	output: {
		filename: 'build/index.js',
		path: __dirname,
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: 'babel-loader',
			},
			{
				test: /\.pug$/,
				exclude: /node_modules/,
				use: 'pug-loader',
			},
			{
				test: /style\.css$/,
				include: [ /blocks/ ],
				use: blocksCSSPlugin.extract( extractConfig ),
			},
		],
	},
	plugins: [
		new webpack.DefinePlugin( {
			'process.env.NODE_ENV': JSON.stringify( process.env.NODE_ENV || 'development' ),
			'TEMPLATE_DIRECTORY': JSON.stringify( __dirname + '/includes/templates' ),
		} ),
		blocksCSSPlugin,
	],
	devServer: {
		port: 8081,
	},
};
