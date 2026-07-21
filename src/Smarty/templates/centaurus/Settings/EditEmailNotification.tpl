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
{if $NOTIFY_DETAILS.type eq "select"}
<div id="orgLay1" class="modal fade" >
<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			
			<h4 class="modal-title">{$NOTIFY_DETAILS.label}</h4>
		</div>

<div class="modal-body">


<table border=0 cellspacing=0 cellpadding=5 width=95% align=center> 
<tr>
	<td>
	<table border=0 celspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
	<tr>
	<th align="right"   width="40%"><b>{$MOD.LBL_STATUS} :</b></th>
	<td align="left"  width="60%">
		<select  id="notify_status" disabled>
	{if $NOTIFY_DETAILS.active eq 1}
		<option value="1" "selected">{$MOD.LBL_ACTIVE}</option>
		<option value="0">{$MOD.LBL_INACTIVE}</option>
	{else}
		<option value="1">{$MOD.LBL_ACTIVE}</option>
		<option value="0" "selected">{$MOD.LBL_INACTIVE}</option>
	{/if}
	</select>
</td>
</tr>
<tr><th colspan="2" ><b>{$MOD.LBL_SELECT_EMAIL_TEMPLATE_FOR}  {$NOTIFY_DETAILS.name}</b></th></tr>
<tr>
<td align="right" ><b>{$MOD.LBL_TEMPLATE} : </b></td>
<td align="left"  >
<input type="hidden" id="notifysubject" value="aaaa">
	<select  id="notifybody">

	{foreach from=$VALUES key=k item=v}
		{if $k eq $SEL_ID}
		<option value="{$k}" "selected">{$v}</option>
		{else}
		<option value="{$k}">{$v}</option>
		{/if}
	{/foreach}

	</select>

</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
<table border=0 cellspacing=0 cellpadding=5 width=100% class="table">
<tr>
<td class="small" align="center">
	<input name="save" value=" {$APP.LBL_SAVE_BUTTON_LABEL} " class="class="btn btn-primary" type="button" onClick="fetchSaveNotify('{$NOTIFY_DETAILS.id}')">
	<input name="cancel" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="class="btn btn-cancel" type="button" onClick="hide('editdiv');">
</td>
</tr>
</table>
</div>
</div>
</div>


	{else}



<div id="orgLay1" class="modal fade" >
<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			
			<h4 class="modal-title">{$NOTIFY_DETAILS.label}</h4>
		</div>
<div class="modal-body">
<table border=0 cellspacing=0 cellpadding=5 width=95% align=center class="table"> 
<tr>
	<td >
	<table border=0 celspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
	<tr>
	<td align="right"   width="40%"><b>{$MOD.LBL_STATUS} :</b></td>
	<td align="left"   width="60%">
	{if $NOTIFY_DETAILS.id neq 7}
		<select  id="notify_status">
	{else}	
		<select  disabled id="notify_status">
	{/if}
	{if $NOTIFY_DETAILS.active eq 1}
		<option value="1" "selected">{$MOD.LBL_ACTIVE}</option>
		<option value="0">{$MOD.LBL_INACTIVE}</option>
	{else}
		<option value="1">{$MOD.LBL_ACTIVE}</option>
		<option value="0" "selected">{$MOD.LBL_INACTIVE}</option>
	{/if}
	</select>
</td>
</tr>
<tr><td colspan="2" ><b>{$MOD.LBL_EMAIL_CONTENTS}</b></td></tr>
<tr>
<td align="right" ><b>{$MOD.LBL_SUBJECT} : </b></td>
<td align="left"  ><input class="txtBox" id="notifysubject" name="notifysubject" value="{$NOTIFY_DETAILS.subject}" size="40" type="text"></td>
</tr>
<tr>
<td align="right"   valign="top"><b>{$MOD.LBL_MESSAGE} : </b></td>
<td align="left"  ><textarea id="notifybody" name="notifybody" class="txtBox" rows="5" cols="40">{$NOTIFY_DETAILS.body}</textarea></td>
</tr>
</table>
</td>
</tr>
</table>
</div>
<table border=0 cellspacing=0 cellpadding=5 width=100% class="table">
<tr>
<td  align="center">
	<input name="save" value=" {$APP.LBL_SAVE_BUTTON_LABEL} " class="crmButton small save" type="button" onClick="fetchSaveNotify('{$NOTIFY_DETAILS.id}')">
	<input name="cancel" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="crmButton small cancel" type="button" onClick="hide('editdiv');">
</td>
</tr>
</table>
</div>
</div>
</div>

{/if}
