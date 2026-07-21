{*<!--
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

-->*}
<div class="row">
	<div class="col-lg-12" id="searchResultContainerId">
		<div id="global_list_{$MODULE}" class="main-box clearfix">


			<form name="massdelete" method="POST">
				<input name="idlist" type="hidden">
				<input name="change_owner" type="hidden">
				<input name="change_status" type="hidden">
				<input name="search_tag" type="hidden" value="{$TAG_SEARCH}" >
				<input name="search_criteria" type="hidden" value="{$SEARCH_STRING}">
				<input name="module" type="hidden" value="{$MODULE}" />
				<input name="{$MODULE}RecordCount" id="{$MODULE}RecordCount" type="hidden" value="{$ModuleRecordCount.$MODULE.count}" />
				{assign var="MODULELABEL" value=$MODULE}
				{if $APP.$MODULE neq ''}
					{assign var="MODULELABEL" value=$APP.$MODULE}
				{/if}
				hola 
				<header class="main-box-header clearfix">
					<h2>{$CANTIDAD} resultados en {$MODULELABEL} para: <span class="emerald">&quot;{$SEARCH_STRING}&quot;</span></h2>
					<small class="gray">{* Request time (1.86 seconds) *}</small>
				</header>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table">
							<tr>
							{if $DISPLAYHEADER eq 1}
								{foreach item=header from=$LISTHEADER}
								<td>{$header}</td>
								{/foreach}
							{else}
								<td colspan=$HEADERCOUNT> {$APP.LBL_NO_DATA} </td>
							{/if}
							</tr>
						   {foreach item=entity key=entity_id from=$LISTENTITY}
						   <tr>
							{foreach item=data from=$entity.records}	
								<td>{$data}</td>
							{/foreach}
						   </tr>
						   {/foreach}
						</table>
					</div>
					{$NAVIGATION}
				</div>
			</form>
				
		</div>
	</div>
</div>

{if $SEARCH_MODULE eq 'All'}
<script>
displayModuleList(document.getElementById('global_search_module'));
</script>
{/if}