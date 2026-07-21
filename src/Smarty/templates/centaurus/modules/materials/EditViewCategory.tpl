{strip}
    {if ($CATEGORY neq NULL)}
        {assign var='categoryId' value=$CATEGORY->getId()}
        {assign var='categoryName' value=$CATEGORY->getName()}
        {assign var='categoryDescrition' value=$CATEGORY->getDescription()}
        {assign var='categoryStatus' value=$CATEGORY->getStatus()}
    {else}
        {assign var='categoryId' value=null}
        {assign var='categoryName' value=null}
        {assign var='categoryDescrition' value=null}
        {assign var='categoryStatus' value=null}
    {/if}
    <link type="text/css" href="modules/materials/materials.css"/>
    <form method="post" enctype="multipart/form-data" action="index.php">
        <input type="hidden" name="module" value="materials"/>
        <input type="hidden" name="action" value="SaveCategory"/>
        <input type="hidden" name="record" value="{$categoryId}"/>
        <input type="hidden" name="Ajax" value="true"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=materials&action=ListView&parenttab=Settings&tab=category-tab">{$MOD['APP_TITLE']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=materials&action=ListView&parenttab=Settings&tab=category-tab"
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
                    <h2 class="pull-left">Registrar categorías de documentos</h2>
                </header>
                <div class="main-box-body">
                    {* Form *}
                    <div class="row">
                        <div class="row form-group">
                            <label for="categoryname" class="col-md-2 control-label">Nombre:</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Nombre de la categoría" id="category-name"
                                       name="categoryname" value="{$categoryName}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="categorydescription" class="col-md-2 control-label">Descripción de la categoría</label>
                            <div class="col-md-10">
                                <textarea id="category-description" name="categorydescription" class="form-control" rows="3"
                                          placeholder="Breve descripción de la categoría">{$categoryDescrition}</textarea>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="folderid" class="col-md-2 control-label">Estado:</label>
                            <div class="col-md-10">
                                <select class="form-control" name="status" id="status">
                                    {foreach $CATEGORY_STATUS as $status}
                                        <option value="{$status}"
                                                {if $categoryStatus eq $status}selected{/if} >{$MOD[$status]}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                    {* Form *}
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="modules/materials/materials-utils.js"></script>
{/strip}