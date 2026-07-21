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
				<h4 class="modal-title">{$MOD.DELETE_PICKLIST_VALUES} - {$FIELDLABEL}</h4>
			</div>
			<div class="modal-body">
				<form role="form">
				<div class="form-group">

					<table border=0 cellspacing=0 cellpadding=5 width="100%">
						<tr>
						<td valign=top align=left>
							
							<select id="delete_availPickList" class="form-control" multiple="multiple" wrap size="20" name="availList" style="">
								{foreach item=pick_val key=pick_key from=$PICKVAL}
									<option value="{$pick_key}">{$pick_val}</option>
								{/foreach}
							</select>

							
						</td>
						</tr>
						<tr>
							<td>
								<b>{$MOD.LBL_REPLACE_WITH}</b>&nbsp;
								<select id="replace_picklistval" name="replaceList" style="" class="form-control">
									<option value=""></option>
									{foreach item=pick_val key=pick_key from=$PICKVAL}
										<option value="{$pick_key}">{$pick_val}</option>
									{/foreach}
									{foreach item=nonedit from=$NONEDITPICKLIST}
										<option value="{$nonedit}">{$nonedit}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="text-center">
								<input type="button" value="{$APP.LBL_DELETE_BUTTON_LABEL}" name="del" class="btn btn-primary btn-sm" onclick="validateDelete('{$FIELDNAME}','{$MODULE}');">
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


