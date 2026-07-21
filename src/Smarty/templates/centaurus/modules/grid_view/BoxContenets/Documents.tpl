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
                   data-title="Documentos:" data-width="850"
                   data-toggle="lightbox" data-parent=""
                   data-gallery="remoteload" class="link">
                    <i class="fa fa-eye fa-lg"></i></a>&nbsp;<a href="index.php?module=grid_view&action=DetailViewAttachment&record={$RECORD}&formodule={$MODULE}&Ajax=true"
                         data-title="Documentos:" data-width="950"
                         style="margin-top: 2.2px"
                         data-toggle="lightbox" data-parent=""
                         data-gallery="remoteload" class="link pull-right btn btn-primary btn-circle btn-xs">
                    <i class="fa fa-plus  fa-lg"></i>
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
            {foreach $content as $document}
                {assign var='docType' value='.'|explode:$document.name}
                <div class="row" style="margin-bottom: 6px">
                    <div class="col-md-1 text-center" style="vertical-align: center">
                        {if 'pdf'|in_array:$docType}
                            <i class="fa fa-file-pdf-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'txt'|in_array:$docType}
                            <i class="fa fa-file-text-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'gif'|in_array:$docType}
                            <i class="fa fa-file-image-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'jpg'|in_array:$docType}
                            <i class="fa fa-file-image-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'png'|in_array:$docType}
                            <i class="fa fa-file-image-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'docs'|in_array:$docType}
                            <i class="fa fa-file-word-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'docx'|in_array:$docType}
                            <i class="fa fa-file-word-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'doc'|in_array:$docType}
                            <i class="fa fa-file-word-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'xls'|in_array:$docType}
                            <i class="fa fa-file-excel-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'xlsx'|in_array:$docType}
                            <i class="fa fa-file-excel-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'cvs'|in_array:$docType}
                            <i class="fa fa-file-excel-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'mp4'|in_array:$docType}
                            <i class="fa fa-file-video-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'mp3'|in_array:$docType}
                            <i class="fa fa-file-video-o" style="color: #17a2b8;font-size:2em"></i>
                        {elseif 'wav'|in_array:$docType}
                            <i class="fa fa-file-video-o" style="color: #17a2b8;font-size:2em"></i>
                        {else}
                            <i class="fa fa-file-o" style="color: #17a2b8;font-size:2em"></i>
                        {/if}
                    </div>
                    <div class="col-md-10">
                        <div class="grid-item border-bottom border-secondary"><strong>{$document.name}</strong>
                            <small style="float: right">{*$document.type*}
                                &nbsp;({number_format ($document.size, 2, '.', '')}
                                KB)
                            </small>
                        </div>
                        <div class="grid-item"><a href="{$document.uri}" title="{$document.name}"
                                                  target="_blank">
                                <small class="attachment-name"><i class="fa fa-link"></i>&nbsp;{$document.name}</small>
                            </a></div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        {* ----- *}
        <h4 class="text-center" style="margin-bottom: 6px; margin-top: -2px; z-index: 1000;top: -4px"><small>Sin&nbsp;{$boxContenet->getLabel()}.&nbsp;¡Crea el primero!</small></h4>
        {* ----- *}
    {/if}
</div>
<div class="project-box-footer clearfix">
</div>
<div class="project-box-ultrafooter clearfix">
    {*
    <a href="index.php?module=grid_view&action=DetailViewAttachment&record={$RECORD}&formodule={$MODULE}&Ajax=true"
       data-title="Documentos:" data-width="850"
       data-toggle="lightbox" data-parent=""
       data-gallery="remoteload" class="link pull-right">
        <i class="fa fa-plus-circle  fa-lg"></i>
    </a>
    <span class="pull-right" style="margin:5px 5px 0 0"><strong>Asociar un documento</strong>&nbsp;</span>
    *}
</div>