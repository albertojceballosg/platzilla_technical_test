{math equation= rand() assign= "idFieldImport"}
{strip}
    {if $ACTION_MODULE_FIELDS neq NULL}
        {foreach from=$ACTION_MODULE_FIELDS key=keyIndex item=fieldName name=linkages}
        <tr id="import-field-{$ID_LINKAGE}-{$idFieldImport}" class="field" data-id="{$idFieldImport}">

            <td>
                <select id="module-field-{$idFieldImport}" name="linkages[{$FIELD_NAME}][modulefield][]" class="form-control linkage-related-module"
                        onchange="TableFieldUtils.selectedFieldImport(this, '{$idFieldImport}')"
                        title="Campo para importar">
                    <option value="" data-type="0">Seleccionar campo</option>
                    {foreach $AVAILABLE_FIELD as $field}
                        {if !in_array($field->getUitype(), $AVAILABLE_FIELD_TYPES)}{continue}{/if}
                        <option value="{$field->getName()}" data-type="{$field->getUitype()}" {if $fieldName eq $field->getName()}selected{/if} >{$field->getLabel()}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="tabble-column-{$idFieldImport}" name="linkages[{$FIELD_NAME}][tablefield][]" class="form-control linkage-related-module"
                        title="Columna destino">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" {if $ACTION_TABLE_FIELDS[$smarty.foreach.linkages.index] eq $column.name}selected{else}disabled="disabled"{/if}  >{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteRowLinkage (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>
            {math equation= rand() assign= "idFieldImport"}
        {/foreach}

    {else}
        <tr id="import-field-{$ID_LINKAGE}-{$idFieldImport}" class="field" data-id="{$idFieldImport}">
            <td>
                <select id="module-field-{$idFieldImport}" name="linkages[{$FIELD_NAME}][modulefield][]" class="form-control linkage-related-module"
                        onchange="TableFieldUtils.selectedFieldImport(this, '{$idFieldImport}')"
                        title="Campo para importar">
                    <option value="" data-type="0">Seleccionar campo</option>
                    {foreach $AVAILABLE_FIELD as $field}
                        {if !in_array($field->getUitype(), $AVAILABLE_FIELD_TYPES)}{continue}{/if}
                        <option value="{$field->getName()}" data-type="{$field->getUitype()}">{$field->getLabel()}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="tabble-column-{$idFieldImport}" name="linkages[{$FIELD_NAME}][tablefield][]" class="form-control linkage-related-module"
                        title="Columna destino">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" disabled="disabled" >{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteRowLinkage (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>

    {/if}
{/strip}