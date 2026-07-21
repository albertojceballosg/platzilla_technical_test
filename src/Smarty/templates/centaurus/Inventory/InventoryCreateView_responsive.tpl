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

<div class="box">
	{include file='EditViewHidden.tpl'}
	<div class="box-header">
		<h3 class="box-title">
			<small>
				{if $OP_MODE eq 'edit_view'}
					 <span class="lvtHeaderText"><font color="purple">[ {$ID} ] </font>{$NAME} -  {$APP.LBL_EDITING} {$SINGLE_MOD|@getTranslatedString:$MODULE} {$APP.LBL_INFORMATION}</span> <br>
						{$UPDATEINFO}
					{/if}
					{if $OP_MODE eq 'create_view'}
					{if $DUPLICATE neq 'true'}
					<span class="lvtHeaderText">{$APP.LBL_CREATING} {$APP.LBL_NEW} {$SINGLE_MOD|@getTranslatedString:$MODULE}</span> <br>
					{else}
					<span class="lvtHeaderText">{$APP.LBL_DUPLICATING} "{$NAME}" </span> <br>
					{/if}
				 {/if}
			</small>
		</h3>
		<div class="box-tools pull-right">
			<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="this.form.action.value='Save';  return validateInventory('{$MODULE}')" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
			<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
		</div>
	</div>
	<div class="box-body table-responsive no-padding">
		<!-- included to handle the edit fields based on ui types -->
			<div class="box box-solid">
				<div class="box-header">
					<h3 class="box-title">{$header}</h3>
				</div><!-- /.box-header -->
				<div class="box-body table-responsive no-padding" id="tbl{$header|replace:' ':''}">
					<table class="table table-bordered">
					{foreach key=header item=data from=$BLOCKS}
						{include file="DisplayFields.tpl"}
					{/foreach}
					</table>
				</div>
				<div class="box-body table-responsive no-padding">
					<table class="table table-bordered">
					   {if $MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'quote' || $MODULE eq 'myinvoice'}
						<!-- Added to display the product details -->
						<!-- This if is added when we want to populate product details from the related entity  for ex. populate product details in new SO page when select Quote -->
						{if $AVAILABLE_PRODUCT eq true}
							{include file="Inventory/ProductDetailsEditView.tpl"}
						{else}
							{include file="Inventory/ProductDetails_responsive.tpl"}
					    {/if}

					   {/if}
					</table>
				</div>
			</div>
			
		<div class="box-header">
			<div class="box-tools pull-right">
				<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="this.form.action.value='Save'; return validateInventory('{$MODULE}')" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " style="width:70px" >
				<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " style="width:70px">
				<input type="hidden" name="convert_from" value="{$CONVERT_MODE}">
				<input type="hidden" name="duplicate_from" value="{$DUPLICATE_FROM}">
			</div>
		</div>
	</div><!-- /.box-body -->
	</form>

</div><!-- /.box-->