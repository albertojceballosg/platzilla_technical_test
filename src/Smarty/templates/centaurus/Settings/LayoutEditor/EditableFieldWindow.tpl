{if $EDITABLE_BUTTOM neq NULL}
    {assign var='moduleName' value=$EDITABLE_BUTTOM->getModuleName()}
    {assign var='objButtonsFields' value=$EDITABLE_BUTTOM->getEditableFields()}
    {assign var='objButton' value=$EDITABLE_BUTTOM}
    {assign var='fldvalue' value=$COLUMN_FIELDS}
    {assign var="mandatory_field" value="*"}
    {strip}
        <li class="nav-item" role="presentation"><a class="nav-link route-nav" href="#"
                                                    data-control="out"
                                                    rel="{$objButton->getName()}-{$RECORD}"
                                                    onclick="EditableFieldsUtils.showWindowsFields(this, event)">{$objButton->getLabel()}</a>
            <div id="{$objButton->getName()}-{$RECORD}" class="editableFieldContainer">
                <div class="editableFieldTitle"><i class="fa fa-pencil"></i>&nbsp;{$objButton->getLabel()}</div>
                <div class="editableFieldBody">
                    <div class="row">
                        <div id="div-windows-form-{$objButton->getName()}-{$RECORD}" class="col-xs-12">
                            <form action="index.php" name="windows-form-{$objButton->getName()}-{$RECORD}">
                                <input type="hidden" name="module" value="{$moduleName}"/>
                                <input type="hidden" name="action" value="SaveFromListView"/>
                                <input type="hidden" name="Ajax" value="true"/>
                                <input type="hidden" name="formodule" value="{$moduleName}"/>
                                <input type="hidden" name="record" value="{$RECORD}"/>
                                <input type="hidden" name="buttonname" value="{$objButton->getName()}"/>
                                <div class="row" id="block_{$RECORD}">
                                    <div class="col-xs-12">
                                        {foreach $objButtonsFields as $buttonField}
                                            {assign var='objField' value=$buttonField->getField()}
                                            {if ($objField->getUitype() == '5')}
                                                {assign var='dummy' value=key($fldvalue[$objField->getName()])}
                                                {if (!empty ($dummy))}
                                                    {assign var='dateValue' value=$dummy}
                                                {else}
                                                    {assign var='dateValue' value=null}
                                                {/if}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">
                                                            &nbsp;{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="ce-td_{$objField->getName()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <div class="input-group" style="width: 100%;">
                                                        <div class="input-group-addon"
                                                             style="border: 1px solid #ddd !important">
                                                            <i class="fa fa-calendar"
                                                               id="jscal_trigger_{$objField->getName()}"></i>
                                                        </div>
                                                        <input type="text"
                                                               id="jscal_field_{$objField->getName()}_{$RECORD}"
                                                               name="{$objField->getName()}"
                                                               value="{$fldvalue[$objField->getName()]}"
                                                               class="form-control pull-right input-readonly b-left"
                                                               tabindex="{$vt_tab}" size="11" maxlength="18"
                                                               readonly="readonly" placeholder=""/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>

                                                        <script type="text/javascript">
                                                            jQuery('#jscal_field_{$objField->getName()}_{$RECORD}').datepicker({
                                                                format: 'yyyy-mm-dd',
                                                                language: 'es',
                                                                weekStart: 1,
                                                                container: 'div#{$objButton->getName()}-{$RECORD}'
                                                            });
                                                        </script>
                                                    </div>
                                                </div>
                                            {elseif ($objField->getUitype() == '53')}
                                                <div class="col-xs-5 label-input">

                                                    <label for="assigneduser">Asignado a
                                                        {if ($objField->isMandatory())}
                                                        <span class="required">&nbsp;{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="ce-td_{$objField->getName()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               class="form-control"
                                                               placeholder=""/>
                                                    {/if}
                                                    <select name="{$objField->getName()}" class="form-control"
                                                            style="margin-top: .5em;"
                                                            title="{if ($objField->isMandatory())} {$objField->getLabel()}{/if}">
                                                        {$CHANGE_OWNER}
                                                    </select>
                                                    <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                </div>
                                            {elseif ($objField->getUitype() == '15')}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">
                                                            &nbsp;{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <select id="{$objField->getName()}" name="{$objField->getName()}"
                                                            title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                            class="form-control for-filter" tabindex="{$vt_tab}">
                                                        <option value="" disabled="disabled"
                                                                selected="selected">{$objField->getLabel()}</option>
                                                        {foreach $objField->getPicklist()->getValues() as $objPicklist}
                                                            <option value="{{$objPicklist->getValue()}}"{if ($objPicklist->getValue() == $fldvalue[$objField->getName()])} selected="selected"{/if}>{$objPicklist->getValue()}</option>
                                                            {foreachelse}
                                                            <option value="{$objPicklist->getValue()}"></option>
                                                            <option value="" style="color: #777777"
                                                                    disabled="disabled">{$APP.LBL_NONE}</option>
                                                        {/foreach}
                                                    </select>
                                                    <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                </div>
                                            {elseif ($objField->getUitype() == '13')}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <div class="input-group" style="width: 100%;">
                                                            <span class="input-group-addon"><i
                                                                        class="fa fa-envelope"></i></span>
                                                        <input type="text" id="{$objField->getName()}"
                                                               name="{$objField->getName()}"
                                                               value="{$fldvalue[$objField->getName()]}"
                                                               title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                               class="form-control"
                                                               tabindex="{$vt_tab}"/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                    </div>
                                                </div>
                                            {elseif ($objField->getUitype() == 1)}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    {if ($objField->getDataType () == 'I')}
                                                        <input type="text" tabindex="{$vt_tab}"
                                                               name="{$objField->getName()}" id="{$objField->getName()}"
                                                               value="{if $fldvalue[$objField->getName()] gt 0}{$fldvalue[$objField->getName()]}{else}0{/if}"
                                                               title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                               class="form-control"/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                    {else}
                                                        <input type="text" tabindex="{$vt_tab}"
                                                               name="{$objField->getName()}" id="{$objField->getName()}"
                                                               value="{$fldvalue[$objField->getName()]}"
                                                               title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                               class="form-control"/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                    {/if}
                                                </div>
                                            {elseif ($objField->getUitype() == 7)}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <div class="input-group" style="width: 100%;">
                                                        <input type="text" id="{$objField->getName()}"
                                                               name="{$objField->getName()}"
                                                               value="{$fldvalue[$objField->getName()]}"
                                                               title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                               class="form-control" tabindex="{$vt_tab}"
                                                               onkeyup="var field = jQuery (this); field.val (field.val ().replace (/[^\d.-]/g, ''));"/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                    </div>
                                                </div>
                                            {elseif ($objField->getUitype() == 9)}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}
                                                        &nbsp;{$APP.COVERED_PERCENTAGE}{if ($objField->isMandatory())}
                                                            <span class="required">{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <div class="input-group" style="width: 100%;">
                                                            <span class="input-group-addon"
                                                                  style="cursor: default; background-color: #eee;"><i
                                                                        class="fa">%</i></span>
                                                        <input type="text" id="{$objField->getName()}"
                                                               name="{$objField->getName()}"
                                                               value="{$fldvalue[$objField->getName()]}"
                                                               title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                               class="form-control" tabindex="{$vt_tab}"/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                    </div>
                                                </div>
                                            {elseif ($objField->getUitype() == '11')}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <div class="input-group" style="width: 100%;">
			<span class="input-group-addon" style="cursor: default; background-color: #eee;">
				<i class="fa fa-{if ($objField->getName() == 'phone')}phone{elseif ($objField->getName() == 'mobile') || ($objField->getName() == 'num_cel')}mobile{elseif ($objField->getName() == 'fax')}fax{else}home{/if}"></i>
			</span>
                                                        <input type="text" id="{$objField->getName()}"
                                                               name="{$objField->getName()}"
                                                               value="{$fldvalue[$objField->getName()]}"
                                                               class="form-control"
                                                               title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                               tabindex="{$vt_tab}"/>
                                                        <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                    </div>
                                                </div>
                                            {elseif ($objField->getUitype() == '21')}
                                                <div class="col-xs-5 label-input">
                                                    <label for="{$objField->getName()}">{$objField->getLabel()}{if ($objField->isMandatory())}
                                                            <span class="required">{$mandatory_field}</span>{/if}
                                                    </label>
                                                </div>
                                                <div class="form-group col-xs-7 field-container"
                                                     id="td_{$objField->getName()}"
                                                     data-uitype="{$objField->getUitype()}">
                                                    {if ($MASS_EDIT == '1')}
                                                        <input type="checkbox"
                                                               id="{$objField->getName()}_mass_edit_check"
                                                               name="{$objField->getName()}_mass_edit_check"
                                                               class="form-control" placeholder=""/>
                                                    {/if}
                                                    <textarea id="{$objField->getName()}" name="{$objField->getName()}"
                                                              title="{if ($objField->isMandatory())}{$objField->getLabel()}{/if}"
                                                              class="form-control" tabindex="{$vt_tab}"
                                                              rows="2">{$fldvalue[$objField->getName()]}</textarea>
                                                    <span id="ce-{$objField->getName()}" class="help-block"></span>
                                                </div>
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="editableFieldFooter">
                    <div class="row">
                        <div class="col-xs-12">
                            <span>
                            <a href="#" class="btn btn-success btn-xs"
                               rel="windows-form-{$objButton->getName()}-{$RECORD}"
                               onclick="EditableFieldsUtils.saveWindowsFields(this, event)" title="Guardar">Guardar</a></span>
                            <span>&nbsp;
                            <a href="#" class="btn btn-default btn-xs" rel="{$objButton->getName()}-{$RECORD}"
                               onclick="EditableFieldsUtils.hideWindowsFields(this, event)"
                               title="Cancelar">Cancelar</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    {/strip}
{else}
    {strip}
        <li class="nav-item" role="presentation"><a class="nav-link route-nav" href="#"
                                                    data-control="out"
                                                    rel="">muestra</a>
            <div class="editableFieldContainer">
                <div class="editableFieldTitle">&nbsp;</div>
                <div class="editableFieldBody">
                    <div class="row">
                        <div class="col-xs-12">
                        </div>
                    </div>
                </div>
                <div class="editableFieldFooter">
                </div>
            </div>
        </li>
    {/strip}
{/if}