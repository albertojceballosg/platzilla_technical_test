<div class="card-header platzilla-card-header rounded" style="{*background-color: #f9f8f7;*}{if $hiddenButton eq 'yes'}display:none;{/if}">
    <div class="row">
        <div class="col-md-5">
            <p class="text-center pull-left" style="font-weight: bold">{$boxContenet->getLabel()}</p>
        </div>
        <div class="col-md-7">
            <div class="pull-right">
                {if $hiddenButton eq 'not'}
                    <a href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$RECORD}&formodule={$MODULE}&boxtype={$boxContenet->getName()}&function=ITERATIONS&Ajax=true"
                       class="btn btn-success btn-circle btn-xs"
                       data-title="Tareas:" data-width="950"
                       data-toggle="lightbox" data-parent=""
                       data-gallery="remoteload" class="link">
                        <i class="fa fa-eye fa-lg"></i></a>&nbsp;<a href="#"
                                                              onclick="CalendarWizard.open ('{$MODULE}', '{$ID}', '{$ENTITY_IDENTIFIER_VALUE}','index.php?module={$MODULE}&parenttab=&action=DetailView&record={$ID}&card_tab=ITERATIONS', 'Activity')"
                                                              class=" pull-right btn btn-primary btn-circle btn-xs">
                    <i class="fa fa-plus fa-lg"></i>
                </a>
                {/if}
            </div>
        </div>
    </div>
</div>
<div class="card-body"  {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 140px; !important;" {elseif $content eq NULL} style="height: 55px; !important;" {/if}>
    <div class="grid-container" {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 120px; !important;" {elseif $content eq NULL} style="height: 50px; !important;" {/if}>
        {if $content neq NULL}
            <div class="project-box-content">
                <div class="row">
                    <div class="col-md-6 text-left">
                        <div class="grid-item"><strong>Nombre&nbsp;/Asunto</strong></div>
                    </div>
                    <div class="col-md-3 text-left">
                        <div class="grid-item"><strong>Vencimiento</strong></div>
                    </div>
                    <div class="col-md-3">
                        <div class="grid-item"><strong>Estado</strong></div>
                    </div>
                </div>
                {foreach $content as $task}
                    <div class="row border-info border-top">
                        <div class="col-md-6 text-left">
                            <div class="grid-item border-right border-secondary">{$task->getFirstName()}
                                &nbsp;{$task->getLastName()}</div>
                            <div class="grid-item border-right border-secondary">
                                <a
                                        href="index.php?module=Calendar&action=EditView&record={$task->getActivityId()}&Ajax=true&return_module={$MODULE}&return_action=DetailView&card_tab=ITERATIONS&return_id={$ID}&isWork=1"
                                        data-title="{$task->getFirstName()}:{$task->getSubject()}" data-width="850"
                                        data-toggle="lightbox" data-parent=""
                                        data-gallery="remoteload">{if $task->getDescription() neq NULL}{$task->getDescription()|truncate:60:"...":true}{else}{$task->getSubject()|truncate:60:"...:true"}{/if}</a>
                            </div>
                        </div>
                        <div class="col-md-3 text-left border-right border-secondary">
                            <small>{$task->getDueDate()}</small>
                        </div>
                        <div class="col-md-3">
                            <small>{$APP[$task->getEventStatus()]}</small>
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
           onclick="CalendarWizard.open ('{$MODULE}', '{$ID}', '{$ENTITY_IDENTIFIER_VALUE}','index.php?module={$MODULE}&parenttab=&action=DetailView&record={$ID}&card_tab=ITERATIONS', 'Activity')"
           class="link pull-right">
            <i class="fa fa-plus-circle  fa-lg"></i>
        </a>
        <span class="pull-right" style="margin:5px 5px 0 0"><strong>Crear una tarea</strong>&nbsp;</span>
        *}
    </div>
