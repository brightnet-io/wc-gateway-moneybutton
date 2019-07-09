/* global WoocommerceMoneyButtonGateway */
( function ( $ ) {
	'use strict';
	/**
	 * Public javascript that add the click handlers to the callback buttons used in dev mode.
	 * This file should only be enqued if gateway is in dev mode
	 */


	WoocommerceMoneyButtonGateway.randomHash = function ( nChar ) {
		const nBytes = Math.ceil( nChar = ( +nChar || 8 ) / 2 );
		const u = new Uint8Array( nBytes );
		window.crypto.getRandomValues( u );
		// eslint-disable-next-line require-jsdoc
		const zpad = str => '00'.slice( str.length ) + str;
		const a = Array.prototype.map.call( u, x => zpad( x.toString( 16 ) ) );
		let str = a.join( '' ).toUpperCase();
		if ( nChar % 2 ) str = str.slice( 1 );
		return str;
	};

	// Simulates a successful callback from moneybutton
	$( '#wooGatewayMoneyButtonPaymentCallbackBtn' ).on( 'click', function () {
		const moneyButton = $( '.money-button' );
		const paymentId = Math.random().toString( 36 ).substring( 2, 15 ) + Math.random().toString( 36 ).substring( 2, 15 );
		const now = new Date().toISOString();

		const payment = {
			id: paymentId,
			'createdAt': now,
			'updatedAt': now,
			txid: WoocommerceMoneyButtonGateway.randomHash( 64 ),
			normalizedTxid: WoocommerceMoneyButtonGateway.randomHash( 64 ),
			amount: moneyButton.data( 'amount' ),
			currency: moneyButton.data( 'currency' ),
			satoshis: '18943',
			status: 'RECEIVED', // just assuming this is the success response
			buttonId: moneyButton.data( 'button-id' ),
			buttonData: JSON.stringify( moneyButton.data( 'button-data' ) ),
			paymentOutputs: [
				{
					'id': '322757',
					'createdAt': now,
					'updatedAt': now,
					'paymentId': paymentId,
					'to': '3658',
					'amount': moneyButton.data( 'amount' ),
					'currency': moneyButton.data( 'currency' ),
					'satoshis': '55555',
					'type': 'USER', // Note that 'TO' is hardcardod in the dev mode response
					'userId': 'XXXXX',
					'address': null,
					'amountUsd': '0.02062489300836752'
				}
			]
		};

		console.log( 'on payment', payment ); // eslint-disable-line no-console
		WoocommerceMoneyButtonGateway.wooMoneyButtonOnPayment( payment );
	} );

	// Simulates an error callback from moneybutton
	$( '#wooGatewayMoneyButtonErrorCallbackBtn' ).on( 'click', function () {
		WoocommerceMoneyButtonGateway.wooMoneyButtonOnError( new Error( 'An error message bought to you by Money Button' ) );
	} );

} )( jQuery );

