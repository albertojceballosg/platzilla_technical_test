{extends file="base/EditView.tpl"}

{block name="css" append}{/block}

{block name="js" append}
<script type="text/javascript" src="modules/myinvoice/Inventory.js"></script>
<script type="text/javascript" src="modules/Services/Services.js"></script>
<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
{* <!-- [ TT11173 ] Fallos-Ajustes 2 Facturas
	DM 15/06/2016
-->*}
<script type="text/javascript" src="modules/product/product.js"></script>

{/block}

<!-- Se incluyo desde /modules/myinvoice/EditView.php AV 2017/03/15-->
{block name="scripts" append}
	<script language="javascript">

        jQuery(document).ready(function(event){
            jQuery("#jscal_field_invoicedate").keydown(function() {
                return false;
            });
            jQuery("#invoicestatus").attr('readonly', true);
            jQuery("#invoicestatus").find('option:not(:selected)' ).attr('disabled',true);

            jQuery("#invoicestatus").click(function() {
                return false;
            });

        });

	</script>
{/block}

{block name="scripts" append}

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

{/block}

{block name="content"}

	{include file='EditViewHidden.tpl'}

	{foreach key=header item=data from=$BLOCKS}
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2>{$header}</h2>
					</header>
					<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
						{include file="DisplayFields.tpl"}
					</div>
				</div>
			</div>
		</div>
	{/foreach}

	{if $MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'quote' || $MODULE eq 'myinvoice'}
		<!-- [TT11204] - 07/07/16 - Johana Romero - Contenido terminos y condiciones -->
		{if $MODULE eq 'myinvoice'}
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box">
						<header class="title-section main-box-header clearfix" style="padding-bottom:15px !important">
							<h2>{$MOD.LBL_TERMS_INFORMATION}</h2>
						</header>
						<div class="main-box-body clearfix">
							<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-responsive">
								<tr>
									<td  valign=top style="padding:20px; padding-top:0;">
										{$INV_TERMSANDCONDITIONS}
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		{/if}
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2>Productos</h2>
					</header>
					<div class="main-box-body clearfix">
						<table class="table table-bordered">
							{include file="Inventory/ProductDetailsEditView.tpl"}
						</table>
					</div>
				</div>
			</div>
		</div>
	{/if}
	{block name="content-after-blocks"}{/block}
	<div class="clearfix" style="height: 25px; margin-bottom: 16px;"></div>
	<div class="row">
		<div id="fixed-btns-bar" style="display:block">
			<div class="container">
				<div class="row">
					<div class="col-xs-12" style="padding: 25px; height: 75px;">
	{block name="buttons-bar"}{/block}
					</div>
				</div>
			</div>
		</div>
	</div>
	<input name='search_url' id="search_url" type='hidden' value='{$SEARCH}'>
{/block}

{block name="buttons-bar"}

	<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success" onclick="this.form.action.value='Save'; return validateInventory('{$MODULE}')" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}">
	<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-default" onclick="window.history.back()" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}">
	<input type="hidden" name="convert_from" value="{$CONVERT_MODE}">
	<input type="hidden" name="duplicate_from" value="{$DUPLICATE_FROM}">

{/block}

{block name="scripts" append}
<!-- This div is added to get the left and top values to show the tax details-->
<div id="tax_container" style="display:none; position:absolute; z-index:1;"></div>

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

var ProductImages=new Array();
	var count=0;
	function delRowEmt(imagename)
	{ldelim}
	var borrar = jQuery("#del_file_list").val();
	borrar = borrar+"###"+imagename;
	jQuery("#del_file_list").val(borrar);
	multi_selector.count--;
	{rdelim}
	function displaydeleted()
	{ldelim}
		if(ProductImages.length > 0)
			document.EditView.del_file_list.value=ProductImages.join('###');
	{rdelim}


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

{/block}

{block name="js" append}
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>
<!-- END -->
{/block}