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
<style>
	.table > thead {
		font-size: 14px !important;
	}
	.table .form-control {
		font-size: 11px !important;
	}
	.discountUI > td{
		font-size: 9px !important;
		padding: 6px !important;
	}
</style>


<script type="text/javascript" src="modules/myinvoice/Inventory.js"></script>
<script type="text/javascript" src="modules/Services/Services.js"></script>
<script>
if(!e)
	window.captureEvents(Event.MOUSEMOVE);

//  window.onmousemove= displayCoords;
//  window.onclick = fnRevert;
  
function displayCoords(currObj,obj,mode,curr_row) 
{ldelim}

	if(mode != 'discount_final' && mode != 'sh_tax_div_title' && mode != 'group_tax_div_title')
	{ldelim}
		var curr_productid = document.getElementById("hdnProductId"+curr_row).value;
		if(curr_productid == '')
		{ldelim}
			alert("{$APP.PLEASE_SELECT_LINE_ITEM}");
			return false;
		{rdelim}

		var curr_quantity = document.getElementById("qty"+curr_row).value;
		if(curr_quantity == '')
		{ldelim}
			alert("{$APP.PLEASE_FILL_QUANTITY}");
			return false;
		{rdelim}
	{rdelim}

	//Set the Header value for Discount
	if(mode == 'discount')
	{ldelim}
		document.getElementById("discount_div_title"+curr_row).innerHTML = '<b>{$APP.LABEL_SET_DISCOUNT_FOR_COLON} '+document.getElementById("productTotal"+curr_row).innerHTML+'</b>';
	{rdelim}
	else if(mode == 'tax')
	{ldelim}
		document.getElementById("tax_div_title"+curr_row).innerHTML = "<b>{$APP.LABEL_SET_TAX_FOR} "+document.getElementById("totalAfterDiscount"+curr_row).innerHTML+'</b>';
	{rdelim}
	else if(mode == 'discount_final')
	{ldelim}
		document.getElementById("discount_div_title_final").innerHTML = '<b>{$APP.LABEL_SET_DISCOUNT_FOR} '+document.getElementById("netTotal").innerHTML+'</b>';
	{rdelim}
	else if(mode == 'sh_tax_div_title')
	{ldelim}
		document.getElementById("sh_tax_div_title").innerHTML = '<b>{$APP.LABEL_SET_SH_TAX_FOR_COLON} '+document.getElementById("shipping_handling_charge").value+'</b>';
	{rdelim}
	else if(mode == 'group_tax_div_title')
	{ldelim}
		var net_total_after_discount = eval(document.getElementById("netTotal").innerHTML)-eval(document.getElementById("discountTotal_final").innerHTML);
		document.getElementById("group_tax_div_title").innerHTML = '<b>{$APP.LABEL_SET_GROUP_TAX_FOR_COLON} '+net_total_after_discount+'</b>';
	{rdelim}

	fnvshobj(currObj,'tax_container');
	if(document.all)
	{ldelim}
		var divleft = document.getElementById("tax_container").style.left;
		var divabsleft = divleft.substring(0,divleft.length-2);
		document.getElementById(obj).style.left = eval(divabsleft) - 120;

		var divtop = document.getElementById("tax_container").style.top;
		var divabstop =  divtop.substring(0,divtop.length-2);
		document.getElementById(obj).style.top = eval(divabstop) - 200;
	{rdelim}else
	{ldelim}
		document.getElementById(obj).style.left =  document.getElementById("tax_container").left;
		document.getElementById(obj).style.top = document.getElementById("tax_container").top;
	{rdelim}
	document.getElementById(obj).style.display = "block";

{rdelim}
  
	function doNothing(){ldelim}
	{rdelim}
	
	function fnHidePopDiv(obj){ldelim}
		document.getElementById(obj).style.display = 'none';
	{rdelim}
	function updatecssinputs(){ldelim}
		J( "#row"+rowCnt+" input.small " ).each(function() {ldelim}
		  J(this).attr({ldelim}'class':'form-control','style':'','onfocus':''{rdelim});
		{rdelim});
		J( "#row"+rowCnt+" textarea.small " ).each(function() {ldelim}
		  J(this).attr({ldelim}'class':'form-control','style':''{rdelim});
		{rdelim});
	{rdelim}


</script>

<!-- Added this file to display and hanld the Product Details in Inventory module  -->

   <tr>
	<td colspan="4" align="left">


<div class="table-responsive">
	
<table width="100%"  border="0" align="center" cellpadding="1" cellspacing="0" class="table" id="proTab">
	<thead>
   <tr>
	   	{if $MODULE neq 'PurchaseOrder'}
				<th colspan="2" class="text-center">
		{else}
				<th colspan="1" class="text-center">
		{/if}
			<b>{$APP.LBL_ITEM_DETAILS}</b>
		</th>
		<!--se agrego invoiceCurrencyUpdate() para actualizar tipo de moneda en faturas
		[ TT11174 ] Fallos-Ajustes 1 Facturas
		JA 20/06/2016-->
		<th class="text-center" align="center" colspan="7">
			<input type="hidden" value="{$INV_CURRENCY_ID}" id="prev_selected_currency_id" />
			<b>{$APP.LBL_CURRENCY}</b>&nbsp;&nbsp;
			<select class="form-control" id="inventory_currency" name="inventory_currency" onchange="updatePrices(); invoiceCurrencyUpdate();">
			{foreach item=currency_details key=count from=$CURRENCIES_LIST}
				{if $currency_details.curid eq $INV_CURRENCY_ID}
					{assign var=currency_selected value="selected"}
				{else}
					{assign var=currency_selected value=""}
				{/if}
				<OPTION value="{$currency_details.curid}" {$currency_selected}>{$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})</OPTION>
			{/foreach}
			</select>
		</th>

	   <th class="text-center" align="center" colspan="3" style="display:none">
		   <b>{$APP.LBL_TAX_MODE}</b>&nbsp;&nbsp;
		   <select id="taxtype" name="taxtype" onchange="decideTaxDiv(); calcTotal();" class="form-control">
			   <OPTION value="individual" selected>{$APP.LBL_INDIVIDUAL}</OPTION>
			   <OPTION value="group">{$APP.LBL_GROUP}</OPTION>
		   </select>
	   </th>


	</tr>
	<!-- Header for the Product Details -->
   <tr valign="top">
	<th width=5% valign="top" class="text-center" align="right" style="display: none"><b></b></th>
	<th width=20% class="text-center"><font color='red'>*</font><b>{$APP.LBL_ITEM_NAME}</b></th>
	{if $MODULE neq 'PurchaseOrder'}
		<th width=3% class="text-center">{$APP.LBL_QTY_IN_STOCK}</th>
	{/if}
	<th width=21% class="text-center">{$APP.LBL_DESCRIPTION}</th>
	<th width=10% class="text-center"><i title="{$APP.LBL_QTY}" class="fa fa-fw fa-plus-circle"></i> {$APP.LBL_QTY}</th>
	<th width=10% class="text-center" align="right">{$APP.Price}</th>
	   <th width=11% class="text-center" align="right">{$APP.LBL_ADJUSTMENT}</th>
	   <th width=10% class="text-center" align="right">{$APP.LBL_TAX}</th>
		<th width=13% valign="top" class="text-center" align="right"><b>{$APP.LBL_NET_PRICE}</b></th>
   </tr>


   </tr>

		{foreach key=row_no item=data from=$ASSOCIATEDPRODUCT name=outer1}
			{assign var="deleted" value="deleted"|cat:$row_no}
			{assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
			{assign var="productName" value="productName"|cat:$row_no}
			{assign var="comment" value="comment"|cat:$row_no}
			{assign var="productDescription" value="productDescription"|cat:$row_no}
			{assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
			{assign var="qty" value="qty"|cat:$row_no}
			{assign var="listPrice" value="listPrice"|cat:$row_no}
			{assign var="productTotal" value="productTotal"|cat:$row_no}
			{assign var="subproduct_ids" value="subproduct_ids"|cat:$row_no}
			{assign var="subprod_names" value="subprod_names"|cat:$row_no}
			{assign var="entityIdentifier" value="entityType"|cat:$row_no}
			{assign var="entityType" value=$data.$entityIdentifier}

			{assign var="discount_type" value="discount_type"|cat:$row_no}
			{assign var="discount_percent" value="discount_percent"|cat:$row_no}
			{assign var="checked_discount_percent" value="checked_discount_percent"|cat:$row_no}
			{assign var="style_discount_percent" value="style_discount_percent"|cat:$row_no}
			{assign var="discount_amount" value="discount_amount"|cat:$row_no}
			{assign var="checked_discount_amount" value="checked_discount_amount"|cat:$row_no}
			{assign var="style_discount_amount" value="style_discount_amount"|cat:$row_no}
			{assign var="checked_discount_zero" value="checked_discount_zero"|cat:$row_no}

			{assign var="discountTotal" value="discountTotal"|cat:$row_no}
			{assign var="totalAfterDiscount" value="totalAfterDiscount"|cat:$row_no}
			{assign var="taxTotal" value="taxTotal"|cat:$row_no}
			{assign var="netPrice" value="netPrice"|cat:$row_no}


   			<tr id="row{$row_no}" style="vertical-align: top;">
				<!-- column 1 - delete link - starts -->
				<td id="row{$row_no}_col{$row_no}" class="crmTableRow small text-center" style="display: none;">
					{*if $row_no neq 1*}
						<img src="{'delete.gif'|@vtiger_imageurl:$THEME}" border="0" onclick="deleteRow('{$MODULE}',{$row_no},'{$IMAGE_PATH}')">
					{*/if*}<br/><br/>
					{if $row_no neq 1}
						&nbsp;<a href="javascript:moveUpDown('UP','{$MODULE}',{$row_no})" title="Move Upward"><img src="{'up_layout.gif'|@vtiger_imageurl:$THEME}" border="0"></a>
					{/if}
					{if not $smarty.foreach.outer1.last}
						&nbsp;<a href="javascript:moveUpDown('DOWN','{$MODULE}',{$row_no})" title="Move Downward"><img src="{'down_layout.gif'|@vtiger_imageurl:$THEME}" border="0" ></a>
					{/if}
					<input type="hidden" id="{$deleted}" name="{$deleted}" value="0">
				</td>

				<!-- column 2 - Product Name - starts -->
				<td class="crmTableRow small text-center">
					<!-- Product Re-Ordering Feature Code Addition Starts -->
					<input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
					<table width="100%" cellspacing="0" cellpadding="1" border="0">
						<tbody>
							<tr>
								<td class="mini-products">
									<div class="input-group">
										<input type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="form-control" readonly />
										{if $entityType eq 'Services'}
											<div class="input-group-addon" id="searchIcon1" onclick="servicePickList(this,'{$MODULE}','{$row_no}')">
												<i class="fa fa-pencil-square-o"></i>
											</div>
										{else}
											<div class="input-group-addon" id="searchIcon1" onclick="productPickList(this,'{$MODULE}','{$row_no}')">
												<i class="fa fa-cogs"></i>
											</div>
										{/if}
									</div>
									<input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" />
									<input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" />
								</td>
							</tr>
							<tr>
								<td class="mini-products">
									<input type="hidden" value="{$data.$subproduct_ids}" id="{$subproduct_ids}" name="{$subproduct_ids}" />
									<span id="{$subprod_names}" name="{$subprod_names}"  style="color:#C0C0C0;font-style:italic;">{$data.$subprod_names}</span>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
				<!-- column 2 - Product Name - ends -->



				<!-- column 3 - Quantity in Stock - starts -->
				{if $MODULE eq 'quote' || $MODULE eq 'SalesOrder' || $MODULE eq 'Invoice' || $MODULE eq 'myinvoice'}
				   <td class="crmTableRow small text-center"><p></p><span id="{$qtyInStock}">{$data.$qtyInStock}</span></td>
				{/if}
				<!-- column 3 - Quantity in Stock - ends -->

				<!-- column 4 - Description Products -->
				<td class="crmTableRow small text-center">
					<textarea id="{$comment}" name="{$comment}" class="form-control">{$data.$comment}</textarea>

				</td>
				<!-- column 4 - Quantity - ends -->

				<!-- column 5 - Quantity - starts -->
				<td class="crmTableRow small text-center">
					<input id="{$qty}" name="{$qty}" type="text" onkeyup="validateDecimalGeneral('{$qty}')" class="small form-control" style="width:100px" onfocus="this.className='detailedViewTextBoxOn form-control small'" onBlur="settotalnoofrows(); loadTaxes_Ajax('{$row_no}'); calcTotal();
						{if $MODULE eq 'myinvoice' && $entityType neq 'Services'} stock_alert('{$row_no}');{/if}" onChange="setDiscount(this,'{$row_no}')" value="{$data.$qty}"/><br><span id="stock_alert{$row_no}"></span>
				</td>
				<!-- column 5 - Quantity - ends -->

				<!-- column 6 - List Price with Discount, Total After Discount and Tax as table - starts -->
				<td class="crmTableRow small text-center">
					<input id="{$listPrice}" name="{$listPrice}" value="{$data.$listPrice}" type="text" class="form-control text-right" onBlur="calcTotal(); setDiscount(this,'{$row_no}');callTaxCalc('{$row_no}');" style="width:100px" onkeyup="validateDecimalGeneral('{$listPrice}')"/>
				</td>
				<!-- column 6 - List Price with Discount, Total After Discount and Tax as table - ends -->


				<!-- column 7 - Discount, Total After Discount - starts -->
				<td class="crmTableRow small text-center">
					<a href="javascript:doNothing();" onClick="displayCoords(this,'discount_div{$row_no}','discount','{$row_no}')" ><div id="discountTotal{$row_no}" align="right">{$data.$discountTotal}</div></a>
					<!--Descuentos -->
					<div class="discountUI" id="discount_div{$row_no}" style="display:none">
						<input type="hidden" id="discount_type{$row_no}" name="discount_type{$row_no}" value="{$data.$discount_type}">
						<div id="email-box" class="clearfix" nowrap>
							<div style="float:left;padding-left:10px;width:100%;font-size:0.9em;">
								<div class="main-box infographic-box" style="float:left;width:360px;max-width:360px;height:182px;max-height:250px;margin-left:5px;padding:10px;">
									<a onclick="fnHidePopDiv('discount_div{$row_no}')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
									<span class="headline" id="discount_div_title{$row_no}" style="font-size:0.9em; text-align: left;"></span>
									<div class="form-group">
										<table width="100%" cellpadding="0" cellspacing="0" class="table">
											<tbody>
											<tr>
												<td style="padding: 0px; font-size:0.9em; text-align: left;">
													<div>
														<input class="discount" id="discount" type="radio" name="discount{$row_no}" {$data.$checked_discount_zero} onclick="setDiscount(this,'{$row_no}'); callTaxCalc('{$row_no}');loadTaxes_Ajax('{$row_no}'); calcTotal();"><label>&nbsp; {$APP.LBL_ZERO_DISCOUNT}</label>
													</div>
												</td>
												<td style="padding: 0px; font-size:0.9em; text-align: left;"></td>
											</tr>
											<tr>
												<td style="padding: 0; font-size:0.9em; text-align: left;">
													<div>
														<input class="discount" type="radio" id="percentaje" {$data.$checked_discount_percent} name="discount{$row_no}" onclick="setDiscount(this,'{$row_no}'); callTaxCalc('{$row_no}'); loadTaxes_Ajax('{$row_no}'); calcTotal();"><label>&nbsp; % {$APP.LBL_OF_PRICE}</label>
													</div>
												</td>
												<td style="padding: 0px; font-size:0.9em; text-align: left;">
													<input type="text" class="form-control text-right" size="6" onkeyup="validateDecimalGeneral('discount_percentage{$row_no}')" id="discount_percentage{$row_no}" name="discount_percentage{$row_no}" value="{$data.$discount_percent}" {$data.$style_discount_percent} onBlur="setDiscount(this,'{$row_no}'); callTaxCalc('{$row_no}'); loadTaxes_Ajax('{$row_no}'); calcTotal();">
												</td>
											</tr>
											<tr>
												<td style="padding: 0; font-size:0.9em; text-align: left;">
													<div>
														<input  class="discount" id="direct" type="radio" name="discount{$row_no}" onclick="setDiscount(this,'{$row_no}'); callTaxCalc('{$row_no}'); loadTaxes_Ajax('{$row_no}'); calcTotal();" {$data.$checked_discount_amount}><label>&nbsp;{$APP.LBL_DIRECT_PRICE_REDUCTION}</label>
													</div>
												</td>
												<td style="padding: 0px; font-size:0.9em; text-align: left;">
													<input type="text" class="form-control text-right" id="discount_amount{$row_no}" name="discount_amount{$row_no}" size="6" value="{$data.$discount_amount}" {$data.$style_discount_amount} onBlur="setDiscount(this,{$row_no}); callTaxCalc('{$row_no}'); loadTaxes_Ajax('{$row_no}'); calcTotal();">
												</td>
											</tr>
											</tbody>
										</table>
									</div>

								</div>
							</div>
						</div>
					</div>
				</td>
				<!-- column 7 - Discount, Total After Discount - END -->


				<!-- column 8 - Tax as table - starts -->
				<td class="crmTableRow small text-center">
					<div id="individual_tax_row{$row_no}" class="TaxShow">
						<a href="javascript:doNothing();" onClick="displayCoords(this,'tax_div{$row_no}','tax','{$row_no}')" ><div id="taxTotal{$row_no}" align="right">{$data.$taxTotal}</div></a>
							<div class="discountUI" id="tax_div{$row_no}" style="display:none">
								<!-- we will form the table with all taxes -->
								<div class="clearfix" nowrap>
									<div style="float:left;padding-left:10px;width:100%;">
										<div class="main-box infographic-box" style="float:left;width:360px;max-width:360px;height:147px;max-height:250px;margin-left:5px;padding:10px;overflow:auto">
											<a onclick="fnHidePopDiv('tax_div{$row_no}')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
											<span class="headline" id="tax_div_title{$row_no}" style="font-size:0.9em; text-align: left;">Set Tax for : {$data.$totalAfterDiscount}</span>
											<div class="form-group" style="font-size:0.9em; text-align: left;">
												<table width="100%" cellpadding="0" cellspacing="0" class="table" id="tax_table{$row_no}">
													<tbody>
													{foreach key=tax_row_no item=tax_data from=$data.taxes}
														{assign var="taxname" value=$tax_data.taxname|cat:"_percentage"|cat:$row_no}
														{assign var="tax_id_name" value="hidden_tax"|cat:$tax_row_no+1|cat:"_percentage"|cat:$row_no}
														{assign var="taxlabel" value=$tax_data.taxlabel|cat:"_percentage"|cat:$row_no}
														{assign var="popup_tax_rowname" value="popup_tax_row"|cat:$row_no}
														<tr style="font-size: 0.9em;">
															<td style="padding: 1;">
																<input type="text" class="form-control text-right" onkeyup="validateDecimalGeneral('{$taxname}')" size="5" name="{$taxname}" id="{$taxname}" value="{$tax_data.percentage}" onBlur="calcCurrentTax('{$taxname}',{$row_no},{$tax_row_no})">
																<input type="hidden" id="{$tax_id_name}" value="{$taxname}">
															</td>
															<td align="center" style="padding: 1;">%{$tax_data.taxlabel}</td>
															<td align="right" style="padding: 1;  font-size:0.9em;">
																<input type="text" class="form-control text-right" size="6" name="{$popup_tax_rowname}" id="{$popup_tax_rowname}" style="cursor:pointer;" value="{$tax_data.calc}" readonly>
															</td>
														</tr>

													{/foreach}
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
					</div>
				</td>	<!-- This above div is added to display the tax informations -->
				<!-- column 8 - Tax as table - END -->

				<!-- column 9 - Product Total - starts -->
				<td class="crmTableRow small text-center" style="display: none">
					<table width="100%" cellpadding="5" cellspacing="0">
					   <tr>
						<td id="productTotal{$row_no}" align="right">{$data.$productTotal}</td>
					   </tr>
					   <tr>
						<td id="discountTotal{$row_no}" align="right">{$data.$discountTotal}</td>
					   </tr>
					   <tr>
						<td id="totalAfterDiscount{$row_no}" align="right">{$data.$totalAfterDiscount}</td>
					   </tr>
					   <tr>
						<td id="taxTotal{$row_no}" align="right">{$data.$taxTotal}</td>
					   </tr>
					</table>
				</td>
				<!-- column 9 - Product Total - ends -->

				<!-- column 10 - Net Price - starts -->
				<td class="crmTableRow small text-center">
					<span id="netPrice{$row_no}"><b>{$data.$netPrice}</b></span>
				</td>
				<!-- column 10 - Net Price - ends -->
			</div>
		 <!-- Product Details First row - Ends -->
		{/foreach}
	<thead>

	</table>



<table class="table">
   <!-- Add Product Button -->
   <tr>
	<td colspan="3">
			<input type="button" name="Button" class="btn btn-primary btn-sm" value="{$APP.LBL_ADD_PRODUCT}" onclick="fnAddProductRow('{$MODULE}','{$IMAGE_PATH}');updatecssinputs();" />
			&nbsp;&nbsp;
			<input type="button" name="Button" class="btn btn-info btn-sm" value="{$APP.LBL_ADD_SERVICE}" onclick="fnAddServiceRow('{$MODULE}','{$IMAGE_PATH}');updatecssinputs();" />
	</td>
   </tr>


<!--
All these details are stored in the first element in the array with the index name as final_details
so we will get that array, parse that array and fill the details
-->
{assign var="FINAL" value=$ASSOCIATEDPRODUCT.1.final_details}

   <!-- Product Details Final Total Discount, Tax and Shipping&Hanling  - Starts -->

   <!-- Product Details Final Total Discount, Tax and Shipping&Hanling  - Starts -->
   <tr valign="top">
	<td colspan="2" class="mini-total" align="right"><b>{$APP.LBL_NET_TOTAL}</b></td>
	<td width="12%" id="netTotal" class="text-right" align="right">0.00</td>
   </tr>

   <tr valign="top" >
	<!--td class="mini-total" width="60%" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan="2">
		<div class="discountUI" id="discount_div_final" style="display:none">
			<input type="hidden" id="discount_type_final" name="discount_type_final" value="{$FINAL.discount_type_final}">
			<div id="email-box" class="clearfix" nowrap>
				<div style="float:left;padding-left:10px;width:100%;">
					<div class="main-box infographic-box" style="float:right;width:360px;max-width:360px;height:182px;max-height:250px;margin-left:5px;padding:10px;">
						<a onclick="fnHidePopDiv('discount_div_final')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
						<span class="headline" id="discount_div_title_final" style="font-size:0.9em; text-align: left;"></span>
						<div class="form-group">
							<table width="100%" cellpadding="0" cellspacing="0" class="table">
								<tbody>
									<tr>
										<td style="padding: 0px; font-size:0.9em; text-align: left;">
											<div>
												<input type="radio" name="discount_final" checked onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp; {$APP.LBL_ZERO_DISCOUNT}
											</div>
										</td>
										<td style="padding: 0px;"></td>
									</tr>
									<tr>
										<td style="padding: 0; font-size:0.9em; text-align: left;">
											<div>
												<input type="radio" name="discount_final" {$FINAL.checked_discount_percentage_final} onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();" >&nbsp; % {$APP.LBL_OF_PRICE}
											</div>
										</td>
										<td style="padding: 0px; font-size:0.9em; text-align: left;">
											<input type="text" class="form-control" size="6" id="discount_percentage_final" name="discount_percentage_final" value="{$FINAL.discount_percentage_final}" style="visibility:hidden" onkeyup="validateDecimalGeneral('discount_percentage_final')" onBlur="setDiscount(this,'_final'); calcGroupTax();calcTotal();">
										</td>
									</tr>
									<tr>
										<td style="padding: 0; font-size:0.9em; text-align: left;">
											<div>
												<input type="radio" name="discount_final" {$FINAL.checked_discount_amount_final} onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp;{$APP.LBL_DIRECT_PRICE_REDUCTION}
											</div>
										</td>
										<td style="padding: 0px; font-size:0.9em; text-align: left;">
											<input type="text" id="discount_amount_final" class="form-control" name="discount_amount_final" size="6" style="visibility:hidden" value="{$FINAL.discountTotal_final}" onkeyup="validateDecimalGeneral('discount_amount_final')" onBlur="setDiscount(this,'_final'); calcGroupTax();calcTotal();">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

	</td>
	<td align="right">
		<a href="javascript:doNothing();" onClick="displayCoords(this,'discount_div_final','discount_final','1')"><div title="(-)Descuentos" id="discountTotal_final">$FINAL.discountTotal_final}</div></a>

	</td>
   </tr>


   <!-- Group Tax - starts -->
   <tr id="group_tax_row" valign="top" class="TaxHide" >
	<!--td class="mini-total" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan=2>
		<!-- Pop Div For Group TAX -->
		<div class="discountUI" id="group_tax_div" style="display:none">
			<div id="email-box" class="clearfix" nowrap>
				<div style="float:left;padding-left:10px;width:100%;">
					<div class="main-box infographic-box" style="float:right;width:360px;max-width:360px;height:147px;max-height:250px;margin-left:5px;padding:10px;overflow:auto;">
						<a onClick="fnHidePopDiv('group_tax_div')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
						<span class="headline" id="group_tax_div_title" style="font-size:0.9em; text-align: left;"></span>
						<div class="form-group">
							<table width="100%" cellpadding="0" cellspacing="0" class="table">
								<tbody>
									{foreach item=tax_detail name=group_tax_loop key=loop_count from=$GROUP_TAXES}
										<tr>
											<td style="padding: 1;  font-size:0.9em;">
												<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.group_tax_loop.iteration}" onkeyup="validateDecimalGeneral('{$tax_detail.taxname}_group_percentage')" value="0.00" onBlur="calcTotal()">
											</td>
											<td style="padding: 1; font-size:0.9em;">%</td>
											<td align="center" style="padding: 1;  font-size:0.9em;">{$tax_detail.taxlabel}</td>
											<td align="right" style="padding: 1;  font-size:0.9em;">
												<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_group_amount" id="group_tax_amount{$smarty.foreach.group_tax_loop.iteration}" style="cursor:pointer;" value="0.00" readonly>
											</td>
										</tr>
									{/foreach}
									<input type="hidden" id="group_tax_count" value="{$smarty.foreach.group_tax_loop.iteration}">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Popup Div Group Tax -->
	</td>
	   <td align="right"><a href="javascript:doNothing();" onClick="displayCoords(this,'group_tax_div','group_tax_div_title',''); calcGroupTax();" ><div id="tax_final" class="mini-total" title="(+)Impuestos")>0.00</div></a></td>
   </tr>
   <!-- Group Tax - ends -->


   <tr valign="top"  style="display:none;">
	<!--td class="mini-total" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan=2>
		(+)&nbsp;<b>{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES} </b>
	</td>
	<td class="mini-total" align="right">
		<input id="shipping_handling_charge" onkeyup="validateDecimalGeneral('shipping_handling_charge')" name="shipping_handling_charge" type="text" class="form-control text-right" align="right" value="{$FINAL.shipping_handling_charge}" onBlur="calcSHTax();calcTotal();">
	</td>
   </tr>

   <tr valign="top" style="display:none;">
	<!--td class="mini-total" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan=2>
		(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'shipping_handling_div','sh_tax_div_title',''); calcSHTax();" >{$APP.LBL_TAX_FOR_SHIPPING_AND_HANDLING} </a></b>

		<!-- Pop Div For Shipping and Handlin TAX -->
		<div class="discountUI" id="shipping_handling_div" style="display:none">
			<div id="email-box" class="clearfix" nowrap>
				<div style="float:left;padding-left:10px;width:100%;">
					<div class="main-box infographic-box" style="float:right;width:360px;max-width:360px;height:250px;max-height:250px;margin-left:5px;padding:10px;overflow:auto;">
						<a onClick="fnHidePopDiv('shipping_handling_div')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
						<span class="headline" id="sh_tax_div_title" style="font-size:0.9em; text-align: left;"></span>
						<div class="form-group">
							<table width="100%" cellpadding="0" cellspacing="0" class="table">
								<tbody>
									{foreach item=tax_detail name=sh_loop key=loop_count from=$FINAL.sh_taxes}
										<tr>
											<td align="left" style="padding: 1; font-size:0.9em; text-align: left;">
												<div class="form-group">
													<input type="text" class="form-control" size="6" onkeyup="validateDecimalGeneral('sh_tax_percentage{$smarty.foreach.sh_loop.iteration}')" name="{$tax_detail.taxname}_sh_percent" id="sh_tax_percentage{$smarty.foreach.sh_loop.iteration}" value="{$tax_detail.percentage}" onBlur="calcSHTax()">
													<span class="input-group-addon">%</span>
												</div>
											</td>
											<td align="center" style="padding: 1;">{$tax_detail.taxlabel}</td>
											<td align="right" style="padding: 1;">
												<input type="text" class="form-control" size="6" onkeyup="validateDecimalGeneral('{$tax_detail.taxname}_sh_amount')" name="{$tax_detail.taxname}_sh_amount" id="sh_tax_amount{$smarty.foreach.sh_loop.iteration}" style="cursor:pointer;" value="{$tax_detail.amount}" readonly>
											</td>
										</tr>
									{/foreach}
									<input type="hidden" id="sh_tax_count" value="{$smarty.foreach.sh_loop.iteration}">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Popup Div for Shipping and Handling TAX -->



				

	</td>
	<td id="shipping_handling_tax"  align="right">{$FINAL.shtax_totalamount}</td>
   </tr>


   <tr valign="top" >
	<!--td class="mini-total" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan=2>
		<label>{$APP.LBL_ADJUSTMENT}</label>
		<select id="adjustmentType" name="adjustmentType"  class="form-control" onchange="calcTotal();" style="width:150px;">
			<option value="+">{$APP.LBL_ADD_ITEM}</option>
			<option value="-">{$APP.LBL_DEDUCT}</option>
		</select>
	</td>
	<td class="mini-total" align="right" style="vertical-align: bottom;">
		<input id="adjustment" name="adjustment" onkeyup="validateDecimalGeneral('adjustment')" type="text" class="form-control text-right" align="right" value="{$FINAL.adjustment}" onBlur="calcTotal();">
	</td>
   </tr>
   <tr valign="top">
	<td class="" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="mini-total" align="right"><b>{$APP.LBL_GRAND_TOTAL}</b></td>
	<td id="grandTotal" name="grandTotal" class="text-right" align="right">&nbsp;</td>
   </tr>
</table>

</div>
		<input type="hidden" name="totalProductCount" id="totalProductCount" value="">
		<input type="hidden" name="subtotal" id="subtotal" value="">
		<input type="hidden" name="total" id="total" value="">

	</td>
   </tr>


<script>
	
	calcTotal();

</script>



