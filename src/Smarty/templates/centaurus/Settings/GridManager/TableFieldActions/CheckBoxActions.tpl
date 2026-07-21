{math equation= rand() assign= "idCheckAction"}
{strip}
    {if $ACTIONS_FILES neq NULL}
        {assign var="fieldname" value=$ACTIONS_FILES['fieldname']}
        {assign var="action" value=$ACTIONS_FILES['action']}
        {foreach $fieldname as $key => $value}
        <tr id="check-box-action{$ID_LINKAGE}-{$idCheckAction}" class="field" data-id="{$idCheckAction}">
            <td>
                <select id="tabble-column-{$idCheckAction}" name="activation[{$CHECKBOX_NAME}][fieldname][]" class="form-control activation-check-box"
                        onchange="TableFieldUtils.selectedFieldActivation(this, '{$idCheckAction}', '{$CHECKBOX_NAME}')"
                        title="Campo afeactado">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" {if $value eq $column.name}selected{/if}>{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="checkbox-action-{$idCheckAction}" name="activation[{$CHECKBOX_NAME}][action][]" class="form-control activation-check-box"
                        title="Estado al marcar el check">
                    <option value="">Seleccionar estado</option>
                    <option value="ENABLED" {if $action[$key] eq 'ENABLED'}selected{/if}>Activado</option>
                    <option value="DISABLED" {if $action[$key] eq 'DISABLED'}selected{/if}>Desactivado</option>
                </select>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteRowActivation (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>
        {/foreach}
    {else}
    <tr id="check-box-action{$ID_LINKAGE}-{$idCheckAction}" class="field" data-id="{$idCheckAction}">
        <td>
            <select id="tabble-column-{$idCheckAction}" name="activation[{$CHECKBOX_NAME}][fieldname][]" class="form-control activation-check-box"
                    onchange="TableFieldUtils.selectedFieldActivation(this, '{$idCheckAction}', '{$CHECKBOX_NAME}')"
                    title="Campo afeactado">
                <option value="" data-type="0">Seleccionar columna</option>
                {foreach $AVAILABLE_COLUMNS as $column}
                    <option value="{$column.name}" data-type="{$column.type}" >{$column.label}</option>
                {/foreach}
            </select>
        </td>
        <td>
            <select id="checkbox-action-{$idCheckAction}" name="activation[{$CHECKBOX_NAME}][action][]" class="form-control activation-check-box"
                    title="Estado al marcar el check">
                <option value="">Seleccionar estado</option>
                <option value="ENABLED" selected="">Activado</option>
                <option value="DISABLED">Desactivado</option>
            </select>
        </td>

        <td class="text-center">
            <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteRowActivation (this);">
                <i class="fa fa-trash-o"></i></button>
        </td>
    </tr>
    {/if}
{/strip}