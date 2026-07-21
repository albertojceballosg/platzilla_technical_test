{strip}
<section class="cd-container" id="cd-timeline">
{foreach $TIMELINE as $cd}
	{if $cd.tipo eq 'Nuevo' }
		{assign var=fecha value=$cd.createdtime}
	{else}
		{assign var=fecha value=$cd.modifiedtime}
	{/if}
	<div class="cd-timeline-block">
		<div class="cd-timeline-img cd-picture">
			<i class="fa fa-clipboard fa-2x"></i>
		</div>
		<div class="cd-timeline-content">
			<h2>{$cd.tipo}: <a href="index.php?module={$cd.module}&action={$cd.action}&record={$cd.recordid}"><small>{$cd.label_entity}</small></a></h2>
			<p style="margin: 0;">{$fecha|date_format: 'd/m/Y h:i:s a'} / {$cd.last_name}</p>
		</div>
	</div>
{/foreach}
</section>
{/strip}