{strip}
<select{if (isset ($ID)) && ($ID)} id="{$ID}"{/if} name="{$NAME}" multiple="multiple"{if (isset ($CLASS))} class="{$CLASS}"{/if}{if (isset ($TITLE))} title="{$TITLE}"{/if}>
{if (isset ($OPTIONS)) && (is_array ($OPTIONS)) && (count ($OPTIONS) > 0)}
	{if (isset ($SELECTED_VALUES)) && (is_array ($SELECTED_VALUES))}
		{assign var="selectedValues" value=$SELECTED_VALUES}
	{else}
		{assign var="selectedValues" value=array()}
	{/if}
	{foreach $OPTIONS as $option}
	<option value="{$option.value}"{if ((isset ($option.selected)) && ($option.selected)) || (in_array ($option.value, $selectedValues))} selected="selected"{/if}>{$option.text}</option>
	{/foreach}
{/if}
</select>
{/strip}