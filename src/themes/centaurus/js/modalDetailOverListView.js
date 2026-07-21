var ModalEffects = (function() {

	function getModalContent (obj, modal, idModal) {
		// Asegurar que obj sea el enlace <a> con los atributos (podría ser el icono <i> interno)
		var linkObj = obj.is('a') ? obj : obj.closest('a[data-modal]');
		if (linkObj.length === 0) {
			linkObj = obj.parent(); // fallback
		}

		var url         = (linkObj.attr ('href') !== undefined) ? linkObj.attr ('href') : linkObj.parent ().attr ('href'),
			modalTitle  = (linkObj.attr ('modal-title') !== undefined) ? linkObj.attr ('modal-title') : linkObj.parent ().attr ('modal-title'),
			modalBody   = jQuery (modal).find ('.modal-body'),
			modalStatus = jQuery ('#' + idModal);

		// Detectar si es vista especial de orden_de_trabajo
		var isSpecialWorkView = linkObj.hasClass ('sjv-special-preview') || linkObj.attr ('data-module') === 'orden_de_trabajo';
		var recordId = linkObj.attr ('data-record');

		// Agregar/quitar clase especial al modal para estilos CSS específicos
		if (isSpecialWorkView) {
			jQuery(modal).addClass('md-modal-special-job');
		} else {
			jQuery(modal).removeClass('md-modal-special-job');
		}

		jQuery(modal).find('.modal-title').html(modalTitle);
        modalBody.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block" style="max-width:75%; height:auto; display:block; margin:auto;"/>');
        modalStatus.attr ('data-status', '1');

		// Construir URL según el tipo de vista
		if (isSpecialWorkView && recordId) {
			url = 'index.php?module=orden_de_trabajo&action=SpecialJobView&record=' + recordId + '&Ajax=true&view_source=listview';
		} else {
			url += '&Ajax=true';
		}

        jQuery.get (url, function (data) {
            try {
                if((data !== '') && data !== undefined) {
                	if (modalStatus.attr('data-status') === '1') {
                        modalBody.html (data);
					} else {
                        modalBody.html ('');
					}
                } else {
                    modalBody.html ('<h2>Información no encontrada!</h2>');
				}
            }
            catch (e) {
                alert(e);
                modalBody.html ('');
            }
        }).fail(function() {
            modalBody.html ('<h2>Error al cargar la información</h2>');
        }).always(function() {
            // Asegurar que NProgress se oculte después de cargar el modal
            if (typeof NProgress !== 'undefined') {
                NProgress.done();
            }
        });

	};
	function init() {
		var overlay = document.querySelector( '.md-overlay' ),
			idModal = '';

		[].slice.call( document.querySelectorAll( '.md-trigger' ) ).forEach( function( el, i ) {

			var modal   = document.querySelector( '#' + el.getAttribute( 'data-modal' ) ),
				close   = modal ? modal.querySelector( '.md-close' ) : null,
                idModal = jQuery (modal).find ('.modal-body').attr ('id');

			function removeModal( hasPerspective ) {
				if (!modal) {
					return;
				}

				classie.remove( modal, 'md-show' );
				jQuery('.md-overlay').css({opacity: 0.0, visibility: "hidden"});
                jQuery (modal).find ('.modal-body').empty();
				if( hasPerspective ) {
					classie.remove( document.documentElement, 'md-perspective' );
				}
			}

			function removeModalHandler() {
				removeModal( classie.has( el, 'md-setperspective' ) );
			}

            el.addEventListener( 'click', function( ev ) {
                var target = jQuery (ev.target);
                classie.add( modal, 'md-show' );
                overlay.removeEventListener( 'click', removeModalHandler );
                overlay.addEventListener( 'click', removeModalHandler );
                jQuery ('.md-overlay').css ({opacity: 1.0, visibility: "visible"});
                jQuery (modal).find ('.modal-title').html (target.html());
                if( classie.has( el, 'md-setperspective' ) ) {
                    setTimeout( function() {
                        classie.add( document.documentElement, 'md-perspective' );
                    }, 25 );
                }
                getModalContent (target, modal, idModal);
                ev.preventDefault();
                ev.stopPropagation();
            });

            // Corregido: usar jQuery(modal) para .on()
            jQuery(modal).on('hide.bs.modal', function(){
                jQuery (modal).find ('.modal-body').empty();
            });
			document.addEventListener( 'click', function( ev ) {
	            if (classie.has(ev.target, "md-close")) {
					jQuery ('#' + idModal).attr('data-status', '0');
                    ev.preventDefault();
	                ev.stopPropagation();
	                removeModalHandler();
	            }
        	});

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