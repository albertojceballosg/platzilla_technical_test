{strip}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
    <div class="container-fluid base-list-container">
    <div id="email-box" class="clearfix">
        <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
            <tbody>
            <tr>
                <td rowspan="2" valign="top">
                    <div class="infographic-box" style="width: 30px; padding: 0;">
                        <i class="fa fa-th green-bg"></i>
                    </div>
                </td>
                <td class="heading2" valign="bottom">
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
                        </li>
                        <li class="active">{$MOD.LBL_GRID_VIEW|upper}</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="small" valign="top">{$MOD.LBL_GRID_VIEW_DESCRIPTION}</td>
            </tr>
            </tbody>
        </table>
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <div class="col-xs-6">
                    &nbsp;
                </div>
                <div class="col-xs-6 text-right">
                    <a href="index.php?module=grid_view&action=EditView&parenttab=Settings" class="btn btn-primary">
                        <i class="fa fa-plus-circle"></i> Crear vista cuadricula
                    </a>
                </div>
            </header>
            <div class="main-box-body clearfix" id="ListViewContents">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="col-label"><b>Vista</b></th>
                            <th class="col-modulename"><b>Módulo</b></th>
                            <th class="col-modulename"><b>Posición</b></th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        {if ($GRID_VIEWS neq NULL) }
                            {foreach $GRID_VIEWS as $gridView}
                                <tr class="lvtColData">
                                    <td class="col-label">{$gridView->getLabel()}</td>
                                    <td class="col-modulename">{$gridView->getModuleName()|getTranslatedString: $gridView->getModuleName()}</td>
                                    <td class="col-label">{$MOD[$gridView->getPosition()]}</td>
                                    <td class="col-actions">
                                        <ul class="actions">
                                            <li class="action">
                                                <form method="post">
                                                    <input type="hidden" name="module" value="grid_view"/>
                                                    <input type="hidden" name="action" value="ChangeStatus"/>
                                                    <input type="hidden" name="tabname" value="{$gridView->getTabName()}"/>
                                                    <input type="hidden" name="tablabel" value="{$gridView->getModuleName()}"/>
                                                    <input type="hidden" name="gridviewname"
                                                           value="{$gridView->getGridViewName()}"/>
                                                    <input type="hidden" name="viewstatus"
                                                           value="{$gridView->getStatus()}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button class="btn {if $gridView->getStatus() eq 'ENABLED'}btn-success{else}btn-warning{/if}" type="button"{if $gridView->getTabName() eq 'Home'} disabled {/if}onclick="GridViewUtils.changeStatus(this)"
                                                            title="{if $gridView->getStatus() eq 'ENABLED'}Desahabilitar{else}Habilitar{/if}">
                                                        {if $gridView->getStatus() eq 'ENABLED'}
                                                            <i class="fa fa-check"></i>
                                                        {else}
                                                            <i class="fa fa-ban"></i>
                                                        {/if}
                                                    </button>
                                                </form>
                                            </li>
                                            <li class="action">
                                                <a href="index.php?module=grid_view&action=EditView&record={$gridView->getId()}&parenttab=Settings"
                                                   class="btn btn-primary" title="Editar">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            </li>
                                            <li class="action">
                                                <form method="post">
                                                    <input type="hidden" name="module" value="grid_view"/>
                                                    <input type="hidden" name="action" value="DeleteView"/>
                                                    <input type="hidden" name="tablabel" value="{$gridView->getModuleName()}"/>
                                                    <input type="hidden" name="tabname" value="{$gridView->getTabName()}"/>
                                                    <input type="hidden" name="gridviewname"
                                                           value="{$gridView->getGridViewName()}"/>
                                                    <input type="hidden" name="viewlabel" value="{$gridView->getLabel()}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button class="btn btn-danger" type="button"{if $gridView->getTabName() eq 'Home'} disabled {/if}onclick="GridViewUtils.deleteGridView(this)" title="Eliminar">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr class="lvtColData">
                                <td colspan="6" class="text-center">No hay vistas registradas</td>
                            </tr>
                        {/if}
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    </div>
    <script type="text/javascript" src="modules/grid_view/grid-view.js"></script>
{/strip}