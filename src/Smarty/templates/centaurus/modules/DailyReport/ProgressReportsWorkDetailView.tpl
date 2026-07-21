{extends file='modules/DailyReport/base/ProgressReportsWorkLayout.tpl'}
{* define variables of the job report *}
{assign var='summaryEstimated' value=0}
{assign var='summaryUsed' value=0}
{assign var='summaryCost' value=0}
{assign var='workModule' value='orden_de_trabajo'}
{assign var='numFormat' value=$NUMBERING_HELPER->getNumberFormat()}
{* job report *}
{block name="thead-job-report"}
    <tr>
        <td colspan="8" style="text-align: left; background-color:#f9f8f7">
            <strong>Reporte de avance global de trabajo</strong>
        </td>
    </tr>
    <tr class="border" style="vertical-align: top">
        <td style="width:22%;vertical-align: top;"><span style="">Trabajo que se reporta</span></td>
        <td style="width:9%;vertical-align: top;"><span style="">Unidades estimadas</span></td>
        <td style="width:9%;vertical-align: top;"><span style="">(%) de avance anterior</span></td>
        <td style="width:22%;vertical-align: top;"><span style="">Reporte de avance del trabajo</span></td>
        <td style="width:9%;vertical-align: top;"><span style="">Unidades usadas</span>
        </td>
        <td style="width:9%;vertical-align: top;"><span style="">Costo incurrido</span></td>
        <td style="width:9%;vertical-align: top;"><span style="">% avance total alcanzado con el reporte</span></td>
        <td class="text-center" style="width:5%; vertical-align: center;"><span>Evidencias</span></td>
    </tr>
{/block}
{block name="tbody-job-report"}
    {if $GLOBAL_REPORT neq NULL}
        {foreach $GLOBAL_REPORT as $report}
            {math equation= rand() assign= "idRow"}
            {include file='modules/DailyReport/Objects/global_work_view.tpl'}
            {$summaryEstimated = ($summaryEstimated + $report['sum_estimated_time'])}
            {$summaryUsed = ($summaryUsed + $report['sum_duration_time'])}
            {if isset($report['actual_cost'])}{$summaryCost = ($summaryCost + $NUMBERING_HELPER->setSaveNumberFormat($report['actual_cost']))}{/if}
        {/foreach}
    {else}
        <tr style="vertical-align: top">
            <td colspan="8">
                <p class="text-center">No hay reportes de avance global</p>
            </td>
        </tr>
    {/if}
{/block}
{block name="summary-job-report-row"}
    <tr id="summary-row-{$idProgressJob}" style="vertical-align: top">
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text" id="total_time_reported-27368"
                value="{$NUMBERING_HELPER->setNumberFormat ($summaryEstimated)}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text" id="total_time_reported-27368"
                value="{if $summaryUsed neq NULL} {$NUMBERING_HELPER->setNumberFormat ($summaryUsed)} {elseif ($numFormat eq 'EUROPEAN_FORMAT')} 0,00 {else} 0.00 {/if}"
                class="form-control" readonly="">
        </td>
        <td class="text-center">
            <input type="text" id="total_cost_reported"
                value="{if $summaryCost neq NULL} {$NUMBERING_HELPER->setNumberFormat ($summaryCost)} {else}{$NUMBERING_HELPER->getDefaultValue ()}{/if}"
                class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}

{* define variables of planned task *}
{if $DAILY_REPORTS neq NULL}
    {assign var="dailyReportPlanned" value=$DAILY_REPORTS}
    {assign var="totalPlanned" value=count($dailyReportPlanned)}
    {assign var="totalTime" value=null}
    {assign var="totalCost" value=null}
    {assign var="totalTimeUnplanned" value=null}
    {assign var="totalCostUnplanned" value=null}
{else}
    {assign var="dailyReportPlanned" value=null}
    {assign var="totalPlanned" value=null}
    {assign var="totalCost" value=null}
    {assign var="totalTimeUnplanned" value=null}
    {assign var="totalCostUnplanned" value=null}
{/if}
{assign var="hasTasksPerformed" value=null}
{assign var="hasTaskPlanned" value=null}
{* Planned tasks *}
{block name="thead-planned-tasks"}
    <tr>
        <td colspan="7" style="text-align: left; background-color:#f9f8f7"><strong>Tareas planeadas</strong></td>
    </tr>
    <tr style="vertical-align: top">
        <td style="width: 10%"><span style="">Trabajo</span></td>
        <td style="width: 13%"><span style="">Tarea</span></td>
        <td style="width: 13%"><span style="">(%) Avance de la tarea</span></td>
        <td style="width: 27%"><span style="">Reporte de avance</span></td>
        <td style="width: 9.5%"><span style="">Unidades empleadas</span></td>
        <td style="width: 9.5%"><span style="">Costo incurrido</span></td>
        <td class="text-center" style="width: 8%;"><span>Evidencias</span></td>
    </tr>
{/block}
{block name="tbody-planned-tasks"}
    {if $dailyReportPlanned neq NULL}
        {*$dailyReportPlanned|var_dump*}
        {foreach $dailyReportPlanned as $key => $plannedTask}
            {math equation= rand() assign= "idRow"}
            {if empty($plannedTask->getActivity())}{continue}{/if}

            {if
                            (!empty($plannedTask->getActivity()->getActivityCondition()) &&
                            $plannedTask->getActivity()->getActivityCondition() neq 'PLANNED_AND_RECORDED') ||
                            ($plannedTask->getActivity()->getRelatedModule() neq $workModule)
                        }
            {continue}
        {/if}
        {include file='modules/DailyReport/Objects/planned_tasks_view.tpl'}
        {assign var="totalTime" value=($totalTime + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getDurationTime()))}
        {assign var="totalCost" value=($totalCost + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getActualCost()))}
        {assign var="hasTaskPlanned" value='YES'}
    {/foreach}
{else}
    <tr>
        <td colspan="7" style="text-align: center">
            <p class="text-center">No hay tareas planeadas</p>
        </td>
    </tr>
{/if}
{if $hasTaskPlanned neq 'YES'}
    <tr>
        <td colspan="7" style="text-align: center">
            <p class="text-center">&nbsp;</p>
        </td>
    </tr>
{/if}
{/block}
{block name="summary-planned-tasks-row"}
    <tr id="summary-row-{$idDailyReport}" valign="top">
        <td colspan="4">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idDailyReport}">
            <div class="input-group text-right" style="width: 100%;">
                <span id="input-total_time_reported-{$idDailyReport}">
                    {if $totalTime neq NULL} {$NUMBERING_HELPER->setNumberFormat($totalTime)}
                    {else}{$NUMBERING_HELPER->getDefaultValue ()}
                    {/if}
                </span>
            </div>
        </td>
        <td id="td-cost_reported-{$idDailyReport}">
            <div class="input-group text-right" style="width: 100%;">
                <span id="input-total_cost_reported-{$idDailyReport}">
                    {if $totalCost neq NULL} {$NUMBERING_HELPER->setNumberFormat($totalCost)}
                    {else}{$NUMBERING_HELPER->getDefaultValue ()}
                    {/if}
                </span>
            </div>
        </td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}

{* Tasks performed and not previously recorded *}
{block name="thead-tasks-performed"}
    <tr>
        <td colspan="9" style="text-align: left; background-color:#f9f8f7">
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
        <td style="width: 8%"><span>Evidencias</span></td>
    </tr>
{/block}

{block name="tbody-tasks-performed"}
    {if $dailyReportPlanned neq NULL}
        {foreach $dailyReportPlanned as $key => $plannedTask}
            {*$plannedTask|var_dump*}
            {if empty($plannedTask->getActivity())}{continue}{/if}
            {math equation= rand() assign= "idRow"}
            {if
                            empty($plannedTask->getActivity()->getActivityCondition()) ||
                            $plannedTask->getActivity()->getActivityCondition() eq 'PLANNED_AND_RECORDED' ||
                            $plannedTask->getActivity()->getRelatedModule() neq $workModule
                        }
            {continue}
        {/if}
        {include file='modules/DailyReport/Objects/performed_tasks_view.tpl'}
        {assign var="totalTimeUnplanned" value=($totalTimeUnplanned + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getDurationTime()))}
        {assign var="totalCostUnplanned" value=($totalCostUnplanned + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getActualCost()))}
        {assign var="hasTasksPerformed" value='YES'}
    {/foreach}
{else}
    <tr>
        <td colspan="9" style="text-align: center">
            <p class="text-center">No hay tareas previas </p>
        </td>
    </tr>
{/if}
{if $hasTasksPerformed neq 'YES'}
    <tr>
        <td colspan="9" style="text-align: center">
            <p class="text-center">&nbsp;</p>
        </td>
    </tr>
{/if}
{/block}
{block name="summary-tasks-performed-row"}
    <tr id="summary-row-{$idDailyReport}" valign="top">
        <td colspan="6">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idDailyReport}">
            <div class="input-group text-right" style="width: 100%;">
                <span id="input-total_time_reported-{$idDailyReport}">
                    {if $totalTimeUnplanned neq NULL} {$NUMBERING_HELPER->setNumberFormat ($totalTimeUnplanned)}
                    {else}{$NUMBERING_HELPER->getDefaultValue ()}
                    {/if}
                </span>
            </div>
        </td>
        <td id="td-cost_reported_unplanned-{$idDailyReport}">
            <div class="input-group text-right" style="width: 100%;">
                <span id="input-total_cost_reported_unplanned-{$idDailyReport}">
                    {if $totalCostUnplanned neq NULL} {$NUMBERING_HELPER->setNumberFormat ($totalCostUnplanned)}
                    {else}{$NUMBERING_HELPER->getDefaultValue ()}
                    {/if}
                </span>
            </div>
        </td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}