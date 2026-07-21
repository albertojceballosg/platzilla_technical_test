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


{if $MODULE eq 'amef_info'|| $MODULE eq 'product' || $MODULE eq 'Accounts' || $MODULE eq 'Contacts' || $MODULE eq 'Services'}
<script language="JavaScript" type="text/javascript">
	function WindowCrearNuevo()
	{ldelim}
		window.name='ventanaPadre';						//  en el URL se le agrega "action2=Popup"
		window.open('index.php?module={$MODULE}&action=EditView&action2=Popup&return_action=ClosePopup&return_module={$MODULE}&return_id=&parent_id=','popup2do', 'width=980,height=560,left=0,top=0,scrollbars=1,resizable=0');
	{rdelim}
</script>
<div style="height:20px">
	<input id="btn_add_objeto" alt="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|@getTranslatedString:$MODULE}" title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|@getTranslatedString:$MODULE}"  class="btn btn-primary" value="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|@getTranslatedString:$MODULE} {$ADD_TITLE}" LANGUAGE=javascript onClick="WindowCrearNuevo();" type="button"  name="button" class="crmbutton small" style="float:right;margin:15px">
</div>
{/if}


<!-- BEGIN: main -->
<form name="selectall" method="POST">

		<table role="grid" id="table-example" class="table table-hover dataTable no-footer">
			<tr>
				<td>
					<div class="col-md-12" style="border: 0px solid #ff00c3">
						{if $SELECT eq 'enable' && ($POPUPTYPE neq 'inventory_prod' && $POPUPTYPE neq 'inventory_prod_po' && $POPUPTYPE neq 'inventory_service')}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.LBL_SELECT_BUTTON_LABEL} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(SelectAll('{$MODULE}','{$RETURN_MODULE}')) window.close();"/>
							{elseif $SELECT eq 'enable' && ($POPUPTYPE eq 'inventory_prod' || $POPUPTYPE eq 'inventory_prod_po')}
								{if $RECORD_ID}
									<input class="btn btn-primary btn-sm" type="button" value="{$APP.LBL_BACK}" onclick="window.history.back();"/>
								{/if}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.LBL_SELECT_BUTTON_LABEL} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(InventorySelectAll('{$RETURN_MODULE}',image_pth))window.close();"/>
							{elseif $SELECT eq 'enable' && $POPUPTYPE eq 'inventory_service'}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.LBL_SELECT_BUTTON_LABEL} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(InventorySelectAllServices('{$RETURN_MODULE}',image_pth))window.close();"/>
							{else}		
							{/if}


							{if $PATRON eq 'enable' && ($POPUPTYPE neq 'inventory_prod' && $POPUPTYPE neq 'inventory_prod_po' && $POPUPTYPE neq 'inventory_service')}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.ASC_PATRON} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(SelectAllPatron('{$MODULE}','{$RETURN_MODULE}')) window.close();"/>
							{elseif $PATRON eq 'enable' && ($POPUPTYPE eq 'inventory_prod' || $POPUPTYPE eq 'inventory_prod_po')}
								{if $RECORD_ID}
									<input class="btn btn-primary btn-sm" type="button" value="{$APP.LBL_BACK}" onclick="window.history.back();"/>
								{/if}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.ASC_PATRON} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(InventorySelectAll('{$RETURN_MODULE}',image_pth))window.close();"/>
							{elseif $PATRON eq 'enable' && $POPUPTYPE eq 'inventory_service'}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.ASC_PATRON} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(InventorySelectAllServices('{$RETURN_MODULE}',image_pth))window.close();"/>
							{else}		
							{/if}
			<!-- pedido [TT11231] -->
								<!--{if $PATRON eq 'enable'}
								<input class="btn btn-primary btn-sm" type="button" value="{$APP.ASC_PATRON} {$MODULE|@getTranslatedString:$MODULE}" onclick="if(SelectAllPatron('{$MODULE}','{$RETURN_MODULE}')) window.close();"/>
								{/if}-->
			<!-- fin pedido [TT11231] -->				
					</div>

				</td>
				<td style="padding-right:10px;" align="right">{$RECORD_COUNTS}</td></tr>
		   	<tr>
			    <td style="padding:10px;" colspan=3>

		       	<input name="module" type="hidden" value="{$RETURN_MODULE}">
				<input name="action" type="hidden" value="{$RETURN_ACTION}">
		        <input name="pmodule" type="hidden" value="{$MODULE}">
				<input type="hidden" name="curr_row" value="{$CURR_ROW}">	
				<input name="entityid" type="hidden" value="">
				<input name="popuptype" id="popup_type" type="hidden" value="{$POPUPTYPE}">
				<input name="ctrlid" id="ctrlid" type="hidden" value="{$CTRLID}">
				<input name="ctrlname" id="ctrlname" type="hidden" value="{$CTRLNAME}">
				<input name="idlist" type="hidden" value="">
				<div style="overflow:auto;height:348px;">
				<table style="background-color: rgb(204, 204, 204);" class="small" border="0" cellpadding="5" cellspacing="1" width="100%">
				<tbody>
				<tr>
					{if $SELECT eq 'enable'}
						<td class="lvtCol" width="3%"><input type="checkbox" name="select_all" value="" onClick=toggleSelect(this.checked,"selected_id")></td>
		            {/if}
		          <!--  pedido [TT11231] -->
		        	{if $PATRON eq 'enable'}
						<td class="lvtCol" width="3%"><input type="checkbox" name="select_all" value="" onClick=toggleSelect(this.checked,"selected_id")></td>
		            {/if}
		        <!-- fin pedido [TT11231]-->   
				    {foreach item=header from=$LISTHEADER}
				        <td class="lvtCol">{$header}</td>
				    {/foreach}
				<!--Keyla rodriguez-->
				{if $PATRON eq 'enable' && ($POPUPTYPE eq 'inventory_prod' || $POPUPTYPE eq 'inventory_prod_po')}
						{if !$RECORD_ID}
							<td class="lvtCol">{$APP.LBL_ACTION}</td>
						{/if}
					{/if}
				</tr>
				{foreach key=entity_id item=entity from=$LISTENTITY}
			        <tr bgcolor=white onMouseOver="this.className='lvtColDataHover'" onMouseOut="this.className='lvtColData'"  >
				   {if $SELECT eq 'enable'}
					<td><input type="checkbox" name="selected_id" value="{$entity_id}" onClick=toggleSelectAll(this.name,"select_all")></td>
				   {/if}
			<!--  pedido [TT11231] -->
				   {if $PATRON eq 'enable'}
					<td><input type="checkbox" name="selected_id" value="{$entity_id}" onClick=toggleSelectAll(this.name,"select_all")></td>
				   {/if}
			<!-- fin pedido [TT11231] -->	   
		                   {foreach item=data from=$entity}
				        <td>{$data}</td>
		                   {/foreach}
				</tr>
				{foreachelse}
		                        <tr><td colspan="{$HEADERCOUNT}">
		                        <div style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 99%;position: relative; z-index: 10000000;">
		                        <table border="0" cellpadding="5" cellspacing="0" width="98%">
		                                <tr>
		                                        <td rowspan="2" width="25%"><img src="{'empty.jpg'|@vtiger_imageurl:$THEME}" height="60" width="61%"></td>
		                                        {if $recid_var_value neq '' && $mod_var_value neq '' && $RECORD_COUNTS eq 0 }
							<script>redirectWhenNoRelatedRecordsFound();</script>
		                                        <td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%"><span class="genHeaderSmall">{$APP.LBL_NO} {$MODULE|@getTranslatedString:$MODULE} {$APP.RELATED} !</td>
		                                        {else}
		                                        <td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%"><span class="genHeaderSmall">{$APP.LBL_NO} {$MODULE|@getTranslatedString:$MODULE} {$APP.LBL_FOUND} !</td>
		                                        {/if}
		                                </tr>
		                        </table>
		                        </div>
		                        </td></tr>
		                {/foreach}
			      	</tbody>
			    	</table>
					<div>
			    </td>
			</tr>

		</table>
		<table width="100%" align="center" class="reportCreateBottom">
		<tr>
			{$NAVIGATION}	
		<td width="35%">&nbsp;</td>
		</tr>
		</table>

	</div>

</form>

