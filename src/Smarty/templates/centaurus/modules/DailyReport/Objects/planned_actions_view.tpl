{assign var="activty" value=$plannedTask->getActivity()}
{assign var="report" value=$plannedTask->getReport()}
<tr numrowtr="{$key}" id="row_planned_activities-{$idRow}" class="tabla-field-row">
    {* Acciones *}
    <td class="" id="td_planned_activities_reported_task_{$idRow}_Campo1" style="vertical-align: top" width="auto"
        bgcolor="">
        {assign var="actionField" value=$activty->getRelatedId()}
        {assign var="actionString" value=$activty->getRelatedModule()}
        <div class="input-group text-left" style="width: 100%;">
            {if $actionString neq NULL && $actionField neq NULL}
                <a href="index.php?module={$actionString}&parenttab=&action=DetailView&record={$actionField}&tab=task-list"
                   class="daily-report-scroll" style="max-height:110px;"
                   target="_blank"
                   title="Acción reportada">{$activty->getSubject ()}</a>
            {else}
                <span id="reported_task-display-{$idRow}">
                    {if $activty->getSubject () neq NULL}{$activty->getSubject ()}{/if}
                </span>
            {/if}
        </div>
    </td>
    {* % Avance  *}
    <td class="" id="td_planned_activities_task_progress_perc_{$idRow}_-Campo1" style="vertical-align: top"
        width="auto">
        <div class="input-group text-right" style="width: 100%;">
                    <span id="input-task_progress_perc-{$idRow}">
                        {if $activty->getProgress () neq NULL}{$activty->getProgress ()}{/if}
                    </span>
        </div>
    </td>
    {* Reporte de avance  *}
    <td class="" id="td_planned_activities_informe_de_avance_{$idRow}_-Campo1" style="vertical-align: top" width="auto">
        <div id="input-task_advanced_report-{$idRow}" class="input-group" style="width: 100%;">
                    <span id="task_advanced_report-id-{$idRow}" class="daily-report-scroll"
                          style="max-height:110px;overflow-x: auto">
                       {foreach $report as $objReport}
                           {$objReport->getReport ()}
                       {/foreach}
                    </span>
        </div>
    </td>
    {*  tiempo empleado  *}
    <td class="" id="td_planned_activities_time_reported{$idRow}_Campo1" style="vertical-align: top" width="auto">
        <div class="input-group text-right" style="width: 100%;">
                    <span id="input-time_reported-{$idRow}">
                        {if $plannedTask->getDurationTime() neq NULL}
                            {$plannedTask->getDurationTime()}
                        {/if}
                    </span>
        </div>
    </td>
    {*  costo incurrido  *}
    <td class="" id="td_planned_activities_actual_cost{$idRow}_Campo1" style="vertical-align: top" width="auto">
        <div class="input-group text-right" style="width: 100%;">
                    <span id="input-actual_cost-{$idRow}">
                        {if $plannedTask->getActualCost() neq NULL}
                            {$plannedTask->getActualCost()}
                        {/if}
                    </span>
        </div>
    </td>
    {* Columna de acciones  *}
    <td class="" id="td_planned_activities_Acciones_Campo{$key}"
        style="vertical-align: top;width: 5%; text-align: center">
        {if $activty->attachments neq NULL}
            <ul class="inline instance-list" style="list-style: none;text-align: center">
                {foreach $activty->attachments as $attachment}
                    <li style="text-align: center">
                        <a href="{$attachment['uri']}" title="{$attachment['name']}" target="_blank">
                            <i class="fa {$attachment['type']}" style="color: #17a2b8;font-size:2em"></i>
                        </a>
                    </li>
                {/foreach}
            </ul>
        {/if}
    </td>
</tr>