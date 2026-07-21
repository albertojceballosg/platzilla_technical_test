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
	
	<th class="text-center" align="center" colspan="2">
		
	</th>
	
	<th class="text-center" align="center" colspan="3">
		
	</th>
   </tr>


   <!-- Header for the Product Details -->
   <tr valign="top">
	
	<th width=40% class="text-center"><font color='red'>*</font><b>{$APP.LBL_ITEM_NAME}</b></th>
	
	<th width=10% class="text-center"><i title="{$APP.LBL_QTY}" class="fa fa-fw fa-plus-circle"></i> {$APP.LBL_QTY}</th>
	<th width=10% class="text-center" align="right"><i >Cantidad a Devolver</i></th>
	
	
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
	
	<!-- column 3 - Quantity in Stock - ends -->


	<!-- column 4 - Quantity - starts -->
	<td class="crmTableRow small lineOnTop">
		<input id="qty1" name="qty1" type="text" class="form-control" onBlur="settotalnoofrows();calcTotal(); loadTaxes_Ajax(1); setDiscount(this,'1'); calcTotal();{if $MODULE eq 'Invoice'}stock_alert(1);{/if}" value=""/><br><span id="stock_alert1"></span>
	</td>
	<!-- column 4 - Quantity - ends -->
	<td class="crmTableRow small lineOnTop">
		<input id="Cdevuelta{$PRODUCT_ID}" name="Cdevuelta{$PRODUCT_ID}" type="text" class="form-control" /><br>
	</td>

	<!-- column 5 - List Price with Discount, Total After Discount and Tax as table - starts -->
	

   </tr>
   <!-- Product Details First row - Ends -->

   *}




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
	{assign var="Cdevuelta" value="Cdevuelta"|cat:$row_no}

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


   <tr id="row{$row_no}" valign="top">

	<!-- column 1 - delete link - starts -->
	

	<!-- column 2 - Product Name - starts -->
	<td class="text-center">
		<!-- Product Re-Ordering Feature Code Addition Starts -->
		<input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
		<!-- Product Re-Ordering Feature Code Addition ends -->
		<table width="100%"  border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td class="mini-products">
					<div class="input-group">
						<input type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="form-control" readonly />
						
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
			<tr>
				<td class="mini-products" id="setComment">
					<textarea id="{$comment}" name="{$comment}" class="form-control">{$data.$comment}</textarea>
				</td>
			</tr>
		</table>
	</td>
	<!-- column 2 - Product Name - ends -->

	<!-- column 3 - Quantity in Stock - starts -->
	
	<!-- column 3 - Quantity in Stock - ends -->


	<!-- column 4 - Quantity - starts -->
	<td class="text-center" style="vertical-align:top;"> 
		<input id="{$qty}" name="{$qty}" type="text" class="form-control " onfocus="this.className='detailedViewTextBoxOn'" onBlur="settotalnoofrows(); calcTotal(); loadTaxes_Ajax('{$row_no}');{if $MODULE eq 'myinvoice' && $entityType neq 'Services'} stock_alert('{$row_no}');{/if}" onChange="setDiscount(this,'{$row_no}')" value="{$data.$qty}"/><br><span id="stock_alert{$row_no}"></span>
	</td>
	<!-- column 4 - Quantity - ends -->
	<td class="text-center" style="vertical-align:top;"> 
		<input id="{$Cdevuelta}{$data.$hdnProductId}" name="{$Cdevuelta}{$data.$hdnProductId}" type="text" class="form-control " /><br>
	</td>
	<!-- column 5 - List Price with Discount, Total After Discount and Tax as table - starts -->
	
    <input type="hidden" name="ASSOCIATEDPRODUCT" value="{$ASSOCIATEDPRODUCT}" />

   </tr>
   <!-- Product Details First row - Ends -->
   {/foreach}


















</table>
<!-- Upto this has been added for form the first row. Based on these above we should form additional rows using script -->










<table class="table">
   <!-- Add Product Button -->
   




   <!-- Product Details Final Total Discount, Tax and Shipping&Hanling  - Starts -->
   

   <tr valign="top" style="display:none;">
	<!--td class="crmTableRow small lineOnTop" width="60%" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="crmTableRow small lineOnTop" align="right" colspan="2">
		(-)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'discount_div_final','discount_final','1')">{$APP.LBL_DISCOUNT}</a>
		<!-- Popup Discount DIV -->
		<div class="discountUI" id="discount_div_final">
			<input type="hidden" id="discount_type_final" name="discount_type_final" value="">
			<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
			   <tr>
				<td id="discount_div_title_final" nowrap align="left" ></td>
				<td align="right"><img src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0" onClick="fnHidePopDiv('discount_div_final')" style="cursor:pointer;"></td>
			   </tr>
			   <tr>
				<td align="left" class="lineOnTop"><input type="radio" name="discount_final" checked onclick="setDiscount(this,'_final'); calcGroupTax();calcTotal();">&nbsp; {$APP.LBL_ZERO_DISCOUNT}</td>
				<td class="lineOnTop">&nbsp;</td>
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
		</div>
		<!-- End Div -->
	</td>
	
   </tr>


   <!-- Group Tax - starts -->
   <tr id="group_tax_row" valign="top" class="TaxHide" style="display:none;">
	<!--td class="crmTableRow small lineOnTop" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="crmTableRow small lineOnTop" align="right" colspan=2>
		(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'group_tax_div','group_tax_div_title',''); calcGroupTax();" >{$APP.LBL_TAX}</a></b>
				<!-- Pop Div For Group TAX -->
				<div class="discountUI" id="group_tax_div">
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

				</div>
				<!-- End Popup Div Group Tax -->

	</td>
	<td id="tax_final" class="crmTableRow small lineOnTop" align="right">0.00</td>
   </tr>
   <!-- Group Tax - ends -->


   <tr valign="top" style="display:none;">
	<!--td class="crmTableRow small" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="crmTableRow small" align="right" colspan=2>
		(+)&nbsp;<b>{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES} </b>
	</td>
	<td class="crmTableRow small" align="right">
		<input id="shipping_handling_charge" name="shipping_handling_charge" type="text" class="form-control" align="right" value="0.00" onBlur="calcSHTax();">
	</td>
   </tr>

   <tr valign="top" style="display:none;">
	<!--td class="crmTableRow small" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="crmTableRow small" align="right" colspan=2>
		(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'shipping_handling_div','sh_tax_div_title',''); calcSHTax();" >{$APP.LBL_TAX_FOR_SHIPPING_AND_HANDLING} </a></b>

				<!-- Pop Div For Shipping and Handlin TAX -->
				<div class="discountUI" id="shipping_handling_div">
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
				</div>
				<!-- End Popup Div for Shipping and Handling TAX -->

	</td>
	<td id="shipping_handling_tax" class="crmTableRow small" align="right">0.00</td>
   </tr>
   <tr valign="top" style="display:none;">
	<!--td class="crmTableRow small" style="border-right:1px #dadada;">&nbsp;</td-->
	<td class="text-right" align="right" colspan=2>
		<label>{$APP.LBL_ADJUSTMENT}</label>
		<select id="adjustmentType" name="adjustmentType" class="form-control" onchange="calcTotal();">
			<option value="+">{$APP.LBL_ADD_ITEM}</option>
			<option value="-">{$APP.LBL_DEDUCT}</option>
		</select>
	</td>
	<td class="text-right" align="right" style="vertical-align: bottom;">
		<input id="adjustment" name="adjustment" type="text" class="form-control" align="right" value="0.00" onBlur="calcTotal();">
	</td>
   </tr>
   <tr valign="top">
	<td class="" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="text-right" align="right"><b></b></td>
	<td id="grandTotal" name="grandTotal" class="text-right" align="right">&nbsp;</td>
   </tr>
</table>

</div>
		<input type="hidden" name="totalProductCount" id="totalProductCount" value="">
		<input type="hidden" name="subtotal" id="subtotal" value="">
		<input type="hidden" name="total" id="total" value="">




	</td>
   </tr>




