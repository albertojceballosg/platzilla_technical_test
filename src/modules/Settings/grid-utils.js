(function (jQuery) {
	// Private variables
	var modal = null;

	// Private functions

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	// Public functions

	var closeModal = function () {
		modal.modal ('hide');
	};

	var openModal = function (moduleName) {
		var modalTemplate = jQuery ('#grid-modal-template'),
			arguments = [
			'module=Settings',
			'action=AddGridTable',
			'formodule=' + encodeURIComponent (moduleName),
			'Ajax=true'
		];

		modal = jQuery (modalTemplate.html ());
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'text',
			method:   'get'
		}).done (function (response) {
			modal.find ('#camposGrid').html (response);
			var scriptTags = modal.find ('#camposGrid script');
			for (var i = 0; i < scriptTags.length; i++) {
				var scriptTag = scriptTags [ i ];
				var script = document.createElement ('script');
				script.type = scriptTag.type;
				var head = document.getElementsByTagName ('head')[ 0 ];
				if (scriptTag.src == '') {
					script.appendChild (document.createTextNode (scriptTag.innerHTML));
					head.appendChild (script);
				}
			}
		}).fail (function () {
			alert ('Se ha presentado un error inesperado. Intenta más tarde');
		});
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	window.GridUtils = {
		closeModal: closeModal,
		openModal: openModal
	};
} (jQuery));