{assign var="activty" value=$plannedTask->getActivity()}
{assign var="report" value=$plannedTask->getReport()}
<tr id="tr-row-{$idRow}" data-row-id="{$idRow}" class="tabla-field-row" valign="top">
    <td {*width="10.8%"*}  style="vertical-align: top">
        <div id="input-reported_task_condition-{$idRow}" class="input-group text-justify" style="width: 100%;">
                    <span id="reported_task_condition-{$idRow}">
                        {if $activty->getActivityCondition() neq NULL}{$MOD[$activty->getActivityCondition()]}{/if}
                    </span>
        </div>
    </td>
    <td {*width="16.2%"*}  style="vertical-align: top">
        {assign var="actionField" value=$activty->getRelatedId()}
        {assign var="actionString" value=$activty->getRelatedModule()}
        <div class="input-group text-left"   style="width: 100%;">
            {if $actionString neq NULL && $actionField neq NULL}
                <a href="index.php?module={$actionString}&parenttab=&action=DetailView&record={$actionField}&tab=task-list"
                   class="daily-report-scroll" style="max-height:110px;"
                   target="_blank"
                   title="Reporte diario">{$activty->getSubject ()}</a>
            {else}
                <span id="reported_task-display-{$idRow}">
                    {if $activty->getSubject () neq NULL}{$activty->getSubject ()}{/if}
                </span>
            {/if}
        </div>
    </td>
    <td {*width="9%"*}  style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
                    <span id="input-task_progress_perc-{$idRow}">
                        {if $activty->getTimeDuration() neq NULL}{$activty->getTimeDuration()}{/if}
                    </span>
        </div>
    </td>
    <td {*width="9%"*} style="vertical-align: top">
        <div id="input-task_importance-{$idRow}" class="input-group text-justify" style="width: 100%;">
                    <span  id="task_importance-{$idRow}">
                        {if $activty->getImportance() neq Null}{$MOD[$activty->getImportance ()]}{/if}
                    </span>
        </div>
    </td>
    <td{*width="9%"*} style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <div id="input-task_priority-{$idRow}" class="input-group text-justify" style="width: 100%;">
                    <span  id="task_priority-{$idRow}">
                        {if $activty->getPriority() neq Null}{$activty->getPriority ()}{/if}
                    </span>
            </div>
        </div>
    </td>
    <td {*width="9%"*} style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
                    <span id="inputpc_task_advanced-{$idRow}">
                        {if $activty->getProgress () neq NULL}{$activty->getProgress ()}{/if}
                    </span>
        </div>
    </td>
    <td {*width="18%"*} style="vertical-align: top">
        <div id="input-task_advanced_report-{$idRow}"  class="input-group" style="width: 100%;">
                    <span id="task_advanced_report-id-{$idRow}" class="daily-report-scroll" style="max-height:110px;overflow-x: auto">
                       {foreach $report as $objReport}
                           {$objReport->getReport ()}
                       {/foreach}
                    </span>
        </div>
    </td>
    <td {*width="9%"*} style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
                    <span id="input-time_reported-{$idRow}">
                        {if $plannedTask->getDurationTime() neq NULL}
                            {$plannedTask->getDurationTime()}
                        {/if}
                    </span>
        </div>

    </td>
    <td class="text-center" width="10%" style="vertical-align: top">&nbsp;</td>
</tr>