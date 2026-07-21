{strip}
    <select class="form-control target_month"  data-local-id="{$idTargetMonth}"  id="target_month-{$idTargetMonth}" name="target_month[]"
            {if $optionType eq 'target_month'}
                onchange="IndicatorUtils.monthOfApplication (this, '{$idBoxScore}', 'week')"
            {else}
                onchange="IndicatorUtils.monthOfApplication (this, '{$idBoxScore}', 'month')"
            {/if}
            title="Mes objetivo">
        <option value="">{if $optionType neq 'target_month'}{$MOD.LBL_SELECTION_MONTH}{else}{$MOD.LBL_MONTH}{/if}</option>
        {if $optionType neq 'target_month'}
            <option value="all">{$MOD.LBL_ALL_YEAR}</option>
        {/if}
        {foreach $MOD.MONTH_APPLICATION as $key => $month}
            {assign var=status value=null}
            {if $key < $THIS_MONTH}
                {assign var=status value='disabled'}
            {/if}
            <option value="{$key}"  {$status}  {if $key eq $targetMonth}selected{/if}>{$month}</option>
        {/foreach}
    </select>
{/strip}