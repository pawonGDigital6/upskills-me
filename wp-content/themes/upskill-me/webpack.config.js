const defaults = require( '@wordpress/scripts/config/webpack.config' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const path = require( 'path' );

const copyPreviewImage = [];

if ( process.env.COPY_PREVIEW_IMAGE === 'true' ) {
	copyPreviewImage.push(
		new CopyWebpackPlugin( {
			patterns: [
				{
					from: '**/preview.png',
					to: '[path][name][ext]',
					context: path.resolve( __dirname, 'src', 'blocks' ),
					// A block without an inserter preview is allowed.
					noErrorOnMissing: true,
				},
			],
		} )
	);
}

module.exports = {
	...defaults,
	output: {
		...defaults.output,
		// Only wipe the output folder on a production build. In watch mode on
		// Windows the clean step can hit files WAMP/PHP still holds open (EBUSY).
		clean: defaults.mode === 'production',
	},
	watchOptions: {
		...defaults.watchOptions,
		// Never treat webpack's own output as a source change; on Windows the
		// write -> watch -> rebuild cycle can otherwise loop.
		ignored: [ ...( defaults.watchOptions?.ignored || [] ), '**/build/**', '**/node_modules/**' ],
	},
	externals: {
		...defaults.externals,
		jquery: 'jQuery',
	},
	module: {
		...defaults.module,
		rules: [
			...defaults.module.rules,
			{
				// Tailwind 4 runs on the plain .css entries (src/global/tailwind).
				test: /\.css$/,
				use: [
					{
						loader: 'postcss-loader',
						options: {
							postcssOptions: {
								plugins: [ require( '@tailwindcss/postcss' ) ],
							},
						},
					},
				],
			},
		],
	},
	plugins: [ ...defaults.plugins, ...copyPreviewImage ],
};
