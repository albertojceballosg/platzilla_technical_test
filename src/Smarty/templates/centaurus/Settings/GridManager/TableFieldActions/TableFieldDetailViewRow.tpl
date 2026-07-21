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
                <div class="input-group text-left"   style="width: 100%;">
                    {if $fieldValueIds neq Null}
                        <a href="index.php?module={{$actionField['relatedmodule']}}&parenttab=&action=DetailView&record={$fieldValueIds[$indexRow]}"
                           target="_blank"
                           title="{$MODULE}">{$fieldValueArr[$indexRow]}</a>
                    {/if}
                </div>
            {elseif (in_array($tableField->getUiType(), array(1, 5, 11, 13)))} {* UI_TYPE_TEXT               ($tableField->getUiType() eq 1) || ($tableField->getUiType() eq 13) *}
                <div id="input-{$tableField->getFieldName()}-{$idRow}" class="input-group {if $tableField->getUiType() eq 5}text-center{else}text-justify{/if}" style="width: 100%;">
                    <span
                        id="{$tableField->getFieldName()}-{$idFieldTable}">
                        {if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}
                    </span>
                </div>
            {elseif (in_array($tableField->getUiType(), array(7, 9, 71)))} {* UI_TYPE_NUMBER *}
                <div class="input-group text-right" style="width: 100%;">
                    <span id="{$tableField->getFieldName()}-{$idFieldTable}">
                        {if $tableField->getUiType() eq 9 && $fieldValueArr[$indexRow] < 1}
                            {if $fieldValueArr neq Null}{number_format($fieldValueArr[$indexRow], 4, ',', '.')}{/if}
                        {else}
                            {if $fieldValueArr neq Null}{number_format($fieldValueArr[$indexRow], 2, ',', '.')}{/if}
                        {/if}
                    </span>
                </div>
            {elseif ($tableField->getUiType() eq 15)} {* UI_TYPE_PICKLIST *}
                {assign var="actionField" value=$tableField->getActionArray()}
                {assign var="actionString" value=str_replace('"', "'", json_encode($actionField))}
                <div class="input-group text-justify" style="width: 100%;">
                    <span id="{$tableField->getFieldName()}-{$idFieldTable}">
                        {if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}
                    </span>
                </div>
            {elseif ($tableField->getUiType() eq 16)} {* UI_TYPE_GLOBAL_PICKLIST *}
                {if $fieldValueArr neq Null}
                    {assign var="GLOBAL_PICKLIST" value=$tableFieldsData['globallists']}
                {/if}
            <div class="input-group" style="width: 100%;">
                <span id="{$tableField->getFieldName()}-{$idFieldTable}">
                    {if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}
                </span>
            </div>
            {elseif ($tableField->getUiType() eq 17)} {* UI_TYPE_URL *}
                <div class="input-group">
                    <span class="form-control b-left"
                          style="overflow-x: hidden;width: 100%" data-toggle="tooltip">
                        {if $fieldValueArr neq Null}
                        <a href="{$fieldValueArr[$indexRow]}"
                            target="_blank">{$fieldValueArr[$indexRow]}</a>
                        {/if}
                    </span>
                </div>
            {elseif ($tableField->getUiType() eq 21)} {* UI_TYPE_TEXTAREA  *}
                <div id="input-{$tableField->getFieldName()}-{$idRow}"  class="input-group" style="width: 100%;">
                    <span id="{$tableField->getFieldName()}-{$idFieldTable}">
                        {if $fieldValueArr neq Null}{$fieldValueArr[$indexRow]}{/if}
                    </span>
                </div>
            {elseif ($tableField->getUiType() eq 56)} {* UI_TYPE_CHECKBOX *}
                {assign var="actionField" value=$tableField->getActionArray()}
                {assign var="actionString" value=str_replace('"', "'", json_encode($actionField))}
                <div class="checkbox text-center" style="vertical-align: center">
                    <span id="{$tableField->getFieldName()}-{$idFieldTable}">
                        {if $fieldValueArr neq Null}
                            {if $fieldValueArr[$indexRow] eq 'on'}Si{else}No{/if}
                        {/if}
                    </span>
                </div>
            {else}
                {$tableField->getFieldName()}
                {$tableField->getUiType()}
            {/if}
        </td>
    {/foreach}
</tr>