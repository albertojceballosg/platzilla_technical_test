<tr numrowtr="{$key}" id="row_planned_activities-{$idRow}" class="tabla-field-row">
    {* Acciones *}
    <td class="" id="td_planned_activities_reported_task_{$idRow}_Campo1" style="vertical-align: top">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_tarea{$idRow}">
                    <div class="input-group col-xs-12 {if $plannedTask->getActivity()->getActivityCondition() neq 'PLANNED_AND_RECORDED'}has-error{/if}"
                        style="width: 100%;">
                        <textarea id="reported_task-display-{$idRow}"
                            title="{if $plannedTask->getActivity()->getActivityCondition() eq 'PLANNED_AND_RECORDED'}Actividad Planeada{else}Actividad no planeada{/if}"
                            class="form-control input-readonly  b-right" readonly="readonly"
                            placeholder="Seleccionar tarea">{if $plannedTask->getActivity()->getSubject() neq NULL}{$plannedTask->getActivity()->getSubject()}{else}Sin descripción{/if}</textarea>
                        <div class="input-group-addon" style="border:1px solid #dee2e6!important">
                            <a onclick="DailyReportUtils.openModal(this, event,'{$idProgressJob}')"
                                href="index.php?module=daily_report&action=AjaxEditViewUtils&function=DAILY_TASK_MATRIX&rowid={$idRow}&Ajax=true"
                                title="Seleccionar tarea">
                                <i class="fa fa-plus-circle"></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" id="reported_task-id-{$idRow}" name="planned_actions[reported_task_id][]"
                        value="{$plannedTask->getActivity()->getActivityId()}" class="for-filter">
                    <input type="hidden" id="reported_task-module-{$idRow}"
                        name="planned_actions[reported_task_module][]"
                        value="{$plannedTask->getActivity()->getRelatedModule()}" class="for-filter">
                </div>
            </div>
            <div class="col-md-4 hide"></div>
        </div>
    </td>
    {* % avance  *}
    <td class="" id="td_planned_activities_task_progress_perc_{$idRow}_Campo1" style="vertical-align: top">
        <input autocomplete="off" onkeyup="DailyReportUtils.updateNumFields(this, '')" numrow="1"
            value="{$plannedTask->getProgress()}" name="planned_actions[task_progress_perc][]"
            id="task_progress_perc-{$idRow}" class="form-control percentvalidate" style="min-width:80px" type="text">
    </td>
    {* Reporte  de avance *}
    <td class="" id="td_planned_activities_informe_de_avance{$idRow}-Campo1" style="vertical-align: top">
        <textarea name="planned_actions[task_advanced_report][]" id="task_advanced_report-{$idRow}"
            class="form-control daily-report-scroll tinymce-class edit-tinyMce">
            {foreach $plannedTask->getReport() as $report}
                        {$report->getReport()}
            {/foreach}
        </textarea>
        <input type="hidden" id="task_advanced_report-id-{$idRow}" name="planned_actions[report_id][]"
            value="{$plannedTask->getReportIds()}" class="for-filter">
        <a href="javascript:void(0);"
            onclick="DailyReportUtils.uploadTaskEvidence(this, '{$idRow}', 'reported_task-id-{$idRow}')"
            style="color: #28a745; font-size: 11px; display: block; margin-top: 3px;">
            <i class="fa fa-plus" aria-hidden="true"></i> Cargar evidencias
        </a>
    </td>
    {* tiempo *}
    <td class="" id="td_planned_activities_time_reported{$idRow}_Campo1" style="vertical-align: top">
        <input autocomplete="off" onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')" numrow="1"
            value="{$plannedTask->getDurationTime()}" name="planned_actions[time_reported][]"
            id="time_reported-{$idRow}" class="form-control numericvalidate planned-action-total-time"
            style="min-width:80px" type="text">
    </td>
    {* Costo incurrido *}
    <td class="" id="td_planned_activities_actual_cost{$idRow}_Campo1" style="vertical-align: top">
        <input autocomplete="off" placeholder="Costo"
            onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')" numrow="1"
            value="{if $plannedTask->getActualCost() neq NULL}{$plannedTask->getActualCost()}{else}0{/if}"
            name="planned_actions[actual_cost][]" id="actual_cost-{$idRow}" class="form-control numericvalidate"
            style="min-width:80px" type="text">
    </td>
    {* Acciones *}
    <td class="" id="td_planned_activities_Acciones_Campo{$key}" style="vertical-align: top; text-align: center">
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-primary btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowUp (this, 'row_planned_activities-{$idRow}')">
                <i class="fa fa-arrow-up" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowDown (this, 'row_planned_activities-{$idRow}')">
                <i class="fa fa-arrow-down" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
        </div>
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-warning btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-module="reported_task-module-{$idRow}" data-id="reported_task-id-{$idRow}"
                onclick="DailyReportUtils.uploadDoc (this, 'row_planned_activities-{$idRow}')">
                <i class="fa fa-upload" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.delRowToTable (this, 'row_planned_activities-{$idRow}', '{$idProgressJob}');">
                <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
        </div>
    </td>
</tr>