{strip}
    {if $TABLE_FIELDS neq NULL}
        {foreach $TABLE_FIELDS as $fieldName}
            {if in_array($fieldName->getUiType(), array(2203, 2207))}
                {continue}
            {/if}
            {assign var="attributes" value=$fieldName->getAttributesArray()}
            <tr class="field" data-id="{$idFieldImport}">
                <td style="vertical-align: top">
                    <input type="text" class="form-control" readonly value="{$fieldName->getFieldLabel()}"/>
                    <input type="hidden" name="appearance[fieldname][]" value="{$fieldName->getFieldName()}">
                </td>
                <td style="vertical-align: top">
                    <div>
                        <input type="number" class="form-control" title="El ancho de la columna" name="appearance[width][]"
                               value="{$attributes['width']}"/>
                        <span class="help-block" style="color: red"></span>
                    </div>
                </td>
                <td>
                    <div>
                    <textarea class="form-control" rows="2" name="appearance[style][]"
                              placeholder="Usar clausulas del typo: text-align: center;color: #FF0000">
                        {$attributes['style']}
                    </textarea>
                        <span class="help-block" style="color: red"></span>
                    </div>
                </td>

            </tr>
        {/foreach}
    {else}
    {foreach $AVAILABLE_COLUMNS as $column}
        <tr class="field" data-id="{$idFieldImport}">
            <td style="vertical-align: top">
                <input type="text" class="form-control" readonly value="{$column.label}"/>
                <input type="hidden" name="appearance[fieldname][]" value="{$column.name}">
            </td>
            <td style="vertical-align: top">
                <div>
                    <input type="number" class="form-control" title="El ancho de la columna" name="appearance[width][]"
                           value="{$column.width}"/>
                    <span class="help-block" style="color: red"></span>
                </div>
            </td>
            <td>
                <div>
                    <textarea class="form-control" rows="2" name="appearance[style][]"
                              placeholder="Usar clausulas del typo: text-align: center;color: #FF0000"></textarea>
                    <span class="help-block" style="color: red"></span>
                </div>
            </td>

        </tr>
    {/foreach}
    {/if}
{/strip}