{extends file="../../CreateView.tpl"}
{if (isset ($smarty.get.error)) && (trim ($smarty.get.error))}
{block name="content" prepend}
{strip}
	<div class="alert alert-danger">
		<strong>Error:</strong> {$smarty.get.error}
	</div>
{/strip}
{/block}
{/if}