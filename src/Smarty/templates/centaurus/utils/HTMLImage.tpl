{strip}
<img
	src="{$SRC}"
	{if (isset ($ID)) && ($ID)} id="{$ID}"{/if}
	{if (isset ($CLASS))} class="{$CLASS}"{/if}
	{if (isset ($TITLE))} title="{$TITLE}"{/if}
	{if (isset ($ADDITIONAL_ATTRIBUTES))} {$ADDITIONAL_ATTRIBUTES}{/if}
/>
{/strip}