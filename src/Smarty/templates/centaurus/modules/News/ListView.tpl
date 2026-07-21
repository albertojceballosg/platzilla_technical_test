{strip}
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
                        <li class="active">TABLÓN DE ANUNCIOS</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="small" valign="top">Administrar anuncios</td>
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
                <li {if ($SELECTED_TAB eq 'news-tab')} class="active"{/if}><a data-toggle="tab" href="#news-tab">Anuncios</a></li>
                <li {if ($SELECTED_TAB eq 'ad-queue-tab')} class="active"{/if}><a data-toggle="tab" href="#ad-queue-tab">Colas de anuncios</a></li>
            </ul>
            <div class="main-box-body clearfix">
                <div class="tab-content">
                    <div id="news-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'news-tab')} active{/if}">
                        <header class="main-box-header clearfix text-right">
                            <div class="pull-right">
                                <a href="index.php?module=News&action=EditView&parenttab=Settings" class="btn btn-primary"><i
                                            class="fa fa-plus-circle"></i> Crear anuncio</a>
                            </div>
                        </header>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="col-title">Título</th>
                                    <th class="col-from">Categoría</th>
                                    <th class="col-from">Desde</th>
                                    <th class="col-to">Hasta</th>
                                    <th class="col-to">Estado</th>
                                    <th class="col-actions">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if ($DATA.totalRecords > 0) }
                                    {foreach $DATA.records as $newsData}
                                        {if $newsData->getAdQueue() neq NULL}
                                            {assign var='newsQueue' value=$newsData->getAdQueue()}
                                        {else}
                                            {assign var='newsQueue' value=null}
                                        {/if}
                                        <tr>
                                            <td class="col-title">
                                                <a href="index.php?module=News&action=EditView&record={$newsData->getId()}&parenttab=Settings">{$newsData->getTitle()}</a>
                                            </td>
                                            <td class="col-from">{$MOD[$newsData->getCategories()]}</td>
                                            <td class="col-from">{if $newsQueue neq NULL}{$newsData->getCreateDateFormat()}{else}{$newsData->getInitDay()}{/if}</td>
                                            <td class="col-to">{if $newsQueue neq NULL}{$MOD[$AVAILABLE_PERIODS[$newsQueue->getPeriod()]]}{else}{$newsData->getDueDate()}{/if}</td>
                                            <td class="col-to">{if $AVAILABLE_STATUS[0] eq $newsData->getStatus()}{$MOD[$AVAILABLE_STATUS[0]]}{else}{$MOD[$AVAILABLE_STATUS[1]]}{/if}</td>
                                            <td class="col-actions">
                                                <form action="index.php" class="form-inline" method="post"
                                                      onclick="return confirm ('¿Estás seguro que quieres eliminar el anuncio seleccionado?');">
                                                    <input type="hidden" name="module" value="News"/>
                                                    <input type="hidden" name="action" value="Delete"/>
                                                    <input type="hidden" name="record" value="{$newsData->getId()}"/>
                                                    <button type="submit" class="btn btn-danger btn-icon"
                                                            title="Eliminar">
                                                        <i class="fa fa-trash-o"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr class="lvtColData">
                                        <td colspan="4" class="text-center">No hay anuncios registrados</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        {if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1)}
                            <ul class="pagination pull-right">
                                <li{if ($DATA.page == 1) } class="disabled"{/if}>
                                    <a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=News&action=ListView&parenttab=Settings&page=1{/if}"><i
                                                class="fa fa-step-backward"></i></a>
                                </li>
                                <li{if ($DATA.page == 1)} class="disabled"{/if}>
                                    <a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=News&action=ListView&parenttab=Settings&page={$DATA.page - 1}{/if}"><i
                                                class="fa fa-chevron-left"></i></a>
                                </li>
                                {for $i=1 to $DATA.totalPages}
                                    <li{if ($i == $DATA.page)} class="active"{/if}>
                                        <a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=News&action=ListView&parenttab=Settings&page={$i}{/if}">{$i}</a>
                                    </li>
                                {/for}
                                <li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
                                    <a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=News&action=ListView&parenttab=Settings&page={$DATA.page + 1}{/if}"><i
                                                class="fa fa-chevron-right"></i></a>
                                </li>
                                <li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
                                    <a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=News&action=ListView&parenttab=Settings&page={$DATA.totalPages}{/if}"><i
                                                class="fa fa-step-forward"></i></a>
                                </li>
                            </ul>
                        {/if}

                    </div>
                    {* AdQueue *}
                    <div id="ad-queue-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'ad-queue-tab')} active{/if}">
                        <header class="main-box-header clearfix text-right">
                            <div class="pull-right">
                                <a href="index.php?module=News&action=EditViewQueues&parenttab=Settings" class="btn btn-primary"><i
                                            class="fa fa-plus-circle"></i> Crear cola de anuncios</a>
                            </div>
                        </header>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="col-title">Cola</th>
                                    <th class="col-from">Frecuencia</th>
                                    <th class="col-to">Fecha Inicio</th>
                                    <th class="col-to">Estado</th>
                                    <th class="col-actions">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if ($AD_QUEUES neq NULL)}
                                    {foreach $AD_QUEUES as $adQueue}
                                        <tr>
                                            <td class="col-title">
                                                {*$adQueue|var_dump*}
                                                <a href="index.php?module=News&action=EditViewQueues&record={$adQueue->getId()}&parenttab=Settings">{$adQueue->getName()}</a>
                                            </td>
                                            <td class="col-from">{$MOD[$AVAILABLE_PERIODS[$adQueue->getPeriod()]]}</td>
                                            <td class="col-to">{$adQueue->getInitDay()}</td>
                                            <td class="col-to">{if $AVAILABLE_STATUS[0] eq $adQueue->getStatus()}{$MOD[$AVAILABLE_STATUS[0]]}{else}{$MOD[$AVAILABLE_STATUS[1]]}{/if}</td>
                                            <td class="col-actions">
                                                <form action="index.php" class="form-inline" method="post"
                                                      onclick="return confirm ('¿Estás seguro que quieres eliminar la cola de anuncios seleccionado?');">
                                                    <input type="hidden" name="module" value="News"/>
                                                    <input type="hidden" name="action" value="DeleteAdQueue"/>
                                                    <input type="hidden" name="record" value="{$adQueue->getId()}"/>
                                                    <input type="hidden" name="tab" value="ad-queue-tab}"/>
                                                    <button type="submit" class="btn btn-danger btn-icon"
                                                            title="Eliminar">
                                                        <i class="fa fa-trash-o"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr class="lvtColData">
                                        <td colspan="4" class="text-center">No hay cola de anuncios registrados</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* / AdQueue *}
                </div>
            </div>
        </div>
    </div>
{/strip}