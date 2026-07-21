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
		document.getElementById("discount_div_title"+curr_row).style.display = "block";
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
	
		<th class="text-center" align="center" colspan="7">
		<input type="hidden" value="{$INV_CURRENCY_ID}" id="prev_selected_currency_id" />
		<b>{$APP.LBL_CURRENCY}</b>&nbsp;&nbsp;
		<select class="form-control" id="inventory_currency" name="inventory_currency" onchange="updatePrices();">
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

</thead>


{*


<!-- Following code is added for form the first row. Based on these we should form additional rows using script -->

   <!-- Product Details First row - Starts -->
   <tr valign="top" id="row1">

	<!-- column 1 - delete link - starts -->
	<td  class="text-center">&nbsp;
		<input type="hidden" id="deleted1" name="deleted1" value="0">
	</td>
	<!-- column 1 - delete link - ends -->

	<!-- column 2 - Product Name - starts -->
	<td class="text-center">
		<table width="100%"  border="0" cellspacing="0" cellpadding="1">
		   <tr>
			<td class="mini-products">
				<div class="input-group">
					<input type="text" id="productName1" name="productName1" class="form-control" value="{$PRODUCT_NAME}" readonly />
					<div class="input-group-addon" id="searchIcon1" onclick="productPickList(this,'{$MODULE}',1)">
						<i class="fa fa-cogs"></i>
					</div>
				</div>
				<input type="hidden" id="hdnProductId1" name="hdnProductId1" value="{$PRODUCT_ID}" />
				<input type="hidden" id="lineItemType1" name="lineItemType1" value="product" />
				&nbsp;<!--img id="searchIcon1" title="product" src="{'product.gif'|@vtiger_imageurl:$THEME}" style="cursor: pointer;" align="absmiddle" onclick="productPickList(this,'{$MODULE}',1)" /-->
			</td>
		</tr>
		<tr>
			<td class="mini-products">
				<input type="hidden" value="" id="subproduct_ids1" name="subproduct_ids1" />
				<span id="subprod_names1" name="subprod_names1" style="color:#C0C0C0;font-style:italic;"> </span>
			</td>
		   </tr>
		   <tr valign="bottom">
			<td class="mini-products" id="setComment">
				<textarea id="comment1" name="comment1" class="form-control"></textarea>
				<!--img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" onClick="{literal}${/literal}('comment1').value=''"; style="cursor:pointer;" /-->
			</td>
		   </tr>
		</table>
	</td>
	<!-- column 2 - Product Name - ends -->

	<!-- column 3 - Quantity in Stock - starts -->
	{if $MODULE neq 'PurchaseOrder'}
		<td class="text-center" ><span id="qtyInStock1">{$QTY_IN_STOCK}</span></td>
	{/if}
	<!-- column 3 - Quantity in Stock - ends -->


	<!-- column 4 - Quantity - starts -->
	<td class="text-center" style="vertical-align:top">
		<input id="qty1" name="qty1" type="text" class="form-control" onBlur="settotalnoofrows();calcTotal(); loadTaxes_Ajax(1); setDiscount(this,'1'); calcTotal();{if $MODULE eq 'Invoice'}stock_alert(1);{/if}" value=""/><br><span id="stock_alert1"></span>
	</td>
	<!-- column 4 - Quantity - ends -->


	<!-- column 5 - List Price with Discount, Total After Discount and Tax as table - starts -->
	<td class="text-center" align="right">				
		<table width="100%" cellpadding="0" cellspacing="0">
		   <tr>
			<td class="mini-products" align="right">
				<input id="listPrice1" name="listPrice1" value="{$UNIT_PRICE}" type="text" class="form-control" onBlur="calcTotal();setDiscount(this,'1'); callTaxCalc(1);calcTotal();"/>&nbsp;
			</td>
		   </tr>
		   <tr>
			<td class="mini-products" align="right" style="" nowrap>
				(-)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'discount_div1','discount','1')" >{$APP.LBL_DISCOUNT}</a> : </b>
				<div class="discountUI" id="discount_div1" style="display:none">
					<input type="hidden" id="discount_type1" name="discount_type1" value="">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table">
					   <tr>
						<td class="mini-products" id="discount_div_title1" nowrap align="left" ></td>
						<td class="mini-products" align="right"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0" onClick="fnHidePopDiv('discount_div1')" style="cursor:pointer;"></td>
					   </tr>
					   <tr>
						<td class="mini-products" align="left"><input type="radio" name="discount1" checked onclick="setDiscount(this,1); callTaxCalc(1);calcTotal();">&nbsp; {$APP.LBL_ZERO_DISCOUNT}</td>
						<td class="mini-products">&nbsp;</td>
					   </tr>
					   <tr>
						<td class="mini-products" align="left"><input type="radio" name="discount1" class="" onclick="setDiscount(this,1); callTaxCalc(1);calcTotal();">&nbsp; % {$APP.LBL_OF_PRICE}</td>
						<td class="mini-products" align="right"><input type="text" class="form-control" size="6" id="discount_percentage1" name="discount_percentage1" value="0" style="visibility:hidden" onBlur="setDiscount(this,1); callTaxCalc(1);calcTotal();">&nbsp;%</td>
					   </tr>
					   <tr>
						<td class="mini-products" align="left" nowrap><input type="radio" name="discount1" class="" onclick="setDiscount(this,1); callTaxCalc(1);calcTotal();">&nbsp;{$APP.LBL_DIRECT_PRICE_REDUCTION}</td>
						<td class="mini-products" align="right"><input type="text" id="discount_amount1" class="form-control" name="discount_amount1" size="6" value="0" style="visibility:hidden" onBlur="setDiscount(this,1); callTaxCalc(1);calcTotal();"></td>
					   </tr>
					</table>
				</div>
			</td>
		   </tr>
		   <tr>
			<td class="mini-products" align="right" style="" nowrap>
				<b>{$APP.LBL_TOTAL_AFTER_DISCOUNT} :</b>
			</td>
		   </tr>
		   <tr id="individual_tax_row1" class="TaxShow">
			<td class="mini-products" align="right" style="padding:5px;" nowrap>
				(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'tax_div1','tax','1')" >{$APP.LBL_TAX} </a> : </b>
				<div class="discountUI" id="tax_div1">
				</div>
			</td>
		   </tr>
		</table> 
	</td>
	<!-- column 5 - List Price with Discount, Total After Discount and Tax as table - ends -->


	<!-- column 6 - Product Total - starts -->
	<td class="" align="right">
		<table width="100%" cellpadding="5" cellspacing="0">
		   <tr>
			<td class="mini-products" id="productTotal1" align="right">&nbsp;</td>
		   </tr>
		   <tr>
			<td class="mini-products" id="discountTotal1" align="right">0.00</td>
		   </tr>
		   <tr>
			<td class="mini-products" id="totalAfterDiscount1" align="right">&nbsp;</td>
		   </tr>
		   <tr>
			<td class="mini-products" id="taxTotal1" align="right">0.00</td>
		   </tr>
		</table>
	</td>
	<!-- column 6 - Product Total - ends -->


	<!-- column 7 - Net Price - starts -->
	<td valign="bottom" class="crmTableRow small lineOnTop" align="right"><span id="netPrice1"><b>&nbsp;</b></span></td>
	<!-- column 7 - Net Price - ends -->

   </tr>
   <!-- Product Details First row - Ends -->
</table>
<!-- Upto this has been added for form the first row. Based on these above we should form additional rows using script -->
*}

 <!-- Popup Discount DIV -->
		<!--div class="discountUI" id="discount_div_final" style="display:none;">
			<input type="hidden" id="discount_type_final" name="discount_type_final" value="">
			<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table">
			   <tr>
				<td id="discount_div_title_final" nowrap align="left" ></td>
				<td align="right"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0" onClick="fnHidePopDiv('discount_div_final')" style="cursor:pointer;"></td>
			   </tr>
			   <tr>
				<td align="left"><input type="radio" name="discount_final" checked onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp; {$APP.LBL_ZERO_DISCOUNT}</td>
				<td>&nbsp;</td>
			   </tr>
			   <tr>
				<td align="left"><input type="radio" name="discount_final" onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp; % {$APP.LBL_OF_PRICE}</td>
				<td align="right"><input type="text" class="form-control" size="6" id="discount_percentage_final" name="discount_percentage_final" value="0" style="visibility:hidden" onBlur="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp;%</td>
			   </tr>
			   <tr>
				<td align="left" nowrap><input type="radio" name="discount_final" onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp;{$APP.LBL_DIRECT_PRICE_REDUCTION}</td>
				<td align="right"><input type="text" id="discount_amount_final" class="form-control" name="discount_amount_final" size="6" value="0" style="visibility:hidden" onBlur="setDiscount(this,'_final'); calcGroupTax();calcTotal();"></td>
			   </tr>
			</table>
		</div-->
		<!-- End Div -->

		<!-- Pop Div For Group TAX -->
				<!--div class="discountUI" id="group_tax_div" style="display:none;">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
					   <tr>
						<td id="group_tax_div_title" colspan="2" nowrap align="left" ></td>
						<td align="right"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0" onClick="fnHidePopDiv('group_tax_div')" style="cursor:pointer;"></td>
					   </tr>

					{foreach item=tax_detail name=group_tax_loop key=loop_count from=$GROUP_TAXES}

					   <tr>
						<td align="left" class="lineOnTop">
							<div class="input-group">
								<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.group_tax_loop.iteration}" value="{$tax_detail.percentage}" onBlur="calcTotal()">
								<span class="input-group-addon">%</span>
							</div>
						</td>
						<td align="center" class="lineOnTop">{$tax_detail.taxlabel}</td>
						<td align="right" class="lineOnTop">
							<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_group_amount" id="group_tax_amount{$smarty.foreach.group_tax_loop.iteration}" style="cursor:pointer;" value="0.00" readonly>
						</td>
					   </tr>

					{/foreach}
					<input type="hidden" id="group_tax_count" value="{$smarty.foreach.group_tax_loop.iteration}">

					</table>

				</div-->
				<!-- End Popup Div Group Tax -->

				<!-- Pop Div For Shipping and Handlin TAX -->
				<!--div class="discountUI" id="shipping_handling_div" style="display:none;">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
					   <tr>
						<td id="sh_tax_div_title" colspan="2" nowrap align="left" ></td>
						<td align="right"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0" onClick="fnHidePopDiv('shipping_handling_div')" style="cursor:pointer;"></td>
					   </tr>

					{foreach item=tax_detail name=sh_loop key=loop_count from=$SH_TAXES}

					   <tr>
						<td align="left" class="lineOnTop">
							<div class="input-group">
								<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_sh_percent" id="sh_tax_percentage{$smarty.foreach.sh_loop.iteration}" value="{$tax_detail.percentage}" onBlur="calcSHTax()">
								<span class="input-group-addon">%</span>
							</div>
						</td>
						<td align="center" class="lineOnTop">{$tax_detail.taxlabel}</td>
						<td align="right" class="lineOnTop">
							<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_sh_amount" id="sh_tax_amount{$smarty.foreach.sh_loop.iteration}" style="cursor:pointer;" value="0.00" readonly>
						</td>
					   </tr>

					{/foreach}
					<input type="hidden" id="sh_tax_count" value="{$smarty.foreach.sh_loop.iteration}">

					</table>
				</div-->
				<!-- End Popup Div for Shipping and Handling TAX -->



<table class="table">
   <!-- Add Product Button -->

	<tr>
	<td colspan="3">
			<input type="button" name="Button" class="btn btn-primary btn-sm" value="{$APP.LBL_ADD_PRODUCT}" onclick="fnAddProductRow('{$MODULE}','{$IMAGE_PATH}');updatecssinputs();" />
			&nbsp;&nbsp;
			<input type="button" name="Button" class="btn btn-info btn-sm" value="{$APP.LBL_ADD_SERVICE}" onclick="fnAddServiceRow('{$MODULE}','{$IMAGE_PATH}');updatecssinputs();" />
	</td>
   </tr>




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


   <tr valign="top" style="display:none;">
	<!--td class="mini-total" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan=2>
		(+)&nbsp;<b>{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES} </b>
	</td>
	<td class="mini-total" align="right">
		<input id="shipping_handling_charge" onkeyup="validateDecimalGeneral('shipping_handling_charge')" name="shipping_handling_charge" type="text" class="form-control" align="right" value="0.00" onBlur="calcSHTax();">
	</td>
   </tr>

   <tr valign="top"  style="display:none;">
	<!--td class="mini-total" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="mini-total" align="right" colspan=2>
		(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'shipping_handling_div','sh_tax_div_title',''); calcSHTax();" >{$APP.LBL_TAX_FOR_SHIPPING_AND_HANDLING} </a></b>

		<!-- Pop Div For Shipping and Handlin TAX -->
		<div class="discountUI" id="shipping_handling_div" style="display:none">
			<div id="email-box" class="clearfix" nowrap>
				<div style="float:left;padding-left:10px;width:100%;">
					<div class="main-box infographic-box" style="float:right;width:360px;max-width:360px;height:147px;max-height:250px;margin-left:5px;padding:10px;overflow:auto;">
						<a onClick="fnHidePopDiv('shipping_handling_div')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
						<span class="headline" id="sh_tax_div_title" style="font-size:0.9em; text-align: left;"></span>
						<div class="form-group">
							<table width="100%" cellpadding="0" cellspacing="0" class="table">
								<tbody>
									{foreach item=tax_detail name=sh_loop key=loop_count from=$SH_TAXES}
										<tr>
											<td align="left" style="padding: 1; font-size:0.9em; text-align: left;">
												<div class="form-group">
													<input type="text" onkeyup="validateDecimalGeneral('sh_tax_percentage{$smarty.foreach.sh_loop.iteration}')" class="form-control" size="6" name="{$tax_detail.taxname}_sh_percent" id="sh_tax_percentage{$smarty.foreach.sh_loop.iteration}" value="{$tax_detail.percentage}" onBlur="calcSHTax()">
													<span class="input-group-addon">%</span>
												</div>
											</td>
											<td align="center" style="padding: 1;">{$tax_detail.taxlabel}</td>
											<td align="right" style="padding: 1;">
												<input type="text" class="form-control" size="6" name="{$tax_detail.taxname}_sh_amount" id="sh_tax_amount{$smarty.foreach.sh_loop.iteration}" style="cursor:pointer;" value="0.00" readonly>
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
	<td id="shipping_handling_tax"  align="right">0.00</td>
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
		<input id="adjustment" onkeyup="validateDecimalGeneral('adjustment')" name="adjustment" type="text" class="form-control" align="right" value="0.00" onBlur="calcTotal();">
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



