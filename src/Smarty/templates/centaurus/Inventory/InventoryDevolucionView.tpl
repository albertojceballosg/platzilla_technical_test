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

{*<!-- module header -->*}

<!-- libraries -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
<!-- this page specific styles -->
<link rel="stylesheet" href="themes/{$THEME}/css/libs/datepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/select2.css" type="text/css" />

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script src="themes/{$THEME}/js/moment.min.js"></script>
<script src="themes/{$THEME}/js/daterangepicker.js"></script>
<script src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script src="themes/{$THEME}/js/select2.min.js"></script>
<script src="themes/{$THEME}/js/hogan.js"></script>
<script src="themes/{$THEME}/js/typeahead.min.js"></script>

<script type="text/javascript" src="modules/myinvoice/Inventory.js"></script>
<script type="text/javascript" src="modules/Services/Services.js"></script>
<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
{if $PICKIST_DEPENDENCY_DATASOURCE neq ''}
<script type="text/javascript">
	jQuery(document).ready(function() {ldelim} (new FieldDependencies({$PICKIST_DEPENDENCY_DATASOURCE})).init() {rdelim});
</script>
{/if}

<script type="text/javascript">

function sensex_info()
{ldelim}
        var Ticker = $('tickersymbol').value;
        if(Ticker!='')
        {ldelim}
                $("vtbusy_info").style.display="inline";
                new Ajax.Request(
                      'index.php',
                      {ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                                method: 'post',
                                postBody: 'module={$MODULE}&action=Tickerdetail&tickersymbol='+Ticker,
                                onComplete: function(response) {ldelim}
                                        $('autocom').innerHTML = response.responseText;
                                        $('autocom').style.display="block";
                                        $("vtbusy_info").style.display="none";
                                {rdelim}
                        {rdelim}
                );
        {rdelim}
{rdelim}

</script>

{include file='Buttons_List.tpl'}

{include file='EditViewHidden1.tpl'}

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2 class="col-lg-10 pull-left">
					{if $OP_MODE eq 'edit_view'}
						 <font color="purple">[ {$ID} ] </font>{$NAME} -  {$APP.LBL_EDITING} {$SINGLE_MOD|@getTranslatedString:$MODULE} {$APP.LBL_INFORMATION}
							{$UPDATEINFO}
						{/if}
						{if $OP_MODE eq 'create_view'}
						{if $DUPLICATE neq 'true'}
						{$APP.LBL_CREATING} {$APP.LBL_NEW} {$SINGLE_MOD|@getTranslatedString:$MODULE}
						{else}
						{$APP.LBL_DUPLICATING} "{$NAME}"
						{/if}
					{/if}
				</h2>
				<div class="icon-box pull-right">
					<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="this.form.action.value='Save';  return validateInventory('{$MODULE}')" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
					<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
				</div>
			</header>
		</div>
	</div>
</div>

{if $MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'quote' || $MODULE eq 'Invoice' || $MODULE eq 'myinvoice'}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2>Productos</h2>
				</header>
				<div class="main-box-body clearfix">
					<table class="table table-bordered">
						{include file="Inventory/ProductDetailsDevolucionView.tpl"}
					</table>
				</div>
			</div>
		</div>
	</div>
{/if}
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<div class="icon-box pull-right">
					<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="this.form.action.value='Save'; return validateInventory('{$MODULE}')" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " style="width:70px" >
					<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " style="width:70px">
					<input type="hidden" name="convert_from" value="{$CONVERT_MODE}">
					<input type="hidden" name="duplicate_from" value="{$DUPLICATE_FROM}">
				</div>
			</header>
		</div>
	</div>
</div>
</form>


<!-- This div is added to get the left and top values to show the tax details-->
<div id="tax_container" style="display:none; position:absolute; z-index:1px;"></div>

<script>



        var fieldname = new Array({$VALIDATION_DATA_FIELDNAME})

        var fieldlabel = new Array({$VALIDATION_DATA_FIELDLABEL})

        var fielddatatype = new Array({$VALIDATION_DATA_FIELDDATATYPE})

	var product_labelarr = {ldelim}CLEAR_COMMENT:'{$APP.LBL_CLEAR_COMMENT}',
				DISCOUNT:'{$APP.LBL_DISCOUNT}',
				TOTAL_AFTER_DISCOUNT:'{$APP.LBL_TOTAL_AFTER_DISCOUNT}',
				TAX:'{$APP.LBL_TAX}',
				ZERO_DISCOUNT:'{$APP.LBL_ZERO_DISCOUNT}',
				PERCENT_OF_PRICE:'{$APP.LBL_OF_PRICE}',
				DIRECT_PRICE_REDUCTION:'{$APP.LBL_DIRECT_PRICE_REDUCTION}'{rdelim};

</script>

<!-- vtlib customization: Help information assocaited with the fields -->
{if $FIELDHELPINFO}
<script type='text/javascript'>
{literal}var fieldhelpinfo = {}; {/literal}
{foreach item=FIELDHELPVAL key=FIELDHELPKEY from=$FIELDHELPINFO}
	fieldhelpinfo["{$FIELDHELPKEY}"] = "{$FIELDHELPVAL}";
{/foreach}
</script>
{/if}
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>
<!-- END -->