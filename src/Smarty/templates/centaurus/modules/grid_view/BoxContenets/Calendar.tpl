<div class="card-header platzilla-card-header rounded" style="{if $hiddenButton eq 'yes'}display:none;{/if}">
    <div class="row">
        <div class="col-md-7">
            <p class="text-center pull-left" style="font-weight: bold">{$boxContenet->getLabel()}</p>
        </div>
        <div class="col-md-5">
            <div class="pull-right">
                {if $hiddenButton eq 'not'}
                <a href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$RECORD}&formodule={$MODULE}&boxtype={$boxContenet->getName()}&function=ITERATIONS&Ajax=true"
                   class="btn btn-success btn-circle"
                   data-title="Reuniones y llamadas:" data-width="950"
                   data-toggle="lightbox" data-parent=""
                   data-gallery="remoteload" class="link">
                    <i class="fa fa-eye fa-lg"></i></a>&nbsp;
                    <a href="#" onclick="CalendarWizard.open ('{$MODULE}', '{$ID}', '{$ENTITY_IDENTIFIER_VALUE}','index.php?module={$MODULE}&parenttab=&action=DetailView&record={$ID}&card_tab=ITERATIONS', 'Meeting')"
                       style="margin-top: 2px"
                       class="link pull-right btn btn-primary btn-circle btn-xs">
                    <i class="fa fa-plus fa-lg"></i></a>
                {/if}
            </div>
        </div>
    </div>
</div>
<div class="card-body"  {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 140px; !important;" {elseif $content eq NULL} style="height: 55px; !important;" {/if}>
    <div class="grid-container" {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 120px; !important;" {elseif $content eq NULL} style="height: 50px; !important;" {/if}>
    {if $content neq NULL}
        <div class="project-box-content">
            {foreach $content as $task}
                <div class="row border-info border-bottom">
                    <div class="col-md-12 text-left">
                        <div class="grid-item" style="margin-bottom: 4px">{if $task->getActivityType () eq 'Meeting'}<i
                                class="fa fa-users  fa-lg"></i>{else}<i class="fa fa-phone  fa-lg"></i>{/if}&nbsp;&nbsp;
                            <a href="index.php?module=Calendar&action=EditView&record={$task->getActivityId()}&Ajax=true&return_module={$MODULE}&return_action=DetailView&card_tab=ITERATIONS&return_id={$ID}"
                               data-title="{$task->getFirstName()}:{$task->getSubject()}" data-width="850"
                               data-toggle="lightbox" data-parent=""
                               data-gallery="remoteload">{if $task->getSubject() neq NULL}{$task->getSubject()|truncate:68:"...:true"}{else}{$task->getDescription()|truncate:68:"...":true}{/if}</a>
                        </div>
                        <div class="grid-item col-md-10"
                             style="margin-bottom: 4px;text-align: right!important;{if (!$task->isLate())} color: red;{/if}">{if (!$task->isLate())}
                                <span title="Ya es muy tarde!" style="border-color:#ffc107!important; cursor: pointer;"><i
                                            class="fa fa-exclamation-triangle"></i></span>
                                &nbsp;&nbsp;{/if}{*<i class="fa fa-calendar fa-lg"></i>&nbsp;&nbsp;*}{$task->getDateSet()}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        {* ----- *}
        <h4 class="text-center" style="margin-bottom: 6px; margin-top: -2px; z-index: 1000;top: -4px"><small>Sin&nbsp;{$boxContenet->getLabel()}.&nbsp;¡Crea la primera!</small></h4>
        {* ----- *}
    {/if}
</div>
<div class="project-box-footer clearfix">
</div>
<div class="project-box-ultrafooter clearfix">
    {*
    <a href="#"
       onclick="CalendarWizard.open ('{$MODULE}', '{$ID}', '{$ENTITY_IDENTIFIER_VALUE}','index.php?module={$MODULE}&parenttab=&action=DetailView&record={$ID}&card_tab=ITERATIONS', 'Meeting')"
       class="link pull-right">
        <i class="fa fa-plus-circle  fa-lg"></i>
    </a>
    <span class="pull-right" style="margin:5px 5px 0 0"><strong>Planificar una reunión o llamada</strong>&nbsp;</span>
    *}
</div>
