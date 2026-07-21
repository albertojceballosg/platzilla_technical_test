(function (jQuery) {
    var activeGroup = '',
        moduleName  = '',
        lastGroupId = 0,
        btnTabs     = [
            'detail-view-btn-tab',
            'okr-view-btn-tab',
            'progress-plan-btn-tab',
            'strategies-initiatives-btn-tab',
            'summary-view-btn-tab'
        ],
        dataTable;

    // private method
    var drawChart = function () {
        var data = google.visualization.arrayToDataTable(dataTable);
        var options = {
            height: 300,
            width:  475,
            vAxis: {maxValue: 100},
            chartArea:{left:'10%',right:'10%',top:10,width:'80%',height:'80%'},
            bar: {groupWidth: "55%"},
            legend: { position: "none" },
            vAxes: {
                0: {title: '% aplicación'}
            }

        };
        var view = new google.visualization.DataView(data);
        view.setColumns([0, 1,
            { calc: "stringify",
                sourceColumn: 1,
                type: "string",
                role: "annotation" },
            2]);
        var chartTask = new google.visualization.ColumnChart(document.getElementById('columnchart_values'));
        //google.visualization.events.addListener(chartTask, 'ready', DailyMatrixUtls.taskHandler);
        chartTask.draw (view, options);


    };

    var setActiveButton = function (selectedBotton) {
        var idSelected = selectedBotton;
        btnTabs.forEach (function (element, index) {
            var btn = jQuery ('#' + element);
            if (element !== idSelected) {
                if (btn.hasClass('btn-primary')) {
                    btn.addClass('btn-default');
                    btn.removeClass('btn-primary');
                }
            }  else if (btn.hasClass('btn-default')) {
                btn.addClass('btn-primary');
                btn.removeClass('btn-default');
            }
        })
    };

    // public method
    var activeInitiativesTab = function (event, moduleName, idTab, recordId) {
        var arguments = {
                'module':   'action_plan',
                'action':   'AjaxActionPlanUtils',
                'flmodule':  moduleName,
                'function': 'STRATEGIES-INITIATIVES-VIEW',
                'tabid':    idTab,
                'record':   recordId,
                'Ajax':     true
            },
            content = jQuery ('#strategies-initiatives-' + idTab),
            mainTab = jQuery('#detal-view-group-tab');
        if ((content.contents ().length <= 3) && moduleName !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html (message.html);
                    }
                }
                catch (e) {
                    if (e === undefined) {
                        alert ('¡Uoops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error');
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    } else {
                        alert(e);
                    }

                }
            });
        }
    };

    var activeOKRTab = function (event, moduleName, idTab, recordId) {
        var arguments = {
            'module':   'action_plan',
                'action':   'AjaxActionPlanUtils',
                'flmodule':  moduleName,
                'function': 'OKR-VIEW',
                'tabid':    idTab,
                'record':   recordId,
                'Ajax':     true
            },
            content = jQuery ('#okr-plan-' + idTab),
            mainTab = jQuery('#detal-view-group-tab');
        if ((content.contents ().length <= 3) && moduleName !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify(data));
                    console.log(message);
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html (message.html);
                    }
                } catch (e) {
                    if (e === undefined) {
                        alert ('¡Uoops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error');
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    } else {
                        alert(e);
                    }
                }
            });
        }
    }

    var activeProgressTab = function (event, moduleName, idTab, recordId) {
        var arguments = {
                'module':   'action_plan',
                'action':   'AjaxActionPlanUtils',
                'flmodule':  moduleName,
                'function': 'PROGRESS-PLAN-VIEW',
                'tabid':    idTab,
                'record':   recordId,
                'Ajax':     true
            },
            content = jQuery ('#progress-plan-' + idTab),
            mainTab = jQuery('#detal-view-group-tab');
        if ((content.contents ().length <= 3) && moduleName !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html (message.html);
                    }
                }
                catch (e) {
                    if (e === undefined) {
                        alert('¡Uoooops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error');
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    } else {
                        alert(e);
                    }

                }
            });
        }
    };

    var activeSummaryTab = function (event, moduleName, idTab, recordId) {
        var arguments = {
                'module':   'action_plan',
                'action':   'AjaxActionPlanUtils',
                'flmodule':  moduleName,
                'function': 'SUMMARY-PLAN-VIEW',
                'tabid':    idTab,
                'record':   recordId,
                'Ajax':     true
            },
            content = jQuery ('#summary-plan-' + idTab),
            mainTab = jQuery('#detal-view-group-tab');
        if ((content.contents ().length <= 3) && moduleName !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html (message.html);
                    }
                }
                catch (e) {
                    if (e === undefined) {
                        alert('¡Uoooops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error');
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    } else {
                        alert(e);
                    }

                }
            });
        }
    };

    var activeTrakingTab = function (event, moduleName, idTab, recordId) {
        setActiveButton (btnTabs[0]);
        event.preventDefault ();
    };

    var addRowToTable = function (obj, tBody, idTable) {
        var row          = jQuery ('#' + tBody),
            btn          = jQuery (obj),
            sequence     = btn.attr ('data-sequence'),
            rowId        = Math.floor (Math.random() * 500) + 1,
            template     = jQuery ('#objetice-template-' + idTable).html ()
                .replace (/__ID__/g, rowId),
            taskRow = jQuery (template);

        if (sequence === '0') {
            row.empty();
        }

        row.append (taskRow);
        sequence = parseInt(sequence) + 1;
        btn.attr('data-sequence', sequence.toString());
    };

    var delRowToTable = function (buttonElement, tr, idTable) {
        var tBody = jQuery('#tbody-task-project-' + idTable),
            row   = jQuery ('#' + tr),
            rows  = row.parent(),
            trs  =  rows.find ('tr').length,
            rowClass = jQuery('.' + tr);

        if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
            return;
        }
        rowClass.remove ()
        jQuery (buttonElement).closest ('tr').remove ();
        if (trs === 1) {
            var template = jQuery ('#task-project-tr-' + idTable).html ();
            tBody.append (template);
        }
    };

    var initGuideline = function (data, t) {
        var columnColor = [
            '#3366CC',
            '#DC3912',
            '#FF9900',
            '#109618',
            '#990099',
            '#3B3EAC',
            '#0099C6',
            '#DD4477',
            '#66AA00',
            '#B82E2E',
            '#316395',
            '#994499',
            '#22AA99',
            '#AAAA11',
            '#6633CC',
            '#E67300',
            '#8B0707',
            '#329262',
            '#5574A6',
            '#3B3EAC'
        ], totalColumns = 0;
        Array.prototype.shuffle = function () {
            var i = this.length, j, temp;
            if ( i === 0 ) return this;
            while ( --i ) {
                j = Math.floor( Math.random() * ( i + 1 ) );
                temp = this[i];
                this[i] = this[j];
                this[j] = temp;
            }
            return this;
        };
        columnColor.shuffle ();
        dataTable    = Object.entries(JSON.parse (JSON.stringify(data)));
        totalColumns = dataTable.length;
        dataTable[0].push ({ role: "style" });

        for (var k = 1;k < totalColumns; k++) {
            dataTable[k].push (columnColor[k]);
        }

        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback (drawChart);
    };

    var moveRowDown = function (btn, tr, tr_table) {
        var rowToMove      = jQuery ('#' + tr),
            rowTableToMove = jQuery ('#' + tr_table),
            next           = rowToMove.next('tr.tabla-field-row');

        for (var i = 0; i < 3; i++) {
            next.after (rowToMove);
            next = rowToMove.next('tr.tabla-field-row');
            console.log(i + ' ' + next);
        }

        next = rowTableToMove.next('tr.tabla-field-row');
        for (var j = 0; j < 3; j++) {
            next.after (rowTableToMove);
            next = rowTableToMove.next('tr.tabla-field-row');
            console.log(j + ' ' + next);
        }
    };

    var moveRowUp = function (btn, tr, tr_table) {
        var rowToMove      = jQuery ('#' + tr),
            rowTableToMove = jQuery ('#' + tr_table),
            prev           = rowToMove.prev ('tr.tabla-field-row');
        //prev.before (rowToMove);
        for (var i = 0; i < 2; i++) {
            prev.before (rowToMove);
            prev = rowToMove.prev('tr.tabla-field-row');
        }

        prev = rowTableToMove.prev('tr.tabla-field-row');
        for (var j = 0; j < 2; j++) {
            prev.before (rowTableToMove);
            prev = rowTableToMove.prev('tr.tabla-field-row');
        }
    };

    var relatedKR = function (obj) {
        var arguments        = {},
            dummy            = jQuery(obj).attr('data-row-ids').split('@'),
            crmId            = jQuery (obj).val(),
            krTableContainer = jQuery('#kr-data-' + dummy[1]),
            fieldAverage     = jQuery ('#objetive_average-' + dummy[1]),
            row              = jQuery ('#tr-row-' + dummy[ 1 ]),
            tFoot            = jQuery ('#tfoot-' + dummy[ 0 ]),
            tFootBtn         = tFoot.find('button').eq (0);
        tFootBtn.attr ('disabled','disabled');
        tFootBtn.parent().find('span').eq(0).removeClass('hide');
        row.find('button').each(function (index, btn) {
            jQuery(btn).attr('disabled','disabled');
        });
        arguments = {
            'module':   'action_plan',
            'action':   'AjaxActionPlanUtils',
            'flmodule': 'business_objective',
            'function': 'RELATED-KR',
            'idtable':  dummy[ 0 ],
            'record':    crmId,
            'Ajax':     'true'
        };

        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    tFootBtn.removeAttr('disabled');
                    tFootBtn.parent().find('span').eq(0).addClass('hide');
                    row.find('button').each(function (index, btn) {
                        jQuery(btn).removeAttr('disabled');
                    });
                    krTableContainer.html(message.html);
                    fieldAverage.val(message.goal_progress);
                }
            } catch (e) {
                alert(e);
                tFootBtn.removeAttr('disabled');
                tFootBtn.parent().find('span').eq(0).addClass('hide');
                row.find('button').each(function (index, btn) {
                    jQuery(btn).removeAttr('disabled');
                });
            }
        });
        console.log(dummy);
        console.log (crmId)
    }

    window.ActionPlanUtls = {
        activeInitiativesTab: activeInitiativesTab,
        activeOKRTab:         activeOKRTab,
        activeProgressTab:    activeProgressTab,
        activeSummaryTab:     activeSummaryTab,
        activeTrakingTab:	  activeTrakingTab,
        addRowToTable:        addRowToTable,
        delRowToTable:  	  delRowToTable,
        initGuideline:        initGuideline,
        moveRowDown:	      moveRowDown,
        moveRowUp:	          moveRowUp,
        relatedKR:	          relatedKR
    };

    var onDocumentReadyHandler = function () {
        var activeTab   = jQuery ('#detal-view-group-tab').attr('data-tab');

        if (((activeTab !== 'detail') || (activeTab !== 'summary-plan')) && activeTab !== '') {
            if (activeTab === 'strategies-initiatives') {

            } else if (activeTab === 'progress-plan') {

            } else if (activeTab === 'detail') {

            } else if (activeTab === 'summary-plan') {

            }
        } else {
            jQuery('#summary-view-btn-tab').trigger("click");
        }
    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));