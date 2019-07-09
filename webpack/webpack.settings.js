/* global module */

// Webpack settings exports.
module.exports = {
	entries: {
		// JS files.
		'admin': './src/main/assets/js/admin/admin.js',
		'frontend': './src/main/assets/js/frontend/frontend.js',
		'shared': './src/main/assets/js/shared/shared.js',
		'devmode': './src/main/assets/js/frontend/devmode.js',
		'admin-devmode': './src/main/assets/js/admin/devmode.js',
		// CSS files.
		'admin-style': './src/main/assets/css/admin/admin-style.scss',
		'shared-style': './src/main/assets/css/shared/shared-style.scss',
		'style': './src/main/assets/css/frontend/style.scss',
	},
	filename: {
		js: 'js/[name].js',
		css: 'css/[name].css'
	},
	paths: {
		src: {
			base: './src/main/assets/',
			css: './src/main/assets/css/',
			js: './src/main/assets/js/'
		},
		dist: {
			base: './dist/',
			clean: ['./images', './css', './js']
		},
	},
	stats: {
		// Copied from `'minimal'`.
		all: false,
		errors: true,
		maxModules: 0,
		modules: true,
		warnings: true,
		// Our additional options.
		assets: true,
		errorDetails: true,
		excludeAssets: /\.(jpe?g|png|gif|svg|woff|woff2)$/i,
		moduleTrace: true,
		performance: true
	},
	copyWebpackConfig: {
		from: '**/*.{jpg,jpeg,png,gif,svg,eot,ttf,woff,woff2}',
		to: '[path][name].[ext]'
	},
	BrowserSyncConfig: {
		host: 'localhost',
		port: 3000,
		proxy: 'http://wp-plugin-scaffold.test',
		open: false,
		files: [
			'**/*.php',
			'dist/js/**/*.js',
			'dist/css/**/*.css',
			'dist/svg/**/*.svg',
			'dist/images/**/*.{jpg,jpeg,png,gif}',
			'dist/fonts/**/*.{eot,ttf,woff,woff2,svg}'
		]
	},
	performance: {
		maxAssetSize: 100000
	},
	manifestConfig: {
		basePath: ''
	},
};
