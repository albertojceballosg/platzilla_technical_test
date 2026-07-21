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
				<h4 class="modal-title">{$MOD.ASSIGN_PICKLIST_VALUES} - {$FIELDLABEL}</h4>
			</div>
			<div class="modal-body">
				<form role="form">
				<div class="form-group">






					<table border="0" cellspacing="0" cellpadding="5" width="100%" id="assignPicklistTable">
						<tbody>
							
							<tr>
								<td colspan="2"><b>{$MOD.LBL_PICKLIST_VALUES}</b></td>
								<td colspan="2"><b>{$MOD.LBL_PICKLIST_VALUES_ASSIGNED_TO} {$ROLENAME}</b></td>
							</tr>



							<tr>	
								<td width="40%;">	
									
									<select multiple id="availList" name="availList" class="form-control" style="overflow:auto;">
										{foreach item=pick_val key=pick_key from=$PICKVAL}
											<option value="{$pick_key}">{$pick_val}</option>
										{/foreach}
									</select>
								</td>
								<td align="center" width="25px;vertical-align:middle">
									<i class="fa fa-chevron-right" onclick="moveRight();" style="cursor: pointer" ></i>
									<br>
									<!-- [ TT11270 ] Resolución de fallas Setting Editor de Listas Desplegables - Jesus A.- 10/08/2016 - Se agrega en onclick la funcion removeValue -->
									<i class="fa fa-chevron-left" onclick="removeValue();" style="cursor: pointer" ></i>
								</td>
								<td width="40%;">
									
									<select multiple id="selectedColumns" name="selectedColumns" class="form-control" style="overflow:auto;">
										{foreach item=val key=key from=$ASSIGNED_VALUES}
											<option value="{$key}">{$val}</option>
										{/foreach}
					        	    </select>
								</td>
									<td style="text-align:center;vertical-align:middle">
									<i class="fa fa-chevron-up" onclick="moveUp();" style="cursor: pointer" ></i>
									<br>
									<i class="fa fa-chevron-down" onclick="moveDown();" style="cursor: pointer" ></i>
								</td>
							</tr>
							<tr>
								<td>
									<a href='javascript:;' onclick="showRoleSelectDiv('{$ROLEID}')" id="addRolesLink">
										<b>{$MOD.LBL_ADD_TO_OTHER_ROLES}</b>
									</a>
								</td>
								<td colspan="3" class="text-center">
									<input type="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" name="save" class="btn btn-primary btn-sm" onclick="saveAssignedValues('{$MODULE}','{$FIELDNAME}','{$ROLEID}');">
									<input type="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" name="cancel" class="btn btn-primary btn-sm" onclick="fnhide('actiondiv');">
								</td>			
							</tr>
						</tbody>
						</table>





				</div>
				</form>
			</div>
		</div>
	</div>
</div>	





