<div class="card-header platzilla-card-header rounded" style="{if $hiddenButton eq 'yes'}visibility: hidden;{/if}">
    <div class="row">
        <div class="col-md-5">
            <p class="text-center pull-left" style="font-weight: bold">{$boxContenet->getLabel()}</p>
        </div>
        <div class="col-md-7">
            <div class="pull-right">
                {if $hiddenButton eq 'not'}
                <a href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$RECORD}&formodule={$MODULE}&boxtype={$boxContenet->getName()}&function=ITERATIONS&Ajax=true"
                   class="btn btn-success btn-circle"
                   data-title="Mensajes:" data-width="950"
                   data-toggle="lightbox" data-parent=""
                   data-gallery="remoteload" class="link">
                    <i class="fa fa-eye fa-lg"></i></a>&nbsp;<a href="index.php?module=grid_view&action=DetailViewMessage&record={$RECORD}&formodule={$MODULE}&Ajax=true"
                                                              data-title="Mensajes:" data-width="850"
                                                              data-toggle="lightbox" data-parent=""
                                                              data-gallery="remoteload" class="link btn btn-primary btn-circle btn-xs">
                    <i class="fa fa-plus fa-lg"></i>
                </a>
                {/if}
            </div>
        </div>
    </div>
</div>
<div class="card-body" {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 140px; !important;" {elseif $content eq NULL} style="height: 55px; !important;" {/if}>
<div class="grid-container" {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 120px; !important;" {elseif $content eq NULL} style="height: 50px; !important;" {/if}>
    {if $content neq NULL}
        {assign var='messaje' value='Responder o enviar un mensaje'}
        <div class="project-box-content conversation-wrapper ">
            {assign var="CHATS" value=$content}
            <div id="instant-messages-section"
                 class="row-parley justify-content-center">
                <div id="parley_chats" class="col-md-11">
                    {include file="modules/notification_center/chatParley.tpl"}
                </div>
            </div>

        </div>
    {else}
        {assign var='messaje' value='Enviar un mensaje'}
        {* ----- *}
        <h4 class="text-center" style="margin-bottom: 6px; margin-top: -2px; z-index: 1000;top: -4px"><small>Sin&nbsp;{$boxContenet->getLabel()}.&nbsp;¡Crea el primero!</small></h4>
        {* ----- *}
    {/if}
</div>

<div class="project-box-footer clearfix">
</div>
<div class="project-box-ultrafooter clearfix">
</div>
