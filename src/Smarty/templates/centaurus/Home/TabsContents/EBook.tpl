{strip}
    {if ($FILE neq NULL)}
        {assign var='fileId' value=$FILE->getId()}
        {assign var='folderId' value=$FILE->getFolderId()}
        {assign var='folderName' value=$FILE->getFolderName()}
        {assign var='fileUrl' value=$FILE->getUrl()}
        {assign var='fileUrlPublic' value=$FILE->getUrlPublic()}
        {assign var='fileName' value=$FILE->getName()}
        {assign var='filePublicName' value=$FILE->getPublicName()}
        {assign var='filePhoto' value=$FILE->getPhoto()}
        {assign var="filePhotoType" value=$FILE->getPhotoType()}
        {assign var='fileDescrition' value=$FILE->getDescription()}
        {assign var='fileType' value=$FILE->getType()}
        {assign var='fileRelated' value=$FILE->getRelatedFiles ()}
        {assign var='siteUrl' value=$SITE_URL}
        {if $fileType eq 'UPLOADED'}
            {assign var='btnName' value='<i class="fa fa-download"></i>&nbsp;DESCARGAR'}
            {assign var='href' value='/index.php?module=materials&action=AjaxActions&Ajax=true&function=DOWNLOAD_DOCUMENT&record='|cat:$fileId}
            {assign var='targe' value=''}
        {else}
            {assign var='btnName' value='<i class="fa fa-link" aria-hidden="true"></i>&nbsp;Ver Artículo'}
            {assign var='href' value=$fileUrl}
            {assign var='targe' value='target="_blank"'}
        {/if}
    {else}
        {assign var='fileId' value=null}
        {assign var='folderId' value=null}
        {assign var='fileName' value=null}
        {assign var='filePublicName' value=null}
        {assign var='fileUrlPublic' value=null}
        {assign var='filePhoto' value=null}
        {assign var='fileVideo' value=null}
        {assign var='fileDescrition' value=null}
        {assign var='fileType' value=null}
        {assign var='fileRelated' value=array()}
    {/if}
    {assign var='noPhoto' value='themes/centaurus/img/docs-no-images.png'}
    <div class="main-box no-header">
        <h1>
            <a href="#{*index.php?module=Home&action=index&tab=MATERIALS*}"
               onclick="MaterialsUtils.loadFolderPage(event)"
               title="Documentos de gestión empresarial"
               style="margin-left:.5em; margin-right: 0;text-decoration: none">
                <strong><i class="fa fa-home" aria-hidden="true"></i>&nbsp;Documentos</strong>
                <span style="color: #000;font-size: smaller">&nbsp;></span></a>
            <small style="font-weight: bold">{$fileName}</small>
            </a>
        </h1>
        <div class="main-box-body clearfix">
            <div class="row">
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12" style="padding-left: 8px">
                            {*<h2 class="text-center">{$fileName}</h2>*}
                            {if !empty ($filePhoto)}
                                <img id="file-photo-{$fileId}"
                                     src="data:{$filePhotoType}; base64, {$filePhoto}"
                                     class="center-block img-thumbnail img-responsive pull-left">
                            {else}
                                <img class="img-thumbnail img-responsive center-block  pull-left"
                                     style="margin-left: 10px"
                                     src="data:{$DEFAULT_PHOTO['type']}; base64,{$DEFAULT_PHOTO['imagen']}"/>
                            {/if}
                        </div>
                        <div class="col-md-12">
                            <div class="" style="margin-top: 20px;width: 100%">
                                {if $fileUrlPublic neq NULL}
                                    <a href="{$fileUrlPublic}" class="btn btn-primary" target="_blank" >&nbsp;Ver artículo en línea</a>
                                {else}
                                    &nbsp;
                                {/if}
                                {* <button type="button" class="btn btn-primary" onclick="MaterialsUtils.loadFolderPage()">
                                    <i class="fa fa-backward"></i>&nbsp;Volver
                                </button> &nbsp; *}
                                <a href="{$href}" class="btn btn-success" {$targe} >{$btnName}</a>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="text-center" style="font-weight: bold">¿Que aprenderás en este Documento?</h2>
                            <div class="rounded border" style="padding: 20px">
                                <p>{$fileDescrition}</p>
                            </div>
                        </div>
                        {* caraucel *}
                        <div class="col-md-12">
                            {* carousel*}
                            {if $FILES neq NULL}
                                <h2 class="text-center" style="font-weight: bold; margin-top: 25px">Tal vez pueda interesarte</h2>
                                {assign var="index" value='1D'}
                                {* caraucel *}
                                <div class="col-xs-12 category">
                                    <div class="col-xs-12 course-portraits">
                                        {math equation='(x / y) + z' x=$FILES|count y=4 z=0 assign='numIndicators'}
                                        <div id="Carousel-{$index}" class="carousel slide">
                                            <ol class="carousel-indicators {if $numIndicators|floor lt 1} hide{/if}">
                                                {foreach $FILES as $file}
                                                    {if (($file@iteration gt ($numIndicators|floor)) )}
                                                        {continue}
                                                    {/if}
                                                    <li data-target="#Carousel-{$index}"
                                                        data-slide-to="{$smarty.foreach.foo.index}"
                                                        class="{if {$smarty.foreach.foo.index} eq 0}active{/if}"></li>
                                                {/foreach}
                                            </ol>
                                            <div class="carousel-inner">
                                                {assign var='caroucelItem' value=1}
                                                {assign var='activeItem' value='active'}
                                                {foreach from=$FILES item= $file name=foo}
                                                    {assign var='fileId' value=$file->getId ()}
                                                    {assign var="filePhoto" value=$file->getPhoto()}
                                                    {assign var="filePhotoType" value=$file->getPhotoType()}
                                                    {assign var="folderName" value=$file->getFolderName()}
                                                    {assign var="fileName" value=$file->getName ()}
                                                    {assign var="filePublicName" value=$file->getPublicName ()}
                                                    {if $caroucelItem eq 1}
                                                        <div class="item {$activeItem}">
                                                        {assign var='activeItem' value=''}
                                                        <div class="row">
                                                    {/if}
                                                    <div class="col-md-3">
                                                        <a <a href="#"
                                                              onclick="MaterialsUtils.showDocumentPage(event, '{$fileId}')"
                                                              rel="{$fileId}"
                                                              title="{$filePublicName}"
                                                              class="center-block"
                                                              style="margin-bottom: 1px!important;">
                                                            {if !empty ($filePhoto)}
                                                                <img id="file-photo-{$fileId}"
                                                                     src="data:{$filePhotoType}; base64, {$filePhoto}"
                                                                     class="center-block img-thumbnail img-responsive">
                                                            {else}
                                                                <img class="img-thumbnail img-responsive center-block"
                                                                     src="data:{$DEFAULT_PHOTO['type']}; base64,{$DEFAULT_PHOTO['imagen']}"/>
                                                            {/if}</a>
                                                        {*<p class="file-name text-justify">{$fileName}</p>*}
                                                    </div>
                                                    {if $caroucelItem eq 4}
                                                        </div><!--.row-->
                                                        </div><!--.item-->
                                                    {/if}
                                                    {math equation='x + y' x=$caroucelItem y=1 assign='caroucelItem'}
                                                    {if $caroucelItem eq 5}
                                                        {assign var='caroucelItem' value=1}
                                                    {/if}
                                                {/foreach}
                                                {if ($caroucelItem neq 4) ||(($caroucelItem neq 1))}
                                            </div><!--.row-->
                                        </div><!--.item-->
                                        {/if}
                                    </div><!--.carousel-inner-->
                                    <a data-slide="prev" href="#Carousel-{$index}"
                                       class="left carousel-control{if $numIndicators|floor lt 1} hide{/if}">‹</a>
                                    <a data-slide="next" href="#Carousel-{$index}"
                                       class="right carousel-control{if $numIndicators|floor lt 1} hide{/if}">›</a>
                                </div>
                                {* /caraucel *}
                            {/if}
                            {* /carousel*}
                        </div>
                        {* caraucel *}
                    </div>
                </div>
            </div>
            <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
            <script type="text/javascript" src="modules/materials/materials-utils.js"></script>
            <script type="text/javascript" src="modules/materials/carousel.js"></script>
        </div>
    </div>
{/strip}
