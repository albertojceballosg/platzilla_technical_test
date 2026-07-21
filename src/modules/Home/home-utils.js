(function (jQuery) {
    var availableFilter       = ['standard', 'user', 'date-time'],
        activityId            = '',
        maxMassOperationLimit = 500;

    var activeTab = function (event, idTab, module, tabName, tabGroup) {
        var arguments = {
                'module':    module,
                'action':   'AjaxListViewUtils',
                'function':  tabName,
                'hometabid':  idTab,
                'tabgroup':  tabGroup,
                'buttons':    '',
                'howusename': '',
                'Ajax':       true
            },
            content = jQuery ('#' + tabName + '-' + idTab),

            mainTab = jQuery ('#ListViewContents-' + idTab);
        if ((content.contents().length <= 3) && module !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html(message.html);
                    }
                }
                catch (e) {
                    alert(e);
                    if (tabGroup === 'record') {
                        mainTab.find('#' + tabName + '-' + idTab).removeClass ('active').removeClass ('in');
                        mainTab.find('#ListViewHomeContents-' + idTab).addClass('in').addClass ('active');
                    } else {
                        mainTab = jQuery ('#list-view-container-' + idTab);
                        mainTab.find('#' + tabName + '-' + idTab).removeClass ('active').removeClass ('in');
                        mainTab.find('#VIEW-TASK-' + idTab).addClass('in').addClass ('active');
                    }
                }
            });
        }
    };

    var activeTaskTab = function (event, idTab, module, tabName, tabGroup) {
        var arguments = {
                'module':   'Home',
                'action':   'AjaxHomeUtils',
                'flmodule':  module,
                'function':  tabName,
                'hometabid': idTab,
                'tabgroup':  tabGroup,
                'Ajax':      true
            },
            content = jQuery ('#' + tabName + '-' + idTab),
            mainTab = jQuery ('#ListViewContents-' + idTab);
        if ((content.contents().length <= 3) && module !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html(message.html);
                    }
                }
                catch (e) {
                    alert(e);
                    mainTab.find('#' + tabName + '-' + idTab).removeClass ('active').removeClass ('in');
                    if (tabGroup === 'record') {
                        mainTab.find('#ListViewHomeContents-' + idTab).addClass('in').addClass ('active');
                    } else {
                        mainTab.find('#VIEW-TASK-' + idTab).addClass('in').addClass ('active');
                    }

                }
            });
        }
    };

    var checkObject = function (obj) {
        var check = jQuery(obj),
            duplicate    = [],
            tabId        = check.data('home-tab-id'),
            chekValue    = check.val(),
            selectGlobal = [],
            selected     = jQuery('#allselectedboxes-' + tabId),
            skip         = jQuery ('#excludedRecords-' + tabId),
            result       = '',
            size;

        selectGlobal = selected.val().split(";");
        duplicate    = selectGlobal.indexOf(chekValue);
        size         = selectGlobal.length-1;

        if (check.prop ("checked")) {
            if (selected.val() === 'all') {
                skip.val (skip.val().replace (skip.val().match (chekValue + ";"),''));
                selected.val('all');
            } else {
                if(duplicate === -1)  {
                    selectGlobal [ size ] = chekValue;
                }
                size = selectGlobal.length - 1;

                for (i=0; i<= size; i++) {
                    if(selectGlobal[i] !== '')
                        result = selectGlobal [i] + ";" + result;
                }

                selected.val (result);
            }
            default_togglestate (check.attr('name'), '');
        } else {
            if (selected.val() === 'all') {
                skip.val(chekValue + ";" + skip.val());
                selected.val ('all');
            } else {
                if(duplicate !== -1){
                    selectGlobal.splice (duplicate,1);
                }
                size = selectGlobal.length-1;
                var i = 0;
                for ( i= size; i >= 0; i--) {
                    if (trim (selectGlobal [i]) !== '')
                        result = selectGlobal [i] + ";" + result;
                }
                default_togglestate (check.attr('name'), '');
                selected.val (result);
            }
            jQuery('#selectCurrentPageRec'+ tabId).checked = false;

        }
    };

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

    var getListViewEntries = function (module, url, idTab) {
        var viewname = jQuery('#viewname-' + idTab).val(),
            arguments = [
                'module=' + encodeURIComponent (module),
                'action=' + module + 'Ajax',
                'idTab=' + idTab,
                'file=ListView',
                'viewname=' + viewname,
                'homeViewId=' + viewname,
                'isHomeTab=1',
                'ajax=true'
            ],
            status = jQuery ("#status");
        status.css('display','inline');
        jQuery.ajax ('index.php', {
            data:     arguments.join ('&') + '&' + url,
            dataType: 'html',
            method:   'post'
        }).done (function (response) {
            jQuery("#ListViewContents-" + idTab).html(response);
            status.css('display','none');
        }).fail (function (jQueryResponse) {
            alert ('Se ha presentado un error inesperado. Intenta más tarde');
            console.err (jQueryResponse);
        });
    };

    var massDelete = function (obj, event, module, idTab) {
        var arguments       = [],
            confirmStatus   = true,
            excludedRecords = jQuery('#excludedRecords-' + idTab ),
            form            = jQuery('#massdelete-' + idTab),
            idList          = form.find('#idlist'),
            numOfRows       = form.find('#numOfRows').val(),
            searchurl       = form.find('#search_url').val(),
            selectOptions   = jQuery('#allselectedboxes-' + idTab),
            status          = jQuery ("#status"),
            viewId          = jQuery('#viewname-' + idTab).val(),
            idstring, skiprecords, count, countX, alertStr, url;

        if(selectOptions === 'all'){
            idList.val (selectOptions.val());
            idstring    = selectOptions.val();
            skiprecords = excludedRecords.val ().split (";");
            count       = skiprecords.length;
            if(count > 1){
                count = numOfRows - count + 1;
            } else {
                count = numOfRows;
            }
        } else {
            countX = selectOptions.val ().split (";");
            console.log(countX);
            count  = countX.length;
            if (count > 1) {
                idList.val (selectOptions.val());
                idstring = selectOptions.val();
            } else {
                alert(alert_arr.SELECT);
                event.preventDefault();
                return false;
            }
            //we have to decrese the count value by 1 because when we split with semicolon we will get one extra count
            count = count - 1;
        }
        if(count > maxMassOperationLimit) {
            if (!confirm (alert_arr.MORE_THAN_500)) {
                confirmStatus = false;
            }
        }
        if(confirmStatus){
            alertStr = alert_arr.DELETE + count +alert_arr.RECORDS;
            if (confirm (alertStr)) {
                status.css ('display','inline');
                url = "&excludedRecords=" + excludedRecords.val();
                arguments = [
                    'module=Users',
                    'return_module=' + encodeURIComponent (module),
                    'action=massdelete',
                    'idlist=' + idstring,
                    'idTab=' + idTab,
                    'viewname=' + viewId,
                    'isHomeTab=1',
                    'ajax=true'
                ];
                jQuery.ajax ('index.php', {
                    data:     arguments.join ('&'),
                    dataType: 'html',
                    method:   'post'
                }).done (function (response) {
                    try {
                        if (response !== 'ok') {
                            throw  response;
                        }
                        if (countX.length > 1) {
                            for (var k = 0; k < countX.length; k++) {
                                jQuery('#row_' + countX [k]).remove()
                            }
                        }
                        selectOptions.val('');
                    } catch (e) {
                        alert(e)
                    }
                }).fail (function (jQueryResponse) {
                    alert ('Se ha presentado un error inesperado. Intenta más tarde');
                    console.err (jQueryResponse);
                });
                status.css('display','none');
            } else {
                event.preventDefault();
                return false;
            }
        }
        event.preventDefault();
    };

    var searchCalendar = function (obj, idTab, module, tabName) {
        var calendarId = jQuery(obj).val (),
            content    = jQuery ('#' + tabName + '-' + idTab),
            arguments  = {
                'module':     module,
                'action':    'AjaxListViewUtils',
                'function':   tabName,
                'buttons':    '',
                'record':     calendarId,
                'hometabid':  idTab,
                'howusename': '',
                'Ajax':       true
            },
            status = jQuery ("#status");
        if (calendarId !== '') {
            status.css('display','inline');
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html(message.html);
                        status.css('display','none');
                    }
                }
                catch (e) {
                    alert(e);
                    status.css('display','none');
                    mainTab.find('#' + tabName + '-' + idTab).removeClass ('active').removeClass ('in');
                    mainTab.find('#ListViewHomeContents-' + idTab).addClass('in').addClass ('active');
                }
            });
        }
    };

    var searchKanban = function (obj, idTab, module, tabName) {
        var kanba = jQuery (obj),
            kanbaId    = kanba.val (),
            fieldName  = kanba.find ('option:selected').attr ('fieldname'),
            content    = jQuery ('#' + tabName + '-' + idTab),
            mainTab    = jQuery ('#ListViewContents-' + idTab),
            kanbanData = {'kanbanviewid': kanbaId, 'fieldname': fieldName},
            arguments  = {
                'module':    module,
                'action':    'AjaxListViewUtils',
                'function':  'VIEW-KANBAN',
                'kanban':    kanbanData,
                'hometabid':  idTab,
                'buttons':   '',
                'is_search': true,
                'Ajax':      true
            },
            status = jQuery ("#status");
        if ((kanbaId !== '') && (fieldName !== '')) {
            status.css('display','inline');
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html(message.html);
                        status.css('display','none');
                    }
                }
                catch (e) {
                    alert(e);
                    status.css('display','none');
                    mainTab.find('#' + tabName + '-' + idTab).removeClass ('active').removeClass ('in');
                    mainTab.find('#ListViewHomeContents-' + idTab).addClass('in').addClass ('active');
                }
            });

        }
    };

    var setDefaultIdView = function (idView, idAvtivity) {
        var btn     = jQuery ('#btn-quick-' + idAvtivity + '-' + idView);
        activityId  = idAvtivity;
        jQuery('button[id ^= btn-quick-' + idAvtivity + '-]').removeClass ('btn-primary').addClass('btn-default');
        if (btn.hasClass('btn-default')) {
            btn.removeClass('btn-default').addClass('btn-primary');
        }
    };

    var setFilter = function (obj, event) {
      var btnSelected = jQuery (obj),
          liSelected  = btnSelected.parent (),
          idActivity  = btnSelected.attr ('rel'),
          filterClass = btnSelected.attr ('data-filter'),
          toolBar     = jQuery ('#btn-toolbar-' + idActivity);

      if (filterClass === 'hidden') {
          availableFilter.forEach(function (item, index) {
              var filter = '.' + item + '-' + idActivity;
              if (!toolBar.find (filter).hasClass ('hide')) {
                  toolBar.find (filter).addClass('hide')
              }
          });
          liSelected.parent ().find ('li').removeClass ('active');
      } else {
          var filter = '.' + filterClass + '-' + idActivity;
          if (!toolBar.find (filter).hasClass ('hide')) {
              toolBar.find (filter).addClass('hide');
              liSelected.removeClass ('active');
          } else {
              toolBar.find (filter).removeClass ('hide');
              liSelected.addClass ('active');
          }
      }
      event.preventDefault ();
      event.stopPropagation ();
    };

    var setQuickView = function (obj, idView) {
        var btn = jQuery(obj),
            filters,
            arguments = [
                'module=' + encodeURIComponent (btn.data ('module-name')),
                'relmodule=' + encodeURIComponent (btn.data ('related-module')),
                'idTab=' + btn.data ('activity-id'),
                'action=ListView',
                'viewid=' + encodeURIComponent (idView),
                'Ajax=true'
            ];
        filters = getFilters (btn.data ('activity-id'));
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
                viewHtml = jQuery ('#list-view-container-' + btn.data ('activity-id')).find ('.main-box');

            viewHtml.find ('.main-box-body').remove ();
            viewHtml.find ('.btn-footer').remove ();
            viewHtml.append (pageBody).append (pageFooter);
        }).fail (function (jQueryResponse) {
            alert ('Se ha presentado un error inesperado. Intenta más tarde');
            console.err (jQueryResponse);
        });
        btn.parent().find('button').removeClass ('btn-primary').addClass('btn-default');
        btn.removeClass ('btn-default').addClass('btn-primary');
        if (activityId !== '') {
            jQuery('#btn-toolbar-' + btn.data ('activity-id')).find('select[id ^=viewname-home]').val(parseInt(idView))
        }
    };

    var setStdFilter = function (obj, idActivity) {
        var idView = jQuery(obj).val ();
        setDefaultIdView (idView, idActivity);
    };

    var showRecordCustomView = function (obj,module,parenttab) {
        var select = jQuery(obj),
            idTab  = select.data('record-tab-id'),
            viewname = select.val(),
            arguments = [
                'module=' + encodeURIComponent (module),
                'action=' + module + 'Ajax',
                'idTab=' + idTab,
                'file=ListView',
                'parenttab=' + parenttab,
                'viewname=' + viewname,
                'homeViewId=' + viewname,
                'isHomeTab=1',
                'ajax=true'
            ],
            status = jQuery ("#status");
        status.css('display','inline');
        jQuery.ajax ('index.php', {
            data:     arguments.join ('&'),
            dataType: 'html',
            method:   'post'
        }).done (function (response) {
            jQuery("#ListViewContents-" + idTab).html(response);
            status.css('display','none');
        }).fail (function (jQueryResponse) {
            alert ('Se ha presentado un error inesperado. Intenta más tarde');
            console.err (jQueryResponse);
        });

    };

    var toggleSelectListView = function (obj, relCheckName) {
        var checkAll = jQuery(obj),
            tabId    = checkAll.data('home-tab-id'),
            count    = jQuery ('#massdelete' + tabId).find ('#numOfRows').val (),
            selected     = jQuery('#allselectedboxes-' + tabId);
        jQuery('.home-tab-check-' + tabId).each(function () {
            jQuery(this).prop ("checked", checkAll.prop ("checked"));
            checkObject (this);
        })
    };

    window.HomeUtils = {
        activeTab:            activeTab,
        activeTaskTab:        activeTaskTab,
        checkObject:          checkObject,
        getListViewEntries:   getListViewEntries,
        massDelete:           massDelete,
        searchCalendar:       searchCalendar,
        searchKanban:         searchKanban,
        setDefaultIdView:     setDefaultIdView,
        setFilter:            setFilter,
        setQuickView:         setQuickView,
        setStdFilter:         setStdFilter,
        showRecordCustomView: showRecordCustomView,
        toggleSelectListView: toggleSelectListView
    };
} (jQuery));