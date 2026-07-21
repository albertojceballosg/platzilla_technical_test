(function (jQuery) {
	var activityId = 0;
	var getFilters = function (idActivity) {
		var container = jQuery ('#list-view-header-columns-' + idActivity),
			filterElements = container.find ('.list-view-filter-' + idActivity),
			i, filterElement, filters;

		if (filterElements.length === 0) {
			return null;
		}

		filters = [];
		for (i = 0; i < filterElements.length; i += 1) {
			filterElement = jQuery (filterElements [i]);
			if (filterElement.val () === '') {
				continue;
			} else if (filterElement.hasClass ('start-date')) {
				filters.push (encodeURIComponent (filterElement.attr ('name')) + '>=' + encodeURIComponent (filterElement.val ()))
			} else if (filterElement.hasClass ('end-date')) {
				filters.push (encodeURIComponent (filterElement.attr ('name')) + '<=' + encodeURIComponent (filterElement.val ()))
			} else {
				filters.push (encodeURIComponent (filterElement.attr ('name')) + '=' + encodeURIComponent (filterElement.val ()))
			}
		}
		return filters;
	};

	var editView = function (linkElement, parentTab) {
		var link = jQuery (linkElement),
			viewSelect = link.closest ('.input-group').find ('select[id ^=viewname-home]'),
			viewId = viewSelect.val (),
			moduleName = viewSelect.data ('module-name'),
			arguments;

		if ((viewId === null) || (viewId === undefined) || (viewId.trim () === '')) {
			return;
		}

		arguments = [
			'module=' + encodeURIComponent (moduleName),
			'action=CustomView',
			'record=' + encodeURIComponent (viewId),
            'parenttab=' + encodeURIComponent (parentTab),
		];
		window.location.href = 'index.php?' + arguments.join ('&');
	};

	var goToPage = function (event, buttonElement, id) {
		var button       = jQuery (buttonElement),
			form         = jQuery ('#actions_progress_form-' + id),
			arguments,
			argumentArr,
			calendarView = jQuery ('#calendar_view-' + id),
			page         = button.attr ('data-pagination-page'),
			period       = jQuery ('#period-dates-' + id).val (),
			paginator    = jQuery ('#pager-' + id),
			records      = jQuery ('#show-records-' + id),
			users        = jQuery ('#daily-matrix-user-' + id + ' li'),
			tBody        = jQuery ('#main-box-action-in-progress-' + id),
			fronDate     = jQuery ('#start-date-' + id).val(),
			toDate       = jQuery ('#end-date-' + id).val(),
			functionName = jQuery ('#function-name-' + id).val(),
			dateFrom     = new Date (fronDate),
			dateTo       = new Date (toDate),
			userSelected = [];
		users.each (function() {
			var li = jQuery (this);
			if (li.hasClass('active')) {
				userSelected.push (li.children ('a').attr('rel'));
			}
		});
		if (userSelected.length === 0) {
			alert ('Uoops! Seleccione al menos un usuario');
			return;
		} else if (period === '') {
			alert ('Uoops! seleccione un período.');
			return;
		} else if (period === 'custom') {
			if ((dateFrom.getTime() > dateTo.getTime()) || fronDate === '' || toDate === '' ) {
				alert ('Uoops! error en periodo personalizado');
				return;
			}
		}
		calendarView.val('NO')
		records.removeClass('hidden');
		paginator.removeClass('hidden');
		form.find('input[name="page"]').val (page);
		arguments   = form.serialize ()
		argumentArr = arguments.split ('&');
		argumentArr.push ('inviteesid=' + encodeURIComponent (userSelected.join(', ')));
		tBody.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
		arguments = form.serialize();
		jQuery.post('index.php', argumentArr.join ('&'), function (data) {
			var message;
			try {
				message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {

					if (functionName !== 'ACTIVITY_REPORT') {
						tBody.html (message.html.rows);
						paginator.html (message.html.paginator);
						records.html (message.html.records);
					} else {
						tBody.html (message.html);
					}
				}
			}
			catch (e) {
				alert(e);
			}
		});
		event.preventDefault();

	};

	var goToPartWork = function (event, buttonElement, id) {
		var button       = jQuery (buttonElement),
			form         = jQuery ('#actions_progress_form-' + id),
			page         = button.attr ('data-pagination-page'),
			period       = jQuery ('#period-dates-' + id).val (),
			paginator    = jQuery ('#pager-' + id),
			records      = jQuery ('#show-records-' + id),
			users        = jQuery ('#daily-matrix-user-' + id + ' li'),
			tBody        = jQuery ('#action-in-progress-' + id),
			fronDate     = jQuery ('#start-date-' + id).val(),
			toDate       = jQuery ('#end-date-' + id).val(),
			functionName = jQuery ('#function-name-' + id).val(),
			dateFrom     = new Date (fronDate),
			dateTo       = new Date (toDate),
			userSelected = [];
		users.each (function() {
			var li = jQuery (this);
			if (li.hasClass('active')) {
				userSelected.push (li.children ('a').attr('rel'));
			}
		});
		if (userSelected.length === 0) {
			alert ('Uoops! Seleccione al menos un usuario');
			return;
		} else if (period === '') {
			alert ('Uoops! seleccione un período.');
			return;
		} else if (period === 'custom') {
			if ((dateFrom.getTime() > dateTo.getTime()) || fronDate === '' || toDate === '' ) {
				alert ('Uoops! error en periodo personalizado');
				return;
			}
		}
		form.find ('input[name="module"]').val ('part_work');
		form.find ('input[name="action"]').val ('ListView');
		form.find ('input[name="function"]').val ('PART_WORK');
		form.find ('input[name="page"]').val ('0');
		form.find ('input[name="Ajax"]').val ('false');
		form.find ('input[name="invitees_id"]').val (userSelected.join (','));
		form.submit ();
	}

	var openView = function (selectElement, tabName) {
		var select         = jQuery (selectElement),
			moduleName     = select.data ('module-name'),
			relModule      = select.data ('related-module'),
			selectedOption = select.find ('option:selected'),
			viewId         = selectedOption ? selectedOption.val () : '',
			viewType       = selectedOption ? selectedOption.data ('view-type') : '',
			arguments;

		if ((viewId === undefined) || (viewId === null) || (viewId.trim () === '')) {
			return;
		}

		if (viewType === 'KANBAN') {
			arguments = [
				'module=' + encodeURIComponent (moduleName),
				'action=ListView',
				'kviewid=' + encodeURIComponent (viewId)
			];
			window.location.href = 'index.php?' + arguments.join ('&');
		} else {
            activityId =  select.data ('activity-id');
			openPage (moduleName, 0, '','', relModule, select.data ('activity-id'), tabName);
		}
	};

	var openSelectedPage = function (inputElement, moduleName, sortBy, sortOrder, relModule, idActivity, tabName) {
		var input = jQuery (inputElement),
			requestedPage  = input.val (),
			actualPage = input.data ('actual-page'),
			totalPages = input.data ('total-pages'),
			viewId = jQuery ('#viewname-home'+ idActivity).val ();

		if ((isNaN (requestedPage)) || (requestedPage <= 0) || (requestedPage >= totalPages)) {
			input.val (actualPage);
			return;
		}

		openPage (moduleName, requestedPage, sortBy, sortOrder, relModule, idActivity, tabName);
	};

	var openPage = function (moduleName, page, sortBy, sortOrder, relModule, idActivity, tabName) {
		var filters,
			viewId = jQuery ('#viewname-home-' + idActivity).val (),
			arguments = [
			'module=' + encodeURIComponent (moduleName),
			'relmodule=' + encodeURIComponent (relModule),
			'idTab='+idActivity,
			'action=ListView',
			'viewid=' + encodeURIComponent (viewId),
			'selectedtab=' + encodeURIComponent (tabName),
			'Ajax=true'
		];
		if ((!isNaN (page)) && (page > 0)) {
			arguments.push ('page=' + encodeURIComponent (page))
		}
		if ((sortBy !== null) && (sortBy !== undefined) && (sortBy.trim () !== '')) {
			arguments.push ('sortby=' + encodeURIComponent (sortBy));
		}
		if ((sortOrder !== null) && (sortOrder !== undefined) && (sortOrder.trim () !== '')) {
			arguments.push ('sortorder=' + encodeURIComponent (sortOrder));
		}

		filters = getFilters (idActivity);
		if ((filters !== null) && (jQuery.isArray (filters)) && (filters.length > 0)) {
			arguments.push ('filters=' + filters.join (';'));
		}

		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'html',
			method:   'get'
		}).done (function (response) {
			var pageHtml = jQuery (response),
				pageBody = pageHtml.find ('.main-box-body'),
				pageFooter = pageHtml.find ('.btn-footer'),
				viewHtml = jQuery ('#list-view-container-' + idActivity).find ('.main-box');

			viewHtml.find ('.main-box-body').remove ();
			viewHtml.find ('.btn-footer').remove ();
			viewHtml.append (pageBody).append (pageFooter);
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error inesperado. Intenta más tarde');
			console.err (jQueryResponse);
		});
	};

	var printPartWork = function (obj, modulename) {
		var btn        = jQuery (obj),
			dataReport = jQuery ('input[name=report_data]').val (),
			label       = btn.html(),
			arguments;
		arguments = [
			'module=reportmanager',
			'action=reportmanagerAjax',
			'ajax=true',
			'file=reportValidate',
			'modulename=' + encodeURIComponent (modulename)
		];
		btn.html ('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + btn.html ());
		jQuery.ajax ('index.php', {
				data:     arguments.join ('&'),
				dataType: 'text',
				method:   'post'
		}).done (function (response) {
			setTimeout(function(){
				btn.html (label);
			}, 60000, btn, label);
			if (response == 'report_inactive') {
				alert (alert_arr.ACTIVATE_REPORT_PDF);
			} else if (response == 'ERROR') {
				alert (alert_arr.ERROR + ' modulo: ' + modulename + ' no encontrado, intente mas tarde');
			} else {
				window.location.href = 'index.php?module=reportmanager&action=View&Ajax=true&report_data=' + dataReport + '&modulename=' + modulename;
			}
		});
	}

	var showInCalendar = function (event, buttonElement, id) {
		var button       = jQuery (buttonElement),
            functionData = button.attr('data-action'),
			form         = jQuery ('#actions_progress_form-' + id),
			arguments,
			argumentArr,
			calendarView = jQuery ('#calendar_view-' + id),
			page         = button.attr ('data-pagination-page'),
			period       = jQuery ('#period-dates-' + id).val (),
			paginator    = jQuery ('#pager-' + id),
			records      = jQuery ('#show-records-' + id),
			users        = jQuery ('#daily-matrix-user-' + id + ' li'),
			tBody        = jQuery ('#main-box-action-in-progress-' + id),
			fronDate     = jQuery ('#start-date-' + id).val(),
			toDate       = jQuery ('#end-date-' + id).val(),
			functionName = (functionData === '') ? jQuery ('#function-name-' + id).val() : functionData,
			dateFrom     = new Date (fronDate),
			dateTo       = new Date (toDate),
			userSelected = [];
		users.each (function() {
			var li = jQuery (this);
			if (li.hasClass('active')) {
					userSelected.push (li.children ('a').attr('rel'));
			}
		});
		if (userSelected.length === 0) {
			alert ('Uoops! Seleccione al menos un usuario');
			return;
		} else if (period === '') {
			alert ('Uoops! seleccione un período.');
			return;
		} else if (period === 'custom') {
			if ((dateFrom.getTime() > dateTo.getTime()) || fronDate === '' || toDate === '' ) {
					alert ('Uoops! error en periodo personalizado');
					return;
			}
		}
		calendarView.val('YES')
		records.addClass('hidden');
		paginator.addClass('hidden');
		form.find('input[name="page"]').val (page);
		arguments   = form.serialize ()
		argumentArr = arguments.split ('&');
        console.log(argumentArr);
		console.log(functionData)
		argumentArr = argumentArr.map(function(param) {
		  if (param.startsWith("function=")) {
		    return "function=" + functionName; // Cambia OTRO_VALOR por el valor que desees
		  }
		  return param;
		});
		console.log(argumentArr);
		argumentArr.push ('inviteesid=' + encodeURIComponent (userSelected.join(', ')));
		tBody.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
		arguments = form.serialize();
		jQuery.post('index.php', argumentArr.join ('&'), function (data) {
			var message;
			try {
				message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {
					tBody.html (message.html);
				}
			}
			catch (e) {
				alert(e);
			}
		});
		event.preventDefault();

	};

	window.DataViewUtils = {
		editView:         editView,
		goToPage:         goToPage,
		goToPartWork:     goToPartWork,
		openPage:         openPage,
		openSelectedPage: openSelectedPage,
		openView:         openView,
		printPartWork:	  printPartWork,
		showInCalendar:   showInCalendar
	};

} (jQuery));