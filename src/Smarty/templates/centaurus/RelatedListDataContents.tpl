{*<!--
/*+********************************************************************************
  * The contents of this file are subject to the vtiger CRM Public License Version 1.0
  * ("License"); You may not use this file except in compliance with the License
  * The Original Code is:  vtiger CRM Open Source
  * The Initial Developer of the Original Code is vtiger.
  * Portions created by vtiger are Copyright (C) vtiger.
  * All Rights Reserved.
  *********************************************************************************/
-->*}
{* Agregado porque si es carga como uitype se rompe el theme centaurus en la redeclaracion de una tabla responsive dentro de otra *}
{if !$AS_UITYPE}
<div class="clearfix">
{/if}
	<header class="main-box-header clearfix">
		{if !$AS_UITYPE}
		<div class="pull-left col-lg-4">
			{$RELATEDLISTDATA.navigation.0}
		</div>
		<div class="pull-left col-lg-4">
			{$RELATEDLISTDATA.navigation.1}
		</div>
		{/if}
		<div class="icon-box pull-right">
			{$RELATEDLISTDATA.CUSTOM_BUTTON}

			{if $HEADER eq 'Contacts' && $MODULE neq 'Campaigns' && $MODULE neq 'Accounts' && $MODULE neq 'Potentials' && $MODULE neq 'Vendors'}
				{if $MODULE eq 'Calendar'}
					<input alt="{$APP.LBL_SELECT_CONTACT_BUTTON_LABEL}" title="{$APP.LBL_SELECT_CONTACT_BUTTON_LABEL}" accessKey="" class="btn btn-success" value="{$APP.LBL_SELECT_BUTTON_LABEL} {$APP.Contacts}" LANGUAGE=javascript onclick='return window.open("index.php?module=Contacts&return_module={$MODULE}&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid={$ID}{$search_string}","test","width=640,height=602,resizable=0,scrollbars=0");' type="button"  name="button">
				{else}
					<input title="{$APP.LBL_ADD_NEW} {$APP.Contact}" accessKey="F" class="btn btn-success" onclick="this.form.action.value='EditView';this.form.module.value='Contacts'" type="submit" name="button" value="{$APP.LBL_ADD_NEW} {$APP.Contact}">
				{/if}
			{elseif $HEADER eq 'Users' && $MODULE eq 'Calendar'}
				<input title="Change" accessKey="" tabindex="2" type="button" class="btn btn-success" value="{$APP.LBL_SELECT_USER_BUTTON_LABEL}" name="button" LANGUAGE=javascript onclick='return window.open("index.php?module=Users&return_module=Calendar&return_action={$return_modname}&activity_mode=Events&action=Popup&popuptype=detailview&form=EditView&form_submit=true&select=enable&return_id={$ID}&recordid={$ID}","test","width=640,height=525,resizable=0,scrollbars=0");'>
			{/if}
		</div>
	</header>
{if !$AS_UITYPE}
	<div class="main-box-body clearfix">
		<div class="table-responsive">
{/if}
			<table class="table">
				<thead>
					<tr>
						{if $MODULE eq 'Campaigns' && ($RELATED_MODULE eq 'Contacts' || $RELATED_MODULE eq 'Leads' || $RELATED_MODULE eq 'Accounts')
							&& $RELATEDLISTDATA.entries|@count > 0}
						<th>
							<input name ="{$RELATED_MODULE}_selectall" id="{$MODULE}_{$RELATED_MODULE}_selectCurrentPageRec" onclick="rel_toggleSelect(this.checked,'{$MODULE}_{$RELATED_MODULE}_selected_id','{$RELATED_MODULE}');"  type="checkbox">
						</th>
						{/if}
						{foreach key=index item=_HEADER_FIELD from=$RELATEDLISTDATA.header}
							<th>{$_HEADER_FIELD}</th>
						{/foreach}
						<th style="width: 5em;"></th>
					</tr>
				</thead>
				<tbody>
					{if $MODULE eq 'Campaigns'}
					<tr>
						<td id="{$MODULE}_{$RELATED_MODULE}_linkForSelectAll" class="linkForSelectAll" style="display:none;" colspan=10>
							<span id="{$MODULE}_{$RELATED_MODULE}_selectAllRec" class="selectall" style="display:inline;" onClick="rel_toggleSelectAll_Records('{$MODULE}','{$RELATED_MODULE}',true,'{$MODULE}_{$RELATED_MODULE}_selected_id')">{$APP.LBL_SELECT_ALL} <span id={$RELATED_MODULE}_count class="folder"> </span> {$APP.LBL_RECORDS_IN} {$RELATED_MODULE|@getTranslatedString:$RELATED_MODULE} {$APP.LBL_RELATED_TO_THIS} {$APP.SINGLE_Campaigns}</span>
							<span id="{$MODULE}_{$RELATED_MODULE}_deSelectAllRec" class="selectall" style="display:none;" onClick="rel_toggleSelectAll_Records('{$MODULE}','{$RELATED_MODULE}',false,'{$MODULE}_{$RELATED_MODULE}_selected_id')">{$APP.LBL_DESELECT_ALL} {$RELATED_MODULE|@getTranslatedString:$RELATED_MODULE} {$APP.LBL_RELATED_TO_THIS} {$APP.SINGLE_Campaigns}</span>
						</td>
					</tr>
					{/if}
					{foreach key=_RECORD_ID item=_RECORD from=$RELATEDLISTDATA.entries}
						<tr bgcolor={$_RECORD.color}>
							{if $MODULE eq 'Campaigns' && ($RELATED_MODULE eq 'Contacts' || $RELATED_MODULE eq 'Leads' || $RELATED_MODULE eq 'Accounts')}
							<td><input name="{$MODULE}_{$RELATED_MODULE}_selected_id" id="{$_RECORD_ID}" value="{$_RECORD_ID}" onclick="rel_check_object(this,'{$RELATED_MODULE}');" type="checkbox"  {$RELATEDLISTDATA.checked.$_RECORD_ID}></td>
							{/if}
							{assign var="cant" value="0"}
							{foreach key=index item=_RECORD_DATA from=$_RECORD.records}
								{assign var="cant" value=$cant+1}
								 {* vtlib customization: Trigger events on listview cell *}
								 <td {if $RELATEDLISTDATA.header|@count eq $cant }nowrap {/if}>{$_RECORD_DATA}</td>
								 {* END *}
							{/foreach}
							<td><button type="button" class="btn btn-danger btn-icon" data-current-record="{$ID}" data-current-module="{$MODULE}" data-related-module="{$RELATED_MODULE}" data-related-record="{$_RECORD_ID}" title="Eliminar relación" onclick="RelatedModuleModalUtils.unrelateRecord (this);"><i class="fa fa-trash-o"></i></button></td>
						</tr>
					{foreachelse}
						<tr><td><i>{$APP.LBL_NONE_INCLUDED}</i></td></tr>
					{/foreach}
				</tbody>
			</table>
{if !$AS_UITYPE}
		</div>
	</div>
</div>
{/if}
{if $MODULE eq 'Campaigns' && ($RELATED_MODULE eq 'Contacts' || $RELATED_MODULE eq 'Leads' || $RELATED_MODULE eq 'Accounts')
			&& $RELATEDLISTDATA.entries|@count > 0 && $RESET_COOKIE eq 'true'}
			<script type='text/javascript'>set_cookie('{$RELATED_MODULE}_all', '');</script>
{/if}