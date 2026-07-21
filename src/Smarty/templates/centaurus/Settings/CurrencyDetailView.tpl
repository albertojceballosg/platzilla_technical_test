{strip}
<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<h1><a href="index.php?module=Settings&action=CurrencyListView&parenttab=Settings">{$MOD.LBL_CURRENCY_SETTINGS}</a>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix pull-right">
				<form action="index.php" method="post" name="index" id="form" onsubmit="VtigerJS_DialogBox.block();">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="parenttab" value="{$PARENTTAB}" />
					<input type="hidden" name="action" value="index" />
					<input type="hidden" name="record" value="{$CURRENCY_ID}" />
					<input type="submit" class="btn btn-primary btn-sm" value="Editar" onclick="this.form.action.value='CurrencyEditView'; this.form.parenttab.value='Settings'; this.form.record.value='{$CURRENCY_ID}'" />&nbsp;
					<a href="index.php?module=Settings&action=CurrencyListView&parenttab=Settings" class="btn btn-warning btn-sm">Volver</a>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix"></header>
			<div class="main-box-body clearfix">
				<ul class="widget-users row">
					<li class="col-md-6">
						<div class="details">
							<div class="name">{$MOD.LBL_CURRENCY_NAME}</div>
							<div class="type">{$CURRENCY_NAME}</div>
						</div>
					</li>
					<li class="col-md-6">
						<div class="details">
							<div class="name">{$MOD.LBL_CURRENCY_CODE}</div>
							<div class="type">{$CURRENCY_CODE}</div>
						</div>
					</li>
					<li class="col-md-6">
						<div class="details">
							<div class="name">{$MOD.LBL_CURRENCY_SYMBOL}</div>
							<div class="type">{$CURRENCY_SYMBOL}</div>
						</div>
					</li>
					<li class="col-md-6">
						<div class="details">
							<div class="name">{$MOD.LBL_CURRENCY_CRATE} ({$MOD.LBL_BASE_CURRENCY} {$MASTER_CURRENCY})</div>
							<div class="type">{$CURRENCY_CONVERSION_RATE}</div>
						</div>
					</li>
					<li class="col-md-6">
						<div class="details">
							<div class="name">{$MOD.LBL_CURRENCY_STATUS}</div>
							<div class="type">{$CURRENCY_STATUS}</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
{/strip}