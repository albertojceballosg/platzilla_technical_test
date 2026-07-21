{strip}
    {if (isset ($MODULE))}
        {assign var='moduleName' value=$MODULE->getName ()}
    {else}
        {assign var='moduleName' value=null}
    {/if}
    <script type="text/html" id="grid-properties-modal-template">
        <div id="grid-properties-modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Propiedades del campo <span id="field-name"></span></h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="fieldname"/>
                        <input type="hidden" name="modulename" value="{$moduleName}"/>
                        <input type="hidden" id="calculatedSystemId" name="calculatedSystemId">
                        <div class="panel-group">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#field-properties"
                                           href="#grid-properties">Tablas inteligentes</a>
                                    </h4>
                                </div>
                                <div id="grid-properties" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <form id="grid-form" name="grid-form">
                                            <input type="hidden" name="fieldId"/>
                                            <input type="hidden" id="idEditSelected" name="idEditSelected"/>
                                            <input type="hidden" id="valEditSelected"/>
                                            <input type="hidden" id="field-list-edit" name="listaCampo"/>
                                            <input type="hidden" id="module-list-edit" name="actualLista"/>
                                            <input type="hidden" name="importModuleReference"
                                                   id="importEditModuleReference" value=""/>
                                            <input type="hidden" name="modulename" value="{$moduleName}"/>
                                            <div class="col-xs-12" style="margin-top: 12px;">
                                                <ul class="nav nav-tabs">
                                                    <li>
                                                        <a href="#tab-importar" role="tab"
                                                           data-toggle="tab">Importar</a>
                                                    </li>
                                                    <li class="active">
                                                        <a href="#tab-columnas" role="tab"
                                                           data-toggle="tab">Columnas</a>
                                                    </li>
                                                    <li>
                                                        <a href="#tab-acciones" role="tab"
                                                           data-toggle="tab">Acciones</a>
                                                    </li>
                                                    <li><a href="#tab-filters" role="tab" data-toggle="tab">Aspecto</a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <div class="tab-pane" role="tabpanel" id="tab-importar">
                                                        <div class="row" style="margin: 12px 0;">
                                                            <div class="col-md-12">
                                                                <h4 class="pull-left bloc-name">&nbsp;</h4>
                                                            </div>
                                                            {if ($MODULE_WITH_GRID)}
                                                                <div class="col-md-12">
                                                                    <select class="form-control" id="moduleEditFieldId"
                                                                            name="moduleFieldId"
                                                                            onchange="GridPropertiesUtils.getGridImportField (this);"
                                                                            title="">
                                                                        <option value="0">Seleccionar tabla</option>
                                                                        {foreach from=$MODULE_WITH_GRID key=k item=v}
                                                                            <option value="{$v.fieldid}@{$v.name}">{$v.tablabel}
                                                                                : {$v.fieldlabel}</option>
                                                                        {/foreach}
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-12">&nbsp;</div>
                                                                <div class="row">
                                                                    <div class="col-md-12" style="display: none">
                                                                        <input type="checkbox" id="column0"
                                                                               name="column[0]" value="0"
                                                                               checked="checked" placeholder=""/>
                                                                    </div>
                                                                    <div class="col-xs-12" id="mix-edit-grid">&nbsp;
                                                                    </div>
                                                                </div>
                                                            {else}
                                                                <div class="col-md-12" style="margin-top: 25px">
                                                                    <div class="alert alert-info alert-dismissable">
                                                                        <button type="button" class="close"
                                                                                data-dismiss="alert">&times;
                                                                        </button>
                                                                        No se encontraron tablas para combinar!
                                                                    </div>
                                                                </div>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane active" role="tabpanel" id="tab-columnas">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <h4 class="pull-left bloc-name">&nbsp;</h4>
                                                            </div>
                                                            <div class="table-responsive col-md-12" style="top: -15px;">
                                                                <table class="table" id="proTabFields">
                                                                    {$MOD.POS_CAMPO = 'Orden'}
                                                                    {$MOD.LBL_ETIQUETA_CAMPO= 'Columna'}
                                                                    {$MOD.LBL_NOMBRE_CAMPO = '&nbsp;'}
                                                                    {$MOD.LBL_ADD_CAMPO = ' Añadir columna'}
                                                                    {$NOMBRE_WIDTH = '15%'}
                                                                    {include file='Settings/GridManager/GridEditStep3Field.tpl' BLOCK_NAME='' BLOCK_NUMBER='1' FIELD_NAMES=array()}
                                                                </table>
                                                                <hr>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tab-acciones">
                                                        <div class="row" style="margin: 12px 0; min-height: 200px;">
                                                            <div class="col-md-12">
                                                                <div class="btn-group">
                                                                    <button type="button"
                                                                            class="btn btn-success dropdown-toggle"
                                                                            data-toggle="dropdown"> Vinculaciones
                                                                        <span class="caret"></span></button>
                                                                    &nbsp;
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        <li>
                                                                            <a href="#"
                                                                               onclick="GridPropertiesUtils.linkGridEditAction ();">Vincular
                                                                                listas</a>
                                                                        </li>
                                                                        <li>
                                                                            <a href="#"
                                                                               onclick="GridPropertiesUtils.editImportFieldAction();">Importar
                                                                                Valores</a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="btn-group">
                                                                    <button type="button"
                                                                            class="btn btn-success dropdown-toggle"
                                                                            data-toggle="dropdown"> Activaciones
                                                                        <span class="caret"></span></button>
                                                                    &nbsp;
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        <li>
                                                                            <a href="#"
                                                                               onclick="GridPropertiesUtils.checkGridEditAction ();">Acivar
                                                                                campos</a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="btn-group">
                                                                    <button type="button"
                                                                            class="btn btn-success dropdown-toggle"
                                                                            data-toggle="dropdown">
                                                                        Fila Resumen <span class="caret"></span>
                                                                    </button>
                                                                    &nbsp;
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        <li>
                                                                            <a href="#"
                                                                               onclick="GridPropertiesUtils.summaryEditFieldAction ()">Activar
                                                                                fila resumen</a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12" style="display: inline">
                                                                <div id="editLinkField"
                                                                     class="table-responsive col-md-12 hide">
                                                                    <h5 class="pull-left bloc-name">Vincular
                                                                        listas.</h5>
                                                                    <div class="pull-right">
                                                                        <button type="button" class="btn btn-danger"
                                                                                onclick="GridPropertiesUtils.removeGridActions ('tr-list-action');">
                                                                            Eliminar acciones de lista
                                                                        </button>
                                                                    </div>
                                                                    <table class="table action-fields"
                                                                           id="editActionTable">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>Campo Lista</th>
                                                                            <th>Valor de lista</th>
                                                                            <th>Vincular con:</th>
                                                                            <th>Campo Destino</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody></tbody>
                                                                    </table>
                                                                    <hr>
                                                                </div>
                                                                {* Importar Valores *}
                                                                <div id="gridEditImportValues"
                                                                     class="table-responsive col-md-12 hide">
                                                                    <h5 class="pull-left bloc-name">Importar
                                                                        Valores.</h5>
                                                                    <div id="div-modules-to-editImport"
                                                                         class="table-responsive hide col-md-12"
                                                                         style="margin: 12px 0; padding: 0 25px">
                                                                        <select id="select-modules-to-editImport"
                                                                                class="center-block form-control"
                                                                                title="Del modulo"
                                                                                onchange="GridPropertiesUtils.searchFieldToEditImport (this)"></select>
                                                                    </div>
                                                                    <div class="pull-right">
                                                                        <button type="button" class="btn btn-danger"
                                                                                onclick="GridPropertiesUtils.removeEditImport ();">
                                                                            Eliminar Valores Importados
                                                                        </button>
                                                                    </div>
                                                                    <hr id="separator-edit-line">
                                                                </div>
                                                                {* Aciones de checkbox *}
                                                                <div id="editCheckField"
                                                                     class="table-responsive col-md-12 hide">
                                                                    <h5 class="pull-left bloc-name">Activar campos.</h5>
                                                                    <div class="pull-right">
                                                                        <button type="button" class="btn btn-danger"
                                                                                onclick="GridPropertiesUtils.removeGridActions ('tr-check-action');">
                                                                            Eliminar acciones de Checkbox
                                                                        </button>
                                                                    </div>
                                                                    <table class="table action-fields"
                                                                           id="editActionCheck">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>Campo Checkbox</th>
                                                                            <th>Estado al marcar</th>
                                                                            <th>Campo(s) afectado(s)</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody></tbody>
                                                                    </table>
                                                                    <hr/>
                                                                </div>
                                                                {* /Aciones de checkbox *}
                                                            </div>
                                                            <div id="summaryField"
                                                                 class="table-responsive col-md-12 hide">
                                                                <h5 class="pull-left bloc-name">Fila resumen.</h5>
                                                                <div class="pull-right" style="margin-bottom: 12px">
                                                                    <button type="button" class="btn btn-danger"
                                                                            onclick="GridPropertiesUtils.removeEditSummary ('td-summary-action');">
                                                                        Eliminar fila resumen
                                                                    </button>
                                                                </div>
                                                                <table class="table table-bordered action-summary"
                                                                       id="actionSummary">
                                                                    <thead></thead>
                                                                    <tbody>
                                                                    <tr class="tr-summary-action"></tr>
                                                                    </tbody>
                                                                </table>
                                                                <hr>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" role="tabpanel" id="tab-filters">
                                                        <div class="row" style="margin: 12px 0; min-height: 200px;">
                                                            <div class="col-md-12">
                                                                <div class="btn-toolbar" role="toolbar">
                                                                    <div class="btn-group-sm">
                                                                        <button type="button" class="btn btn-success"
                                                                                onclick="GridPropertiesUtils.setGridColorEditFilter ();">
                                                                            Filtro de color
                                                                        </button>
                                                                        &nbsp;
                                                                        <button type="button" class="btn btn-success">
                                                                            Filtro de datos
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="editFilterFieldTable"
                                                                 class="table-responsive col-md-12 hide">
                                                                <div class="pull-right">
                                                                    <button type="button"
                                                                            class="btn btn-success btn-sm addButton"
                                                                            onclick="GridPropertiesUtils.addGridColorFilter ();">
                                                                        <i class="fa fa-plus" aria-hidden="true"
                                                                           style="margin-right: 1px"></i> Condición
                                                                    </button>
                                                                    &nbsp;
                                                                </div>
                                                                <table class="table action-fields" id="editFilterTable">
                                                                    <thead>
                                                                    <tr>
                                                                        <th width="20%">Columna a pintar</th>
                                                                        <th width="10%">Color</th>
                                                                        <th width="20%">Columna condicionante</th>
                                                                        <th>Condición</th>
                                                                        <th>Valor</th>
                                                                        <th width="10%">&nbsp</th>
                                                                        <th>&nbsp</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody></tbody>
                                                                </table>
                                                                <hr/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary grid-stuff hidden"
                                onclick="GridPropertiesUtils.saveDataGrid ()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <script type="text/html" id="field-edit-template">
        {include file='Settings/GridManager/GridEditStep3FieldDetail.tpl'
        BLOCK_NUMBER=''
        FIELD_LABEL=''
        FIELD_LENGTH=''
        FIELD_MODULE= ''
        FIELD_NAME=''
        FIELD_PRECISION=''
        FIELD_PREFIX=''
        FIELD_SEQUENCE=''
        FIELD_TYPE=1
        FIELD_VALUE=''
        VISIBLE=true
        }
    </script>
    <script type="text/html" id="edit-action-template">
        {include file='Settings/fieldActionTable.tpl' }
    </script>
    <script type="text/html" id="edit-check-action-template">
        {include file='Settings/fieldActionChk.tpl' }
    </script>
    <script type="text/html" id="edit-filter-color-template">
        {include file='Settings/filterColorTable.tpl' }
    </script>
    <script type="text/html" id="grid-summary-edit-template">
        {include file='Settings/summaryEditActionTable.tpl' }
    </script>
    <script type="text/html" id="grid-RelationShip-edit-template">
        {include file='Settings/GridManager/moduleRelationShipEdit.tpl' }
    </script>
    <script type="text/html" id="import-module-fields-edit-tabla-template">
        <table class="table action-fields" data-module="__DATA_MODULE__" id="importEditFieldsTable-__ID__">
            <thead>
            <tr>
                <th>Importar el valor del Campo</th>
                <th>A la Columna</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <tr id="edit-import-bar-__ID__" class="edit-import-bar hide">
                <td colspan="3" align="center" style="padding: 1px;">
                    <div class="center-block">
                        <button type="button" class="btn btn-primary btn-icon "
                                data-target="#importEditFieldsTable-__ID__"
                                onclick="GridPropertiesUtils.addEditImportRelationship (this);"><i
                                    class="fa fa-plus"></i></button>
                        <button type="button" class="btn btn-danger btn-icon"
                                data-target="#importEditFieldsTable-__ID__"
                                onclick="GridPropertiesUtils.deleteEditImportRelationship (this);"><i
                                    class="fa fa-minus"></i></button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </script>
{/strip}