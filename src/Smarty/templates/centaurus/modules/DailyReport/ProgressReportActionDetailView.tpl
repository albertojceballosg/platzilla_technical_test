{extends file='modules/DailyReport/base/ProgressReportsWorkLayout.tpl'}
{* define variables of the job report *}
{assign var='summaryEstimated' value=0}
{assign var='summaryUsed' value=0}
{assign var='isAction' value='YES'}
{assign var='workModule' value='orden_de_trabajo'}
{assign var='numFormat' value=$NUMBERING_HELPER->getNumberFormat()}
{* job report *}
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
{assign var="hasActionPerformed" value=null}
{assign var="hasActionPlanned" value=null}
{* Planned tasks *}
{block name="thead-planned-tasks"}
    <tr>
        <td colspan="6" style="text-align: left; background-color:#f9f8f7"><strong>Acciones planeadas</strong></td>
    </tr>
    <tr style="vertical-align: top">
        <td style="width: 17%"><span style="">Acciones</span></td>
        <td style="width: 12%"><span style="">(%) Avance de la tarea</span></td>
        <td style="width: 30%"><span style="">Reporte de avance</span></td>
        <td style="width: 11%"><span style="">Unidades empleadas</span></td>
        <td style="width: 11%"><span style="">Costo incurrido</span></td>
        <td class="text-center" style="width: 5%;">&nbsp;</td>
    </tr>
{/block}
{block name="tbody-planned-tasks"}
    {if $dailyReportPlanned neq NULL}
        {foreach $dailyReportPlanned as $key => $plannedTask}
            {if empty($plannedTask->getActivity())}{continue}{/if}
            {math equation= rand() assign= "idRow"}
            {if
                            ($plannedTask->getActivity()->getActivityCondition() neq 'PLANNED_AND_RECORDED') ||
                            ($plannedTask->getActivity()->getRelatedModule() eq $workModule)
                        }
            {continue}
        {/if}
        {include file='modules/DailyReport/Objects/planned_actions_view.tpl'}
        {assign var="totalTime" value=($totalTime + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getDurationTime()))}
        {assign var="totalCost" value=($totalCost + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getActualCost()))}
        {assign var="hasActionPlanned" value='YES'}
    {/foreach}
{else}
    <tr>
        <td colspan="6" style="text-align: center">
            <p class="text-center">No hay acciones planeadas</p>
        </td>
    </tr>
{/if}
{if $hasActionPlanned neq 'YES'}
    <tr>
        <td colspan="6" style="text-align: center">
            <p class="text-center">&nbsp;</p>
        </td>
    </tr>
{/if}
{/block}
{block name="summary-planned-tasks-row"}
    <tr id="summary-row-{$idDailyReport}" style="vertical-align: top">
        <td colspan="3">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idDailyReport}">
            <div class="input-group text-right" style="width: 100%;">
                <span id="input-total_time_reported-{$idDailyReport}">
                    {if $totalTime neq NULL} {$NUMBERING_HELPER->setNumberFormat ($totalTime)}
                    {else}{$NUMBERING_HELPER->getDefaultValue ()}
                    {/if}
                </span>
            </div>
        </td>
        <td id="td-cost_reported-{$idDailyReport}">
            <div class="input-group text-right" style="width: 100%;">
                <span id="input-total_cost_reported-{$idDailyReport}">
                    {if $totalCost neq NULL} {$NUMBERING_HELPER->setNumberFormat ($totalCost)}
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
        <td colspan="8" style="text-align: left; background-color:#f9f8f7">
            <strong>Acciones realizadas y no registradas previamente</strong>
        </td>
    </tr>
    <tr style="vertical-align: top">
        <td style="vertical-align:middle;width: 10%"><span>Situación</span></td>
        <td style="vertical-align:middle; width: 18%"><span>Acción</span></td>
        <td style="vertical-align:middle; width: 10%"><span>Unidades estimadas</span></td>
        <td style="vertical-align:middle; width: 10%"><span>(%) avance</span></td>
        <td style="vertical-align:middle; width: 20%"><span>Reporte de avance</span></td>
        <td style="vertical-align:middle; width: 10%"><span>Unidades empleadas</span></td>
        <td style="vertical-align:middle; width: 10%"><span>Costo incurrido</span></td>
        <td style="width: 5%"><span>Evidencias</span></td>
    </tr>
{/block}
{block name="tbody-tasks-performed"}
    {if ($dailyReportPlanned neq NULL)}
        {foreach $dailyReportPlanned as $key => $plannedTask}
            {if empty($plannedTask->getActivity())}{continue}{/if}
            {math equation= rand() assign= "idRow"}
            {if
                            ($plannedTask->getActivity()->getActivityCondition() eq 'PLANNED_AND_RECORDED') ||
                            ($plannedTask->getActivity()->getRelatedModule() eq $workModule)
                        }
            {continue}
        {/if}
        {include file='modules/DailyReport/Objects/actions_performed_view.tpl'}
        {assign var="totalTimeUnplanned" value=($totalTimeUnplanned + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getDurationTime()))}
        {assign var="totalCostUnplanned" value=($totalCostUnplanned + $NUMBERING_HELPER->setSaveNumberFormat($plannedTask->getActualCost()))}
        {assign var="hasActionPerformed" value='YES'}
    {/foreach}
{else}
    <tr>
        <td colspan="8" style="text-align: center">
            <p class="text-center">No hay tareas previas </p>
        </td>
    </tr>
{/if}
{if $hasActionPerformed neq 'YES'}
    <tr>
        <td colspan="8" style="text-align: center">
            <p class="text-center">&nbsp;</p>
        </td>
    </tr>
{/if}
{/block}
{block name="summary-tasks-performed-row"}
    <tr id="summary-row-{$idDailyReport}" valign="top">
        <td colspan="5">
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