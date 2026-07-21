(function (jQuery) {
	var serializeNonEmptyFormData = function (formName) {
		var fields                            = jQuery ('form[name=' + formName + ']').find ('.for-filter'),
			n                                 = fields.length,
			i, field, name, value, serialized = [];
		if (n === 0) {
			return '';
		}
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			name = field.attr ('name');
			value = field.val ();
			if ((!name) || (value === undefined) || (value === null) || (value.trim () === '')) {
				continue;
			}
			serialized.push (encodeURIComponent (name) + '=' + encodeURIComponent (value));
		}
		return serialized.length > 0 ? '&' + serialized.join ('&') : '';
	};

	var addSelectedLocations = function () {
		var dialog                        = jQuery ('#available-locations-dialog'),
			selectedLocationsCheckboxes   = dialog.find ('input.select[type="checkbox"]:checked'),
			locationsBody                 = jQuery ('table.locations').find ('tbody'),
			template                      = jQuery ('#location-template').html (),
			i, j, m, n, selectedLocations = [], selectedLocationCheckbox, selectedLocationRow, location, locationsRows, found;
		if (selectedLocationsCheckboxes.length === 0) {
			alert ('Selecciona algún espacio');
			return;
		}
		n = selectedLocationsCheckboxes.length;
		for (i = 0; i < n; i += 1) {
			selectedLocationCheckbox = jQuery (selectedLocationsCheckboxes [ i ]);
			selectedLocationRow = selectedLocationCheckbox.closest ('tr');
			selectedLocations.push ({
				id:       selectedLocationCheckbox.val (),
				code:     selectedLocationRow.find ('.col-code').html (),
				number:   selectedLocationRow.find ('.col-number').html (),
				type:     selectedLocationRow.find ('.col-type').html (),
				location: selectedLocationRow.find ('.col-location').html ()
			})
		}

		n = selectedLocations.length;
		for (i = 0; i < n; i += 1) {
			found = false;
			locationsRows = locationsBody.find ('tr');
			m = locationsRows.length;
			for (j = 0; j < m; j += 1) {
				if (jQuery (locationsRows [ j ]).find ('.col-code > input[type="hidden"]').val () === selectedLocations [ i ].id) {
					found = true;
					break;
				}
			}
			if (!found) {
				location = jQuery (template);
				location.find ('.col-code > input[type="hidden"]').val (selectedLocations [ i ].id);
				location.find ('.col-code > .code').html (selectedLocations [ i ].code);
				location.find ('.col-number').html (selectedLocations [ i ].number);
				location.find ('.col-type').html (selectedLocations [ i ].type);
				location.find ('.col-location').html (selectedLocations [ i ].location);
				locationsBody.append (location);
			}
		}
		dialog.modal ('hide');
	};

	var addSelectedUsers = function () {
		var dialog                    = jQuery ('#available-users-dialog'),
			selectedUsersCheckboxes   = dialog.find ('input.select[type="checkbox"]:checked'),
			usersBody                 = jQuery ('table.users').find ('tbody'),
			template                  = jQuery ('#user-template').html (),
			i, j, m, n, selectedUsers = [], selectedUserCheckbox, selectedUserRow, user, usersRows, found;
		if (selectedUsersCheckboxes.length === 0) {
			alert ('Selecciona algún usuario');
			return;
		}
		n = selectedUsersCheckboxes.length;
		for (i = 0; i < n; i += 1) {
			selectedUserCheckbox = jQuery (selectedUsersCheckboxes [ i ]);
			selectedUserRow = selectedUserCheckbox.closest ('tr');
			selectedUsers.push ({
				id:    selectedUserCheckbox.val (),
				code:  selectedUserRow.find ('.col-code').html (),
				name:  selectedUserRow.find ('.col-name').html (),
				phone: selectedUserRow.find ('.col-phone').html (),
				email: selectedUserRow.find ('.col-email').html ()
			})
		}

		n = selectedUsers.length;
		for (i = 0; i < n; i += 1) {
			found = false;
			usersRows = usersBody.find ('tr');
			m = usersRows.length;
			for (j = 0; j < m; j += 1) {
				if (jQuery (usersRows [ j ]).find ('.col-code > input[type="hidden"]').val () === selectedUsers [ i ].id) {
					found = true;
					break;
				}
			}
			if (!found) {
				user = jQuery (template);
				user.find ('.col-code > input[type="hidden"]').val (selectedUsers [ i ].id);
				user.find ('.col-code > .code').html (selectedUsers [ i ].code);
				user.find ('.col-name').html (selectedUsers [ i ].name);
				user.find ('.col-phone').html (selectedUsers [ i ].phone);
				user.find ('.col-email').html (selectedUsers [ i ].email);
				usersBody.append (user);
			}
		}
		dialog.modal ('hide');
	};

	var deleteRow = function (button) {
		var row = jQuery (button).closest ('tr');
		if (!confirm ('¿Estás seguro de borrar el registro seleccionado?')) {
			return;
		}
		row.remove ();
	};

	var openAvailableLocationsDialog = function () {
		jQuery.ajax ('index.php?module=espacios&action=espaciosAjax&file=GetLocations' + serializeNonEmptyFormData ('EditView'), {
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			var dialog     = jQuery ('#available-locations-dialog'),
				dialogBody = dialog.find ('tbody'),
				template   = jQuery ('#available-location-template').html (),
				i, n, availableLocation;
			if ((!response) || (response.length === 0)) {
				dialogBody.html ('<tr><td colspan="5">No se encuentran espacios registrados</td></tr>');
			} else {
				dialogBody.html ('');
				n = response.length;
				for (i = 0; i < n; i += 1) {
					availableLocation = jQuery (template);
					availableLocation.find ('.col-checkbox > input[type="checkbox"]').val (response [ i ][ 'espaciosid' ]);
					availableLocation.find ('.col-code').html (response [ i ][ 'cod_espacios' ]);
					availableLocation.find ('.col-number').html (response [ i ][ 'numero_' ]);
					availableLocation.find ('.col-type').html (response [ i ][ 'tipo_area' ]);
					availableLocation.find ('.col-location').html (response [ i ][ 'centro' ]);
					dialogBody.append (availableLocation);
				}
			}
			dialog.modal ('show');
		}).fail (function () {
			alert ('Se ha presentado un error. Por favor intente más tarde');
		});
	};

	var openAvailableUsersDialog = function () {
		jQuery.ajax ('index.php?module=usuarios_colladito&action=usuarios_colladitoAjax&file=GetUsers' + serializeNonEmptyFormData ('EditView'), {
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			var dialog     = jQuery ('#available-users-dialog'),
				dialogBody = dialog.find ('tbody'),
				template   = jQuery ('#available-user-template').html (),
				i, n, availableUser;
			if ((!response) || (response.length === 0)) {
				dialogBody.html ('<tr><td colspan="5">No se encuentran usuarios registrados</td></tr>');
			} else {
				dialogBody.html ('');
				n = response.length;
				for (i = 0; i < n; i += 1) {
					availableUser = jQuery (template);
					availableUser.find ('.col-checkbox > input[type="checkbox"]').val (response [ i ][ 'usuarios_colladitoid' ]);
					availableUser.find ('.col-code').html (response [ i ][ 'cod_usuarios_col' ]);
					availableUser.find ('.col-name').html ((response [ i ][ 'nombres' ] + ' ' + response [ i ][ 'apellidos' ]).trim ());
					availableUser.find ('.col-phone').html (response [ i ][ 'telf' ]);
					availableUser.find ('.col-email').html (response [ i ][ 'correo_electronico' ]);
					dialogBody.append (availableUser);
				}
			}
			dialog.modal ('show');
		}).fail (function () {
			alert ('Se ha presentado un error. Por favor intente más tarde');
		});
	};

	var toggleRecordsSelected = function (checkbox) {
		var isChecked  = jQuery (checkbox).is (':checked'),
			checkboxes = jQuery ('input.select[type="checkbox"]'),
			i, n;
		n = checkboxes.length;
		for (i = 0; i < n; i += 1) {
			jQuery (checkboxes [ i ]).prop ('checked', isChecked);
		}
	};

	var toggleAllRecordsSelected = function () {
		var selectAllCheckbox = jQuery ('input.select-all[type="checkbox"]'),
			checkboxes        = jQuery ('input.select[type="checkbox"]'),
			i, n;

		n = checkboxes.length;
		for (i = 0; i < n; i += 1) {
			if (!jQuery (checkboxes [ i ]).is (':checked')) {
				selectAllCheckbox.prop ('checked', false);
				return;
			}
		}
		selectAllCheckbox.prop ('checked', true);
	};

	window.ReservationUtils = {
		addSelectedLocations:         addSelectedLocations,
		addSelectedUsers:             addSelectedUsers,
		deleteRow:                    deleteRow,
		openAvailableLocationsDialog: openAvailableLocationsDialog,
		openAvailableUsersDialog:     openAvailableUsersDialog,
		toggleAllRecordsSelected:     toggleAllRecordsSelected,
		toggleRecordsSelected:        toggleRecordsSelected
	};
} (jQuery));
