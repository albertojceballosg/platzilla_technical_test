{assign var="activty" value=$plannedTask->getActivity()}
{assign var="report" value=$plannedTask->getReport()}
<tr numrowtr="{$key}" id="row_planned_activities-{$idRow}" class="tabla-field-row">
    {*  Trabajo  *}
    <td class="" id="td_planned_activities_reported_task_{$idRow}_Campo1" style="vertical-align: top">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_tarea_{$idRow}_">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <input type="text" id="planned_job-display-{$idRow}" value="{$activty->getRelatedTitle ()}"
                            class="form-control input-readonly  b-right" readonly="readonly"
                            placeholder="Seleccionar trabajo">
                        <div class="input-group-addon" style="border:1px solid #dee2e6!important">
                            <a onclick="DailyReportUtils.openModal(this, event,'{$idProgressJob}')"
                                href="index.php?module=daily_report&action=AjaxEditViewUtils&function=FETCH_JOBS&rowid={$idRow}&Ajax=true"
                                title="Seleccionar trabajo">
                                <i class="fa fa-plus-circle"></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" id="planned_job-id-{$idRow}" name="planned_tasks[crm_job][]"
                        value="{$activty->getRelatedId()}" class="for-filter">
                    <input type="hidden" id="planned_job-module-{$idRow}" name="planned_tasks[reported_task_module][]"
                        value="orden_de_trabajo" class="for-filter">
                </div>
            </div>
            <div class="col-md-4 hide"></div>
        </div>
    </td>
    {*  Tarea  *}
    <td class="" id="td_planned_activities_reported_task_{$idRow}_Campo1" style="vertical-align: top">
        <select class="form-control" id="planned_task_list-{$idRow}"
            onchange="DailyReportUtils.getReportsTask(this,'{$idRow}', '{$idProgressJob}');"
            name="planned_tasks[reported_task_id][]">
            {if $activty->relatedTasks neq NULL}
                <option value="">Seleccione una tarea</option>
                {foreach $activty->relatedTasks as $taskId => $task}
                    <option value="{$taskId}" {if $activty->getActivityId() eq $taskId} selected {/if}>{$task}</option>
                {/foreach}
            {else}
                <option value="">Tarea no encontrada</option>
            {/if}
        </select>
    </td>
    {*  % de avance  *}
    <td class="" id="td_planned_activities_task_progress_perc_{$idRow}_-Campo1" style="vertical-align: top">
        <input autocomplete="off" placeholder="% de avance" onkeyup="DailyReportUtils.updateNumFields(this, '')"
            numrow="1" value="{if $activty->getProgress () neq NULL}{$activty->getProgress ()}{else}0{/if}"
            name="planned_tasks[task_progress_perc][]" id="task_progress_perc-{$idRow}"
            class="form-control percentvalidate" style="min-width:80px" type="text">
    </td>
    {*  Reporte de avance  *}
    <td class="" id="td_planned_activities_informe_de_avance_{$idRow}_-Campo1" style="vertical-align: top">
        <textarea name="planned_tasks[task_advanced_report][]" id="task_advanced_report-{$idRow}"
            class="form-control daily-report-scroll tinymce-class edit-tinyMce">{if $report neq NULL}
                                            {foreach $report as $objReport}{$objReport->getReport ()}
                                            {/foreach}
            {/if}</textarea>
        <input type="hidden" id="task_advanced_report-id-{$idRow}" name="planned_tasks[report_id][]"
            value="{$plannedTask->getReportIds()}" class="for-filter">
        <a href="javascript:void(0);"
            onclick="DailyReportUtils.uploadTaskEvidence(this, '{$idRow}', 'planned_task_list-{$idRow}')"
            style="color: #28a745; font-size: 11px; display: block; margin-top: 3px;">
            <i class="fa fa-plus" aria-hidden="true"></i> Cargar evidencias
        </a>
        <div id="evidence-list-{$idRow}" class="evidence-list" style="font-size: 11px; margin-top: 3px;">
            {if $activty->attachments neq NULL}
                {foreach $activty->attachments as $attachment}
                    <div class="evidence-item" style="color: #333333;">
                        <i class="fa fa-file" aria-hidden="true"></i> {$attachment.name}
                    </div>
                {/foreach}
            {/if}
        </div>
    </td>
    {*  tiempo empleado  *}
    <td class="" id="td_planned_activities_time_reported{$idRow}_Campo1" style="vertical-align: top">
        <input autocomplete="off" placeholder="Horas"
            onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')" numrow="1"
            value="{if $plannedTask->getDurationTime() neq NULL}{$plannedTask->getDurationTime()}{else}0{/if}"
            name="planned_tasks[time_reported][]" id="time_reported-{$idRow}"
            class="form-control numericvalidate planned-tasks-total-time" style="min-width:80px" type="text">
    </td>
    {*  costo incurrido  *}
    <td class="" id="td_planned_activities_actual_cost{$idRow}_Campo1" style="vertical-align: top">
        <input autocomplete="off" placeholder="Costo"
            onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')" numrow="1"
            value="{if $plannedTask->getActualCost() neq NULL}{$plannedTask->getActualCost()}{else}0{/if}"
            name="planned_tasks[actual_cost][]" id="actual_cost-{$idRow}" class="form-control numericvalidate"
            style="min-width:80px" type="text">
    </td>
    {*  botones de acción  *}
    <td class="" id="td_planned_activities_Acciones_Campo__N__" style="vertical-align: top; text-align: center">
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
                data-module="planned_job-module-{$idRow}" data-id="planned_job-id-{$idRow}"
                onclick="DailyReportUtils.uploadDoc (this, 'row_planned_activities-{$idRow}')">
                <i class="fa fa-upload" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-template="#tbody-planned-tasks-" data-colspan="#planned_activities-colspan-template-"
                onclick="DailyReportUtils.delRowToTable (this, 'row_planned_activities-{$idRow}', '{$idProgressJob}');">
                <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
        </div>
    </td>
</tr>