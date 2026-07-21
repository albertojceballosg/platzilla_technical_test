{math equation= rand() assign= "idOperationRow"}
{strip}
    {if $ACTIONS_FILES neq NULL}
        {assign var="a" value=$ACTIONS_FILES['fildnameA']}
        {assign var="oper" value=$ACTIONS_FILES['operator']}
        {assign var="b" value=$ACTIONS_FILES['filenameB']}
        {assign var="d" value=$ACTIONS_FILES['filenameD']}
        {* edit operation row*}
        {foreach $a as $key => $value}
        <tr id="opertion-field-{$idOperationRow}" class="field" data-id="{$idOperationRow}">
            <td>
                <select id="table-column-a-{$idOperationRow}" name="opertion[fildnameA][]" class="form-control"
                        {* onchange="TableFieldUtils.selectedOperationColumn (this, 'opertion-field-{$idOperationRow}')" *}
                        title="Columna">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" {if $column.name eq $value} selected{/if}>{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="operation-{$idOperationRow}" name="opertion[operator][]" class="form-control"
                        title="Operación">
                    <option value="" data-type="0">Seleccionar operación</option>
                    {foreach $AVAILABLE_OPERATIONS as $opertion}
                        <option value="{$opertion}" {if $opertion eq $oper[$key]} selected{/if}>{$MOD[$opertion]}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="table-column-b-{$idOperationRow}" name="opertion[filenameB][]" class="form-control"
                        {* onchange="TableFieldUtils.selectedOperationColumn (this, 'opertion-field-{$idOperationRow}')" *}
                        title="Operación">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" {if $column.name eq $b[$key]} selected{/if}>{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="table-column-c-{$idOperationRow}" name="opertion[filenameD][]" class="form-control"
                        {* onchange="TableFieldUtils.selectedOperationColumn (this, 'opertion-field-{$idOperationRow}')" *}
                        title="Columna">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}" {if $column.name eq $d[$key]} selected{/if}>{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteOperation (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>
        {math equation= rand() assign= "idOperationRow"}
        {/foreach}
        {* edit operation row*}


    {else}
        <tr id="opertion-field-{$idOperationRow}" class="field" data-id="{$idOperationRow}">
            <td>
                <select id="table-column-a-{$idOperationRow}" name="opertion[fildnameA][]" class="form-control"
                        {* onchange="TableFieldUtils.selectedOperationColumn (this, 'opertion-field-{$idOperationRow}')" *}
                        title="Columna">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}">{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="operation-{$idOperationRow}" name="opertion[operator][]" class="form-control"
                        title="Operación">
                    <option value="" data-type="0">Seleccionar operación</option>
                    {foreach $AVAILABLE_OPERATIONS as $opertion}
                        <option value="{$opertion}">{$MOD[$opertion]}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="table-column-b-{$idOperationRow}" name="opertion[filenameB][]" class="form-control"
                        {* onchange="TableFieldUtils.selectedOperationColumn (this, 'opertion-field-{$idOperationRow}')" *}
                        title="Operación">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}">{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <select id="table-column-c-{$idOperationRow}" name="opertion[filenameD][]" class="form-control"
                        {* onchange="TableFieldUtils.selectedOperationColumn (this, 'opertion-field-{$idOperationRow}')" *}
                        title="Columna">
                    <option value="" data-type="0">Seleccionar columna</option>
                    {foreach $AVAILABLE_COLUMNS as $column}
                        <option value="{$column.name}" data-type="{$column.type}">{$column.label}</option>
                    {/foreach}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteOperation (this);">
                    <i class="fa fa-trash-o"></i></button>
            </td>
        </tr>
    {/if}
{/strip}