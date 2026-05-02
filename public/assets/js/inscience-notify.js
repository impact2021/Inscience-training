/* global inscienceNotify */
(function ( $ ) {
	'use strict';

	$( '#inscience-notify-form' ).on( 'submit', function ( e ) {
		e.preventDefault();

		var $form = $( this );
		var $btn  = $( '#inscience-notify-submit' );

		$btn.find( '.inscience-btn-text' ).hide();
		$btn.find( '.inscience-btn-loading' ).show();
		$btn.prop( 'disabled', true );
		$( '#inscience-notify-error' ).hide();

		var data = $form.serializeArray();
		data.push( { name: 'action', value: 'inscience_notify_signup' } );
		data.push( { name: 'nonce',  value: $form.find( '[name="inscience_notify_nonce"]' ).val() } );

		$.post( inscienceNotify.ajaxurl, $.param( data ), function ( response ) {
			if ( response.success ) {
				$form.html(
					'<div class="inscience-notice inscience-success">' +
					inscienceNotify.successMessage +
					'</div>'
				);
			} else {
				$( '#inscience-notify-error' ).text( response.data.message ).show();
				$btn.find( '.inscience-btn-text' ).show();
				$btn.find( '.inscience-btn-loading' ).hide();
				$btn.prop( 'disabled', false );
			}
		} ).fail( function () {
			$( '#inscience-notify-error' ).text( inscienceNotify.errorMessage ).show();
			$btn.find( '.inscience-btn-text' ).show();
			$btn.find( '.inscience-btn-loading' ).hide();
			$btn.prop( 'disabled', false );
		} );
	} );
} )( jQuery );
