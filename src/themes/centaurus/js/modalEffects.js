/**
 * modalEffects.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
var ModalEffects = (function() {

	function init() {

		var overlay = document.querySelector( '.md-overlay' );

		[].slice.call( document.querySelectorAll( '.md-trigger' ) ).forEach( function( el, i ) {

			var modal = document.querySelector( '#' + el.getAttribute( 'data-modal' ) ),
				close = modal ? modal.querySelector( '.md-close' ) : null;

			function removeModal( hasPerspective ) {
				if (!modal) {
					return;
				}
				classie.remove( modal, 'md-show' );
				jQuery('.md-overlay').css({opacity: 0.0, visibility: "hidden"})

				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( classie.has( el, 'md-setperspective' ) );
			}

			el.addEventListener( 'click', function( ev ) {
				classie.add( modal, 'md-show' );
				overlay.removeEventListener( 'click', removeModalHandler );
				overlay.addEventListener( 'click', removeModalHandler );
				jQuery('.md-overlay').css({opacity: 1.0, visibility: "visible"})

				if( classie.has( el, 'md-setperspective' ) ) {
					setTimeout( function() {
						classie.add( document.documentElement, 'md-perspective' );
					}, 25 );
				}
			});


/*			close.addEventListener( 'click', function( ev ) {
				ev.stopPropagation();
				removeModalHandler();
			});

			Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla
			Abrir y cerrar varios modals. Se comentó lo que estaba anteriormente y a continuación la solución de la falla
*/
			//inicio
			document.addEventListener( 'click', function( ev ) {
	            if (classie.has(ev.target, "md-close")) {
	                ev.stopPropagation();
	                removeModalHandler();
	            }
        	});
			//fin

			//close on escape
			jQuery(document).keyup(function(e) {
				if (e.keyCode == 27) {
					e.stopPropagation();
					removeModalHandler();
				}
			});

		} );

	}

	init();

})();