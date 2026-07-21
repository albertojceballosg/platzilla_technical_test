{math equation= rand() assign= "idKanbanDiagram"}
{if $KANBAN_BLOCKS neq NULL}
<div class="row" id="task-gantt-{$idGanttDiagram}">
    <link rel="stylesheet" href="themes/centaurus/draggable-kanban-board-ui/css/kanban.min.css">
    <style>
        .cd_kanban_board_block_item_title {
            font-size: 1.5rem !important;
        }
        .kanban-task-btns {
            float: left;
        }
        .kanban-task-btn {
            display: inline!important;
            text-decoration: none;
            color: #e7e7e7;
        }

    </style>
    <div class="col-md-12">
        <div id="kanban"></div>
    </div>
    {*<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>*}
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="themes/centaurus/draggable-kanban-board-ui/js/kanban.js"></script>
    <script type="text/javascript" src="themes/centaurus/draggable-kanban-board-ui/js/KanbanTaskUtils.js"></script>
    <script type="text/javascript" src="modules/Calendar/TaskViewModal.js"></script>
    <script>
        var relatedModule = '{$RELATED_MODULE}';
            kanbanData = {$KANBAN_BLOCKS};

        jQuery('#kanban').kanban({
            titles: ['Planeado', 'Pendiente', 'Pospuesto', 'Realizado'],
            colours: ['#00aaff','#ff921d','#00ff40','#ffe54b'],
            onReceive: function (e,ui) {
                var record      = jQuery (ui.item[0]).attr ('data-id'),
                    eventStatus = jQuery (e.target).attr ('data-block'),
                    taskModule  = jQuery (ui.item[0]).find ('.kanban-task-title').attr('rel'),
                    moduleName  = (taskModule !== 'NA') ? taskModule : encodeURIComponent (relatedModule),
                    arguments   = {
                        'module':   moduleName,
                        'action':   'AjaxDetailViewUtils',
                        'function': 'CHANGE-STATUS-TASK',
                        'taskId':    record,
                        'status':   eventStatus,
                        'Ajax':     'true'
                    };
                jQuery.post('index.php', arguments, function (data) {
                    try {
                        var message = JSON.parse(JSON.stringify(data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            alert('El estatus de la tarea han sido actualizada')
                        }
                    }
                    catch (e) {
                        alert (e);
                    }
                });
            },
            onChange: function (e,ui) {
                console.log(e);
                console.log(ui)
            },
            onSelect: function (e, ui) {

            },
            items: kanbanData
        });
    </script>
</div>
{else}
    <div class="alert alert-info">No hay tareas!  en el periodo</div>
{/if}
