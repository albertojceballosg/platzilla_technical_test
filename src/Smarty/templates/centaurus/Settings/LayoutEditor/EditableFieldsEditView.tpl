{strip}
    {if $EDITABLE_BUTTOM neq NULL}
        {assign var='moduleName' value=$EDITABLE_BUTTOM->getModuleName()}
        {assign var='objButtonsFields' value=$EDITABLE_BUTTOM->getEditableFields()}
        {assign var='objButton' value=$EDITABLE_BUTTOM}
        <form action="index.php" name="editable-edit-fields-form">
            <input type="hidden" name="module" value="Settings"/>
            <input type="hidden" name="action" value="SaveEditableFieldsButton"/>
            <input type="hidden" name="Ajax" value="true"/>
            <input type="hidden" name="formodule" value="{$moduleName}"/>
            <input type="hidden" name="buttonname" value="{$objButton->getName()}"/>
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
                                   value="{$objButton->getLabel()}"
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
                                   value="{$objButton->getDescription()}"
                                   placeholder="Descripción del botón">
                            <span id="ce-descriptions" class="help-block"></span>
                        </div>
                    </div>
                    <div id="btn-field" class="form-group col-xs-12">
                        <label for="btn-field"
                               class="col-xs-2 control-label">Estatus</label>
                        <div id="ce-div-status" class="col-xs-10">
                            <select name="status" class="form-control "
                                    title="Estatus del campo">
                                <option value="">Seleccione..</option>
                                <option value="1"
                                        {if $objButton->isStatus ()}selected="selected"{/if}>{$MOD.LBL_ACTIVE}</option>
                                <option value="0"
                                        {if !$objButton->isStatus ()}selected="selected"{/if}>{$MOD.LBL_INACTIVE}</option>
                            </select>
                            <span id="ce-status" class="help-block"></span>
                        </div>
                    </div>
                    {* field group *}
                    {foreach from=$objButtonsFields item=obj name=field}
                        <div {if $smarty.foreach.field.iteration eq 1}id="btn-field-edit" {/if}
                             class="form-group col-xs-12">
                            <label for="btn-field"
                                   class="col-xs-2 control-label">Campo</label>
                            <div id="ce-div-fields[]" class="col-xs-9">
                                <select name="fields[]" class="form-control "
                                        title="Campo editable">
                                    <option value="">Seleccione un campo</option>
                                    {foreach $EDITABLE_FIELDS as $FIELD}
                                        <option value="{$FIELD->getName()}" {if $FIELD->getName() eq $obj->getFieldName()} selected="selected" {/if}>{$FIELD->getLabel()}</option>
                                    {/foreach}
                                </select>
                                <span id="ce-fields[]" class="help-block"></span>
                            </div>
                            <div class="col-xs-1">
                                <div class="action-bar pull-left">
                                    <button type="button"
                                            class="btn btn-danger btn-icon {if $smarty.foreach.field.iteration eq 1}hide {/if}"
                                            onclick="EditableFieldsUtils.delField (this);"
                                            title="Eliminar campo">
                                        <i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                    {* /field group *}
                </div>
                <div class="col-xs-12">
                    <div class="action-bar text-center" style="padding: 12px 0">
                        <button type="button" data-action="#btn-field-edit" class="btn btn-info btn-icon"
                                onclick="EditableFieldsUtils.addField (this);"
                                title="Agregar nuevo campo">
                            <i class="fa fa-plus"></i>&nbsp;Campo
                        </button>
                    </div>
                </div>

            </div>
        </form>
    {/if}
{/strip}