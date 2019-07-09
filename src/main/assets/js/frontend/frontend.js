// import foo from './components/bar';
// import foo from './components/bar';
/* global WoocommerceMoneyButtonGateway */
( function ( $ ) {
	'use strict';

	$.extend( WoocommerceMoneyButtonGateway, {
		/**
         * On Payment  (success) callback function for Money Button
         * @param payment
         */
		wooMoneyButtonOnPayment: function ( payment ) {
			// Clear the errors
			const errorEl = $( '#wc-gateway-moneybutton-error' );
			errorEl.removeClass( 'has-error' );
			const errorMessageEl = $( '#wc-gateway-moneybutton-error-message' );
			errorMessageEl.html( '' );


			const paymentForm = $( '#wc-gateway-moneybutton-paymentform' );

			paymentForm.append( `<input type="hidden" name="wc-gateway-moneybutton-payment_id" value="${  payment.id  }"/>` );
			const buttonData = JSON.parse( payment.buttonData );
			paymentForm.append( `<input type="hidden" name="wc-gateway-moneybutton-order_key" value="${  buttonData.order_key  }"/>` );
			paymentForm.append( `<input type="hidden" name="wc-gateway-moneybutton-order_id" value="${  buttonData.order_id  }"/>` );
			paymentForm.append( `<input type="hidden" name="wc-gateway-moneybutton-cart_hash" value="${  buttonData.cart_hash }"/>` );

			setTimeout( ()=>{
				// Delay to allow money button to finish it's own success render
				paymentForm.submit();
			},1000 );

		},
		/**
         * On error call back function for Money Button
         * @param error
         */
		wooMoneyButtonOnError: function ( error ) {
			const errorEl = $( '#wc-gateway-moneybutton-error' );
			const errorMessageEl = $( '#wc-gateway-moneybutton-error-message' );
			errorEl.addClass( 'has-error' );
			errorMessageEl.html( error );

		}

	} );

	/**
     * Global scoped onPayment (success) callback function for money button.
     *
     * Money Button requires functions in the global scope
     * @param payment
     */
	window.wooMoneyButtonOnPayment = function ( payment ) {
		WoocommerceMoneyButtonGateway.wooMoneyButtonOnPayment( payment );
	};

	/**
     * Global scoped onError callback for money button.
     *
     * Money Button requires functions in the global scope
     *
     * @param error
     */
	window.wooMoneyButtonOnError = function ( error ) { // eslint-disable-line no-unused-vars
		WoocommerceMoneyButtonGateway.wooMoneyButtonOnError( error );
	};
} )( jQuery );




