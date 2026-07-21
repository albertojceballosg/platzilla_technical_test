{strip}
    <tr class="tr-list-action">
        <td><input type="text" name="selectField[]" value="" readonly class="form-control"/></td>
        <td>
            <input type="text" name="selectValue[]" value="" readonly class="form-control"/>
        </td>
        <td>
            <select required="required" class="form-control action-list" id="actionFieldId" onchange="AddGridFieldsUtils.checkSelection(this)" name="actionFieldId[]" data-select="" >
                <option value="">Seleccionar campo</option>
                <option value="1">No vincular</option>
                {foreach from=$MODULE_WITH_LIST key=k item=v}
                    <option value="{$v.fieldname}">{$v.tablabel}: {$v.fieldlabel}</option>
                {/foreach}
            </select>
        </td>
        <td>
            <select  required="required" class="form-control" id="destinationField" name="destinationField[]" >
                <option value="">Seleccionar campo</option>
            </select>
        </td>
        <input type="hidden" name="selectNameField[]" value="">
    </tr>

{/strip}