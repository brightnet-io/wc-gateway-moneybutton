/* global require */
const fs = require( 'fs-extra' );
const archiver = require( 'archiver' );
const cwd = process.cwd();
const pluginName = 'wc-gateway-moneybutton';
const buildDir = `${cwd}/target`;

async function copyPhp() {

	await fs.ensureDir( `${buildDir}/${pluginName}` );
	await fs.copy( `${cwd}/src/main/php`, `${buildDir}/${pluginName}` );


}

async function copyAssets() {

	await fs.ensureDir( `${buildDir}/${pluginName}/dist` );
	await fs.copy( `${cwd}/dist`, `${buildDir}/${pluginName}/dist` );

}

async function zip() {
	const output = fs.createWriteStream( `${buildDir}/wc-gateway-moneybutton.zip` );
	const archive = archiver( 'zip', {zlib: {level: 9}} );

	archive.on( 'warning', function ( err ) {
		if ( 'ENOENT' === err.code ) {
			console.warn( err );
		} else {
			// throw error
			throw err;
		}
	} );

	// good practice to catch this error explicitly
	archive.on( 'error', function ( err ) {
		throw err;
	} );

	archive.pipe( output );
	archive.directory( `${buildDir}/${pluginName}`, pluginName );
	archive.finalize();

}

async function makePackage() {
	try {
		await fs.emptyDir( buildDir );
		await Promise.all( [copyPhp(), copyAssets()] );
		await zip();
	} catch ( err ) {
		console.error( err );
		process.exit( 1 );
	}
}

makePackage();

