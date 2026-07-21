{strip}
{foreach from=$APPLICATIONS item="application"}
	<tr>
		<td style="width: 50%">{$application.app_name}</td>
		<td>{$application.app_price} EUR</td>
	</tr>
{/foreach}
	<tr class="total">
		<td><strong class="uppercase">Total</strong> / Mes</td>
		<td>
			<input type="hidden" id="totalpricehidden" name="totalpricehidden" value="{$TOTAL}" />
			<input type="hidden" id="totalpriceapps" name="totalpriceapps" value="{$TOTAL_APPS_PRICE}" />
			<span id="totalprice"><strong>{$TOTAL}</strong></span> EUR
		</td>
	</tr>
{/strip}