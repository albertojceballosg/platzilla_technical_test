{if (isset ($RELHISTORY))}
    {assign var='lastDay' value=$RELHISTORY[0]['day']}
    {assign var='lastClass' value=''}
    {assign var='badge' value=true}

{/if}
{if (isset ($RELHISTORY))}
    <ul class="timeline">
        {foreach $RELHISTORY as $relhistory}
            {if $relhistory.day neq $lastDay}
                {assign var='lastDay' value=$relhistory.day}
                {assign var='badge' value=true}
                {if $lastClass eq ''}
                    {assign var='lastClass' value='timeline-inverted'}
                {else}
                    {assign var='lastClass' value=''}
                {/if}
            {/if}
            <li class="{$lastClass}">
                {if $badge}
                    {assign var='badge' value=false}
                    <div class="timeline-badge">
                        <span class="timeline-balloon-date-day">{$relhistory.day}</span>
                        <span class="timeline-balloon-date-month">{$relhistory.month}</span>
                    </div>
                    <div><p><br></p></div>
                {/if}
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4 class="timeline-title">
                            <a {if $relhistory.type eq 'seactivityrel'}
                                href='index.php?action=DetailView&module=Calendar&record={$relhistory.record}&activity_mode=Events&parenttab='
                                title="Ver la tarea: {$relhistory.title}" target="_blank"
                                    {elseif $relhistory.type eq 'crmentityrel'}
                                href='index.php?action=DetailView&module={$relhistory.title}&record={$relhistory.record}&tab=detail'
                                title="Ver el registro en: {$relhistory.module}" target="_blank"
                                    {else}
                                title=" En este registro"
                                onclick="HistoryUtils.activateTab('history-data', 'history-graphic', 'history-events')"
                                style="cursor: pointer"
                                    {/if}>
                                {if $relhistory.type eq 'seactivityrel'}{/if}
                                {$relhistory.module}{if $relhistory.type eq 'seactivityrel'}:&nbsp;{$relhistory.title}{/if}</a>
                            <small class="text-muted pull-right">
                                <i class="glyphicon glyphicon-time"></i>
                                {$relhistory.createdDay}
                            </small>
                        </h4>
                        <small class="text-muted"></small>
                    </div>
                    <div class="timeline-body">
                        <a>{$relhistory.userName}</a>
                        {if $relhistory.uitype eq 4}
                            {$MOD.LBL_ACTION_ASOCIAR}
                            <span class="text-success"
                                  title="{$relhistory.newValue}">{$relhistory.newValue}</span>
                        {elseif $relhistory.oldValue eq NULL}
                            {$MOD.LBL_ACTION_ASIGNAR}&nbsp;
                            <span class="text-success"
                                  title="{$relhistory.newValue}">{$relhistory.newValue}</span>
                            &nbsp;{$MOD.LBL_TO_FIELD}&nbsp;
                            <b>{$relhistory.field}</b>
                        {else}
                            &nbsp;{$MOD.LBL_ACTION_MODIFY}&nbsp;
                            <b>{$relhistory.field}</b>
                            &nbsp;{$newLine}{$MOD.LBL_STEP_5_TITLE}
                            &nbsp;
                            <span class="text-primary"
                                  title="{$relhistory.oldValue}"> {$relhistory.oldValue}</span>
                            &nbsp;{$newLine}{$MOD.LBL_STEP_6_TITLE}
                            &nbsp;
                            <span class="text-success"
                                  title="{$relhistory.newValue}">{$relhistory.newValue}</span>
                        {/if}
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
{else}
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><strong><span aria-hidden="true">&times;</strong>
        </button>
        <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;
        No se encontró información de eventos relacionados con este registro.
    </div>
{/if}