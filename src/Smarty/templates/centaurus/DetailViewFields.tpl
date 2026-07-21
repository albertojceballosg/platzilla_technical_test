{*<!--

/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

-->*}
{if $keyid eq '83' || $keyval neq ''}

	<!-- This file is used to display the fields based on the ui type in detailview -->
	{if $keyid eq '1' || $keyid eq 2 || $keyid eq '11' || $keyid eq '7' || $keyid eq '9' || $keyid eq '55' || $keyid eq '71' || $keyid eq '72' || $keyid eq '255' || $keyid eq '83'}
		<!--TextBox-->

		{if $keyid eq '55' || $keyid eq '255'}
			<!--SalutationSymbol-->

			{if $keyaccess eq $APP.LBL_NOT_ACCESSIBLE}
				<font color='red'>{$APP.LBL_NOT_ACCESSIBLE}</font>
			{else}
				{$keysalut}
			{/if}

			{*elseif $keyid eq '71' || $keyid eq '72'}  <!--CurrencySymbol-->

			{$keycursymb*}

		{/if}

		{if $keyid eq 11 && $USE_ASTERISK eq 'true'}

			<span id="dtlview_{$label}"><a href='javascript:' onclick='startCall("{$keyval}", "{$ID}")'>{$keyval}</a></span>

		{elseif $keyid eq '9'}

			<div class="col-md-6">

				<div class="col-md-4">
					<div class="label-input">
						<label for="">{$label}</label>
					</div>
				</div>

				<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
					<div class="input-group" style="width: 100%;">
						<span class="input-group-addon label-readonly" data-toggle="tooltip"><i class="fa">%</i></span>
						<span class="form-control label-readonly b-left" readonly>
							<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}>
							{$keyval}
						</span>
					</div>
				</div>

			</div>
		{else}
			<div class="col-md-6">

				<div class="col-md-4">
					<div class="label-input">
						<label for="">{$label}</label>
					</div>
				</div>

				<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
					<div class="input-group" style="width: 100%;">
						<span class="form-control label-readonly" readonly id="dtlview_{$label}" data-toggle="tooltip"
							{if $uitype eq '7'} data-value="{$keyval|replace:',':'.'}" {/if}>{$keyval}</span>
					</div>
				</div>
			</div>
		{/if}

		{if $keyid eq '71' && $keyfldname eq 'unit_price'}

			{if $PRICE_DETAILS|@count > 0}
				<span id="multiple_currencies" width="38%" style="align:right;">
					{*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*}<a href="javascript:void(0);"
						onclick="toggleShowHide('currency_class','multiple_currencies');">{$APP.LBL_MORE_CURRENCIES} &raquo;</a>
				</span>

				<div id="currency_class" class="multiCurrencyDetailUI">
					<table width="100%" height="100%" class="small" cellpadding="5">
						<tr class="detailedViewHeader">
							<th colspan="2">
								<b>{$MOD.LBL_PRODUCT_PRICES}</b>
							</th>
							<th align="right">
								<img border="0" style="cursor: pointer;"
									onclick="toggleShowHide('multiple_currencies','currency_class');"
									src="{'close.gif'|@vtiger_imageurl:$THEME}" />
							</th>
						</tr>

						<tr class="detailedViewHeader">
							<th>{$APP.LBL_CURRENCY}</th>
							<th colspan="2">{$APP.LBL_PRICE}</th>
						</tr>

						{foreach item=price key=count from=$PRICE_DETAILS}
							<tr>
								{*if $price.check_value eq 1*}
								<td class="dvtCellLabel" width="40%">
									{$price.currencylabel} ({$price.currencysymbol})
								</td>
								<td class="dvtCellInfo" width="60%" colspan="2">
									{$price.curvalue}
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
			{else}
				<div class="col-md-6">

					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$label}</label>
						</div>
					</div>

					<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-money"></i></span>
							<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}>
							<span class="form-control label-readonly b-left" readonly data-toggle="tooltip">{$keyval}</span>
						</div>
					</div>

				</div>
			{/if}
		{/if}
	{elseif $keyid eq '15' || $keyid eq '16'}
		<!--ComboBox-->

		{foreach item=arr from=$keyoptions}
			{if $arr[0] eq $APP.LBL_NOT_ACCESSIBLE}
				{assign var=keyval value=$APP.LBL_NOT_ACCESSIBLE}
				{assign var=fontval value='red'}
			{else}
				{assign var=fontval value=''}
			{/if}
		{/foreach}

		{* Personalización para work_situation: aplicar colores de fondo según valor *}
		{if $keyfldname eq 'work_situation'}
			{assign var=bgColor value=''}
			{assign var=textColor value=''}
			{assign var=cleanVal value=$keyval|trim}

			{if $cleanVal eq 'Óptima'}
				{assign var=bgColor value='#2E7D32'}
				{assign var=textColor value='white'}
			{elseif $cleanVal eq 'En control'}
				{assign var=bgColor value='#8BC34A'}
				{assign var=textColor value='white'}
			{elseif $cleanVal eq 'Alerta de eficiencia'}
				{assign var=bgColor value='#1976D2'}
				{assign var=textColor value='white'}
			{elseif $cleanVal eq 'Retraso operativo'}
				{assign var=bgColor value='#FF9800'}
				{assign var=textColor value='white'}
			{elseif $cleanVal eq 'Crítica'}
				{assign var=bgColor value='#D32F2F'}
				{assign var=textColor value='white'}
			{/if}

			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="">{$label}</label>
					</div>
				</div>

				<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
					<div class="input-group" style="width: 100%;">
						<span class="form-control" readonly id="dtlview_{$label}" data-toggle="tooltip"
							style="background-color: {$bgColor}; color: {$textColor}; font-weight: bold; text-align: center; border: 2px solid {$bgColor};">
							{$keyval}
						</span>
					</div>
				</div>
			</div>
			<!-- DEBUG: valor='{$keyval}' cleanVal='{$cleanVal}' bgColor='{$bgColor}' -->
		{else}
			{* Renderizado estándar para otros campos uitype 15/16 *}
			<div class="col-md-6">

				<div class="col-md-4">
					<div class="label-input">
						<label for="">{$label}</label>
					</div>
				</div>

				<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
					<div class="input-group" style="width: 100%;">
						<span class="form-control" readonly id="dtlview_{$label}" data-toggle="tooltip">
							{*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*}<font color="{$fontval}">{$keyval}
							</font>
						</span>
					</div>
				</div>

			</div>
		{/if}
	{elseif $keyid eq '33'}

		&nbsp;
		{foreach item=sel_val from=$keyoptions }
			{if $sel_val[2] eq 'selected'}
				{if $selected_val neq ''}
					{assign var=selected_val value=$selected_val|cat:', '}
				{/if}
				{assign var=selected_val value=$selected_val|cat:$sel_val[0]}
			{/if}
		{/foreach}
		{$selected_val|replace:"\n":"<br>&nbsp;&nbsp;"}

	{elseif $keyid eq '14'}
		<!--Time-->
		<div class="col-md-6">

			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>

			<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon label-readonly"><i class="fa fa-tachometer"></i></span>
					<span class="form-control label-readonly b-left" readonly data-toggle="tooltip">{$keyval}</span>
				</div>
			</div>

		</div>
	{elseif $keyid eq '17'}
		<!--WebSite-->
		<div class="col-md-6">

			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>

			<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon label-readonly"><i class="fa fa-wordpress"></i></span>
					<span class="form-control label-readonly b-left" readonly data-toggle="tooltip"><a href="{$keyval}"
							target="_blank">{$keyval}</a></span>
				</div>
			</div>

		</div>
	{elseif $keyid eq '85'}
		<!--Skype-->

		&nbsp;<img src="{'skype.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SKYPE}" title="{$APP.LBL_SKYPE}"
			LANGUAGE=javascript align="absmiddle"></img>
		<span id="dtlview_{$label}"><a href="skype:{$keyval}?call">{$keyval}</a></span>

	{elseif $keyid eq '19' || $keyid eq '20'}
		<!--TextArea/Description-->

		{if $label eq $MOD.LBL_ADD_COMMENT}

			{assign var=keyval value=''}

		{/if}
		&nbsp;
		<!--To give hyperlink to URL-->
		{$keyval|regex_replace:"/(^|[\n ])([\w]+?:\/\/.*?[^ \"\n\r\t<]*)/":"\\1<a href=\"\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:\/[^ \"\t\n\r<]*)?)/":"\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)/i":"\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>"|regex_replace:"/,\"|\.\"|\)\"|\)\.\"|\.\)\"/":"\""|replace:"\n":"<br>&nbsp;"}

	{elseif $keyid eq '21' || $keyid eq '24' || $keyid eq '22'}
		<!--TextArea/Street-->

		<div class="col-md-6">
			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>

			<div class="form-group col-md-8">
				&nbsp;<span id="dtlview_{$label}" data-toggle="tooltip">{$keyval}</span>
			</div>
		</div>
	{elseif $keyid eq '50' || $keyid eq '73' || $keyid eq '51' || $keyid eq '57' || $keyid eq '59' || $keyid eq '75' || $keyid eq '81' || $keyid eq '76' || $keyid eq '78' || $keyid eq '80'}
		<!--AccountPopup-->

		&nbsp;<a href="{$keyseclink}">{$keyval}</a>

	{elseif $keyid eq 82}
		<!--Email Body-->

		&nbsp;{$keyval}

	{elseif $keyid eq '53'}
		<!--Assigned To-->

		<div class="col-md-6">
			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>
			<div class="form-group col-md-8">
				{if $keyseclink eq ''}
					{$keyval}
				{else}
					<a href="{$keyseclink.0}">{$keyval}</a>
				{/if}
			</div>
		</div>

	{elseif $keyid eq '56'}
		<!--CheckBox-->
		<div class="col-md-6">
			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>

			<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block;">
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon label-readonly"><i class="fa fa-check-square"></i></span>
					<span class="form-control label-readonly b-left" readonly id="dtlview_{$label}"
						data-toggle="tooltip">{$keyval}</span>
				</div>
			</div>
		</div>

	{elseif $keyid eq 83}
		<!-- Handle the Tax in Inventory -->
		<!-- Ajustando vista de taxes desde el modulo de productos.
						[ TT11176 ] Ajustes TPL Factura Calculo Impuestos
					DM 16/06/2016-->
		{*
		<td align="right" class="dvtCellLabel">
			{$APP.LBL_VAT} {$APP.COVERED_PERCENTAGE}
		</td>
		<td class="dvtCellInfo" align="left">&nbsp;
			{$VAT_TAX}
		</td>
		<td colspan="2" class="dvtCellInfo">&nbsp;</td>
		</tr>

		<tr>
			<td align="right" class="dvtCellLabel">
				{$APP.LBL_SALES} {$APP.LBL_TAX} {$APP.COVERED_PERCENTAGE}
			</td>
			<td class="dvtCellInfo" align="left">&nbsp;
				{$SALES_TAX}
			</td>
			<td colspan="2" class="dvtCellInfo">&nbsp;</td>
		</tr>

		<tr>
			<td align="right" class="dvtCellLabel">
				{$APP.LBL_SERVICE} {$APP.LBL_TAX} {$APP.COVERED_PERCENTAGE}
			</td>
			<td class="dvtCellInfo" align="left" >&nbsp;
				{$SERVICE_TAX}
			</td>
		</tr>*}


		<tr>
			<td>
				<table width="50%" class="table">
					<tr>
						<td colspan="2" class="dvtCellLabel" align="center">{$APP.LBL_TAXES_TABLE}</td>
					</tr>
					{foreach item=tax key=count from=$TAX_DETAILS}
						<tr>
							<td align="right" width="50%" class="dvtCellLabel">
								{$tax.taxlabel} {$APP.COVERED_PERCENTAGE}&nbsp;:
							</td>
							<td align="left" class="dvtCellLabel">
								{$tax.percentage}
							</td>
						</tr>
					{/foreach}
				</table>
			</td>
		</tr>
		<!-- FIN IMPLEMENTACIÓN-->
	{elseif $keyid eq 69}
		<!-- for Image Reflection -->

		&nbsp;{$keyval}

	{elseif $keyid eq '100'}

		{$keyval}

	{elseif $keyid eq '101'}
		<div id="progress_{$keyval.relmodule}">
			<div id="progress_lbl_{$keyval.relmodule}"
				style="float:left; margin-left: 0; width: {$keyval.progress}%; margin-top: 4px; background:#32ff50; font-weight: bold; text-shadow: 1px 1px 0 #fff;text-align:center;">
				{$keyval.progress}%</div>
			<input type="hidden" id="progress_fldname_{$keyval.relmodule}" value="{$keyfldname}" />
		</div>
		<!--script>jQuery(document).ready(function(){ldelim}
					jQuery( "#progress_{$keyval.relmodule}" ).progressbar({ldelim}"value":{$keyval.progress}{rdelim});
						jQuery('#progress_{$keyval.relmodule}').find( ".ui-progressbar-value" ).css({ldelim}
							 "background": '{$keyval.color}'
						 {rdelim});
			{rdelim})</script-->
	{elseif $keyid eq '70' || $keyid eq '52'}
		<div class="col-md-6">

			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>

			<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon label-readonly"><i class="fa fa-calendar"></i></span>
					<span class="form-control label-readonly b-left" readonly data-toggle="tooltip">{$keyval}</span>
				</div>
			</div>

		</div>
	{else}
		<div class="col-md-6">

			<div class="col-md-4">
				<div class="label-input">
					<label for="">{$label}</label>
				</div>
			</div>

			<div class="form-group col-md-8" id="td_{$keyfldname}" style="display: block">
				<div class="input-group" style="width: 100%;">
					<span class="form-control" readonly id="dtlview_{$label}" data-toggle="tooltip" {if $uitype eq '7'}
						data-value="{$keyval|replace:',':'.'}" {/if}>
						{*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*}
						{$keyval}
					</span>
				</div>
			</div>

		</div>
	{/if}
{/if}