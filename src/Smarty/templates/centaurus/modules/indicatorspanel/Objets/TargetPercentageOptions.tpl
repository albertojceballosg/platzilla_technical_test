{strip}
    <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
    {section name=target start=0 loop=105 step=5}
        {$smarty.section.target.index}
        <option value="{$smarty.section.target.index}"
                {if ($selectedValue eq $smarty.section.target.index)} selected="selected"{/if}>
            {$smarty.section.target.index}%
        </option>
    {/section}
{/strip}