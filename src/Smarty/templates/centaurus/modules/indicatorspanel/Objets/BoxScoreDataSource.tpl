{strip}
    <select id="data_source-{$idBoxScore}" name="dataSource" class="form-control"
            onchange="IndicatorUtils.selectedDataSource(this,'{$idBoxScore}')"
        {if (!$IS_MOTHER && $isEditable eq 'NO')}disabled
        title="Bloqueado para edición"{/if}>
        <option value=""
                {if $MODE eq 'create'}selected="selected"{/if}>{$MOD.LBL_SELECTION_DEFAULT}
        </option>
        <option value="0"
                {if (($FIELD_NAME eq NULL) && ($CALCULATED_SYSTEM eq NULL)) && ($MODE eq 'edit')}selected="selected"{/if}>
            {$MOD.OP_MANUAL}
        </option>
        <option value="1"
                {if ($FIELD_NAME neq NULL) && ($MODE eq 'edit')}selected="selected"{/if}>
            {$MOD.OP_AUTOMATIC}
        </option>
        <option value="2"
                {if ($CALCULATED_SYSTEM neq NULL) && ($MODE eq 'edit')}selected="selected"{/if}>
            {$MOD.OP_CALCULATION_ENGINE}
        </option>
    </select>
{/strip}