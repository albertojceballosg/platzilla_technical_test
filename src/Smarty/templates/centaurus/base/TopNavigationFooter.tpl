{extends file="../boilerplate_out_footer.tpl"}

{block name="title"}
{/block}

{block name="css"}	
{/block}

{block name="body"}
<div class="page-wrap container">
	<div class="row">
		<div class="col-xs-12">
			{*{include file="base/Navigation.tpl"}*}

			{block name="body-content"}
			{/block}
		</div>
	</div>
</div>
{/block}