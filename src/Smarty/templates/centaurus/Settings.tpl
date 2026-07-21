{strip}
<div id="settings-section" class="main-box-body clearfix" style="background-color: #FFFFFF; width: 100%;">
	<div class="tabs-wrapper">
		<ul class="nav nav-tabs">
{foreach $TABS as $outerTabIndex => $outerTab}
	{if (empty ($outerTab.blocks))}
		{continue}
	{/if}
	{if (empty ($MOD[$outerTab.label]))}
		{assign var='label' value=$outerTab.label}
	{else}
		{assign var='label' value=$MOD[$outerTab.label]}
	{/if}
			<li{if ($outerTabIndex == 0)} class="active"{/if}>
				<a href="#tab-{$outerTabIndex}" data-toggle="tab"{if ($outerTab.label == 'LBL_ADMINISTRATION')} style="background-image: url('themes/centaurus/img/platzillaman.png'); background-repeat: no-repeat; background-position: 10px center; background-size: 30px 30px; padding-left: 45px;"{/if}>{$label}</a>
			</li>
{/foreach}
		</ul>
		<div class="tab-content" style="margin-bottom: 0; padding: 15px;">
{foreach $TABS as $outerTabIndex => $outerTab}
	{if (empty ($outerTab.blocks))}
		{continue}
	{/if}
	{assign var='innerTabs' value=$outerTab.innerTabs}
			<div id="tab-{$outerTabIndex}" class="tab-pane fade in{if ($outerTabIndex == 0)} active{/if}">
	{if (count ($innerTabs) > 0)}
				<div class="tabbable tabs-wrapper">
					<ul class="nav nav-tabs" style="margin-bottom: 2px;">
		{foreach $innerTabs as $innerTabIndex => $innerTab}
						<li{if ($innerTab@index == 0)} class="active"{/if}><a href="#tab-{$outerTabIndex}-{$innerTabIndex}">{$innerTab}</a></li>
		{/foreach}
					</ul>
					<div class="tab-content" style="margin-bottom: 0; padding: 15px 0 0 0;">
		{foreach $innerTabs as $innerTabIndex => $innerTab}
						<div id="tab-{$outerTabIndex}-{$innerTabIndex}" class="tab-pane fade in{if ($innerTab@index == 0)} active{/if}">
			{include file='Settings/SettingsTabContent.tpl' BLOCKS=$outerTab.blocks[$innerTab] SECTION_NAME=$outerTab.label TAB_NAME=$innerTab}
						</div>
		{/foreach}
					</div>
				</div>
	{else}
		{include file='Settings/SettingsTabContent.tpl' BLOCKS=$outerTab.blocks[0] SECTION_NAME=$outerTab.label TAB_NAME=''}
	{/if}
			</div>
{/foreach}
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery ('#settings-section').find ('ul.nav a').click (function (e) {
		e.preventDefault ();
		jQuery (this).tab ('show');
	});
</script>
{/strip}