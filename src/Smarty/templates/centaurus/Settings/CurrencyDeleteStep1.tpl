{strip}
<div id="CurrencyDeleteLay" class="layerPopup">
	<form name="newCurrencyForm" action="index.php" style="margin: 0;" onsubmit="VtigerJS_DialogBox.block ();">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="action" value="CurrencyDelete" />
		<input type="hidden" name="delete_currency_id" value="{$CURRENCY_ID}" />
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine">
			<tr>
				<td class="layerPopupHeading" align="left" width="60%">{$MOD.LBL_DELETE_CURRENCY}</td>
				<td align="right" width="40%">
					<img src="{$IMAGE_CLOSE_URL}" border="0" alt="{$APP.LBL_CLOSE}" title="{$APP.LBL_CLOSE}" style="cursor: pointer;" onClick="document.getElementById ('CurrencyDeleteLay').style.display='none';" />
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="5" width="95%" align="center">
			<tr>
				<td class="small">
					<table border="0" cellspacing="0" cellpadding="5" width="100%" align="center" bgcolor="white">
						<tr>
							<td width="50%" class="cellLabel small"><b>{$MOD.LBL_CURRDEL}</b></td>
							<td width="50%" class="cellText small"><b>{$CURRENCY_NAME}</b></td>
						</tr>
						<tr>
							<td class="cellLabel small"><b>{$MOD.LBL_TRANSCURR}</b></td>
							<td class="cellText small">
								<select class="select small" name="transfer_currency_id" id="transfer_currency_id" title="">
{foreach $CURRENCY_OPTIONS as $option}
									<option value="{$option.value}">{$option.text}</option>
{/foreach}
								</select>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerPopupTransport">
			<tr>
				<td align="center">
					<input type="button" name="Delete" value="{$APP.LBL_SAVE_BUTTON_LABEL}" class="crmbutton small save" onclick="transferCurrency ('{$CURRENCY_ID}');" />
				</td>
			</tr>
		</table>
	</form>
</div>
{/strip}