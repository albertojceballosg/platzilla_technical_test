{extends file='modules/DailyReport/base/ProgressReportsWorkLayout.tpl'}
{* job report *}
{assign var='isAction' value='YES'}
{* define variables of planned task *}
{assign var='workModule' value='orden_de_trabajo'}
{assign var='numFormat' value=$NUMBERING_FORMAT}
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
{assign var="hasActionPerformed" value=null}
{assign var="hasActionPlanned" value=null}
{* Planned tasks *}
{block name="thead-planned-tasks"}
    <tr>
        <td colspan="6" style="text-align: left; background-color:#f9f8f7"><strong>Acciones planeadas</strong></td>
    </tr>
    <tr style="vertical-align: top">
        <td style="width: 20%"><span style="">Acciones</span></td>
        <td style="width: 9.5%"><span style="">(%) Avance de la tarea</span></td>
        <td style="width: 35%"><span style="">Reporte de avance</span></td>
        <td style="width: 9.5%"><span style="">Unidades empleadas</span></td>
        <td style="width: 9.5%"><span style="">Costo incurrido</span></td>
        <td class="text-center" style="width: 8%;">&nbsp;</td>
    </tr>
{/block}
{block name="tbody-planned-tasks"}
    {if $dailyReportPlanned neq NULL}
        {foreach $dailyReportPlanned as $key => $plannedTask}
            {if empty($plannedTask->getActivity())}{continue}{/if}
            {math equation= rand() assign= "idRow"}
            {if $plannedTask->getActivity()->getRelatedModule() eq $workModule}
                {continue}
            {/if}
            {include file='modules/DailyReport/Objects/actions_planned_row.tpl'}
        {/foreach}
    {else}
        <tr>
            <td colspan="6" style="text-align: center">
                <p class="text-center">No hay tareas planeadas</p>
            </td>
            {assign var="ActionkPlanned" value='YES'}
        </tr>
    {/if}
    {if $ActionkPlanned neq 'YES'}
        <tr>
            <td colspan="6" style="text-align: center">
                <p class="text-align: center">&nbsp;</p>
            </td>
        </tr>
    {/if}
{/block}
{block name="summary-planned-tasks-row"}
    <tr id="summary-row-{$idProgressJob}" valign="top">
        <td colspan="3">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idProgressJob}">
            <input type="text" id="planned-action-total-time-{$idProgressJob}" name="planned_actions[summaryRow][]"
                rel="SUM_COLUMN"
                value="{if $totalTime neq NULL} {$NUMBERING_HELPER->setNumberFormat ($totalTime)} {elseif ($numFormat eq 'EUROPEAN_FORMAT')} 0,00 {else} 0.00  {/if}"
                class="form-control" readonly="">
        </td>
        <td id="td-cost_reported-{$idProgressJob}">
            <input type="text" id="planned-action-total-cost-{$idProgressJob}" name="planned_actions[summaryCost][]"
                rel="SUM_COLUMN" value="{if ($numFormat eq 'EUROPEAN_FORMAT')} 0,00 {else} 0.00 {/if}" class="form-control"
                readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}
{block name="add-planned-tasks-row"}
    <tr>
        <td colspan="6" class="text-center">
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
            <strong>Acciones realizadas y no registradas previamente</strong>
        </td>
    </tr>
    <tr style="vertical-align: top">
        <td style="vertical-align:middle;width: 10%"><span>Situación</span></td>
        <td style="vertical-align:middle; width: 13%"><span>Acción</span></td>
        <td style="vertical-align:middle; width: 9.5%"><span>Unidades estimadas</span></td>
        <td style="vertical-align:middle; width: 9.5%"><span>(%) avance</span></td>
        <td style="vertical-align:middle; width: 22%"><span>Reporte de avance</span></td>
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
        <td colspan="5">
            <p style="text-align: right">Total:&nbsp;</p>
        </td>
        <td id="td-time_reported-{$idProgressJob}">
            <input type="text" id="performed-action-total-time-{$idProgressJob}" name="performed_actions[summaryRow][]"
                rel="SUM_COLUMN" value="{if ($numFormat eq 'EUROPEAN_FORMAT')} 0,00 {else} 0.00 {/if}" class="form-control"
                readonly="">
        </td>
        <td id="td-cost_reported-{$idProgressJob}">
            <input type="text" id="performed-action-total-cost-{$idProgressJob}" name="performed_actions[summaryCost][]"
                rel="SUM_COLUMN" value="{if ($numFormat eq 'EUROPEAN_FORMAT')} 0,00 {else} 0.00 {/if}" class="form-control"
                readonly="">
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
{block name="script_template"}
    <script type="text/html" id="planned_activities-template-{$idProgressJob}">
        {include file='modules/DailyReport/Objects/planned_actions_edit.tpl'}
    </script>
    <script type="text/html" id="planned_activities-colspan-template-{$idProgressJob}">
        <tr>
            <td colspan="6" style="text-align: center"></td>
        </tr>
    </script>
    <script type="text/html" id="planned_unregistered-template-{$idProgressJob}">
        {include file='modules/DailyReport/Objects/actions_performed_edit.tpl'}
    </script>
    <script type="text/html" id="planned_unregistered-colspan-template-{$idProgressJob}">
        <tr>
            <td colspan="8" style="text-align: center"></td>
        </tr>
    </script>
{/block}