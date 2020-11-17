/**
 * Laravel Mix configuration file.
 *
 * Laravel Mix provides a clean, fluent API for defining basic webpack build steps for your applications.
 * Mix supports several common CSS and JavaScript pre-processors.
 */

const mix = require('laravel-mix');
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );

/*
 * -----------------------------------------------------------------------------
 * Plugin Export Process
 * -----------------------------------------------------------------------------
 * Configure the export process in `webpack.mix.export.js`. This bit of code
 * should remain at the top of the file here so that it bails early when the
 * `export` command is run.
 * -----------------------------------------------------------------------------
 */

if ( process.env.export ) {
	const exportPlugin = require( './webpack.mix.export.js' );
	return;
}

/*
 * -----------------------------------------------------------------------------
 * Build Process
 * -----------------------------------------------------------------------------
 * The section below handles processing, compiling, transpiling, and combining
 * all of the plugin's assets into their final location. This is the meat of the
 * build process.
 * -----------------------------------------------------------------------------
 */

/*
 * Sets the development path to assets. By default, this is the `/resources`
 * folder in the plugin.
 */
const devPath  = 'resources';

/*
 * Sets the path to the generated assets. By default, this is the `/dist` folder
 * in the theme. If doing something custom, make sure to change this everywhere.
 */
mix.setPublicPath( 'dist' );

/*
 * Set Laravel Mix options.
 */
mix.options( {
	postCss        : [ require( 'postcss-preset-env' )() ],
	processCssUrls : false
} );

/*
 * Builds sources maps for assets.
 */
mix.sourceMaps();

/*
 * Versioning and cache busting. Append a unique hash for production assets. If
 * you only want versioned assets in production, do a conditional check for
 * `mix.inProduction()`.
 *
 * @link https://laravel.com/docs/5.6/mix#versioning-and-cache-busting
 */
mix.version();

/*
 * Compile CSS.
 */
var sassConfig = {
	outputStyle : 'expanded',
	indentType  : 'tab',
	indentWidth : 1
};

mix.react( `${devPath}/js/react/index.js`, 'js/react' );

//mix.sass( `${devPath}/scss/react-app.scss`, 'css/react' );

mix.webpackConfig(webpack => {
    return {
		stats       : 'minimal',
		devtool     : mix.inProduction() ? false : 'source-map',
		performance : { hints  : false    },
        plugins: [
            new webpack.ProvidePlugin({
                "React": "react",
            })
        ]
    };
});
