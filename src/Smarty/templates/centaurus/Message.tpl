{strip}
{if (isset ($TYPE))}
	{if ($TYPE == 'ERROR')}
		{assign var='alertType' value='danger'}
		{assign var='alertMessage' value='Error'}
	{elseif ($TYPE == 'SUCCESS')}
		{assign var='alertType' value='success'}
		{assign var='alertMessage' value='Listo'}
	{elseif ($TYPE == 'WARNING')}
		{assign var='alertType' value='warning'}
		{assign var='alertMessage' value='Atención'}
	{else}
		{assign var='alertType' value='info'}
		{assign var='alertMessage' value='Información'}
	{/if}
{else}
	{assign var='alertType' value='info'}
	{assign var='alertMessage' value='Información'}
{/if}
<div class="row">
	<div class="col-xs-12 text-center">
		{*<h1 class="text-{$alertType}">{$alertMessage}</h1>*}
		<p class="text-{$alertType}">{$MESSAGE}</p>
		<p>Ir a <a href="{$URL}">{$LABEL}</a></p>
	</div>
</div>
{/strip}