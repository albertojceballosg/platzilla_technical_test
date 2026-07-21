<div class="card-header platzilla-card-header rounded" style="{if $hiddenButton eq 'yes'}display:none;{/if}">
    <div class="row">
        <div class="col-md-5">
            <p class="text-center pull-left" style="font-weight: bold">{$boxContenet->getLabel()}</p>
        </div>
        <div class="col-md-7">
            <div class="pull-right">
                {if $hiddenButton eq 'not'}
                    <a href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$RECORD}&formodule={$MODULE}&boxtype={$boxContenet->getName()}&function=ITERATIONS&Ajax=true"
                       class="btn btn-success btn-circle"
                       style="margin-right: 5px"
                       data-title="Notas:" data-width="950"
                       data-toggle="lightbox" data-parent=""
                       data-gallery="remoteload" class="link">
                        <i class="fa fa-eye fa-lg"></i></a>
                    <a
                            href="index.php?module=grid_view&action=DetailViewComment&record={$RECORD}&formodule={$MODULE}&Ajax=true"
                            data-title="Notas:" data-width="850"
                            style="margin-top: 1.5px"
                            data-toggle="lightbox" data-parent=""
                            data-gallery="remoteload" class="link pull-right btn btn-primary btn-circle btn-xs">
                        <i class="fa fa-plus  fa-lg"></i>
                    </a>
                {/if}
            </div>
        </div>
    </div>
</div>
<div class="card-body"
     {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 140px; !important;" {elseif $content eq NULL}
     style="height: 55px; !important;" {/if}>
    <div class="grid-container"
         {if ($hiddenButton neq 'yes') && ($content neq NULL)}style="height: 120px; !important;" {elseif $content eq NULL}
         style="height: 50px; !important;" {/if}>
        {if $content neq NULL}
            {*$content|var_dump*}
            <div class="project-box-content">
                {foreach $content as $comment}
                    {*$comment->getWrittenOn ()|var_dump*}
                    <div class="row" style="margin-bottom: 4px">
                        <div class="col-md-2 text-center" style="margin-right: -4px">
                            <figure class="grid-img">
                                <img src="{if !empty($comment->getUserAvatar ())}{$comment->getUserAvatar ()}{else}{$altPhoto}{/if}"
                                     class="img-responsive">
                            </figure>
                        </div>
                        <div class="col-md-10">
                            <div class="grid-item border-bottom border-secondary">
                                <strong>{$comment->getUserName ()}</strong>
                                <small style="float: right"><i
                                            class="fa fa-calendar"></i>&nbsp;&nbsp;{$comment->getWrittenOn ()}
                                </small>

                            </div>

                            <div class="grid-item" {if $comment->getCommentType() neq 'TEXT'}style="padding-top: 2px"{/if} >
                                {if $comment->getCommentType() eq 'TEXT'}
                                    {if ($hiddenButton neq 'yes')}
                                        {$comment->getStatement()|truncate:255:"...":true}
                                    {else}
                                        {$comment->getStatement()}
                                    {/if}
                                {else}
                                    <audio controls src="data:audio/ogg;base64,{$comment->getStatement()}"/>
                                {/if}
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            {* ----- *}
            <h4 class="text-center" style="margin-bottom: 6px; margin-top: -2px; z-index: 1000;top: -4px">
                <small>Sin&nbsp;{$boxContenet->getLabel()}.&nbsp;¡Crea el primero!</small>
            </h4>
            {* ----- *}
        {/if}
    </div>
    <div class="project-box-footer clearfix">
    </div>
    <div class="project-box-ultrafooter clearfix">
        {*
        <a href="index.php?module=grid_view&action=DetailViewComment&record={$RECORD}&formodule={$MODULE}&Ajax=true"
           data-title="Notas:" data-width="850"
           data-toggle="lightbox" data-parent=""
           data-gallery="remoteload" class="link pull-right">
            <i class="fa fa-plus-circle  fa-lg"></i>
        </a>
        <span class="pull-right" style="margin:5px 5px 0 0"><strong>Registrar un comentario</strong>&nbsp;</span>
        *}
    </div>