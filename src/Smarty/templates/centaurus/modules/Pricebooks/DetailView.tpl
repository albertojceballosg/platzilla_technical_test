{strip}
{extends file="DetailView.tpl"}
{block name="css"}{/block}
{block name="js"}
	<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
{/block}
{block name="content-after-blocks"}
	{if (!empty ($CONDITION_GROUPS))}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="title-section main-box-header clearfix">
					<h2>Condiciones</h2>
				</header>
				<div class="main-box-body clearfix">
					<div class="condition-groups">
		{foreach $CONDITION_GROUPS as $conditionGroupId => $conditionGroupData}
			{include file="modules/Pricebooks/ConditionGroupDetailView.tpl" GROUP_ID=$conditionGroupId GROUP_DATA=$conditionGroupData}
		{/foreach}
					</div>
				</div>
			</div>
		</div>
	</div>
	{/if}
{/block}
{/strip}