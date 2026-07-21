{strip}
<ul{if (isset ($ID)) && (!empty ($ID))} id="{$ID}"{/if}{if (isset ($CLASS)) && (!empty ($CLASS))} class="{$CLASS}"{/if}>
{if (isset ($ENTRIES)) && (is_array ($ENTRIES)) && (count ($entries) > 0)}
	{foreach $ENTRIES as $entry}
	<li>{$entry}</li>
	{/foreach}
{/if}
</ul>
{/strip}