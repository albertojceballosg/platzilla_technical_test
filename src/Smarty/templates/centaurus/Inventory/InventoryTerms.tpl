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
<!-- [TT11204] - 06/07/16 - Johana Romero - Vista para modificar los terminos y condiciones -->
<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>


<form action="index.php" method="post" name="tandc" onsubmit="VtigerJS_DialogBox.block();">
<input type="hidden" name="module" value="myinvoice">
<input type="hidden" name="action">
<input type="hidden" name="inv_terms_mode">
<!--<input type="hidden" name="parenttab" value="Settings">-->
<div class="col-lg-12">				
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
			
			
			<li><a href="index.php?module=myinvoice&action=index">{$MOD.SINGLE_myinvoice}</a></li>
		
			 <li>{$MOD.LBL_TERMS_INFORMATION}</li>
			 
			
		</ol>
			
		</div>
	</div>
</div>

<div class="col-lg-12">
							
	<div class="row">
		<div class="main-box no-header clearfix" style="">
				
			
			<div class="col-lg-6 pull-left">



				<h2 style="margin-bottom: 20px;">{$MOD.LBL_INVEN_TANDC_DESC}</h2>
				
			
			
			</div>
		    
		    <div class="col-lg-3 pull-right">
		    
		    {if $INV_TERMS_MODE eq 'view'}
						
							<input class="btn btn-primary btn-sm pull-right" title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" onclick="this.form.action.value='OrganizationTermsandConditions';this.form.inv_terms_mode.value='edit'" type="submit" name="Edit" value="{$APP.LBL_EDIT_BUTTON_LABEL}" style="margin-bottom: 20px;margin-left:10px;">

						{else}
							 
							<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary btn-sm pull-right" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="this.form.action.value='savetermsandconditions';" style="margin-bottom: 20px;margin-left:10px;">
							<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-cancel btn-sm pull-right" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" style="margin-bottom: 20px;margin-left:10px;">
				
						{/if}
				
			</div>
		    
		    
			
		
	
	
		</div>
	
	</div>

</div>



<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
				
				{if $INV_TERMS_MODE eq 'view'}
						<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-responsive">
						<tr>
						<td  valign=top style="padding:20px">
							{$INV_TERMSANDCONDITIONS} </td> 
					  </tr>
					</table>
						{else}
							<table border=0 cellspacing=0 cellpadding=5 width=100% class="table">
							<tr>
								<th valign=top>Escriba el texto a continuación y haga clic en el botón Guardar </th>
					  		</tr>
							<tr>
								<td  valign=top>
								<textarea class="form-control" name="inventory_tandc" style="width:95%; height:200px;text-align:left;">{$INV_TERMSANDCONDITIONS}</textarea>

								</td>
							</tr>
							</table>	
						{/if}
			</div>
		</div>
	</div>
</div>

</form>


