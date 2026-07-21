<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<h1><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
		</div>
	</div>
</div>
<div class="col-lg-12">
	<div class="row">
		<div class="main-box no-header clearfix" style="padding-bottom: 20px;">
			<div class="col-lg-8 pull-left">
				<h2>{$MOD.LBL_CURRENCY_DESCRIPTION}</h2>
			</div>
			<div class="col-lg-3 pull-right">
				<form action="index.php" onsubmit="VtigerJS_DialogBox.block ();">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="CurrencyEditView" />
					<input type="hidden" name="parenttab" value="{$PARENTTAB}" />
					<input type="submit" value="{$MOD.LBL_NEW_CURRENCY}" class="btn btn-primary btn-sm pull-right" />
				</form>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix" style="">
			<header class="main-box-header clearfix">
				<h2>{$MOD.LBL_CURRENCY_LIST}</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<div id="CurrencyListViewContents">
{include file="Settings/CurrencyListViewEntries.tpl"}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="currencydiv" style="display: block; position: absolute; width: 250px;"></div>
<script>
{literal}
	function deleteCurrency (currid) {
		$ ('status').style.display = 'inline';
		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'action=SettingsAjax&file=CurrencyDeleteStep1&return_action=CurrencyListView&return_module=Settings&module=Settings&parenttab=Settings&id=' + currid,
					onComplete: function (response) {
						$ ('status').style.display = 'none';
						$ ('currencydiv').innerHTML = response.responseText;
					}
				}
		);
	}

	function transferCurrency (del_currencyid) {
		var transferCurrencyId = $ ('transfer_currency_id');
		$ ('status').style.display = 'inline';
		$ ('CurrencyDeleteLay').style.display = 'none';
		var trans_currencyid = transferCurrencyId.options[ transferCurrencyId.options.selectedIndex ].value;
		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=Settings&action=SettingsAjax&file=CurrencyDelete&ajax=true&delete_currency_id=' + del_currencyid + '&transfer_currency_id=' + trans_currencyid,
					onComplete: function (response) {
						$ ('status').style.display = 'none';
						$ ('CurrencyListViewContents').innerHTML = response.responseText;
					}
				}
		);
	}
{/literal}
</script>
