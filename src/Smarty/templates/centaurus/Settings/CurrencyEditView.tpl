{strip}
<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<h1><a href="index.php?module=Settings&action=CurrencyListView&parenttab=Settings">{$MOD.LBL_CURRENCY_SETTINGS}</a>
		</div>
	</div>
</div>
<form action="index.php" method="post" name="index" id="form" onsubmit="VtigerJS_DialogBox.block ();">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="parenttab" value="{$PARENTTAB}" />
	<input type="hidden" name="action" value="index" />
	<input type="hidden" name="record" value="{$CURRENCY_ID}" />
	<div class="col-lg-12">
		<div class="row">
			<div class="main-box no-header clearfix" style="padding-bottom: 20px;">
				<div class="col-lg-8 pull-left">
					<h2>
{if ($CURRENCY_ID)}
						{$MOD.LBL_EDIT} &quot;{$CURRENCY_NAME}&quot;
{else}
						{$MOD.LBL_NEW_CURRENCY}
{/if}
					</h2>
				</div>
				<div class="col-lg-3 pull-right text-right">
					<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary btn-md" onclick="this.form.action.value='SaveCurrencyInfo'; return validate()" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" />&nbsp;
					<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-md" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="main-box-header clearfix"></header>
				<div class="main-box-body clearfix">
					<div class="row">
						<div class="form-group col-md-6" id="">
							<label for="currency_name"><span style="color: red;">*</span> {$MOD.LBL_CURRENCY_NAME}</label>
							<select name="currency_name" id="currency_name" class="form-control" onChange="updateSymbolAndCode ();">
{foreach $UNUSED_CURRENCIES as $currencyName => $currency}
								<option value="{$currencyName}"{if ($currencyName == $CURRENCY_NAME)} selected="selected"{/if}>{$currencyName} ({$currency[1]})</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-md-6" id="">
							<label for="currency_code"><span style="color: red;">*</span> {$MOD.LBL_CURRENCY_CODE}</label>
							<input type="text" name="currency_code" id="currency_code" value="{$CURRENCY_CODE}" class="form-control" readonly="readonly" />
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-6" id="">
							<label for="currency_symbol"><span style="color: red;">*</span> {$MOD.LBL_CURRENCY_SYMBOL}</label>
							<input type="text" name="currency_symbol" id="currency_symbol" value="{$CURRENCY_SYMBOL}" class="form-control" readonly="readonly" />
						</div>
						<div class="form-group col-md-6" id="">
							<label for="conversion_rate"><span style="color: red;">*</span> {$MOD.LBL_CURRENCY_CRATE} ({$MOD.LBL_BASE_CURRENCY} {$MASTER_CURRENCY})</label>
							<input type="text" name="conversion_rate" id="conversion_rate" value="{$CURRENCY_CONVERSION_RATE}" class="form-control" />
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-6" id="">
							<input type="hidden" value="{$CURRENCY_STATUS}" id="old_currency_status" />
							<label for="currency_status"><span style="color: red;">*</span> {$MOD.LBL_CURRENCY_STATUS}</label>
							<select name="currency_status" id="currency_status" class="form-control"{if ($CURRENCY_IS_ASSIGNED)} disabled="disabled"{/if}>
								<option value="Active"{if ($CURRENCY_STATUS == 'Active')} selected="selected"{/if}>{$MOD.LBL_ACTIVE}</option>
								<option value="Inactive"{if ($CURRENCY_STATUS == 'Inactive')} selected="selected"{/if}>{$MOD.LBL_INACTIVE}</option>
							</select>
						</div>
						<div class="form-group col-md-6"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="row">
				<div class="main-box no-header clearfix" style="padding-bottom: 20px;">
					<div class="col-lg-8 pull-left"></div>
					<div class="col-lg-3 pull-right text-right">
						<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary btn-md" onclick="this.form.action.value='SaveCurrencyInfo'; return validate()" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" />&nbsp;
						<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-md" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
	var currency_array = {$UNUSED_CURRENCIES|json_encode};

{literal}
	function validate () {
		if (!emptyCheck ('currency_name', 'Currency Name', 'text')) {
			return false;
		}
		if (!emptyCheck ('currency_code', 'Currency Code', 'text')) {
			return false;
		}
		if (!emptyCheck ('currency_symbol', 'Currency Symbol', 'text')) {
			return false;
		}
		if (!emptyCheck ('conversion_rate', 'Conversion Rate', 'text')) {
			return false;
		}
		if (!emptyCheck ('currency_status', 'Currency Status', 'text')) {
			return false;
		}
		if (isNaN (getObj ('conversion_rate').value) || eval (getObj ('conversion_rate').value) <= 0) {
{/literal}
			alert ('{$APP.ENTER_VALID_CONVERSION_RATE}');
			return false;
{literal}
		}
		if ((getObj ('currency_status') != null) && (getObj ('currency_status').value == 'Inactive') && (getObj ('old_currency_status') != null) && (getObj ('old_currency_status').value == 'Active')) {
			if (getObj ('CurrencyEditLay') != null) {
				getObj ('CurrencyEditLay').style.display = 'block';
			}
			return false;
		} else {
			return true;
		}
	}

	function updateSymbolAndCode () {
		var selected_curr = document.getElementById ('currency_name').value;
		getObj ('currency_code').value = currency_array[ selected_curr ][ 0 ];
		getObj ('currency_symbol').value = currency_array[ selected_curr ][ 1 ];
	}

	updateSymbolAndCode ();
</script>
{/literal}
{/strip}