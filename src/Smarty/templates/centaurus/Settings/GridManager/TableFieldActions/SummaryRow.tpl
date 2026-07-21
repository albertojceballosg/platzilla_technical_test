{math equation= rand() assign= "idSummaryRow"}
{strip}
    {if $ACTIONS_FILES neq NULL}
        {assign var="fieldNames" value=$ACTIONS_FILES['filename']}
        {assign var="oper" value=$ACTIONS_FILES['operation']}
        {* edit summary row*}
        {foreach $fieldNames as $key => $fieldName}
        <tr id="summary-row-{$idSummaryRow}" class="field" data-id="{$idSummaryRow}">
            <td>
                <select id="tabble-column-{$idSummaryRow}" name="summry[filename][]" class="form-control"
                        onchange="TableFieldUtils.selectedSummaryColumn (this, '{$idSummaryRow}')"
                        title="Columna">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" {if $fieldName eq $column.name}selected{/if}>{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="summary-operation-{$idSummaryRow}" name="summry[operation][]" class="form-control"
                        title="Operación">
                    <option value="" data-type="0">Seleccionar operación</option>
                    {foreach $AVAILABLE_OPERATIONS as $opertion}
                        <option value="{$opertion}" {if $oper[$key] eq $opertion}selected{/if}>{$MOD[$opertion]}</option>
                    {/foreach}
                </select>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteSummary (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>
            {math equation= rand() assign= "idSummaryRow"}
        {/foreach}
    {else}
        <tr id="summary-row-{$idSummaryRow}" class="field" data-id="{$idSummaryRow}">
            <td>
                <select id="tabble-column-{$idSummaryRow}" name="summry[filename][]" class="form-control"
                        onchange="TableFieldUtils.selectedSummaryColumn (this, '{$idSummaryRow}')"
                        title="Columna">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}">{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="summary-operation-{$idSummaryRow}" name="summry[operation][]" class="form-control"
                        title="Operación">
                    <option value="" data-type="0">Seleccionar operación</option>
                    {foreach $AVAILABLE_OPERATIONS as $opertion}
                        <option value="{$opertion}" disabled="disabled">{$MOD[$opertion]}</option>
                    {/foreach}
                </select>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteSummary (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>
    {/if}
{/strip}