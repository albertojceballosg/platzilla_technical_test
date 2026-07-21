{strip}
<input
	type="checkbox"
	{if (isset ($ID)) && ($ID)} id="{$ID}"{/if}
	name="{$NAME}"
	value="{$VALUE}"
	{if ($CHECKED)}checked="checked"{/if}
	{if (isset ($CLASS))} class="{$CLASS}"{/if}
	{if (isset ($ADDITIONAL_ATTRIBUTES))} {$ADDITIONAL_ATTRIBUTES}{/if}
/>
{/strip}