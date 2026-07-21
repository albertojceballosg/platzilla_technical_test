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
	<th  width="35%">{$MOD.LBL_NOTIFICATION}</th>
	<th width="50%">{$MOD.LBL_DESCRIPTION}</th>
	<th  width="10%">{$MOD.LBL_STATUS}</th>
	<th  width="10%">{$MOD.LBL_TOOL}</th>
	</tr>
	{foreach name=notifyfor item=elements from=$NOTIFICATION}
	<tr>
	<td >{$smarty.foreach.notifyfor.iteration}</td>
	<td >{$elements.label}</td>
	<td >{$elements.schedulename}</td>
	{if $elements.active eq 'Active'}
	<td >{$elements.active}</td>
	{else}
	<td >{$elements.active}</td>
	{/if}
	<td ><a   onClick="fetchEditNotify('{$smarty.foreach.notifyfor.iteration}');" role="button" class="btn btn-large btn-primary" data-toggle="modal" data-target="#orgLay1" style="margin-bottom: 10px;margin-left: 5px">{$APP.LBL_EDIT}</a></td>
	<!--<td ><img onClick="fnvshobj(this,'editdiv');fetchEditNotify('{$smarty.foreach.notifyfor.iteration}');" style="cursor:pointer;" src="{'editfield.gif'|@vtiger_imageurl:$THEME}" title="{$APP.LBL_EDIT}" alt="{$APP.LBL_EDIT}"></td>-->
	</tr>
	{/foreach}
	</table>

