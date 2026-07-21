{extends file='modules/DailyReport/base/ProgressReportsWorkLayout.tpl'}
{* job report *}
{assign var='numFormat' value=$NUMBERING_FORMAT}
{block name="thead-job-report"}
    <tr>
        <td colspan="8" style="text-align: left; background-color:#f9f8f7">
            <strong>Reporte de avance global de trabajo</strong>
        </td>
    </tr>
    <tr class="border" style="vertical-align: top">
        <td style="width:22%;vertical-align: top;"><span style="">Trabajo que se reporta</span></td>
        <td style="width:9.5%;vertical-align: top;"><span style="">Unidades estimadas</span></td>
        <td style="width:9.5%;vertical-align: top;"><span style="">(%) de avance anterior</span></td>
        <td style="width:22%;vertical-align: top;"><span style="">Reporte de avance del trabajo</span></td>
        <td style="width:9.5%;vertical-align: top;"><span style="">Unidades usadas</span>
        </td>
        <td style="width:9.5%;vertical-align: top;"><span style="">% avance total alcanzado con el reporte</span></td>
        <td style="width:9.5%;vertical-align: top;"><span style="">Costo incurrido en el avance</span></td>
        <td class="text-center" style="width:8%; vertical-align: center;">&nbsp;</td>
    </tr>
{/block}
{block name="tbody-job-report"}
    <tr style="vertical-align: top">
        <td colspan="8"></td>
    </tr>
{/block}
{block name="summary-job-report-row"}
    <tr id="summary-row-{$idProgressJob}" style="vertical-align: top">
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text" id="total_estimated_time-{$idProgressJob}" name="report_job[summary_estimated]"
                value="{$NUMBERING_HELPER->getDefaultValue ()}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text" id="total_time_reported-{$idProgressJob}" name="report_job[summary_used]"
                value="{$NUMBERING_HELPER->getDefaultValue ()}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text" id="total_actual_cost-{$idProgressJob}" name="report_job[summary_cost]"
                value="{$NUMBERING_HELPER->getDefaultValue ()}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}
{block name="add-job-report-row"}
    <tr>
        <td colspan="8" class="text-center">
            <button {if $MODE eq 'edit'}disabled{/if} type="button" class="btn btn-primary" data-sequence="0"
                data-template="row-job-report-template-{$idProgressJob}"
                onclick="DailyReportUtils.addRowToTable (this, 'tbody-job-report-{$idProgressJob}', '{$idProgressJob}');">
                <i class="fa fa-plus"></i></button>
        </td>
    </tr>
{/block}

{* define variables of planned task *}
{assign var='workModule' value='orden_de_trabajo'}
{if $DAILY_REPORTS neq NULL}
    {assign var="dailyReportPlanned" value=$DAILY_REPORTS}
    {assign var="totalPlanned" value=count($dailyReportPlanned)}
    {assign var="totalTime" value=null}
    {assign var="totalTimeUnplanned" value=null}
{else}
    {assign var="dailyReportPlanned" value=null}
    {assign var="totalPlanned" value=0}
    {assign var="totalTimeUnplanned" value=null}
{/if}
{* Planned tasks *}
{block name="thead-planned-tasks"}
    <tr>
        <td colspan="7" style="text-align: left; background-color:#f9f8f7"><strong>Tareas planeadas</strong></td>
    </tr>
    <tr style="vertical-align: top">
        <td style="width: 20%"><span style="">Trabajo</span></td>
        <td style="width: 21.5%"><span style="">Tarea</span></td>
        <td style="width: 9.5%"><span style="">(%) Avance de la tarea</span></td>
        <td style="width: 22%"><span style="">Reporte de avance</span></td>
        <td style="width: 9.6%"><span style="">Unidades empleadas</span></td>
        <td style="width: 9.5%"><span style="">Costo incurrido</span></td>
        <td class="text-center" style="width: 8%;">&nbsp;</td>
    </tr>
{/block}
{block name="tbody-planned-tasks"}
    {if $dailyReportPlanned neq NULL}
        {foreach $dailyReportPlanned as $key => $plannedTask}
            {if empty($plannedTask->getActivity())}{continue}{/if}
            {math equation= rand() assign= "idRow"}
            {if $plannedTask->getActivity()->getRelatedModule() neq $workModule}
                {continue}
            {/if}
            {include file='modules/DailyReport/Objects/planned_tasks_row.tpl'}
        {/foreach}
    {else}
        <tr>
            <td colspan="6" style="text-align: center">
                <p class="text-center">No hay tareas planeadas</p>
            </td>
        </tr>
    {/if}
{/block}
{block name="summary-planned-tasks-row"}
    <tr id="summary-row-{$idProgressJob}" valign="top">
        <td colspan="4">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idProgressJob}">
            <input type="text" id="planned-tasks-total-time-{$idProgressJob}" name="planned_tasks[summaryRow][]"
                rel="SUM_COLUMN"
                value="{if $totalTime neq NULL} {$NUMBERING_HELPER->setNumberFormat ($totalTime)} {else}{$NUMBERING_HELPER->getDefaultValue ()}{/if}"
                class="form-control" readonly="">
        </td>
        <td id="td-cost_reported-{$idProgressJob}">
            <input type="text" id="planned-tasks-total-cost-{$idProgressJob}" name="planned_tasks[summaryCost][]"
                rel="SUM_COLUMN" value="{$NUMBERING_HELPER->getDefaultValue ()}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}
{block name="add-planned-tasks-row"}
    <tr>
        <td colspan="5" class="text-center">
            <button type="button" data-id-linkage="{$idProgressJob}" class="btn btn-primary" data-sequence="{$totalPlanned}"
                data-template="planned_activities-template-{$idProgressJob}"
                onclick="DailyReportUtils.addRowToTable (this, 'tbody-planned-tasks-{$idProgressJob}', '{$idProgressJob}');">
                <i class="fa fa-plus"></i></button>

        </td>
    </tr>
{/block}

{* Tasks performed and not previously recorded *}
{block name="thead-tasks-performed"}
    <tr>
        <td colspan="8" style="text-align: left; background-color:#f9f8f7">
            <strong>Tareas realizadas y no registradas previamente</strong>
        </td>
    </tr>
    <tr style="vertical-align: top">
        <td style="vertical-align:middle;width: 10%"><span>Situación</span></td>
        <td style="vertical-align:middle; width: 13%"><span>Trabajo</span></td>
        <td style="vertical-align:middle; width: 13%"><span>Tarea</span></td>
        <td style="vertical-align:middle; width: 9.5%"><span>Unidades estimadas</span></td>
        <td style="vertical-align:middle; width: 9.5%"><span>(%) avance</span></td>
        <td style="vertical-align:middle; width: 18%"><span>Reporte de avance</span></td>
        <td style="vertical-align:middle; width: 9.5%"><span>Unidades empleadas</span></td>
        <td style="vertical-align:middle; width: 9.5%"><span>Costo incurrido</span></td>
        <td style="width: 8%">&nbsp;</td>
    </tr>
{/block}
{block name="tbody-tasks-performed"}
    <tr>
        <td colspan="8" style="text-align: center">&nbsp;</td>
    </tr>
{/block}
{block name="summary-tasks-performed-row"}
    <tr id="summary-row-{$idProgressJob}" valign="top">
        <td colspan="6">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idProgressJob}">
            <input type="text" id="performed-tasks-total-time-{$idProgressJob}" name="performed_tasks[summaryRow][]"
                rel="SUM_COLUMN" value="{$NUMBERING_HELPER->getDefaultValue ()}" class="form-control" readonly="">
        </td>
        <td id="td-cost_reported-{$idProgressJob}">
            <input type="text" id="performed-tasks-total-cost-{$idProgressJob}" name="performed_tasks[summaryCost][]"
                rel="SUM_COLUMN" value="{$NUMBERING_HELPER->getDefaultValue ()}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}
{block name="add-tasks-performed-row"}
    <tr>
        <td colspan="8" class="text-center">
            <button type="button" data-id-linkage="{$idProgressJob}" class="btn btn-primary" data-sequence="0"
                data-template="planned_unregistered-template-{$idProgressJob}"
                onclick="DailyReportUtils.addRowToTable (this, 'tbody-tasks-performed-{$idProgressJob}', '{$idProgressJob}');">
                <i class="fa fa-plus"></i>
            </button>
        </td>
    </tr>
{/block}

{* Tasks performed and previously recorded *}
{block name="script_template"}
    <script type="text/html" id="row-job-report-template-{$idProgressJob}">
        {include file='modules/DailyReport/Objects/row-job-report-template.tpl'}
    </script>
    <script type="text/html" id="tbody-job-report-colspan-template-{$idProgressJob}">
        <tr style="vertical-align: top">
            <td colspan="8"></td>
        </tr>
    </script>
    <script type="text/html" id="planned_activities-template-{$idProgressJob}">
        {include file='modules/DailyReport/Objects/planned_tasks_edit.tpl'}
    </script>
    <script type="text/html" id="planned_activities-colspan-template-{$idProgressJob}">
        <tr style="vertical-align: top">
            <td colspan="8" style="text-align: center"></td>
        </tr>
    </script>
    <script type="text/html" id="planned_unregistered-template-{$idProgressJob}">
        {include file='modules/DailyReport/Objects/performed_tasks_edit.tpl'}
        {*include file='modules/DailyReport/planned_unregistered_template.tpl'*}
    </script>
    <script type="text/html" id="planned_unregistered-colspan-template-{$idProgressJob}">
        <tr>
            <td colspan="5" style="text-align: center"></td>
        </tr>
    </script>
{/block}