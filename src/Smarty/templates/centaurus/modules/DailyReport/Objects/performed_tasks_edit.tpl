<tr id="row_unregistered-task-__ID__" data-row-id="__ID__" class="tabla-field-row" style="vertical-align: top">
    {* Situación   *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="reported_task_condition-__ID__" name="performed_tasks[reported_task_condition][]"
                onchange="DailyReportUtils.setUnregisteredActivity(this, '__ID__')" class="form-control">
                <option value="">Situación</option>
                <option value="PLANNED_UNREGISTERED">Planeada no registrada</option>
                <option value="UNEXPECTED">Imprevista</option>
            </select>
        </div>
    </td>
    {*  Trabajo  *}
    <td class="" id="td_planned_activities_reported_task___ID___Campo1" style="vertical-align: top">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_tarea___ID___">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <input type="text" id="planned_job-display-__ID__" value=""
                            class="form-control input-readonly  b-right" readonly="readonly"
                            placeholder="Seleccionar trabajo">
                        <div class="input-group-addon" style="border:1px solid #dee2e6!important">
                            <a onclick="DailyReportUtils.openModal(this, event,'{$idProgressJob}')"
                                href="index.php?module=daily_report&action=AjaxEditViewUtils&function=FETCH_JOBS&rowid=__ID__&Ajax=true"
                                title="Seleccionar trabajo">
                                <i class="fa fa-plus-circle"></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" id="planned_job-id-__ID__" name="performed_tasks[relatedcrmids][]" value=""
                        class="for-filter">
                    <input type="hidden" id="reported_task-module-__ID__" name="performed_tasks[reported_task_module][]"
                        value="orden_de_trabajo" class="for-filter">
                </div>
            </div>
            <div class="col-md-4 hide"></div>
        </div>
    </td>
    {*  Tarea  *}
    <td style="vertical-align: top">
        <div id="list-reported_task_title-__ID__" class="input-group hide" style="width: 100%;"></div>
        <div id="input-reported_task_title-__ID__" class="input-group" style="width: 100%;">
            <input type="text" id="reported_task_title-__ID__" placeholder="Tarea:"
                name="performed_tasks[reported_task][]" value="" class="form-control">
        </div>
    </td>
    {*  Tiempo estimado  *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="estimated_time_task-__ID__" placeholder="Horas"
                name="performed_tasks[estimated_time_task][]" value="" class="form-control"
                onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    {* Reporte de avance   *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="pc_task_advanced-__ID__" placeholder="% de avance"
                name="performed_tasks[pc_task_advanced][]" value="" class="form-control"
                onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    {* Reporte de avance   *}
    <td style="vertical-align: top">
        <div id="input-task_advanced_report-__ID__" class="input-group" style="width: 100%;">
            <textarea id="task_advanced_report-__ID__" name="performed_tasks[task_advanced_report][]"
                class="form-control" rows="2"></textarea>
        </div>
    </td>
    {*  Tiempo empleado  *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="time_reported-__ID__" name="performed_tasks[time_reported][]" placeholder="Horas"
                value="" class="form-control performed-tasks-total-time"
                onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    {*  Costo incurrido  *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="actual_cost-__ID__" name="performed_tasks[actual_cost][]" placeholder="Costo"
                value="" class="form-control numericvalidate"
                onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    {* Botones de acción   *}
    <td class="text-center" style="vertical-align: top">
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-primary btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowUp (this, 'tr-row-__ID__')">
                <i class="fa fa-arrow-up" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowDown (this, 'tr-row-__ID__')">
                <i class="fa fa-arrow-down" aria-hidden="true" style="font-size: 11px;"></i></button>
        </div>
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-warning btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-module="reported_task-module-__ID__" data-id="reported_task-id-__ID__"
                onclick="DailyReportUtils.uploadDoc (this, 'tr-row-__ID__')">
                <i class="fa fa-upload" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-template="#tbody-tasks-performed-" data-colspan="#planned_unregistered-colspan-template-"
                onclick="DailyReportUtils.delRowToTable (this, 'row_unregistered-task-__ID__', '{$idProgressJob}');">
                <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i></button>
        </div>
    </td>
</tr>