<tr numrowtr="__NUM__" id="row_planned_activities-__ID__" class="tabla-field-row">
    <td class="" id="td_planned_activities_reported_task___ID___Campo1" style="vertical-align: top" width="auto" bgcolor="">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_tarea___ID___">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <input type="text" id="reported_task-display-__ID__" value=""
                               class="form-control input-readonly  b-right" readonly="readonly" placeholder="Seleccionar tarea">
                        <div class="input-group-addon" style="border:1px solid #dee2e6!important">
                            <a onclick="DailyReportUtils.openModal(this, event,'{$idDailyReport}')"
                               href="index.php?module=daily_report&action=AjaxEditViewUtils&function=DAILY_TASK_MATRIX&rowid=__ID__&Ajax=true" title="Seleccionar tarea">
                                <i class="fa fa-plus-circle"></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" id="reported_task-id-__ID__" name="planned_activities[reported_task_id][]"  value="" class="for-filter">
                    <input type="hidden" id="reported_task-module-__ID__" name="planned_activities[reported_task_module][]"  value="" class="for-filter">
                </div>
            </div>
            <div class="col-md-4 hide"></div>
        </div>
    </td>
    <td class="" id="td_planned_activities_task_progress_perc___ID___-Campo1" style="vertical-align: top" width="auto">
        <input autocomplete="off"
               placeholder="% de avance"
               onkeyup="DailyReportUtils.updateNumFields(this, '')"
                numrow="1"
               value="" name="planned_activities[task_progress_perc][]" id="task_progress_perc-__ID__"
               class="form-control percentvalidate" style="min-width:80px" type="text">
    </td>
    <td class="" id="td_planned_activities_informe_de_avance___ID___-Campo1" style="vertical-align: top"  width="auto">
        <textarea name="planned_activities[task_advanced_report][]" id="task_advanced_report-__ID__"
                  class="form-control daily-report-scroll tinymce-class">

        </textarea>
        <input type="hidden" id="task_advanced_report-id-__ID__" name="planned_activities[report_id][]" value="" class="for-filter">
    </td>
    <td class="" id="td_planned_activities_time_reported__ID___Campo1" style="vertical-align: top" width="auto">
        <input autocomplete="off"
               placeholder="Horas"
               onkeyup="DailyReportUtils.updateNumFields(this, '{$idDailyReport}')"
               numrow="1" value="" name="planned_activities[time_reported][]" id="time_reported-__ID__"
               class="form-control numericvalidate total-time" style="min-width:80px" type="text">
    </td>
    <td class="" id="td_planned_activities_Acciones_Campo__N__" style="vertical-align: top" width="6%" bgcolor=""
        align="center">
        <button type="button" class="btn btn-primary btn-xs"
                onclick="DailyReportUtils.moveRowUp (this, 'row_planned_activities-__ID__')">
            <i class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
                onclick="DailyReportUtils.moveRowDown (this, 'row_planned_activities-__ID__')">
            <i class="fa fa-arrow-down" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="DailyReportUtils.delRowToTable (this, 'row_planned_activities-__ID__', '{$idDailyReport}');">
            <i class="fa fa-trash-o"></i>
        </button>
    </td>
</tr>