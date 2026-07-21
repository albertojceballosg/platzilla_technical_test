{assign var="idFieldTable" value=$ID_TABLE_FIELD}
{assign var="idRow" value=$ID_ROW_TABLE}
{strip}
    <select id="{$COLUMN_NAME}-{$idRow}"
            name="{$FIELD_NAME}"
            {*onchange="TableFieldUtils.updatePickListFields(this, '{$idFieldTable}', '{$idRow}', {$actionString}, '{$MODULE}')"*}
            class="form-control">
        <option value="">Seleccionar..</option>
        {foreach $AVAILABLE_PICKLIST as $PicklistValue}
            <option value="{$PicklistValue->getValue()}">{$PicklistValue->getValue()}</option>
        {/foreach}
    </select>
{/strip}