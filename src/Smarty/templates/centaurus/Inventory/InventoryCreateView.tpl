{extends file="Inventory/InventoryEditView.tpl"}
{block name="scripts" append}

{if $POPUPCREATE eq 'create'}
<script type="text/javascript">
	{literal}
		jQuery(document).ready(function() {
			jQuery('#header-navbar').hide();
			jQuery('#nav-col').hide();
			jQuery('#config-tool-bar').hide();
		});
	{/literal}
</script>
{/if}

{/block}

{block name="content"}

	{include file='EditViewHidden.tpl'}

	{foreach key=header item=data from=$BLOCKS}
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2>{$header}</h2>
					</header>
					<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
						{include file="DisplayFields.tpl"}
					</div>
				</div>
			</div>
		</div>
	{/foreach}

	{if $MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'quote' || $MODULE eq 'myinvoice'}
        {if $MODULE eq 'myinvoice'}
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box">
						<header class="title-section main-box-header clearfix" style="padding-bottom:15px !important">
							<h2>{$MOD.LBL_TERMS_INFORMATION}</h2>
						</header>
						<div class="main-box-body clearfix">
							<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-responsive">
								<tr>
									<td  valign=top style="padding:20px; padding-top:0;">
                                        {$INV_TERMSANDCONDITIONS}
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
        {/if}
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2>Productos</h2>
					</header>
					<div class="main-box-body clearfix">
						<table class="table table-bordered">

							{if $CHANGE_PRODUCT_DUPLICATE eq 1}
								{include file="Inventory/ProductDetailsEditView.tpl"}

							{else}
								{include file="Inventory/ProductDetailsCreateView.tpl"}
							{/if}
						</table>
					</div>
				</div>
			</div>
		</div>
	{/if}
	{block name="content-after-blocks"}{/block}
	<div class="clearfix" style="height: 25px; margin-bottom: 16px;"></div>
	<div class="row">
		<div id="fixed-btns-bar" style="display:block">
			<div class="container">
				<div class="row">
					<div class="col-xs-12" style="padding: 25px; height: 75px;">
	{block name="buttons-bar"}{/block}
					</div>
				</div>
			</div>
		</div>
	</div>
	<input name='search_url' id="search_url" type='hidden' value='{$SEARCH}'>
{/block}

{block name="buttons-bar"}

	<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success" onclick="this.form.action.value='Save';  return validateInventory('{$MODULE}')" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
	{if $POPUPCREATE neq 'create'}
		<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-default" onclick="window.history.back()" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
	{else}
		<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-default" onclick="window.close();" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
	{/if}

{/block}
