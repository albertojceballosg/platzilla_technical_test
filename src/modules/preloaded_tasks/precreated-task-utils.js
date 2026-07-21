(function (jQuery) {
    // Private variables
    var modal             = null,
        totalFilterGroups = -1;

    // Private functions


    //public variable
    var statusChange = '';
    //public method
    var changeStatusArea = function (obj, id) {
        var btn       = jQuery (obj),
            faClass   = btn.find('i').eq(0),
            record    = btn.attr ('data-record'),
            status    = btn.attr ('data-status'),
            stColumn  = jQuery ('#area-status-' + record),
            arguments = {
                'module':   'preloaded_tasks',
                'action':   'AjaxPrecreatedTasks',
                'record':   record,
                'function': 'CHANGE-STATUS-AREA',
                'status':   status,
                'Ajax':     'true'
            };
        btn.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    faClass.removeClass('fa-check-square-o');
                    faClass.removeClass('fa-square-o');
                    btn.attr ('data-status', message.html[0]);
                    stColumn.html (message.html[1]);
                    btn.attr('title', message.html[2]);
                    faClass.addClass(message.html[3]);
                    btn.removeAttr('disabled');
                }
            }
            catch (e) {
                alert(e);
                btn.removeAttr('disabled');
            }
        });
    };

    var changeStatusTask = function (obj, id) {
        var btn       = jQuery (obj),
            faClass   = btn.find('i').eq(0),
            record    = btn.attr ('data-record'),
            status    = btn.attr ('data-status'),
            stColumn  = jQuery ('#task-status-' + record),
            arguments = {
                'module':   'preloaded_tasks',
                'action':   'AjaxPrecreatedTasks',
                'record':   record,
                'function': 'CHANGE-STATUS-TASK',
                'status':   status,
                'Ajax':     'true'
            };
        btn.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    faClass.removeClass('fa-check-square-o');
                    faClass.removeClass('fa-square-o');
                    btn.attr ('data-status', message.html[0]);
                    stColumn.html(message.html[1]);
                    btn.attr('title', message.html[2]);
                    faClass.addClass(message.html[3]);
                    btn.removeAttr('disabled');
                }
            }
            catch (e) {
                alert(e);
                btn.removeAttr('disabled');
            }
        });
    };

    var deleteTask = function (obj, id) {
        var btn       = jQuery (obj),
            record    = btn.attr ('data-record'),
            row       = jQuery ("[id$= '-" + record + "-" + id + "']"),
            arguments = {
                'module':   'preloaded_tasks',
                'action':   'AjaxPrecreatedTasks',
                'record':   record,
                'function': 'DELETE-TASK',
                'Ajax':     'true'
            };
        console.log(row);
        if (! confirm('Eliminar la tarea precargada seleccionada?')) {
            return
        }
        btn.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    row.remove()
                }
            }
            catch (e) {
                alert(e);
                btn.removeAttr('disabled');
            }
        });

    };

    var filterArea = function (obj, id) {
        var area      = jQuery (obj),
            tBody     = jQuery('#task-panel-table-' + id),
            searchRow = jQuery ('#search-task-' + id),
            module    = searchRow.find ('select').eq (0).val (),
            searchId  = '';

        if (module === '' && area.val() === '') {
            tBody.find('tr').each(function(index, tr) {
                jQuery(tr).show()
            });
        } else {
            if (module !== '') {
                searchId  = 'task-row-' + module + '-' + area.val()
            } else {
                searchId  = '-' + area.val()
            }
            tBody.find('tr').each(function(index, tr) {
                var trId = jQuery(tr).attr('id');
                if (trId.search(searchId) === -1) {
                    jQuery(tr).hide()
                } else {
                    jQuery(tr).show()
                }

            });
        }
    };

    var filterModules = function (obj, id) {
        var module    = jQuery (obj),
            tBody     = jQuery('#task-panel-table-' + id),
            searchRow = jQuery ('#search-task-' + id),
            area      = searchRow.find ('select').eq (1).val (),
            searchId  = 'task-row-' + module.val() + '-' + area;
        if (module.val() === '' && area === '') {
            tBody.find('tr').each(function(index, tr) {
                jQuery(tr).show()
            });
        } else {
            tBody.find('tr').each(function(index, tr) {
                var trId = jQuery(tr).attr('id');
                if (trId.search(searchId) === -1) {
                    jQuery(tr).hide()
                } else {
                    jQuery(tr).show()
                }

            });
        }
    };

    var selectPreTask = function (e, idTask, id) {
        var rowId       = jQuery ('#row-' + id).val(),
            taskCreate  = jQuery ('#taskname-' + id),
            tabTasks    = jQuery ('#main-tab-task-' + id),
            taskPreName = jQuery ('#task-name-' + idTask).val (),
            taskPreDes  = jQuery ('#task-des-' + idTask).val (),
            taskTitle   = jQuery('#task_title-' + rowId);

        if (rowId === '0') {
        taskCreate.val (taskPreName + '; '+ taskPreDes);
        tabTasks.trigger ('click');
        } else {
            jQuery ('#task-' + rowId).val(taskPreDes);
            if (taskTitle !== undefined) {
                taskTitle.val(taskPreName);
            }
        }

        e.preventDefault ();
        // Cerrar la modal de modelos de tarea automáticamente
        var $modal = jQuery('.modal:visible');
        if ($modal.length) {
            $modal.modal('hide');
        }
    };
    
    var init = function (moduleName, id) {
        var module    = moduleName,
            tBody     = jQuery('#task-panel-table-' + id),
            searchRow = jQuery ('#search-task-' + id),
            area      = searchRow.find ('select').eq (1).val (),
            searchId  = 'task-row-' + module + '-' + area;

        if (module === '' && area === '') {
            tBody.find('tr').each(function(index, tr) {
                jQuery(tr).show()
            });
        } else {
            tBody.find('tr').each(function(index, tr) {
                var trId = jQuery(tr).attr('id');
                if (trId.search(searchId) === -1) {
                    jQuery(tr).hide()
                } else {
                    jQuery(tr).show()
                }
            });
        }
        jQuery('#formodule-' + id).val (module);
    };

    window.PreCreatedTasksUtils = {
        changeStatusArea: changeStatusArea,
        changeStatusTask: changeStatusTask,
        deleteTask:       deleteTask,
        filterArea:       filterArea,
        filterModules:    filterModules,
        init:             init,
        selectPreTask:    selectPreTask,
    };

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));