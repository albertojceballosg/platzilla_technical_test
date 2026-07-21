{strip}
{foreach $BLOCKS as $block}
	{assign var='blockLabel' value=$block.name|@getTranslatedString:'Settings'}
	{assign var='description' value=$block.description|@getTranslatedString:'Settings'}
<div class="main-box infographic-box" style="display: inline-block; height: 150px; margin-left: 5px; max-height: 150px; max-width: 280px; position: relative; width: 280px; vertical-align: top;">
	<a href="{$block.linkto}">
		<i class="{$block.iconpath}"></i>
		<span class="headline"><b>{$blockLabel}</b></span>
	</a>
	<span class="headline" style="font-size: 80%;">{$description}</span>
	{foreach $TUTORIALS as $tutorial}
		{if ($tutorial.sectionname == $SECTION_NAME) && ($tutorial.tabname == $TAB_NAME) && ($tutorial.blockname == $block.name)}
			<a href="{$tutorial.url}" target="_blank" style="position: absolute; bottom: 15px; font-size: 12px; right: 15px;" title="Ver un tutorial en una ventana nueva">Saber más</a>
			{break}
		{/if}
	{/foreach}
</div>
{/foreach}
{/strip}