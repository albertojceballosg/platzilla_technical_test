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


<div id="editinv" class="modal fade" role="dialog">
<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			
			<h4 class="modal-title">{$NOTIFY_DETAILS.label}</h4>
		</div>
		<div class="modal-body">
			<table border=0 cellspacing=0 cellpadding=5 width=95% align=center class="table"> 
<tr>
	<td>
	<table border=0 celspacing=0 cellpadding=5 width=100% align=center bgcolor=white class="table">
	<tr>
		<td colspan="2">
			<b><font color="red">*</font>{$CMOD.LBL_NOTE_DO_NOT_REMOVE_INFO}</b>
		</td>
	</tr>
	<tr>
		<th align="right"  width="40%"><b>{$MOD.LBL_STATUS} :</b></th>
	<td align="left"  width="60%">
		<select class="form-control" id="notify_status" name="notify_status">
	{if $NOTIFY_DETAILS.status eq 1}
		<option value="1" "selected">{$MOD.LBL_ACTIVE}</option>
		<option value="0">{$MOD.LBL_INACTIVE}</option>
	{else}
		<option value="1">{$MOD.LBL_ACTIVE}</option>
		<option value="0" "selected">{$MOD.LBL_INACTIVE}</option>
	{/if}
	</select>
	</td>
	</tr>
	
	<tr>
		<th align="right" ><b>{$MOD.LBL_SUBJECT} : </b></th>
		<td align="left" ><input  id="notifysubject" name="notifysubject" value="{$NOTIFY_DETAILS.subject}" size="40" type="text"></td>
	</tr>
	<tr>
		<th align="right" valign="top"><b>{$MOD.LBL_MESSAGE} : </b></th>
		<td align="left" ><textarea id="notifybody" name="notifybody" class="form-control" rows="5" cols="40">{$NOTIFY_DETAILS.body}</textarea></td>
	</tr>
	</table>
	</td>
</tr>
</table>


<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-responsive">
<tr>
	<td align="center" >
		<input name="save" value="{$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-primary" type="button" onClick="fetchSaveNotify('{$NOTIFY_DETAILS.id}')" style="margin-bottom: 20px;">
		
	</td>
	</tr>
</table>
</div>
</div>
</div>
</div>