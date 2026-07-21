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
<script language="JavaScript" type="text/javascript" src="include/js/Inventory.js"></script>

{literal}
<style>
	.tax_delete{
		text-decoration:none;
	}
	
	.tax_delete td{			
	}
</style>
{/literal}






<div class="col-lg-12">				
	<div class="row">
		<div class="col-lg-12">
			<h1><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
		</div>
	</div>
</div>

<div class="col-lg-12">
							
	<div class="row">
		<div class="main-box no-header clearfix" style="">
			<div class="col-lg-8 pull-left">
				<h2> {$MOD.LBL_TAX_DESC} </h2>
				<br>
			</div>
		</div>
	</div>

</div>






	<div class="row">
		
		<!-- impuestos 1 -->
		<form name="{$formname}" method="POST" action="index.php" onsubmit="VtigerJS_DialogBox.block();">
			
			<input type="hidden" name="module" value="impuesto">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="parenttab" value="Contabilidad">
			<input type="hidden" name="save_tax" value="">
			<input type="hidden" name="edit_tax" value="">
			<input type="hidden" name="add_tax_type" value="">

		<div class="col-md-6">
			<div class="main-box clearfix" style="">
				<header class="main-box-header clearfix">
					<div class="row">
						<div class="col-lg-6 pull-left">
							<h2> {$MOD.LBL_PRODUCT_TAX_SETTINGS} </h2><!-- titulo -->
							<br>
						</div>
						<!-- botones -->
						<div class="col-lg-6 pull-right text-right">
							{if $EDIT_MODE neq 'true'}
								<input title="{$MOD.LBL_ADD_TAX_BUTTON}" accessKey="{$MOD.LBL_ADD_TAX_BUTTON}" onclick="fnAddTaxConfigRow('');" type="button" name="button" value="{$MOD.LBL_ADD_TAX_BUTTON}" class="btn btn-primary btn-sm">
							{/if}
							{if $EDIT_MODE eq 'true'}	
								<input class="btn btn-primary btn-sm" title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"  onclick="this.form.action.value='index'; this.form.save_tax.value='true'; this.form.parenttab.value='Contabilidad'; return validateTaxes('tax_count');" type="submit" name="button2" value=" {$APP.LBL_SAVE_BUTTON_LABEL}  ">&nbsp;
								<input class="btn btn-warning btn-sm" title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" onclick="this.form.action.value='index'; this.form.module.value='impuesto'; this.form.save_tax.value='false'; this.form.parenttab.value='Contabilidad';" type="submit" name="button22" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
							{elseif $TAX_COUNT > 0}
								<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" onclick="this.form.action.value='TaxConfig'; this.form.add_tax_type.value=''; this.form.edit_tax.value='true'; this.form.parenttab.value='Contabilidad';" type="submit" name="button" value="  {$APP.LBL_EDIT_BUTTON_LABEL}  " class="btn btn-primary btn-sm">
							{/if}
						</div>
					</div>
				</header>

				
				<div class="main-box-body clearfix">
					<div class="table-responsive">

						<div id="">

							{if $TAX_COUNT eq 0}
								<h2> {$MOD.LBL_NO_TAXES_AVAILABLE}. {$MOD.LBL_PLEASE} {$MOD.LBL_ADD_TAX_BUTTON} </h2>
							{else}

								<table width="100%" cellpadding="5" cellspacing="0" id="add_tax" class="table table-striped table-hover" >
									<thead>
									
									</thead>
									<tbody>
									<tr>
										<td id="td_add_tax" class="small" colspan="3" align="right" nowrap></td>
									</tr>
									{foreach item=tax key=count from=$TAX_VALUES}
									<tr>	
										<!--assinging tax label name for javascript validation-->
										{assign var=tax_label value="taxlabel_"|cat:$tax.taxname} 
						        	
										<td width=35% class="" >
											{if $EDIT_MODE eq 'true'}
												{assign var = pstax value = $tax.taxlabel}
												<input name="{$pstax|bin2hex}" id={$tax_label} type="text" value="{$tax.taxlabel}" class="form-control">
											{else}
												{$tax.taxlabel}
											{/if}
										</td>
										<td width=55% class="">
											{if $EDIT_MODE eq 'true'}
												<div class="input-group">
													<input class="form-control" id="{$tax.taxname}" name="{$tax.taxname}" type="text" value="{$tax.percentage}">
													<span class="input-group-addon"> %</span>
												</div>
												<!-- <input name="{$tax.taxname}" id="{$tax.taxname}" type="text" value="{$tax.percentage}" class="form-control">&nbsp;% -->
											{else}
												{$tax.percentage}&nbsp;%
											{/if}
										</td>
										<td width=10% class="">
											{if $tax.deleted eq 0}
												<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&disable=true&taxname={$tax.taxname}"><img src="{'enabled.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.LBL_ENABLE}" title="{$MOD.LBL_ENABLE}"></a>
											{else}
												<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&enable=true&taxname={$tax.taxname}"><img src="{'disabled.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.LBL_ENABLE}" title="{$MOD.LBL_DISABLE}"></a>
											{/if}
										</td>
									   </tr>
									{/foreach}
								    </tbody>
								</table>
								{if $EDIT_MODE eq 'true'}
									<input type="hidden" id="tax_count" value="{$count}">
								{/if}
							{/if}

						</div>
					    
					</div>
				</div>
			</div>
		</div>
		</form>
		<!-- fin impuestos 1 -->




		<!-- impuestos 2 -->
		<form name="{$shformname}" method="POST" action="index.php">

			<form name="{$shformname}" method="POST" action="index.php">
			<input type="hidden" name="module" value="impuesto">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="parenttab" value="Contabilidad">
			<input type="hidden" name="sh_save_tax" value="">
			<input type="hidden" name="sh_edit_tax" value="">
			<input type="hidden" name="sh_add_tax_type" value="">

		<div class="col-md-6">
			<div class="main-box clearfix" style="">
				<header class="main-box-header clearfix">
					<div class="row">
						<div class="col-lg-6 pull-left">
							<h2> {$MOD.LBL_SHIPPING_HANDLING_TAX_SETTINGS} </h2><!-- titulo -->
							<br>
						</div>
						<!-- botones -->
						<div class="col-lg-6 pull-right text-right">
							{if $SH_EDIT_MODE neq 'true'}
								<input title="{$MOD.LBL_ADD_TAX_BUTTON}" accessKey="{$MOD.LBL_ADD_TAX_BUTTON}" onclick="fnAddTaxConfigRow('sh');" type="button" name="button" value="  {$MOD.LBL_ADD_TAX_BUTTON}  " class="btn btn-primary btn-sm">
							{/if}
							{if $SH_EDIT_MODE eq 'true'}
								<input class="btn btn-primary btn-sm" title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"  onclick="this.form.action.value='index'; this.form.sh_save_tax.value='true'; this.form.parenttab.value='Contabilidad'; return validateTaxes('sh_tax_count');" type="submit" name="button2" value=" {$APP.LBL_SAVE_BUTTON_LABEL}  ">
								&nbsp;
								<input class="btn btn-warning btn-sm" title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" onclick="this.form.action.value='index'; this.form.module.value='impuesto'; this.form.sh_save_tax.value='false'; this.form.parenttab.value='Contabilidad';" type="submit" name="button22" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
							{elseif $SH_TAX_COUNT > 0}
								<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" onclick="this.form.action.value='TaxConfig'; this.form.sh_add_tax_type.value=''; this.form.sh_edit_tax.value='true'; this.form.parenttab.value='Contabilidad';" type="submit" name="button" value="  {$APP.LBL_EDIT_BUTTON_LABEL}  " class="btn btn-primary btn-sm">
							{/if}
						</div>
					</div>
				</header>

				
				<div class="main-box-body clearfix">
						<div class="table-responsive">
							{if $SH_TAX_COUNT eq 0}
								<h2>{$MOD.LBL_NO_TAXES_AVAILABLE}. {$MOD.LBL_PLEASE} {$MOD.LBL_ADD_TAX_BUTTON}</h2>
						   {else}

								<div id="">
									<table id="sh_add_tax" width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover" >
										<thead>
										
										</thead>
										<tbody>
										<tr>
											<td id="td_sh_add_tax" class="small" colspan="3" align="right" nowrap></td>
										</tr>
										{foreach item=tax key=count from=$SH_TAX_VALUES}
										<tr>
											{assign var=tax_label value="taxlabel_"|cat:$tax.taxname} 
											<td width=35% class="">
											 	{if $SH_EDIT_MODE eq 'true'}
								                    {assign var = shtax value = $tax.taxlabel}
													<input name="{$shtax|bin2hex}" id="{$tax_label}" type="text" value="{$tax.taxlabel}" class="form-control">
											 	{else} 
													{$tax.taxlabel}
												{/if}
											</td>
											<td width=55% class="">
												{if $SH_EDIT_MODE eq 'true'}

													<div class="input-group">
														<input class="form-control" id="{$tax.taxname}" name="{$tax.taxname}" type="text" value="{$tax.percentage}">
														<span class="input-group-addon"> %</span>
													</div>
													<!-- <input name="{$tax.taxname}" id="{$tax.taxname}" type="text" value="{$tax.percentage}" class="detailedViewTextBox small">
													&nbsp;%  -->
												{else} 
													{$tax.percentage}&nbsp;% 
												{/if}
											</td>
											<td width=10% class=""> 
												{if $tax.deleted eq 0}
														<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&sh_disable=true&sh_taxname={$tax.taxname}"><img src="{'enabled.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.LBL_ENABLE}" title="{$MOD.LBL_ENABLE}"></a>
													{else}
														<a href="index.php?module=Settings&action=TaxConfig&parenttab=Settings&sh_enable=true&sh_taxname={$tax.taxname}"><img src="{'disabled.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" alt="{$MOD.LBL_DISABLE}" title="{$MOD.LBL_DISABLE}"></a>
													{/if}
											</td>
										   </tr>
										   {/foreach}
									    </tbody>
									</table>

								</div>

								{if $SH_EDIT_MODE eq 'true'}
									<input type="hidden" id="sh_tax_count" value="{$count}">
							   {/if}
							{/if}
											    
						</div>
					</div>
			</div>
		</div>
		</form>
		<!-- Fin impuestos 2 -->







	</div>
		




















<script>
	var tax_labelarr = {ldelim}SAVE_BUTTON:'{$APP.LBL_SAVE_BUTTON_LABEL}',
                                CANCEL_BUTTON:'{$APP.LBL_CANCEL_BUTTON_LABEL}',
                                TAX_NAME:'{$APP.LBL_TAX_NAME}',
                                TAX_VALUE:'{$APP.LBL_TAX_VALUE}'{rdelim};
</script>
