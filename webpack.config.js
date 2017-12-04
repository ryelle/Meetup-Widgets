const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

// CSS loader for styles specific to blocks in general.
const blocksCSSPlugin = new ExtractTextPlugin({
	filename: './blocks/build/style.css',
});

// Configuration for the ExtractTextPlugin.
const extractConfig = {
	use: [
		{ loader: 'raw-loader' },
		{
			loader: 'postcss-loader',
			options: {
				plugins: [require('autoprefixer')],
			},
		},
	],
};

module.exports = {
	entry: __dirname + '/blocks/src/index.js',
	output: {
		filename: 'blocks/build/index.js',
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
				test: /style\.css$/,
				include: [/blocks/],
				use: blocksCSSPlugin.extract(extractConfig),
			},
		],
	},
	plugins: [
		new webpack.DefinePlugin({
			'process.env.NODE_ENV': JSON.stringify(
				process.env.NODE_ENV || 'development',
			),
		}),
		blocksCSSPlugin,
	],
};