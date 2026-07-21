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




<div class="modal layerPopup" id="orgLay" style="position:relative;display: block;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" onclick="fnhide('actiondiv');" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{$MOD.ADD_PICKLIST_VALUES} - {$FIELDLABEL}</h4>
			</div>
			<div class="modal-body">
				<form role="form">
				<div class="form-group">
	


					<table border=0 cellspacing=0 cellpadding=5 class="table">
						<tr>	
							<td rowspan=2 style="vertical-align:top;">	
								<b>{$MOD.LBL_EXISTING_PICKLIST_VALUES}</b>
								<div id="add_availPickList" name="availList" style=""> 				
									{foreach item=pick_val from=$PICKVAL}
										<div class="picklist_existing_options" style="background-color: #ffffff;">{$pick_val}</div>
									{/foreach}
								</div>
								<br>
								{if is_array($NONEDITPICKLIST)}				
									<b>{$MOD.LBL_NON_EDITABLE_PICKLIST_ENTRIES} :</b>
									<div id="nonedit_pl_values" name="availList" style="overflow:auto; ">
										{foreach item=nonedit from=$NONEDITPICKLIST}
											<div class="picklist_noneditable_options" style="">
												{$nonedit}		
											</div>							
										{/foreach}
									</div>
								{/if}
							</td>
							
							<td valign=top align=left width=300px;>
								<b>{$MOD.LBL_PICKLIST_ADDINFO}</b>
								<textarea id="add_picklist_values" class="form-control" align="left" rows="10"></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<b>{$MOD.LBL_SELECT_ROLES} </b><br />
								<select id="add_availRoles" multiple="multiple" wrap size="5" name="add_availRoles" style="">
									{foreach key=role_id item=role_details from=$ROLEDETAILS}
										<option value="{$role_id}">{$role_details.0}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="text-center">
								<input type="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" id="saveAddButton" name="save" class="btn btn-primary btn-sm" onclick="validateAdd('{$FIELDNAME}','{$MODULE}');">
								<input type="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" name="cancel" class="btn btn-warning btn-sm" onclick="fnhide('actiondiv');">
							</td>			
						</tr>
					</table>


				</div>
				</form>
			</div>
		</div>
	</div>
</div>	


