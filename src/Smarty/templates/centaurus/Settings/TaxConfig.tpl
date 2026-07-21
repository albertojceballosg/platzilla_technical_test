{strip}
<script type="text/javascript" src="include/js/Inventory.js"></script>
<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<h1><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
		</div>
	</div>
</div>
<div class="col-lg-12">
	<div class="row">
		<div class="main-box no-header clearfix" style="">
			<div class="col-lg-8 pull-left">
				<h2>{$MOD.LBL_TAX_DESC}</h2>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<form name="inventory-taxes" method="POST" action="index.php" onsubmit="VtigerJS_DialogBox.block ();">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="action" value="" />
		<input type="hidden" name="parenttab" value="Settings" />
		<input type="hidden" name="save_tax" value="" />
		<input type="hidden" name="edit_tax" value="" />
		<input type="hidden" name="add_tax_type" value="" />
		<div class="col-md-6">
			<div class="main-box clearfix" style="">
				<header class="main-box-header clearfix">
					<div class="row">
						<div class="col-lg-6 pull-left">
							<h2>{$MOD.LBL_PRODUCT_TAX_SETTINGS}</h2>
						</div>
						<div class="col-lg-6 pull-right text-right">
{if ($EDIT_MODE != 'true')}
							<input title="{$MOD.LBL_ADD_TAX_BUTTON}" accessKey="{$MOD.LBL_ADD_TAX_BUTTON}" onclick="fnAddTaxConfigRow ('');" type="button" name="button" value="{$MOD.LBL_ADD_TAX_BUTTON}" class="btn btn-primary btn-sm" />
	{if ($TAX_COUNT > 0)}
							&nbsp;
							<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&edit_tax=true" class="btn btn-warning btn-sm">{$MOD.LBL_EDIT_TAXES}</a>
	{/if}
{else}
							<input class="btn btn-primary btn-sm" title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" onclick="this.form.action.value='TaxConfig'; this.form.save_tax.value='true'; this.form.parenttab.value='Settings'; return validateTaxes ('tax_count');" type="submit" name="button2" value=" {$APP.LBL_SAVE_BUTTON_LABEL}  " />
							&nbsp;
							<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings" class="btn btn-warning btn-sm">{$APP.LBL_CANCEL_BUTTON_TITLE}</a>
{/if}
						</div>
					</div>
				</header>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<div>
							<table width="100%" cellpadding="5" cellspacing="0" id="add_tax" class="table table-striped table-hover">
								<tbody>
{if ($TAX_COUNT == 0)}
								<tr>
									<td colspan="3" align="right" nowrap>
										<h2>{$MOD.LBL_NO_TAXES_AVAILABLE}. {$MOD.LBL_PLEASE} {$MOD.LBL_ADD_TAX_BUTTON}</h2>
									</td>
								</tr>
{else}
								<tr>
									<td id="td_add_tax" class="small" colspan="3" align="right" nowrap></td>
								</tr>
	{foreach $TAX_VALUES as $count => $tax}
								<tr>
									<td width="35%">
		{if ($EDIT_MODE == 'true')}
										<input name="{$tax.taxlabel|bin2hex}" id="taxlabel_{$count}" type="text" value="{$tax.taxlabel}" class="form-control" placeholder="" />
		{else}
										{$tax.taxlabel}
		{/if}
									</td>
									<td width="55%">
		{if ($EDIT_MODE == 'true')}
										<div class="input-group">
											<input class="form-control" id="taxvalor_{$count}" name="{$tax.taxname}" type="text" value="{$tax.percentage}" placeholder="" />
											<span class="input-group-addon"> %</span>
										</div>
		{else}
										{$tax.percentage}&nbsp;%
		{/if}
									</td>
									<td width="10%">
		{if ($tax.deleted == 0)}
										<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&disable=true&taxname={$tax.taxname}"><img src="{'enabled.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.LBL_ENABLE}" title="{$MOD.LBL_ENABLE}" /></a>
		{else}
										<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&enable=true&taxname={$tax.taxname}"><img src="{'disabled.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.LBL_ENABLE}" title="{$MOD.LBL_DISABLE}" /></a>
		{/if}
										&nbsp;
										<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&delete=true&taxname={$tax.taxname}"><img src="{'delete.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.Delete}" title="{$MOD.Delete}" /></a>
									</td>
								</tr>
	{/foreach}
{/if}
								</tbody>
							</table>
{if ($EDIT_MODE == 'true')}
							<input type="hidden" id="tax_count" value="{$count}" />
{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	<!-- Fin impuestos 2 -->
</div>
<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
{/literal}
		var sw = '{$SW}',
			swedit = '{$SW_EDIT}',
			error = '{$ERROR}';
{literal}
		if (sw == '1') {
			alert (alert_arr.LBL_ERR_TAX_LABEL_ALREADY_EXISTS);
		}
		if (swedit == '2') {
			alert (alert_arr.LBL_ERR_SOME_TAX_LABELS_ALREADY_EXISTS);
		}
		if (error == '3') {
			alert (alert_arr.ERR_TAX_LABELS);
		}
	});


	var tax_labelarr = {
		SAVE_BUTTON: {/literal}'{$APP.LBL_SAVE_BUTTON_LABEL}',{literal}
		CANCEL_BUTTON: {/literal}'{$APP.LBL_CANCEL_BUTTON_LABEL}',{literal}
		TAX_NAME: {/literal}'{$APP.LBL_TAX_NAME}',{literal}
		TAX_VALUE: {/literal}'{$APP.LBL_TAX_VALUE}'{literal}
	};
{/literal}
</script>
{/strip}