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
        {assign var='filePhotoType' value=$FILE->getPhotoType()}
        {assign var='fileDescrition' value=$FILE->getDescription()}
        {assign var='featuredFile' value=$FILE->getFeatured()}
        {assign var='fileType' value=$FILE->getType()}
        {assign var='fileRelated' value=$FILE->getRelatedFiles ()}
        {assign var='siteUrl' value=$SITE_URL}
    {else}
        {assign var='fileId' value=null}
        {assign var='folderId' value=null}
        {assign var='fileName' value=null}
        {assign var='fileUrlPublic' value=null}
        {assign var='filePublicName' value=null}
        {assign var='filePhoto' value=null}
        {assign var='filePhotoType' value=''}
        {assign var='fileVideo' value=null}
        {assign var='fileDescrition' value=null}
        {assign var='featuredFile' value=null}
        {assign var='fileType' value=null}
        {assign var='fileRelated' value=array()}
    {/if}
    {assign var='noPhoto' value='themes/centaurus/img/docs-no-images.png'}
    <link type="text/css" href="modules/materials/materials.css"/>
    <form method="post" enctype="multipart/form-data" action="index.php">
        <input type="hidden" name="module" value="materials"/>
        <input type="hidden" name="action" value="SaveFile"/>
        <input type="hidden" name="record" value="{$fileId}"/>
        <input type="hidden" name="type" value="{if $TYPE eq NULL}SAVED{else}{$TYPE}{/if}"/>
        <input type="hidden" name="publicname" value="{$filePublicName}"/>
        <input type="hidden" name="myphoto" value="{$filePhoto}"/>
        <input type="hidden" name="Ajax" value="true"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=materials&action=ListView&parenttab=Settings&tab=files-tab">{$MOD['APP_TITLE']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=materials&action=ListView&parenttab=Settings&tab=files-tab"
                       class="btn btn-warning"
                       style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="col-xs-12">
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2 class="pull-left">Registrar documentos para compartir</h2>
                </header>
                <div class="main-box-body">
                    {* Form *}
                    <div class="row" style="margin-bottom: 12px">
                        <div class="col-md-12">
                            <img id="element-photo"
                                {if $filePhoto eq NULL}
                                    src="data:{$DEFAULT_PHOTO['type']}; base64,{$DEFAULT_PHOTO['imagen']}"
                                 {else}
                                    src="data:{$filePhotoType}; base64,{$filePhoto}"
                                 {/if}
                                 class="img-responsive center-block">
                        </div>
                    </div>
                    <div class="row">
                        <div class="row form-group">
                            <label for="filename" class="col-md-2 control-label">Imagen para el documento (Max 2M):</label>
                            <div class="col-md-10">
                                <div class="fileUpload btn btn-simple" style="width: 9em;">
                                    <span>Examinar</span>
                                    <input type="file" name="imagePhoto" class="upload" value="" tabindex=""
                                           onchange="MaterialsUtils.showPreview(this); MaterialsUtils.validateFileSize(this,'{$UPLOAD_MAXSIZE}');">
                                    <input type="hidden" name="imageType" id="photo-ebook" value="{$filePhotoType}">
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="filename" class="col-md-2 control-label">Nombre:</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Nombre del archivo" id="file-name"
                                       name="filename" value="{$fileName}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="filedescription" class="col-md-2 control-label">Descripción del documento</label>
                            <div class="col-md-10">
                                <textarea id="file-description" name="filedescription" class="form-control" rows="3"
                                          placeholder="Breve descripción del documento">{$fileDescrition}</textarea>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="fileurl" class="col-md-2 control-label">URL:</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Url del documento" id="file-url"
                                       name="fileurl" {if $fileType eq 'UPLOADED'}readonly{/if} value="{$fileUrl}">
                            </div>
                        </div>
                        {* url en el blog *}
                        <div class="row form-group" {if $fileType neq 'UPLOADED'} style="display: none"{/if}>
                            <label for="fileurl" class="col-md-2 control-label">URL del artículo:</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Url del documento" id="file-url"
                                       name="urlInblog" value="{$fileUrlPublic}">
                            </div>
                        </div>
                        {* url en el blog *}

                        <div class="row form-group">
                            <label for="folderid" class="col-md-2 control-label">Carpeta:</label>
                            <div class="col-md-10">
                                <select class="form-control" name="folderid" id="folderid">
                                    <option value="" {if $fileType eq 'UPLOADED'}disabled{/if}>Seleccionar carpeta
                                    </option>
                                    {if $FOLDERS neq NULL}
                                        {foreach $FOLDERS as $key => $folder}
                                            <option value="{$folder->getId()}"
                                                    {if $folder->getId() eq $folderId}selected{elseif $fileType eq 'UPLOADED'}disabled{/if}>{$folder->getName()}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="folderid" class="col-md-2 control-label">Documento destacado:</label>
                            <div class="col-md-10">
                                <select class="form-control" name="featured" id="featured">
                                    {foreach $FEATURED_STATUS as $featuredStatus}
                                        <option value="{$featuredStatus}"
                                                {if $featuredStatus eq $featuredFile}selected{/if} >{$MOD[$featuredStatus]}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        {if $FILES neq NULL}
                            <div class="row form-group">
                                <label for="folderid" class="col-md-2 control-label">Documentos relacionados:</label>
                                <div class="col-md-10">
                                    <select multiple class="form-control" name="relatedfiles[]" id="relatedfiles">
                                        {foreach $FILES as $files}
                                            {if empty($files)}{continue}{/if}
                                            {foreach $files as $file}
                                                {if $file->getId() eq $fileId}{continue}{/if}
                                                <option value="{$file->getId()}"
                                                        {if in_array ($file->getId(), $fileRelated)}selected{/if}>{$file->getName()}</option>
                                            {/foreach}
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        {/if}
                    </div>
                    {* Form *}
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="modules/materials/materials-utils.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
{/strip}