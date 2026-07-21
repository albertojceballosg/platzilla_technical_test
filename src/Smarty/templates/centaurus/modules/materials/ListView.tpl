{strip}
    {if $FOLDER_ID eq NULL}
        {assign var='folderId' value=null}
    {else}
        {assign var='folderId' value=$FOLDER_ID}
    {/if}
    <div id="email-box" class="clearfix" style="padding-bottom: 20px;">
        <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
            <tbody>
            <tr>
                <td rowspan="2" valign="top">
                    <div class="infographic-box" style="width: 30px; padding: 0;"><i
                                class="fa fa-bullhorn purple-bg"></i>
                    </div>
                </td>
                <td class="heading2" valign="bottom">
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                        </li>
                        <li class="active">{$MOD['APP_TITLE']}</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="small" valign="top">{$MOD['APP_DESCRIPTION']}</td>
            </tr>
            </tbody>
        </table>
        {if (!empty ($MESSAGE))}
            <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
                <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        {/if}
        <div class="main-box clearfix">
            <ul class="nav nav-tabs">
                <li {if ($SELECTED_TAB eq 'category-tab')} class="active"{/if}><a data-toggle="tab"
                                                                                  href="#category-tab">{$MOD['BTL_CATEGORIES']}</a>
                </li>
                <li {if ($SELECTED_TAB eq 'folder-tab')} class="active"{/if}><a data-toggle="tab"
                                                                                href="#folder-tab">{$MOD['BTL_FOLDERS']}</a>
                </li>
                <li {if ($SELECTED_TAB eq 'files-tab')} class="active"{/if}><a data-toggle="tab"
                                                                               href="#files-tab">{$MOD['BTL_FILES']}</a>
                </li>
            </ul>
            <div class="main-box-body clearfix">
                <div class="tab-content">
                    {* Categories *}
                    <div id="category-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'category-tab')} active{/if}">
                        <header class="main-box-header clearfix text-right">
                            <div class="pull-right">
                                <a href="index.php?module=materials&action=EditViewCategory&parenttab=Settings"
                                   class="btn btn-primary"><i
                                            class="fa fa-plus-circle"></i>&nbsp;{$MOD['BTL_CREATE_CATEGORY']}</a>
                            </div>
                        </header>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="col-title" style="width: 12%">{$MOD['BTL_CATEGORIES']}</th>
                                    <th class="col-from">&nbsp;</th>
                                    <th class="col-from">&nbsp;</th>
                                    <th class="col-to" style="width: 15%">{$MOD['BTL_DATE']}</th>
                                    <th class="col-to" style="width: 10%">{$MOD['BTL_STATUS']}</th>
                                    <th class="col-actions" style="width: 8%">{$MOD['BTL_ACTION']}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $CATEGORIES neq NULL}
                                    {foreach $CATEGORIES as $category}
                                        <tr>
                                            <td class="col-title">
                                                <a href="index.php?module=materials&action=EditViewCategory&record={$category->getId()}&parenttab=Settings">{$category->getName()}</a>
                                            </td>
                                            <td class="col-from"><p
                                                        style="text-align: left">{if $category->getDescription() neq NULL}{$category->getDescription()|truncate:60:"...":true}{else}{$category->getSubject()|truncate:60:"...:true"}{/if}</p>
                                            </td>
                                            <td class="col-from">&nbsp;</td>
                                            <td class="col-to">{$category->getCreateDate()->format ('d-m-Y')}</td>
                                            <td class="col-to">{if $CATEGORY_STATUS[0] eq $category->getStatus()}{$MOD[$CATEGORY_STATUS[0]]}{else}{$MOD[$CATEGORY_STATUS[1]]}{/if}</td>
                                            <td class="col-actions">
                                                <form action="index.php" class="form-inline" method="post">
                                                    <input type="hidden" name="module" value="materials"/>
                                                    <input type="hidden" name="action" value="DeleteCategories"/>
                                                    <input type="hidden" name="record" value="{$category->getId()}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button type="button" class="btn btn-danger btn-icon"
                                                            onclick="MaterialsUtils.deleteCategories (this)"
                                                            title="Eliminar">
                                                        <i class="fa fa-trash-o"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr class="lvtColData">
                                        <td colspan="6" class="text-center">{$MOD['BTL_EMPTY_CATEGORIES']}</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* / Categories *}
                    <div id="folder-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'folder-tab')} active{/if}">
                        <header class="main-box-header clearfix text-right">
                            <div class="pull-right">
                                <a href="index.php?module=materials&action=EditViewFolders&parenttab=Settings"
                                   class="btn btn-primary"><i
                                            class="fa fa-plus-circle"></i>&nbsp;{$MOD['BTL_CREATE_FOLDER']}</a>
                            </div>
                        </header>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="col-title" style="width: 15%">{$MOD['BTL_FOLDERS']}</th>
                                    <th class="col-from">&nbsp;</th>
                                    <th class="col-from">&nbsp;</th>
                                    <th class="col-to" style="width: 12%">{$MOD['BTL_DATE']}</th>
                                    <th class="col-to" style="width: 10%">{$MOD['BTL_STATUS']}</th>
                                    <th class="col-actions" style="width: 8%">{$MOD['BTL_ACTION']}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $FOLDERS neq NULL}
                                    {foreach $FOLDERS as $folder}
                                        <tr>
                                            <td class="col-title">
                                                <a href="index.php?module=materials&action=EditViewFolders&record={$folder->getId()}&parenttab=Settings">{$folder->getName()}</a>
                                            </td>
                                            <td class="col-from"><p
                                                        style="text-align: left">{if $folder->getDescription() neq NULL}{$folder->getDescription()|truncate:60:"...":true}{else}{$folder->getSubject()|truncate:60:"...:true"}{/if}</p>
                                            </td>
                                            <td class="col-from">&nbsp;</td>
                                            <td class="col-to">Hace:&nbsp;{$folder->getCreateTime()}</td>
                                            <td class="col-to">{if $AVAILABLE_STATUS[0] eq $folder->getStatus()}{$MOD[$AVAILABLE_STATUS[0]]}{else}{$MOD[$AVAILABLE_STATUS[1]]}{/if}</td>
                                            <td class="col-actions">
                                                <form action="index.php" class="form-inline" method="post">
                                                    <input type="hidden" name="module" value="materials"/>
                                                    <input type="hidden" name="action" value="DeleteFolder"/>
                                                    <input type="hidden" name="record" value="{$folder->getId()}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button type="button" class="btn btn-danger btn-icon"
                                                            onclick="MaterialsUtils.deleteFolder (this)"
                                                            title="Eliminar">
                                                        <i class="fa fa-trash-o"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr class="lvtColData">
                                        <td colspan="6" class="text-center">{$MOD['BTL_EMPTY_FOLFERS']}</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* Files *}
                    <div id="files-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'files-tab')} active{/if}">
                        <header class="main-box-header clearfix text-right" style="padding: 12px 0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="pull-left">
                                        <form action="index.php" class="form-inline" method="post">
                                            <input type="hidden" name="module" value="materials"/>
                                            <input type="hidden" name="action" value="ListView"/>
                                            <select class="form-control" name="folderid" id="folderid"
                                                    onchange="submit()">
                                                <option value="" {if $fileType eq 'UPLOADED'}disabled{/if}>Seleccionar
                                                    carpeta
                                                </option>
                                                {if $FOLDERS neq NULL}
                                                    {foreach $FOLDERS as $key => $folder}
                                                        <option value="{$folder->getId()}"
                                                                {if $folder->getId() eq $folderId}selected{elseif $fileType eq 'UPLOADED'}disabled{/if}>{$folder->getName()}</option>
                                                    {/foreach}
                                                {/if}
                                            </select>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="pull-right">
                                        <a href="index.php?module=materials&action=EditViewFiles&parenttab=Settings"
                                           class="btn btn-primary"><i
                                                    class="fa fa-plus-circle"></i>&nbsp;{$MOD['BTL_CREATE_DOCUMENTS']}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </header>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="col-title" style="width: 45%">{$MOD['BTL_DOCUMENTS']}</th>
                                    <th class="col-from" style="width: 12%">{$MOD['BTL_FOLDERS']}</th>
                                    <th class="col-to" style="width: 10%">{$MOD['BTL_DATE']}</th>
                                    <th class="col-to" style="width: 8%">{$MOD['BTL_VIEWED']}</th>
                                    <th class="col-to" style="width: 8%">{$MOD['BTL_TYPE']}</th>
                                    <th class="col-to" style="width: 5%">{$MOD['BTL_FEATURED']}</th>
                                    <th class="col-actions" style="width: 7%">{$MOD['BTL_ACTION']}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if ($FOLDERS neq NULL)}
                                    {foreach $FOLDERS as $folder}
                                        {if ($folder->getFiles() eq NULL) || (($folderId neq NULL) && ($folder->getId () neq $folderId))}{continue}{/if}
                                        {foreach $folder->getFiles() as $file}
                                            <tr>
                                                <td class="col-title">
                                                    {*$file->getName()*}
                                                    <a href="index.php?module=materials&action=EditViewFiles&record={$file->getId()}&type={$file->getType()}&parenttab=Settings">{$file->getName()}</a>
                                                </td>
                                                <td class="col-from"><a
                                                            href="index.php?module=materials&action=EditViewFolders&record={$folder->getId()}&parenttab=Settings">{$folder->getName()}</a>
                                                </td>
                                                <td class="col-to">Hace:&nbsp;{$file->getCreateTime()}</td>
                                                <td class="col-to">{$file->getViewed()}</td>
                                                <td class="col-to">{if $AVAILABLE_TYPE[0] eq $file->getType()}{$MOD[$AVAILABLE_TYPE[0]]}{else}{$MOD[$AVAILABLE_TYPE[1]]}{/if}</td>
                                                <td class="col-to">{if $file->getFeatured() eq $FEATURED_STATUS[0]}
                                                        <i class="fa fa-star"></i>
                                                    {else}
                                                        <i class="fa fa-star-o"></i>
                                                    {/if}</td>
                                                <td class="col-actions">
                                                    <div class="row">
                                                        <div class="col-xs-6">
                                                            <form action="index.php" class="form-inline" method="post">
                                                                <input type="hidden" name="module" value="materials"/>
                                                                <input type="hidden" name="action" value="DeleteFile"/>
                                                                <input type="hidden" name="record"
                                                                       value="{$file->getId()}"/>
                                                                <input type="hidden" name="Ajax" value="true"/>
                                                                <button type="button" class="btn btn-danger btn-icon"
                                                                        onclick="MaterialsUtils.deleteFile (this)"
                                                                        title="Eliminar">
                                                                    <i class="fa fa-trash-o"></i></button>
                                                            </form>
                                                        </div>
                                                        <div class="col-xs-6">
                                                            {if $file->getUrlBlog() neq NULL}
                                                                <button type="button" data-file="{$file->getId()}"
                                                                        class="btn btn-success btn-icon"
                                                                        onclick="MaterialsUtils.copyUrl ('{$file->getId()}')"
                                                                        title="Copiar url">
                                                                    <i class="fa fa-clipboard" aria-hidden="true"></i>
                                                                </button>
                                                                <input type="hidden" id="url-{$file->getId()}"
                                                                       value="{$SITE_URL}{$file->getUrlBlog()}">
                                                            {/if}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {/foreach}
                                {else}
                                    <tr class="lvtColData">
                                        <td colspan="6" class="text-center">{$MOD['BTL_EMPTY_DOCUMENTS']}</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* / Files *}
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="modules/materials/materials-utils.js"></script>
{/strip}