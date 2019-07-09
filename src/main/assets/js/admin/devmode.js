/* global wpApiSettings */
( function ( $ ) {
	'use strict';


	// Simulates a successful callback from moneybutton
	$( '#wc-gateway-moneybutton-mock-webhooks-metabox .mock-button' ).on( 'click', function ( $event ) {
		$event.preventDefault();
		const $button = $( this );
		const $overlay = $( '#wc-gateway-moneybutton-mock-webhooks-metabox .wc-gateway-moneybutton-meta-box .overlay' );
		const $error = $( '#wc-gateway-moneybutton-mock-webhooks-metabox .wc-gateway-moneybutton-meta-box .error' );
		$error.html( '' );
		$overlay.addClass( 'show' );

		const endpoint = `${wpApiSettings.root }wc-gateway-moneybutton/v1/webhook`;
		const json = $button.data( 'json' );


		$.ajax( {
			url: endpoint,
			method: 'POST',
			data: JSON.stringify( json ),


		} ).done( ( /* response */ )=> {
			location.reload();
		} ).fail( ( /* response */ )=> {
			$error.html( 'Sorry. This is broken' );
			$overlay.removeClass( 'show' );
		} );

		return false;
	} );

} )( jQuery );

