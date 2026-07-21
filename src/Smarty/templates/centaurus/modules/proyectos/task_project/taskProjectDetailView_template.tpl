<tr class="tabla-field-row" valign="top" id="tr-row-{$idRow}">
    <td style="" width="34%" colspan="2">
        <div class="input-group text-left" style="width: 100%;">
                    <span id="task-{$idRow}">
                        {if $relatedTask['task'] neq NULL}{$relatedTask['task']}{/if}
                    </span>
        </div>
        <input type="hidden" name="projec_task[taskId][]" value="{$relatedTask['taskId']}">
    </td>
    <td style="vertical-align: top" width="11%;">
        {if $AVAILABLE_ACTIVITY_TYPES neq NULL}
            <div class="input-group" style="width: 100%;">
                {foreach $AVAILABLE_ACTIVITY_TYPES as $sctivityType}
                    <span id="types{$idRow}">
                    {if $relatedTask['types'] eq $sctivityType->type}{$sctivityType->title}{/if}
                </span>
                {/foreach}
            </div>
        {else}
            <span style="">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: top" width="9%">
        <div class="input-group" style="width: 100%;">
            <span id="start_date-{$idRow}">
                {if $relatedTask['start_date'] neq NULL}{$relatedTask['start_date']}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: top" width="9%">
        <div class="input-group" style="width: 100%;">
            <span id="due_date-{$idRow}">
                {if $relatedTask['due_date'] neq NULL}{$relatedTask['due_date']}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: top" width="8%">
        <div class="input-group" style="width: 100%;">
            <span id="duration-{$idRow}">
                {if $relatedTask['duration'] neq NULL}{$relatedTask['duration']}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: top" width="11%">
        {if $AVAILABLE_SYSTEM_USERS neq NULL}
        <div class="input-group" style="width: 100%;">
                <span id="assigned-{$idRow}">
                     {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                         {if $systemUser->getId() eq $relatedTask['assigned']}
                             {if $systemUser->getImageUri() neq NULL}
                                 <figure class="center-block"
                                         style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px;">
                                     <img class="img-responsive img-circle"
                                          alt="{$systemUser->getFirstName()}"
                                          title="{$systemUser->getFirstName()} {$systemUser->getLastName()}"
                                          src="{$systemUser->getImageUri()}">
                                 </figure>
                             {else}
                                 {$systemUser->getFirstName()} {$systemUser->getLastName()}
                             {/if}
                         {/if}
                     {/foreach}
                </span>
            {else}
            <span style="">&nbsp;</span>
            {/if}
    </td>
    <td style="vertical-align: top" width="8%">
        <div class="input-group" style="width: 100%;">
            <span id="advance-{$idRow}">
                {if $relatedTask['advance'] neq NULL}{$relatedTask['advance']}{/if}
            </span>
        </div>
    </td>
</tr>