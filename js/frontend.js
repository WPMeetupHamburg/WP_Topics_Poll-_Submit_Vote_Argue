/**
 * Feature Name:	jQuery Class
 */

( function( $ ) {
	var wpsf_frontend = {
		init : function () {
			
			// Bind buttons
			$( '.btn' ).live( 'click', function() {
				
				// Check if button is active
				var button = $( this );
				
				if ( button.parent( 'ul' ).parent( '.vote' ).hasClass( 'inactive' ) ) {
					return false;
				} else {
					
					// Fetch the data
					var post_vars = {
						action: 'rate_topic',
						topic: $( '.vote' ).attr( 'topicid' ),
						rate_type: button.parent( 'ul' ).attr( 'class' )
					};
					
					$.post( ajaxurl, post_vars, function( response ) {
						if ( response == 0 ) {
							alert( 'You already voted this topic!' );
							return false;
						} else if ( response == 1 ) {
							alert( 'This topic is closed!' );
							return false;
						} else {
							
							// Update counter
							var count = parseInt( $( '.' + button.parent( 'ul' ).attr( 'class' ) ).children( 'li' ).children( '.cnt' ).html() );
							count = count + 1;
							$( '.' + button.parent( 'ul' ).attr( 'class' ) ).children( 'li' ).children( '.cnt' ).html( count )
							
							// Output
							button.parent( 'ul' ).append( response );
						}
					} );
					
					// Disable the buttons
					$( '.vote' ).addClass( 'inactive' );
				}
				
				return false;
			} );
		},
	};
	$( document ).ready( function( $ ) {
		wpsf_frontend.init();
	} );
} )( jQuery );