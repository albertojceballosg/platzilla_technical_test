{strip}
    <style>
        .isDisabled > a {
            color: currentColor;
            display: inline-block; /* For IE11/ MS Edge bug */
            pointer-events: none;
            text-decoration: none;
        }
    </style>
    {if (isset ($MODULE))}
        {assign var='moduleName' value=$MODULE->getName ()}
    {else}
        {assign var='moduleName' value=null}
    {/if}
    <script type="text/html" id="editable-fields-modal-template">
        <div class="modal fade" id="editable-fields-modal" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Botón para campos editables</h4>
                    </div>
                    <div class="row modal-body filters-section">
                        <div class="row">
                            <div class="col-xs-12" style="margin: 6px 0">
                                <ul id="ce-nav-tab" class="nav nav">
                                    <li class="active"><a data-toggle="tab" onclick="EditableFieldsUtils.listView(this)"
                                                          class="btn btn-xs" href="#ec-list-view" role="button"><span
                                                    class="glyphicon glyphicon-align-right"></span>&nbsp;{$MOD.LBL_EDITABLE_FIELDS_LIST}
                                        </a>
                                    </li>
                                    <li style="padding: 0 10px"><a data-toggle="tab"
                                                                   onclick="EditableFieldsUtils.createView(this)"
                                                                   class="btn btn-xs" href="#ec-create-view"
                                                                   role="button"><span
                                                    class="glyphicon glyphicon-plus-sign"></span>&nbsp;{$MOD.LBL_EDITABLE_FIELDS_CREATE}
                                        </a>
                                    </li>
                                    <li><a class="hide" data-toggle="tab" onclick="EditableFieldsUtils.editView(this)"
                                           href="#ec-edit-view"><span
                                                    class="glyphicon glyphicon-pencil"></span>&nbsp;</a>
                                    </li>
                                </ul>
                                <hr style="width: 95%;text-align: center">
                            </div>
                            <div class="col-xs-12">
                                <div class="tab-content">
                                    <div id="ec-list-view" class="ec-tab tab-pane fade in active">
                                        {$EDITABLE_FIELDS_LIST}
                                    </div>
                                    <div id="ec-create-view" class="ec-tab tab-pane fade">
                                        {* Create Button *}
                                        <form action="index.php" name="editable-fields-form">
                                            <input type="hidden" name="module" value="Settings"/>
                                            <input type="hidden" name="action" value="SaveEditableFieldsButton"/>
                                            <input type="hidden" name="Ajax" value="true"/>
                                            <input type="hidden" name="formodule" value="{$moduleName}"/>
                                            <input type="hidden" name="buttonname" value=""/>
                                            {*<input type="hidden" name="asopotamadre" value="1"/>*}
                                            <div class="col-xs-12 button-group">
                                                <div class="row">
                                                    <div class="form-group col-xs-12">
                                                        <label for="btn-title"
                                                               class="col-xs-2 control-label">Botón</label>
                                                        <div id="ce-div-buttonlabel" class="col-xs-10">
                                                            <input type="text" class="form-control" id="btn-title"
                                                                   name="buttonlabel" title="Título del botón"
                                                                   maxlength="25"
                                                                   placeholder="Título del botón">
                                                            <span id="ce-buttonlabel" class="help-block"></span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-xs-12">
                                                        <label for="btn-rescription" class="col-xs-2 control-label">Descripción</label>
                                                        <div id="ce-div-descriptions" class="col-xs-10">
                                                            <input type="text" class="form-control" id="btn-rescription"
                                                                   title="Descripción del botón"
                                                                   name="description"
                                                                   maxlength="60"
                                                                   placeholder="Descripción del botón">
                                                            <span id="ce-descriptions" class="help-block"></span>
                                                        </div>
                                                    </div>
                                                    {*  estado del boton*}
                                                    <div class="form-group col-xs-12">
                                                        <label for="btn-field"
                                                               class="col-xs-2 control-label">Estatus</label>
                                                        <div id="ce-div-status" class="col-xs-10">
                                                            <select name="status" class="form-control "
                                                                    title="Estatus del campo">
                                                                <option value="">Seleccione..</option>
                                                                <option value="1">{$MOD.LBL_ACTIVE}</option>
                                                                <option value="0">{$MOD.LBL_INACTIVE}</option>
                                                            </select>
                                                            <span id="ce-status" class="help-block"></span>
                                                        </div>
                                                    </div>
                                                    {*  estado del boton*}
                                                    <div id="btn-field" class="form-group col-xs-12">
                                                        <label for="btn-field"
                                                               class="col-xs-2 control-label">Campo</label>
                                                        <div id="ce-div-fields[]" class="col-xs-9">
                                                            <select name="fields[]" class="form-control "
                                                                    title="Campo editable">
                                                                <option value="">Seleccione un campo</option>
                                                                {foreach $EDITABLE_FIELDS as $FIELD}
                                                                    <option value="{$FIELD->getName()}">{$FIELD->getLabel()}</option>
                                                                {/foreach}
                                                            </select>
                                                            <span id="ce-fields[]" class="help-block"></span>
                                                        </div>
                                                        <div class="col-xs-1">
                                                            <div class="action-bar pull-left">
                                                                <button type="button"
                                                                        class="btn btn-danger btn-icon hide"
                                                                        onclick="EditableFieldsUtils.delField (this);"
                                                                        title="Eliminar campo">
                                                                    <i class="fa fa-trash-o"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {*
                                                    <div class="col-xs-12">
                                                        <div class="action-bar text-center" style="padding: 12px 0">
                                                            <button type="button" class="btn btn-info btn-icon"
                                                                    onclick="EditableFieldsUtils.addField (this);"
                                                                    title="Agregar nuevo campo">
                                                                <i class="fa fa-plus"></i>&nbsp;Campo
                                                            </button>
                                                        </div>
                                                    </div>
                                                    *}
                                                </div>

                                                <div class="col-xs-12">
                                                    <div class="action-bar text-center" style="padding: 12px 0">
                                                        <button type="button" data-action="#btn-field"
                                                                class="btn btn-info btn-icon"
                                                                onclick="EditableFieldsUtils.addField (this);"
                                                                title="Agregar nuevo campo">
                                                            <i class="fa fa-plus"></i>&nbsp;Campo
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </form>
                                        {* /Create Button *}
                                    </div>
                                    <div id="ec-edit-view" class="ec-tab tab-pane fade">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="ec-btn-send" type="button" data-action="editable-fields-form"
                                onclick="EditableFieldsUtils.saveEditableFields (this)"
                                class="btn btn-primary hide">Guardar
                        </button>
                        <button id="ec-btn-edit-send" type="button" data-action="editable-edit-fields-form"
                                onclick="EditableFieldsUtils.saveEditableFields (this)"
                                class="btn btn-primary hide">Guardar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </script>
{/strip}