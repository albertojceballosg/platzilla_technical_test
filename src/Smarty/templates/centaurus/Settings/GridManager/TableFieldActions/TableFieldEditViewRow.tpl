{math equation= rand() assign= "idRow"}
{assign var="idFieldTable" value=$ID_TABLE_FIELD}
{assign var="tableFields" value=$TABLE_FIELDS}
{assign var="hasSummary" value=null}
<tr valign="top" id="tr-row-{$idRow}" data-row-id="{$idRow}" class="tabla-field-row">
    {foreach $tableFields as $tableField}
        {if $tableFieldsData neq Null}
            {assign var="fieldValueArr" value=$tableFieldsData[$tableField->getFieldName()]}
        {else}
            {assign var="fieldValueArr" value=null}
        {/if}
        {if in_array($tableField->getUiType(), array(2203, 2207))}
            {continue}
        {/if}
        {assign var="stylesCell" value=$tableField->getAttributesArray()}
        <td width="{($stylesCell['width'] * 90)/100}%">
            {if $tableField->getUiType() eq 10} {* UI_TYPE_MODULE_REFERENCE *}
                {if $tableFieldsData neq Null}
                    {assign var="fieldValueIds" value=$tableFieldsData[$tableField->getFieldName()|cat:id]}
                {else}
                    {assign var="fieldValueIds" value=null}
                {/if}
                {assign var="actionField" value=$tableField->getActionArray()}
                {assign var="actionString" value=str_replace('"', "'", json_encode($actionField))}
                <div class="input-group" style="width: 100%;">
                    <input type="hidden" id="{$actionField['fieldname']}-{$idRow}_type" name="{$actionField['fieldname']}_type"
                           value="{$actionField['relatedmodule']}" class="small"/>
                    <input type="hidden" id="{$actionField['fieldname']}-{$idRow}"
                           data-row-ids="{$idFieldTable}@{$idRow}"
                           name="{$FIELD_NAME}[{$tableField->getFieldName()}id][]" value="{if $fieldValueIds neq Null}{$fieldValueIds[$indexRow]}{/if}"
                           onchange="TableFieldUtils.relatedModuleUpdate(this, {$actionString}, '{$MODULE}')"
                           class="for-filter module-reference"/>
                    <input type="text" id="edit_{$actionField['fieldname']}-{$idRow}_display"
                           name="{$FIELD_NAME}[{$tableField->getFieldName()}][]" value="{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}"
                           class="form-control input-readonly b-right{if ($OP_MODE == 'create_view') && ($mandatory_field != '')} placeholderStyle{/if}"
                           readonly="readonly" placeholder=""/>
                    <div class="input-group-addon" data-current-module="{$MODULE}"
                         data-display-field-id="edit_{$actionField['fieldname']}-{$idRow}_display"
                         data-field-id="{$actionField['fieldname']}-{$idRow}"
                         data-referenced-module="{$actionField['relatedmodule']}"
                         data-title="{$tableField->getFieldLabel()}"
                         onclick="RelatedModuleModalUtils.openModal (this);">
                        <i class="fa fa-plus-circle"></i>
                    </div>
                    <div class="input-group-addon"
                         onClick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_{$actionField['fieldname']}_display').val (''); fieldContainer.find ('#{$actionField['fieldname']}').val (''); return false;">
                        <i class="fa fa-eraser"></i>
                    </div>
                </div>
            {elseif (in_array($tableField->getUiType(), array(1, 11, 13)))} {* UI_TYPE_TEXT *}
                <div id="list-{$tableField->getFieldName()}-{$idRow}"  class="input-group hide" style="width: 100%;"></div>
                <div id="input-{$tableField->getFieldName()}-{$idRow}"  class="input-group" style="width: 100%;">
                    <input type="text" id="{$tableField->getFieldName()}-{$idRow}"
                           name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                           value="{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}"
                           class="form-control"/>
                </div>
            {elseif (in_array($tableField->getUiType(), array(17)))} {* UI_TYPE_URL *}
                <div class="input-group" style="width: 100%;">
                    {*<span class="input-group-addon" style="cursor: default; background-color: #eee;"><i class="fa fa-wordpress"></i></span>*}
                    <input type="text" id="{$tableField->getFieldName()}-{$idRow}"
                           name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                           value="{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}" class="form-control"
                           onkeyup="validateUrl('{$tableField->getFieldName()}-{$idRow}');" />
                </div>
            {elseif ($tableField->getUiType() eq 5)} {* UI_TYPE_DATE *}
                <div class="input-group" style="width: 100%;">
                    <div class="input-group-addon" style="border: 1px solid #ddd !important">
                        <i class="fa fa-calendar" id="jscal_trigger_{$tableField->getFieldName()}-{$idRow}"></i>
                    </div>
                    <input type="text" id="jscal_field_{$tableField->getFieldName()}-{$idRow}" name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                           value="{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}"
                           class="form-control pull-right input-readonly b-left" tabindex="{$vt_tab}" size="11" maxlength="18" readonly="readonly" placeholder="" />
                    <script type="text/javascript">
                        jQuery ('#jscal_field_{$tableField->getFieldName()}-{$idRow}').datepicker ({ format: (typeof gUserDateFormat !== 'undefined') ? gUserDateFormat : 'yyyy-mm-dd', language: 'es', weekStart: 1 });
                    </script>
                </div>
            {elseif (in_array($tableField->getUiType(), array(7, 9, 71)))} {* UI_TYPE_NUMBER          $tableField->getUiType() eq 7 *}
                <div class="input-group" style="width: 100%;">
                    <input type="text" id="{$tableField->getFieldName()}-{$idRow}"
                           name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                           value="{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}"
                           rel = '{$tableField->getUiType()}'
                           class="form-control"
                           onkeyup="TableFieldUtils.updateNumFields(this, '{$idFieldTable}', '{$idRow}')" />
                </div>
            {elseif ($tableField->getUiType() eq 15)} {* UI_TYPE_PICKLIST *}
                {assign var="actionField" value=$tableField->getActionArray()}
                {assign var="actionString" value=str_replace('"', "'", json_encode($actionField))}
                <div class="input-group" style="width: 100%;">
                    <select id="{$tableField->getFieldName()}-{$idRow}"
                            name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                            onchange="TableFieldUtils.updatePickListFields(this, '{$idFieldTable}', '{$idRow}', {$actionString}, '{$MODULE}')"
                            class="form-control">
                        <option value="">Seleccionar: {$tableField->getFieldLabel()}</option>
                        {foreach $actionField['list']['option'] as $option}
                            <option value="{$option}"
                            {if $fieldValueArr neq Null}
                                {if $fieldValueArr[$indexRow] eq $option}
                                    selected
                                {/if}
                            {/if}>{$option}</option>
                        {/foreach}
                    </select>
                </div>
            {elseif ($tableField->getUiType() eq 16)} {* UI_TYPE_GLOBAL_PICKLIST *}
                {if $fieldValueArr neq Null}
                    {assign var="GLOBAL_PICKLIST" value=$tableFieldsData['globallists']}
                {/if}
            <div class="input-group" style="width: 100%;">
                <select
                        id="{$tableField->getFieldName()}-{$idRow}"
                        name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                        class="form-control for-filter">
                    <option value="">Seleccionar {$tableField->getFieldLabel()}</option>
                    {foreach $GLOBAL_PICKLIST[$tableField->getFieldName()] as $globalList}
                        <option value="{$globalList->getValue()}"
                                {if $fieldValueArr neq Null}
                            {if $fieldValueArr[$indexRow] eq $globalList->getValue()}
                                selected
                            {/if}
                                {/if}>
                        {$globalList->getValue()}</option>
                    {/foreach}
                </select>
            </div>
            {elseif ($tableField->getUiType() eq 21)} {* UI_TYPE_TEXTAREA  *}
                <div id="input-{$tableField->getFieldName()}-{$idRow}"  class="input-group" style="width: 100%;">
                    <textarea id="{$tableField->getFieldName()}-{$idRow}"
                              name="{$FIELD_NAME}[{$tableField->getFieldName()}][]"
                              class="form-control"
                              rows="2">{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}</textarea>
                </div>
            {elseif ($tableField->getUiType() eq 56)} {* UI_TYPE_CHECKBOX *}
                {assign var="actionField" value=$tableField->getActionArray()}
                {assign var="actionString" value=str_replace('"', "'", json_encode($actionField))}
                <div class="checkbox text-center" style="vertical-align: center">
                    <input type="checkbox" id="{$tableField->getFieldName()}-{$idRow}"
                           style="margin-bottom: 4.5em"
                            {if $fieldValueArr neq Null}
                                {if $fieldValueArr[$indexRow] eq 'on'}
                                    checked
                                {/if}

                            {/if}
                           onclick="TableFieldUtils.updateCheckBoxFields(this, '{$idFieldTable}', '{$idRow}', {$actionString}, '{$MODULE}')"/>
                    <label for="{$tableField->getFieldName()}"></label>
                    <input type="hidden" id="{$tableField->getFieldName()}-{$idRow}" name="{$FIELD_NAME}[{$tableField->getFieldName()}][]" value="{if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}">
                    {if $fieldValueArr neq Null}
                        <script type="text/javascript">
                            {literal}
                            jQuery ( document ).ready(function() {
                                jQuery('#{/literal}{$tableField->getFieldName()}-{$idRow}{literal}').trigger('onclick');
                            });
                            {/literal}
                        </script>
                    {/if}
                </div>
            {else}
                {$tableField->getFieldName()}
                {$tableField->getUiType()}
            {/if}
        </td>
    {/foreach}
    <td width="10%" class="text-center">
        <button type="button" class="btn btn-primary btn-xs"
                onclick="TableFieldUtils.moveRowUp (this, 'tr-row-{$idRow}')"><i
                    class="fa fa-arrow-up" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-xs"
                onclick="TableFieldUtils.moveRowDown (this, 'tr-row-{$idRow}')"><i
                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="TableFieldUtils.delRowToTable (this, 'tr-row-{$idRow}', '{$idFieldTable}');"><i
                    class="fa fa-trash-o"></i></button>
    </td>
</tr>