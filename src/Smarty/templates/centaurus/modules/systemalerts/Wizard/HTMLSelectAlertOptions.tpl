{strip}
{if (isset ($AVAILABLE_OPTION)) && (is_array ($AVAILABLE_OPTION)) && (count ($AVAILABLE_OPTION) > 0)}
    {foreach $AVAILABLE_OPTION as $key => $value}
		<option value="{$key}" {if ($SELECTED_VALUE) && ($SELECTED_VALUE == $key)} selected="selected"{/if}>{$value}</option>
    {/foreach}
{/if}
{/strip}