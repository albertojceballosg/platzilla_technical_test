(function (jQuery) {
	// variables
	var parleyTab       = jQuery ('#tab-conversations'),
		inputSearch     = jQuery ("#search_contact"),
		userListGroup   = jQuery ('.list-group-item'),
		shareDisplay    = jQuery ('#shareDisplay'),
		shareRegister   = jQuery ('#shareReg'),
		shareType       = jQuery ('#typeShare'),
		tabUsers        = jQuery ('#tab-user'),
		scrollUsers     = jQuery ('#users-info'),
		myFirstUser     = tabUsers.find ('a').eq (0),
		userId          = jQuery ('#chatUserId').val (),
		totalActiveUser = parseInt (jQuery ('#totalActiveUser').val ()),
		lastSelected    = '',
		lastUsers       = [];
	parleyTab.find ('#srcImag').val (jQuery (".profile-dropdown .dropdown-toggle img").attr ("src"));

	// Private methods
	// Verificar si typeaheadSource está disponible antes de inicializar
	if (typeof typeaheadSource !== 'undefined' && typeaheadSource.length > 0) {
		inputSearch.typeahead ({
			source:        typeaheadSource,
			triggerLength: 1,
			hint:          true,
			displayField:  'name',
			valueField:    'id',
			scrollBar:     true,
			autoSelect:    false,
			matcher:       function (item) {
				if (item.toLowerCase ().indexOf (this.query.trim ().toLowerCase ()) != -1) {
					return true;
				}
				if (item.toUpperCase ().indexOf (this.query.trim ().toUpperCase ()) != -1) {
					return true;
				}
				var query = this.query.replace (/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
				query = query.replace (/a/ig, '[a\341\301\340\300\342\302\344\304]');
				query = query.replace (/e/ig, '[e\351\311\350\310\352\312\353\313]');
				query = query.replace (/i/ig, '[i\355\315\354\314\356\316\357\317]');
				query = query.replace (/o/ig, '[o\363\323\362\322\364\324\366\326]');
				query = query.replace (/u/ig, '[u\372\332\371\331\373\333\374\334]');
				query = query.replace (/c/ig, '[c\347\307]');
				if (item.toLowerCase ().match (query.toLowerCase ())) {
					return true;
				}
			},

			sorter:   function (items) {
				arr_items = items.sort (function (a, b) {
					var query = inputSearch.val (),
						j     = 0,
						k     = 0,
						reg   = new RegExp (eval ("/^" + query + "/i"));
					if (a[ 'name' ].match (reg)) {
						j = 1;
					}
					if (b[ 'name' ].match (reg)) {
						k = 1;
					}
					if (j == k) {
						return 0;
					}
					if (j > k) {
						return -1;
					} else {
						return 1;
					}
				});
				if (arr_items.length > 0) {
					lastIten = arr_items [ 0 ][ 'name' ];
					lastVal = arr_items [ 0 ][ 'id' ];
					sel = arr_items [ 0 ][ 'name' ];
					return arr_items;
				}
			},
			onSelect: function (item) {
				var myUser;
				if (item.value != undefined) {
					lastSelected = item.text;
					userListGroup.removeClass ('active');
					shareDisplay.val (item.text);
					shareRegister.val (item.value);
					myUser = tabUsers.find ("a[rel='" + item.value + "']");
					shareType.val (myUser.attr ('data-user-type'));
					myUser.parent ().addClass ('active').removeClass ('hide').focus ();
					scrollUsers.scrollTop (myUser.parent ().position ().top - myFirstUser.parent ().position ().top);
					myUser.parent ().focus ();
					messageThread (item.value);
				} else {
					shareRegister.val (0);
					shareType.val ('');
					shareDisplay.val ('');
				}
			}
		});

		inputSearch.click (function (event) {
			jQuery (this).val ('');
		});
	} else {
		// Si no hay typeaheadSource, deshabilitar el search y mostrar mensaje
		inputSearch.attr('placeholder', 'Búsqueda no disponible').prop('disabled', true);
	}

	// general object jquery
	// public methods
	var messageThread = function (userSelected) {
		var parleyChats = jQuery ('#parley_chats'),
			activeChat  = userId + '-' + userSelected,
			thisDiv;

		parleyChats.children ().each (function (i, element) {
			thisDiv = jQuery (element);
			if (thisDiv.hasClass ('conversation-item')) {
				if (thisDiv.attr ('data-user-id') === activeChat) {
					thisDiv.removeClass ('hide');
				} else {
					thisDiv.addClass ('hide');
				}
			}

		});
	};

	var selectEmails = function (listItemElement) {
		var listItem = jQuery (listItemElement),
			instantMessagesSection = jQuery ('#parley_chats');

		instantMessagesSection.find ('.conversation-item').addClass ('hide');
		userListGroup.removeClass ('active');
		listItem.closest ('.list-group-item').addClass ('active');
		jQuery ('#conversation-new-message').hide ();
		jQuery ('#instant-messages-section').hide ();
		jQuery ('#emails-conversation').show ();
	};

	var selectUser = function (obj) {
		var selectedUser     = jQuery (obj),
			selectedContent  = selectedUser.parent ().find ('h5').html (),
			selectedUserName = selectedContent.split ('<span')[ 0 ];

		jQuery ('#related-emails-link').removeClass ('active');
		jQuery ('#emails-conversation').hide ();

		userListGroup.removeClass ('active');
		shareDisplay.val (selectedUserName);
		shareRegister.val (selectedUser.attr ('rel'));
		shareType.val (selectedUser.attr ('data-user-type'));
		selectedUser.parent ().addClass ('active');
		jQuery ('#instant-messages-section').show ();
		jQuery ('#conversation-new-message').show ();
		messageThread (shareRegister.val ())
	};

	var sendMessage = function () {
		var date     = new Date (),
			message  = parleyTab.find ("#comment").val (),
			name     = parleyTab.find ('#chatName').val (),
			question = '',
			response = '',
			src      = parleyTab.find ('#srcImag').val ();

		if (shareRegister.val () == 0 ||
			shareType.val () == '' ||
			shareDisplay.val () == ''
		) {
			jQuery ('#message-help-block').html ('Selecciona un usaurio, cliente o contacto');
			return false;
		} else if (message == '') {
			jQuery ('#message-help-block').html ('Escribe un mesaje');
			return false;
		} else if (((totalActiveUser == 0) && (shareType.val () !== 'Usuario')) || ((shareType.val () !== 'Usuario') && (jQuery.inArray (shareRegister.val (), lastUsers) === -1) )) {
			question = 'Con esta operación estarás compartiendo con ' + shareDisplay.val () +
					   ' el contenido de este registro y los contenidos relacionados con el.\n¿Estás de acuerdo?';
			if (!confirm (question)) {
				return false;
			} else {
				totalActiveUser += 1;
				lastUsers.push (shareRegister.val ());
				jQuery ('#newChat').val (1);
				jQuery ('#relatedUsers').val (lastUsers)
			}
		}
		jQuery ('#message-help-block').html ('');

		response = '<div class="conversation-item item-left clearfix" data-user-id="' + userId + '-' + shareRegister.val () + '">' +
				   '<div class="conversation-user">' +
				   '<img src="' + src + '" alt="" style="width: 100%; height: 100%;">' +
				   '</div>' +
				   '<div class="conversation-body">' +
				   '<div class="name">' + name + '</div>' +
				   '<div class="time hidden-xs">&nbsp;' +
				   date.getFullYear () + "-" +
				   (date.getMonth () + 1) + "-" +
				   date.getDate () +
				   '</div>' +
				   '<div class="text"><span>' + message + '<span></div>' +
				   '</div>' +
				   '</div>';

		var form = parleyTab.find ('form[name="conversation-new-message"]'),
			serialized;
		serialized = form.serialize ();
		jQuery.ajax (
			'index.php',
			{
				data:     serialized,
				dataType: 'text',
				method:   'post'
			}
		).done (function (responseText) {

		});

		parleyTab.find ("#comment").val ('');
		parleyTab.find ("#chat-list").before (response);
		return true;
	};

	var getRelateModal = function (obj) {
		var selectedModule = jQuery (obj).val (), userName,
			selectedTitle  = jQuery ('#shareWith option:selected').text ();
		jQuery (obj).attr ('data-referenced-module', selectedModule);
		jQuery (obj).attr ('data-title', 'Seleccionar: ' + selectedTitle);
		if (selectedModule == 'contactos') {
			RelatedModuleModalUtils.openModal (obj);
		} else if (selectedModule == '') {
			parleyTab.find ('#showShare').html ('');
			parleyTab.find ('#shareReg').val (0);
			parleyTab.find ('#shareDisplay').val ('');
		} else {
			userName = jQuery (obj).children ().children ('option:selected').text ();
			parleyTab.find ('#showShare').html ('');
			parleyTab.find ('#shareReg').val (selectedModule);
			parleyTab.find ('#shareDisplay').val (userName);
		}

	};

	window.parleyUtils = {
		getRelateModal: getRelateModal,
		messageThread:  messageThread,
		selectEmails:   selectEmails,
		selectUser:     selectUser,
		sendMessage:    sendMessage

	};
	var onDocumentReadyHandler = function () {
		var thisDiv, shareUserId,
			selectedContent,
			scrollTo = tabUsers.find ('a').eq (0);
		tabUsers.children ().each (function (i, element) {
			thisDiv = jQuery (element);
			if (thisDiv.hasClass ('active')) {
				shareUserId = thisDiv.find ('a').attr ('rel');
				shareType.val (thisDiv.find ('a').attr ('data-user-type'));
				selectedContent = thisDiv.find ('h5').html ();
				shareDisplay.val (selectedContent.split ('<span')[ 0 ]);
				shareRegister.val (shareUserId);
				scrollUsers.scrollTop (thisDiv.position ().top - scrollTo.parent ().position ().top);
				thisDiv.focus ();
				messageThread (shareUserId);
			}
		});
		var relatedUsersElement = jQuery ('#relatedUsers');
		if (relatedUsersElement.length > 0 && relatedUsersElement.val()) {
			lastUsers = JSON.parse (relatedUsersElement.val ());
			relatedUsersElement.val (lastUsers);
		}
	};
	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
