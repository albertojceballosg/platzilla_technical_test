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










<div class="modal layerPopup" id="" style="position:relative;display: block;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" onclick="fnhide('actiondiv');" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{$MOD.EDIT_PICKLIST_VALUE} - {$FIELDLABEL}</h4>
			</div>
			<div class="modal-body">
				<form role="form">
				<div class="form-group">

					<table border=0 cellspacing=0 cellpadding=5 width="100%">
						<tr>
						<td valign=top align=left>
							<b>{$MOD.LBL_SELECT_TO_EDIT}</b>
							<br>
							<select id="edit_availPickList" name="availList" size="10" style="" class="form-control" onchange="selectForEdit();">
								{foreach item=pick_val key=pick_key from=$PICKVAL}
									<option value="{$pick_key}">{$pick_val}</option>
								{/foreach}
							</select>

							{if is_array($NONEDITPICKLIST)}
							<table border=0 cellspacing=0 cellpadding=0 width=100%>
								<tr><td><b>{$MOD.LBL_NON_EDITABLE_PICKLIST_ENTRIES} :</b></td></tr>
								<tr><td><b>
									<div id="nonedit_pl_values">
										{foreach item=nonedit from=$NONEDITPICKLIST}
											<span class="nonEditablePicklistValues">
												{$nonedit}
											</span><br>
										{/foreach}
									</div>
								</b></td></tr>
							</table>
							{/if}
						</td>
						</tr>
						<tr>
							<td>
								<b>{$MOD.LBL_EDIT_HERE}</b>&nbsp;
								<input type="text" id="replaceVal" class="form-control" style="" onchange="pushEditedValue(event)"/>
								<br>
							</td>
						</tr>
						<tr>
							<td class="text-center">
								<input type="button" value="{$APP.LBL_APPLY_BUTTON_LABEL}" name="apply" class="btn btn-primary btn-sm" onclick="validateEdit('{$FIELDNAME}','{$MODULE}');">
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


