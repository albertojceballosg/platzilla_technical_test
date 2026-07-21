{strip}
{foreach from=$APPLICATIONS item="application"}
	<tr>
		<td style="width: 50%">{$application.app_name}</td>
		<td>{($application.app_price * 10)} EUR</td>
	</tr>
{/foreach}
	<tr class="total">
		<td><strong class="uppercase">Total</strong> / Mes</td>
		<td><strong><span id="totalpriceanual">{$GRAND_TOTAL}</span> EUR</strong></td>
	</tr>
{/strip}