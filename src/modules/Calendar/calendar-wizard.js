(function (jQuery) {
	// Private variables
	var wizard       = null,
		wizardReload = false,
		record       = null,
		mode         = null,
		totalNewRows = 0,
		returnUrl    = null;

	// Private functions
	var addInvitees = function () {
		var availableUsers = wizard.el.find ('#availableusers'),
			selectedUsers  = wizard.el.find ('#selectedusers'),
			options        = availableUsers.find ('option:selected'),
			option, i;

		if (options.length === 0) {
			return;
		}

		for (i = 0; i < options.length; i += 1) {
			option = jQuery (options [ i ]);
			selectedUsers.append ('<option value="' + option.val () + '">' + option.text () + '</option>');
			option.removeAttr ('selected').hide ();
		}
	};

	var addRelatedEntity = function (buttonElement, moduleName, entityId, entityLabel) {
		var relatedEntityTemplate = jQuery ('#calendar-task-related-entity-template').html (),
			button                = jQuery (buttonElement),
			tableBody             = button.closest ('table').find ('tbody'),
			template;
		totalNewRows += 1;
		template = jQuery (relatedEntityTemplate.replace (new RegExp ('__ID__', 'g'), (totalNewRows * -1)));
		if ((moduleName) && (entityId) && (entityLabel)) {
			if (Array.isArray (entityId)) {
				for (var i = 0; i < entityId.length; i++) {
                    template.find ('.modulename').val (moduleName);
                    template.find ('.data-field').val (entityId[ i ]);
                    template.find ('.display-field').val (entityLabel[ i ]);
                    tableBody.append (template);
                    totalNewRows += 1;
                    template = jQuery (relatedEntityTemplate.replace (new RegExp ('__ID__', 'g'), (totalNewRows * -1)));
				}
			} else {
                template.find ('.modulename').val (moduleName);
                template.find ('.data-field').val (entityId);
                template.find ('.display-field').val (entityLabel);
                tableBody.append (template);
			}
		} else {
            tableBody.append (template);
		}
	};

	var clearRelatedEntityFields = function (evt) {
		var button = jQuery (evt.currentTarget);
		button.closest ('.input-group').find ('.display-field').val ('');
		button.closest ('.input-group').find ('.data-field').val ('');
	};

	var deleteRelatedEntity = function (evt) {
		var button = jQuery (evt.currentTarget);
		if (!confirm ('¿Estás seguro que quieres eliminar la relación seleccionada?')) {
			return;
		}

		button.closest ('tr').remove ();
	};

	var destroyWizard = function () {
		wizard = null;
		if (wizardReload) {
            if ((returnUrl === null) || (returnUrl === undefined)) {
                window.location.reload();
            } else {
                window.location.href = returnUrl;
            }
        }
	};

	var getBasicDataError = function (card) {
		var field, value;

		field = card.find ('#activitytype');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona el tipo'
			};
		}

		field = card.find ('#subject');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return {
				field:   field,
				message: 'Introduce el asunto'
			};
		}

		return null;
	};

	var getDatesDataError = function (card) {
		var field, startdate, starttime, enddate, endtime, dateA, dateB, dummyDate, dummyTime;

		field = card.find ('#startdate');
		startdate = field.val ();
		if ((startdate === null) || (startdate === undefined) || (startdate.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona la fecha de inicio'
			};
		}

		field = card.find ('#starttime');
		starttime = field.val ();
		if ((starttime === null) || (starttime === undefined) || (starttime.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona la hora de inicio'
			};
		}

		field = card.find ('#enddate');
		enddate = field.val ();
		if ((enddate === null) || (enddate === undefined) || (enddate.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona la fecha de vencimiento'
			};
		}

		field = card.find ('#endtime');
		endtime = field.val ();
		if ((endtime === null) || (endtime === undefined) || (endtime.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona la hora de vencimiento'
			};
		}

		dummyDate = startdate.split ('-');
		dummyTime = starttime.split (':');
		dateA = Date.UTC (dummyDate [ 0 ], (dummyDate [ 1 ] - 1), dummyDate [ 2 ], dummyTime [ 0 ], dummyTime [ 1 ], dummyTime [ 2 ]);
		dummyDate = enddate.split ('-');
		dummyTime = endtime.split (':');
		dateB = Date.UTC (dummyDate [ 0 ], (dummyDate [ 1 ] - 1), dummyDate [ 2 ], dummyTime [ 0 ], dummyTime [ 1 ], dummyTime [ 2 ]);
		if (dateA > dateB) {
			return {
				field:   card.find ('#startdate'),
				message: 'La fecha de vencimiento debe ser mayor a la fecha de inicio'
			};
		}

		return null;
	};

	var getSelectedRecords = function() {
        var selectedRecords = {},
			recordLabels    = [],
			recordIds       = [];
        jQuery("input:checkbox[name=selected_id]:checked").each(function(e) {
        	var recordId =  jQuery(this).attr('id');
        	recordLabels.push (jQuery ('#row_' + recordId).find('.md-trigger').attr('modal-title'));
            recordIds.push (recordId);
        });
      if (recordLabels.length > 0) {
      	selectedRecords = {
      		'entityId':    recordIds,
			'entityLabel': recordLabels
		}
	  }
	  return selectedRecords;
	};

	var getPriorityDataError = function (card) {
		var field, value;

		field = card.find ('#eventstatus');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona el estado'
			};
		}

		field = card.find ('#taskpriority');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return {
				field:   field,
				message: 'Selecciona la prioridad'
			};
		}

		return null;
	};

	var getRelatedEntitiesDataError = function (card) {
		var relatedEntities = card.find ('.related-record'),
			i, relatedEntity, field, value;

		if (relatedEntities.length === 0) {
			return null;
		}

		for (i = 0; i < relatedEntities.length; i += 1) {
			relatedEntity = jQuery (relatedEntities [i]);

			field = relatedEntity.find ('.modulename');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				return {
					field:   field,
					message: 'Selecciona el módulo'
				};
			}

			field = relatedEntity.find ('.data-field');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				return {
					field: relatedEntity.find ('.display-field'),
					message: 'Selecciona la entidad relacionada'
				};
			}
		}

		return null;
	};

	var openRelatedEntityModal = function (evt) {
		var button            = jQuery (evt.currentTarget),
			relatedRecordRow  = button.closest ('.related-record'),
			moduleNameElement = relatedRecordRow.find ('.modulename'),
			displayFieldId    = relatedRecordRow.find ('.display-field').attr ('id'),
			dataFieldId       = relatedRecordRow.find ('.data-field').attr ('id'),
			moduleName        = moduleNameElement.val (),
			moduleLabel       = moduleNameElement.find ('option:selected').text ();

		if ((moduleName === undefined) || (moduleName === null) || (moduleName.trim () === '')) {
			alert ('Selecciona el módulo');
			moduleNameElement.focus ();
			return false;
		}

		button.attr ('data-current-module', 'Calendar');
		button.attr ('data-display-field-id', displayFieldId);
		button.attr ('data-field-id', dataFieldId);
		button.attr ('data-referenced-module', moduleName);
		button.attr ('data-title', moduleLabel);

		RelatedModuleModalUtils.openModal (evt.currentTarget);
	};

	var removeInvitees = function () {
		var availableUsers = wizard.el.find ('#availableusers'),
			selectedUsers  = wizard.el.find ('#selectedusers'),
			options        = selectedUsers.find ('option:selected'),
			option, i;

		if (options.length === 0) {
			return;
		}

		for (i = 0; i < options.length; i += 1) {
			option = jQuery (options [ i ]);
			availableUsers.find ('option[value="' + option.val () + '"]').show ();
			option.remove ();
		}
	};

	var submitWizard = function () {
		var dummy          = wizard.el.find ('input[name=assigntype]'),
			relatedCrmIds  = wizard.el.find ('.related-record .data-field'),
			invitedUserIds = wizard.el.find ('#selectedusers > option'),
			invitedUserId  = [],
			relatedCrmId, i,
			arguments = [
				'module=Calendar',
				'action=CalendarAjax',
				'file=crearActividadAjax',
				'taskid=' + record,
				'mode=' + mode,
				'activitytype=' + encodeURIComponent (wizard.el.find ('#activitytype').val ()),
				'assigntype=' + encodeURIComponent (dummy.filter (':checked').val ()),
				'assigned_group_id=' + encodeURIComponent (wizard.el.find ('#assigned_group_id').val ()),
				'assigned_user_id=' + encodeURIComponent (wizard.el.find ('#assigned_user_id').val ()),
				'description=' + encodeURIComponent (wizard.el.find ('#description').val ()),
				'enddate=' + encodeURIComponent (wizard.el.find ('#enddate').val ()),
				'endtime=' + encodeURIComponent (wizard.el.find ('#endtime').val ()),
				'eventstatus=' + encodeURIComponent (wizard.el.find ('#eventstatus').val ()),
				'location=' + encodeURIComponent (wizard.el.find ('#location').val ()),
				'startdate=' + encodeURIComponent (wizard.el.find ('#startdate').val ()),
				'starttime=' + encodeURIComponent (wizard.el.find ('#starttime').val ()),
				'subject=' + encodeURIComponent (wizard.el.find ('#subject').val ()),
				'taskpriority=' + encodeURIComponent (wizard.el.find ('#taskpriority').val ()),
				'taskImport=' + encodeURIComponent (wizard.el.find ('#taskImport').val ()),
				'categoryid=' + encodeURIComponent (wizard.el.find ('#categoryid').val ()),
				'visibility=' + encodeURIComponent (wizard.el.find ('#visibility').is (':checked') ? 'Public' : 'Private')
			];

        for (i = 0; i < invitedUserIds.length; i += 1) {
			invitedUserId = jQuery (invitedUserIds [ i ]).val ();
			if (!invitedUserId) {
				continue;
			}
			arguments.push ('inviteduserids[]=' + encodeURIComponent (invitedUserId));
		}

		for (i = 0; i < relatedCrmIds.length; i += 1) {
			relatedCrmId = jQuery (relatedCrmIds [ i ]).val ();
			if (!relatedCrmId) {
				continue;
			}
			arguments.push ('relatedcrmids[]=' + encodeURIComponent (relatedCrmId));
		}
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
            wizardReload = true;
			wizard.submitSuccess ();
			wizard.hideButtons ();
		}).fail (function (jQueryResponse) {
			wizard.el.find ('.wizard-failure .message').text (jQueryResponse.responseJSON);
			wizard.submitFailure ();
			wizard.hideButtons ();
		});
	};

	var toggleAssignType = function (radioElement) {
		var radio = jQuery (this);
		if (radio.val () === 'U') {
			radio.closest ('.form-group').find ('#assigned_group_id').hide ();
			radio.closest ('.form-group').find ('#assigned_user_id').show ();
		} else {
			radio.closest ('.form-group').find ('#assigned_user_id').hide ();
			radio.closest ('.form-group').find ('#assigned_group_id').show ();
		}
	};

	var updateWizardModal = function (idActivity) {
        if (idActivity === '') {
            return;
        }
        //alert('Modo edición: Se auto completarán los campos en el asistente');
        record = idActivity;
        mode   = 'edit';
		var wizardTitle    = jQuery ('.wizard-title'),
			availableUsers = wizard.el.find ('#availableusers > option'),
			arguments = {
            'module':   'Home',
            'action':   'AjaxHomeUtils',
            'function': 'FETCH-ACTIVITY-WIZARD',
            'record':   idActivity,
            'Ajax':     true
        };
        wizardTitle.html('Editar tarea <small style="color: red">Recargando datos...</small>');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    Object.entries(message.html).forEach(entry => {
                        const [key, value] = entry;
                        if (key !== 'invitees') {
                            jQuery (wizard.el.find ('#' + key)).val (value);
						} else if (availableUsers.length > 1)  {
                        	availableUsers.each(function() {
                        		if (jQuery.inArray(this.value, value) !== -1) {
                        			jQuery(this).attr('selected', true)
								}
                            });
                            addInvitees ();
						}
                    wizardTitle.html('Editar tarea');
                });
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

	var updateProgressBar = function () {
		var cards      = wizard.cards,
			activeCard = wizard.getActiveCard (),
			index      = 0,
			cardName;
		for (cardName in cards) {
			if (cardName === activeCard.name) {
				break;
			}
			index += 1;
		}
		wizard.updateProgressBar ((index * 100) / Object.keys (wizard.cards).length);
	};

	var validateBasicCard = function (card) {
		var error = getBasicDataError (card.el);

		card.wizard.hidePopovers ();
		if (error !== null) {
			card.wizard.errorPopover (error.field, error.message);
			error.field.focus ();
			return false;
		}

		return true;
	};

	var validateDatesCard = function (card) {
		var error = getDatesDataError (card.el);

		card.wizard.hidePopovers ();
		if (error !== null) {
			card.wizard.errorPopover (error.field, error.message);
			error.field.focus ();
			return false;
		}
		return true;
	};

	var validateRelatedEntitiesCard = function (card) {
		var error = getRelatedEntitiesDataError (card.el);

		card.wizard.hidePopovers ();
		if (error !== null) {
			card.wizard.errorPopover (error.field, error.message);
			error.field.focus ();
			return false;
		}
		return true;
	};

	var validatePriorityCard = function (card) {
		var error = getPriorityDataError (card.el);

		card.wizard.hidePopovers ();
		if (error !== null) {
			card.wizard.errorPopover (error.field, error.message);
			error.field.focus ();
			return false;
		}
		return true;
	};

	// Public functions
	var close = function () {
		if (wizard) {
			wizard.reset ().close ().trigger ('closed');
		}
	};

	var open = function (moduleName, entityId, entityLabel, returnTo, parameters) {
		var dummy, template = jQuery ('#calendar-task-wizard-template');
		if ((entityLabel === 'inRow')) {
            var selectedRecords = getSelectedRecords();
            if (!jQuery.isEmptyObject(selectedRecords)) {
            	entityId    = selectedRecords.entityId;
            	entityLabel = selectedRecords.entityLabel;
			} else {
                alert('Debes seleccionar algún registro para registrar en el Calendario');
                return
			}
		}
		returnUrl = returnTo;
		wizard = jQuery (template.html ()).wizard ({
			backdrop:   'static',
			showCancel: false,
			buttons:    {
				cancelText:     'Cancelar',
				nextText:       'Siguiente →',
				backText:       '← Atrás',
				submitText:     'Guardar',
				submittingText: 'Guardando...'
			}
		});
		wizard.el.find ('.date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		wizard.el.find ('.time').timepicker ({ minuteStep: 5, showSeconds: true, showMeridian: false, disableFocus: false, showWidget: true });
		wizard.el.find ('.assigntype').on ('click', toggleAssignType);
		wizard.el.find ('.add-invitees').on ('click', addInvitees);
		wizard.el.find ('.remove-invitees').on ('click', removeInvitees);
		wizard.el.find ('.add-related-entity-button').on ('click', function () {
			addRelatedEntity (this, moduleName, entityId, entityLabel);
		});
        dummy = ((parameters !== '') && (parameters !== undefined)) ? parameters.split(';') : undefined;
		if (dummy !== undefined) {
			var activityType      = dummy[ 0 ],
				priority          = dummy[ 1 ],
				importance        = dummy[ 2 ],
				idActivity        = dummy[ 3 ],
				optionsInActivity = jQuery (wizard.el.find ('#activitytype > option'));
            jQuery (wizard.el.find ('#activitytype')).val (activityType);
            jQuery (wizard.el.find ('#taskpriority')).val (priority);
            jQuery (wizard.el.find ('#taskImport')).val (importance);
            updateWizardModal (idActivity);
			/*
            optionsInActivity.each(function () {
				if (activityType === 'Activity') {
					if (jQuery.inArray (this.value, ['Call', 'Meeting']) !== -1) {
						this.hide();
					}
				} else {
					if (jQuery.inArray(this.value, ['Call', 'Meeting']) === -1) {
						this.hide();
					}
				}
            }); */
		}

		wizard.cards [ 'related-entities' ].el.find ('tbody').on ('click', '.delete-related-entity-button', deleteRelatedEntity);
		wizard.cards [ 'related-entities' ].el.find ('tbody').on ('click', '.select-related-entity-button', openRelatedEntityModal);
		wizard.cards [ 'related-entities' ].el.find ('tbody').on ('click', '.clear-related-entity-button', clearRelatedEntityFields);
		wizard.el.find ('.close-wizard').on ('click', close);

		if ((moduleName) && (entityId) && (entityLabel)) {
			addRelatedEntity (wizard.cards [ 'related-entities' ].el.find ('.add-related-entity-button'), moduleName, entityId, entityLabel)
		}
		wizard.cards [ 'basic' ].on ('validate', validateBasicCard);
		wizard.cards [ 'priority' ].on ('validate', validatePriorityCard);
		wizard.cards [ 'dates' ].on ('validate', validateDatesCard);
		wizard.cards [ 'related-entities' ].on ('validate', validateRelatedEntitiesCard);
		wizard.on ('submit', submitWizard)
			  .on ('closed', destroyWizard)
			  .on ('incrementCard', updateProgressBar)
			  .on ('decrementCard', updateProgressBar)
			  .show ();
		jQuery ('.modal-backdrop').css ({ 'background-color': 'transparent', 'bottom': 0, 'opacity': 0, 'z-index': 1038 });
	};

	var validateTask = function (formElement) {
		var form = jQuery (formElement),
			error;

		error = getBasicDataError (form);
		if (error !== null) {
			alert (error.message);
			error.field.focus ();
			return false;
		}

		return true;
	};

	window.CalendarWizard = {
		close:        close,
		open:         open,
		validateTask: validateTask
	};
} (jQuery));
