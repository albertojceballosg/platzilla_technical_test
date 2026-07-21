{strip}
<select id="{$ID}" name="{$NAME}"{if (isset ($CLASS))} class="{$CLASS}"{/if}{if (isset ($TITLE))} title="{$TITLE}"{/if}>
{if (isset ($OPTIONS)) && (is_array ($OPTIONS)) && (count ($OPTIONS) > 0)}
	{foreach $OPTIONS as $option}
	<option value="{$option.value}"{if ($SELECTED_VALUE) && ($SELECTED_VALUE == $option.value)} selected="selected"{/if}>{$option.text}</option>
	{/foreach}
{/if}
</select>
{/strip}