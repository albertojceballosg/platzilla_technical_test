<table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="text-left" width="3%">#</th>
		<th class="text-left" width="9%">{$MOD.LBL_CURRENCY_TOOL}</th>
		<th class="text-left" width="23%">{$MOD.LBL_CURRENCY_NAME}</th>
		<th class="text-left" width="20%">{$MOD.LBL_CURRENCY_CODE}</th>
		<th class="text-left" width="10%">{$MOD.LBL_CURRENCY_SYMBOL}</th>
		<th class="text-left" width="20%">{$MOD.LBL_CURRENCY_CRATE}</th>
		<th class="text-left" width="15%">{$MOD.LBL_CURRENCY_STATUS}</th>
	</tr>
	</thead>
	<tbody>
{foreach $CURRENCIES as $currency}
	<tr>
		<td>{$currency@iteration}</td>
		<td>
	{if ($currency.defaultid != '-11')}
			<a href="index.php?module=Settings&action=CurrencyEditView&parenttab={$PARENTTAB}&record={$currency.id}&detailview=detail_view" class="table-link">
				<span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-search-plus fa-stack-1x fa-inverse"></i></span>
			</a>
			<a href="index.php?module=Settings&action=CurrencyEditView&parenttab={$PARENTTAB}&record={$currency.id}" class="table-link" title="{$APP.LBL_EDIT_BUTTON_LABEL}">
				<span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span>
			</a>
	{/if}
		</td>
		<td>
	{if ($currency.defaultid != '-11')}
			<b><a href="index.php?module=Settings&action=CurrencyEditView&parenttab={$PARENTTAB}&record={$currency.id}&detailview=detail_view">{$currency.name}</a></b>
	{else}
			<b>{$currency.name}</b>
	{/if}
		</td>
		<td>{$currency.code}</td>
		<td>{$currency.symbol}</td>
		<td>{$currency.crate}</td>
		<td><span class="label label-{if ($currency.status == 'Active')}success{else}default{/if}">{$currency.status}</span></td>
	</tr>
{/foreach}
	</tbody>
</table>

