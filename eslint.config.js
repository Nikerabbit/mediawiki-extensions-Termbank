'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

module.exports = defineConfig( [
	globalIgnores( [ 'vendor/**', 'node_modules/**' ] ),
	{
		ignores: [ 'resources/**' ],
		extends: [ wikimedia( 'server', 'mediawiki' ) ]
	},
	{
		files: [ 'resources/**' ],
		extends: [ wikimedia( 'client', 'jquery', 'mediawiki', 'jsduck' ) ],
		rules: {
			'mediawiki/no-nodelist-unsupported-methods': 'off'
		}
	}
] );
