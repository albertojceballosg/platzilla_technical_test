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
<table width="100%" cellpadding="5" cellspacing="0" class="table" >
	<tr>
	<th width="5%">#</th>
	<th  width="40%">{$CMOD.LBL_NOTIFICATION}</th>
	<th  width="50%">{$CMOD.LBL_DESCRIPTION}</th>
	<th  width="10%">{$CMOD.LBL_STATUS}</th>
	<th  width="5%">{$CMOD.Tools}</th>
	</tr>
	{foreach name=notifyfor item=elements from=$NOTIFICATION}
	<tr>
	<td >{$smarty.foreach.notifyfor.iteration}</td>
	<td >{$elements.notificationname}</td>
	<td >{$elements.label}</td>
	{if $elements.status eq 'Active'}
	<td>{$elements.status}</td>
	{else}
	<td >{$elements.status}</td>
	{/if}
	<td  >
	
<a href="#" onclick="fetchEditNotify('{$elements.id}')" role="button" class="btn btn-large btn-primary" data-toggle="modal" data-target="#editinv" style="margin-bottom: 10px;margin-left: 5px">{$APP.LBL_EDIT}</a>
	
	</td>
	</tr>
	{/foreach}
	</table>

