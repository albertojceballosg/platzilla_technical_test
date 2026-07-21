{assign var="activty" value=$plannedTask->getActivity()}
{assign var="report" value=$plannedTask->getReport()}
<tr id="tr-row-{$idRow}" data-row-id="{$idRow}" class="tabla-field-row" style="vertical-align: top">
    {* 1.- situación *}
    <td style="vertical-align: top">
        <div id="input-reported_task_condition-{$idRow}" class="input-group text-left" style="width: 100%;">
            <span id="reported_task_condition-{$idRow}">
                {if $activty->getActivityCondition() neq NULL}{$MOD[$activty->getActivityCondition()]}{/if}
            </span>
        </div>
    </td>
    {* 2.- Trabajo *}
    <td style="vertical-align: top">
        {assign var="actionField" value=$activty->getRelatedId()}
        {assign var="actionString" value=$activty->getRelatedModule()}
        {assign var="actionTitle" value=$activty->getRelatedTitle ()}
        <div class="input-group text-left" style="width: 100%;">
            {if $actionString neq NULL && $actionField neq NULL}
                <a href="index.php?module={$actionString}&parenttab=&action=DetailView&record={$actionField}&tab=task-list"
                   class="daily-report-scroll" style="max-height:110px;"
                   target="_blank"
                   title="Trabajo relacionado">{$actionTitle}</a>
            {else}
                <span id="reported_task-display-{$idRow}">
                    {if $activty->getRelatedTitle () neq NULL}{$activty->getRelatedTitle ()}{else}No hay trabajo asociado{/if}
                </span>
            {/if}
        </div>
    </td>
    {* 3.- Tarea  *}
    <td style="vertical-align: top">
        {assign var="actionField" value=$activty->getRelatedId()}
        {assign var="actionString" value=$activty->getRelatedModule()}
        <div class="input-group text-left" style="width: 100%;">
            {if $actionString neq NULL && $actionField neq NULL}
                <a href="index.php?module={$actionString}&parenttab=&action=DetailView&record={$actionField}&tab=task-list"
                   class="daily-report-scroll" style="max-height:110px;"
                   target="_blank"
                   title="Tarea reportada">{$activty->getSubject ()}</a>
            {else}
                <span id="reported_task-display-{$idRow}">
                        {if $activty->getSubject () neq NULL}{$activty->getSubject ()}{/if}
                    </span>
            {/if}
        </div>
    </td>
    {* 4.- Tiempo estimado *}
    <td style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
            <span id="input-task_progress_perc-{$idRow}">
                {if $activty->getTimeDuration() neq NULL}{$activty->getTimeDuration()}{/if}
            </span>
        </div>
    </td>
    {* 5.- % de avance *}
    <td style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
            <span id="inputpc_task_advanced-{$idRow}">
                {if $activty->getProgress () neq NULL}{$activty->getProgress ()}{/if}
            </span>
        </div>
    </td>
    {* 6.- Reporte de avance *}
    <td style="vertical-align: top">
        <div id="input-task_advanced_report-{$idRow}" class="input-group" style="width: 100%;">
            <span id="task_advanced_report-id-{$idRow}" class="daily-report-scroll"
                  style="max-height:110px;overflow-x: auto">
                {foreach $report as $objReport}
                    {$objReport->getReport ()}
                {/foreach}
            </span>
        </div>
    </td>
    {* 7.- tiempo empleado *}
    <td style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
            <span id="input-time_reported-{$idRow}">
                {if $plannedTask->getDurationTime() neq NULL}
                    {$plannedTask->getDurationTime()}
                {/if}
            </span>
        </div>
    </td>
    {* 8.- costo incurrido *}
    <td style="vertical-align: top">
        <div class="input-group text-right" style="width: 100%;">
            <span id="input-actual_cost-{$idRow}">
                {if $plannedTask->getActualCost() neq NULL}
                    {$plannedTask->getActualCost()}
                {/if}
            </span>
        </div>
    </td>
    {* 9.- Acciones *}
    <td class="text-center" style="vertical-align: top; width: 5%">
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