(function (jQuery) {
	// Private variables
	var notificationModal = jQuery ('#notification-body');
	notificationModal.find ('#date_from').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	notificationModal.find ('#date_to').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	notificationModal.find ('#archivedEmailsDateTo').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	notificationModal.find ('#archivedEmailsDatefrom').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	notificationModal.find ('#emailsDateTo').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	notificationModal.find ('#emailsDatefrom').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });

	// Private methods

	//Public methods
	var archiveEmail = function (objId) {
		var toggleDivId  = jQuery (objId).attr ('data-target'),
			templateForm = jQuery ('#archive-mail-template').html (),
			row,
			elementReference;
		if (!templateForm) {
			return;
		}
		elementReference = toggleDivId.split ('_');
		row = jQuery (templateForm);
		row.find ('input').eq (0).val (elementReference[ 1 ]);
		row.find ('select').eq (0).attr ('onchange', 'notificationCenterUtils.archiveInModule(this)');
		row.find ('button').eq (0).attr ('onclick', 'notificationCenterUtils.searchRecords(this)');
		jQuery (toggleDivId).html (row);

		jQuery (toggleDivId).on ('hide.bs.collapse', function () {
			row.remove ();
			jQuery (toggleDivId).html ('');
		});
		jQuery (toggleDivId).collapse ('toggle');

	};

	var archiveInModule = function (obj) {
		var module = jQuery (obj).val (),
			row    = jQuery (obj).parent ().parent ().parent ();
		fieldOption = row.find ('select').eq (1);
		errorMsn = row.find ('span').eq (0);

		jQuery.ajax ({
			cache:   false,
			data:    { 'module_name': module },
			type:    "POST",
			url:     'index.php?module=notification_center&action=notificationAjax&file=notificationAjaxUtils&option=get_fields&Ajax=true',
			success: function (data) {
				fieldSelected = jQuery.parseJSON (data);
				if (jQuery.inArray ('error', Object.keys (fieldSelected[ 0 ])) == -1) {
					fieldOption.find ('option').remove ().end ().append (jQuery ('<option>', {
						value: '',
						text:  'Campo'
					}));
					errorMsn.html ('');
					for (var op = 0; op < fieldSelected.length; op++) {
						fieldOption.append (jQuery ('<option>', {
							value: fieldSelected[ op ].tablename + '@' + fieldSelected[ op ].columnname,
							text:  fieldSelected[ op ].fieldlabel
						}));
					}
				} else {
					errorMsn.html ('No se encontraron campos');
				}
			}
		})
	};

	var disabledAlert = function (obj) {
		var notificationId = jQuery (obj).attr ('rel'),
			arguments      = [
				'module=notifications',
				'action=Disable',
				'record=' + encodeURIComponent (notificationId),
				'Ajax=true'
			];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'text',
			method:   'post'
		}).done (function (responseText) {
			console.log (responseText);
			jQuery (obj).addClass ('hide');
		});
	};

	var searchByTime = function () {
		notificationModal.find ('#date_from').val ('');
		notificationModal.find ('#date_to').val ('');
	};

	var searchAlerts = function (obj) {
		var arguments = notificationModal.find ('#systen-alerts').serialize (),
			from      = notificationModal.find ('#dateSystem-star'),
			to        = notificationModal.find ('#dateSystem-end'),
			periodId  = notificationModal.find ('#viewSysPeriod'),
			value     = '';

		if (periodId === 'custom') {
			value = from.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				alert ('Selecciona la fecha desde');
				return false;
			}
			value = to.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				alert ('Selecciona la fecha hasta');
				return false;
			}
		} else {
			jQuery.ajax (
				'index.php',
				{
					data:     arguments,
					dataType: 'text',
					method:   'post'
				}
			).done (function (responseText) {
				console.log (responseText);
				notificationModal.find ('#list_alerts').html (responseText)
			})
		}
	};

	var searchArchivedEmailsByTime = function () {
		notificationModal.find ('#archivedEmailsDateTo').val ('');
		notificationModal.find ('#archivedEmailsDatefrom').val ('');
	};

	var searchPeriods = function (obj) {
		var periodId  = jQuery (obj).val (),
			objPeriod = '',
			from      = notificationModal.find ('#dateSystem-star'),
			to        = notificationModal.find ('#dateSystem-end'),
			arguments = [
				'module=notification_center',
				'action=notificationAjax',
				'file=searchSystemAlerts',
				'searchFrom=period',
				'Ajax=true',
				'viewSystemPeriod=' + encodeURIComponent (periodId)
			];
		if (periodId === 'custom') {
			from.val ('').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
			to.val ('').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		} else {
			from.datepicker ('remove');
			to.datepicker ('remove');
			jQuery.ajax (
				'index.php',
				{
					data:     arguments.join ('&'),
					dataType: 'text',
					method:   'post'
				}
			).done (function (responseText) {
				objPeriod = JSON.parse (responseText);
				from.val (objPeriod.startdate);
				to.cal (objPeriod.enddate);
				console.log (respnseText);
			})
		}
	};

	var searchEmailsByTime = function () {
		notificationModal.find ('#emailsDateTo').val ('');
		notificationModal.find ('#emailsDatefrom').val ('');
	};

	var searchParley = function () {
		var form = jQuery ('form[name="search-parley"]'),
			serialized;
		serialized = form.serialize ();
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=notification_center&action=notificationAjax&file=searchParley&Ajax=true',
			success: function (message) {
				notificationModal.find ('#list_parley').html (message)
			}
		})
	};

	var searchEmailsArchived = function () {
		var form = jQuery ('form[name="search-archived-emails"]'),
			serialized;
		serialized = form.serialize ();
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=notification_center&action=notificationAjax&file=searchArchivedEmails&Ajax=true',
			success: function (message) {
				notificationModal.find ('#list_archivedEmails').html (message)
			}
		})
	};

	var searchEmails = function () {
		var form = jQuery ('form[name="not-archived-emails"]'),
			serialized;
		serialized = form.serialize ();
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=notification_center&action=notificationAjax&file=searchEmails&Ajax=true',
			success: function (message) {
				notificationModal.find ('#list_emails').html (message)
			}
		})
	};

	var setArchiveEmail = function (obj) {
		var form = jQuery (obj).parent (),
			serialized,
			errorMsn;
		errorMsn = form.find ('span').eq (0);
		serialized = form.serialize ();
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=notification_center&action=notificationAjax&file=notificationAjaxUtils&option=set_records&Ajax=true',
			success: function (data) {
				results = jQuery.parseJSON (data);
				errorMsn.html (results.result)
			}
		});
	};

	var searchRecords = function (obj) {
		var form       = jQuery (obj).parent (),
			fieldValue = '',
			searchText = '',
			serialized,
			errorMsn;
		fieldValue = form.find ('select').eq (1).val ();
		searchText = form.find ('input').eq (1).val ();
		errorMsn = form.find ('span').eq (0);
		if ((fieldValue == '') || ( searchText == '')) {
			errorMsn.html ('Uops faltan datos!');
			return;
		}
		errorMsn.html ('');
		serialized = form.serialize ();
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=notification_center&action=notificationAjax&file=notificationAjaxUtils&option=get_records&Ajax=true',
			success: function (data) {
				objData = jQuery (data);
				objData.find ('button').eq (0).attr ('onclick', 'notificationCenterUtils.setArchiveEmail(this)');
				jQuery ('.archive-select').html (objData)
			}
		});
	};

	var redArchivedEmail = function (obj) {
		var toggleDivId  = '',
			myFrame      = '',
			msnEmail     = '',
			templateHtml = '';
		toggleDivId = jQuery (obj).attr ('data-target');
		templateHtml = '#emailBody-' + jQuery (obj).attr ('data-record');

		jQuery (toggleDivId).on ('show.bs.collapse', function () {
			jQuery (obj).find (".glyphicon").removeClass ("glyphicon-plus").addClass ("glyphicon-minus");
			jQuery (obj).parent ().find ('p').addClass ('hide')
		}).on ('hide.bs.collapse', function () {
			jQuery (obj).find (".glyphicon").removeClass ("glyphicon-minus").addClass ("glyphicon-plus");
			jQuery (obj).parent ().find ('p').removeClass ('hide')
		});
		jQuery (toggleDivId).collapse ('toggle');
		myFrame = jQuery (toggleDivId).find ('.emailIframe').contents ().find ('body');
		msnEmail = jQuery (templateHtml).html ();
		myFrame.html (msnEmail);
	};

	var redEmail = function (obj) {
		var toggleDivId  = '',
			myFrame      = '',
			msnEmail     = '',
			templateHtml = '';
		toggleDivId = jQuery (obj).attr ('data-target'),
			templateHtml = '#emailBody-' + jQuery (obj).attr ('data-record');

		jQuery (toggleDivId).on ('show.bs.collapse', function () {
			jQuery (obj).find (".glyphicon").removeClass ("glyphicon-plus").addClass ("glyphicon-minus");
			jQuery (obj).parent ().find ('p').addClass ('hide')
		}).on ('hide.bs.collapse', function () {
			jQuery (obj).find (".glyphicon").removeClass ("glyphicon-minus").addClass ("glyphicon-plus");
			jQuery (obj).parent ().find ('p').removeClass ('hide')
		});
		jQuery (toggleDivId).collapse ('toggle');
		myFrame = jQuery (toggleDivId).find ('.emailIframe').contents ().find ('body');
		msnEmail = jQuery (templateHtml).html ();
		myFrame.html (msnEmail);
	};

	var relatedEmailLink = function (obj) {
		var relatedLink = '';
		relatedLink = jQuery (obj).attr ('data-ref');
		if (relatedLink != '') {
			window.location.href = relatedLink;
		}
	}

	window.notificationCenterUtils = {
		archiveEmail:               archiveEmail,
		archiveInModule:            archiveInModule,
		disabledAlert:              disabledAlert,
		setArchiveEmail:            setArchiveEmail,
		searchParley:               searchParley,
		searchByTime:               searchByTime,
		searchEmailsByTime:         searchEmailsByTime,
		searchEmailsArchived:       searchEmailsArchived,
		searchEmails:               searchEmails,
		searchAlerts:               searchAlerts,
		searchArchivedEmailsByTime: searchArchivedEmailsByTime,
		searchPeriods:              searchPeriods,
		searchRecords:              searchRecords,
		redEmail:                   redEmail,
		redArchivedEmail:           redArchivedEmail,
		relatedEmailLink:           relatedEmailLink
	}
} (jQuery));