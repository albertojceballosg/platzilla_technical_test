{strip}
    <tr class="tr-color-filter">
        <td width="20%">
            <select  required="required" class="form-control" id="fieldToColor" name="fieldToColor[]" >
                <option value="">Seleccionar campo</option>
            </select>
        </td>
        <td width="10%"><input id="selectedColor" type="color" class="form-control" name="selectedColor[]" placeholder="clic aquí" value="#f3f3f3" /></td>
        <td width="20%">
            <select  required="required" class="form-control" id="fieldToFilter" name="fieldToFilter[]" >
                <option value="">Seleccionar campo</option>
            </select>
        </td>
        <td>
            <select   required="required" class="form-control" id="actionFilter" name="actionFilter[]" >
                {foreach from=$FILTER_CONDITION key=k item=v}
                    <option value="{$k}">{$v}</option>
                {/foreach}
            </select>
        </td>
        <td>
            <input type="text" name="selectedValue[]" class="form-control" value="" placeholder="Valor para comparar"/>
        </td>
        <td width="10%">
            <select class="form-control " id="join-condition" name="joinCondition[]" >
                <option value="&&" selected >y</option>
                <option value="||">O</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-default btn-sm removeButton" onclick="AddGridFieldsUtils.removeRowColorFilter(this);"><i class="fa fa-minus" aria-hidden="true"></i>
            </button>
        </td>
    </tr>
{/strip}