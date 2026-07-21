{strip}
    <tr class="tr-check-action">
        <td><input type="text" name="checkField[]" value="" readonly class="form-control"/></td>
        <td>
            <select  required="required" class="form-control" id="checkValue" name="checkValue[]" >
                <option value="activado"  selected >Activado</option>
                <option value="desactivado">Desactivado</option>
            </select>
        </td>
        <td>
            <select  required="required" class="form-control" id="destinationField" multiple name="checkFieldDest[]" >
            </select>
        </td>
        <input type="hidden" name="checkNameField[]" value="">
    </tr>
{/strip}