const {spawn} = require( 'child_process' );
const platform = require( 'os' ).platform();
const [ bin, sourcePath, ...args ] = process.argv;
const cmd = /^win/.test( platform )
    ? `${process.cwd()}\\vendor\\bin\\phpunit.bat`
    : `${process.cwd()}/vendor/bin/phpunit`;

spawn( cmd, args, {stdio: 'inherit'} ).on( 'exit', code => process.exit( code ) );
