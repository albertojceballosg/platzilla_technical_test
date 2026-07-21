{strip}
    {if ($FOLDER neq NULL)}
        {assign var='folderId' value=$FOLDER->getId()}
        {assign var='folderVideo' value=$FOLDER->getVideo()}
        {assign var='folderName' value=$FOLDER->getName()}
        {assign var='folderDescrition' value=$FOLDER->getDescription()}
        {assign var='folderStatus' value=$FOLDER->getStatus()}
        {assign var="folderPhoto" value=$FOLDER->getPhoto()}
        {assign var="folderUrl" value=$FOLDER->getFolderName()}
        {assign var='siteUrl' value=$SITE_URL}
    {else}
        {assign var='folderId' value=null}
        {assign var='folderName' value=null}
        {assign var='folderVideo' value=null}
        {assign var='folderDescrition' value=null}
        {assign var='folderStatus' value=null}
        {assign var="folderPhoto" value=null}
        {assign var='newsQueue' value=null}
    {/if}
    {assign var='noPhoto' value='themes/centaurus/img/no_images.jpg'}
    <link type="text/css" href="modules/materials/materials.css"/>
    <form method="post" enctype="multipart/form-data" action="index.php">
        <input type="hidden" name="module" value="materials"/>
        <input type="hidden" name="action" value="SaveFolder"/>
        <input type="hidden" name="record" value="{$folderId}"/>
        <input type="hidden" name="myphoto" value="{$folderPhoto}"/>
        <input type="hidden" name="Ajax" value="true"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=materials&action=ListView&parenttab=Settings&tab=folder-tab">{$MOD['APP_TITLE']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=materials&action=ListView&parenttab=Settings&tab=folder-tab" class="btn btn-warning"
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
                    <h2 class="pull-left">Crear carpeta para compartir</h2>
                </header>
                <div class="main-box-body">
                    {* Form *}

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <!--
                                <div class="row form-group">
                                    <label for="filename" class="col-md-4 control-label">Imagen para la carpeta (formato png y tamaño: 148x185px):</label>
                                    <div class="col-md-2">
                                        <div class="fileUpload btn btn-simple pull-left" style="width: 9em; margin-left: 25px">
                                            <span>Examinar</span>
                                            <input type="file" name="imagePhoto" class="upload" value="" tabindex=""
                                                   onchange="MaterialsUtils.showPreview(this); MaterialsUtils.validateFileSize(this,'{$UPLOAD_MAXSIZE}');">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <img id="element-photo" style="width: 25%; height: 25%"
                                             src="{if $folderPhoto eq NULL}{$noPhoto}{else}{$siteUrl|cat:"/"|cat:$folderUrl|cat:"/"|cat:$folderPhoto}{/if}"
                                             class="img-responsive center-block">
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label for="name">Nombre: <span class="required">*</span></label>
                                    <input type="text" id="name" name="name"
                                         {if ($folderId neq NULL) && ($folderName neq NULL)} readonly {/if}value="{$folderName}" class="form-control" maxlength="255"/>
                                </div>
                                <div class="row form-group">
                                    <label for="description" class="col-md-2 control-label">Descripción: </label>
                                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Breve descripción del contenido">{$folderDescrition}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="video-url">Vídeo: </label>
                                    <input type="url" id="video-url" name="video_url"
                                           value="{$folderVideo}" class="form-control lesson-video-url"
                                           maxlength="2048" onchange="MaterialsUtils.showVideo (this);"/>
                                </div>
                                <div class="form-group">
                                    <label for="status">Categorías: </label>
                                    <select class="form-control" name="category" id="category">
                                        <option value="">Seleccione una categoría</option>
                                        {foreach $CATEGORIES as $category}
                                            <option value="{$category->getId ()}"
                                                    {if $CATEGORY_SELECTED eq $category->getId ()}selected{/if} >{$category->getName()}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Estado: </label>
                                    <select class="form-control" name="status" id="status">
                                        {foreach $AVAILABLE_STATUS as $availableStatus}
                                            <option value="{$availableStatus}"
                                                    {if $availableStatus eq $status}selected{/if} >{$MOD[$availableStatus]}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="video" class="embed-responsive embed-responsive-16by9 video"{if ($folderVideo neq NULL)} data-vimeo-url="{$folderVideo}"{/if}>
                                </div>
                            </div>
                        </div>
                    </div>
                    {* Form *}
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
    <script type="text/javascript" src="modules/materials/materials-utils.js"></script>
{/strip}