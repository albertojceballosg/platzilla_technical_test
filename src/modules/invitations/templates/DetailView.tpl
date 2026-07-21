{extends file="../../DetailView.tpl"}
{block name="messages"}
{strip}
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
	<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
</div>
{/if}
{/strip}
{/block}