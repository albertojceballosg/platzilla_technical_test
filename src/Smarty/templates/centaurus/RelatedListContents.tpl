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
{'RelatedLists'|writeJSOnce:true}

{foreach key=header item=detail from=$RELATEDLISTS}
	{assign var=rel_mod value=$header}
	{assign var="HEADERLABEL" value=$header|@getTranslatedString:$rel_mod}
	{if $SELECTEDHEADERS neq '' && $header|in_array:$SELECTEDHEADERS}
		{assign var="collapsed" value=''}
		{assign var="in" value='in'}
	{else}
		{assign var="collapsed" value='collapsed'}
		{assign var="in" value=''}
	{/if}
	<div class="panel panel-default">
		{if $MODULE eq 'Campaigns'}
		<input id="{$MODULE}_{$header|replace:' ':''}_numOfRows" type="hidden" value="">
		<input id="{$MODULE}_{$header|replace:' ':''}_excludedRecords" type="hidden" value="">
		<input id="{$MODULE}_{$header|replace:' ':''}_selectallActivate" type="hidden" value="false">
		{/if}
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle {$collapsed}" data-toggle="collapse" data-parent="#RLContents" href="#collapse_{$header|replace:' ':''}"
					onclick="loadRelatedListBlock('module={$MODULE}&action={$MODULE}Ajax&file=DetailViewAjax&record={$ID}&ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$detail.relationId}&actions={$detail.actions}&parenttab={$CATEGORY}',
						'tbl_{$MODULE}_{$header|replace:' ':''}','{$MODULE}_{$header|replace:' ':''}');">
					{$HEADERLABEL} <i class="fa fa-spinner fa-spin" id="loading_{$MODULE}_{$header|replace:' ':''}" style="display:none;padding: 0px;"></i>
				</a>
			</h4>
		</div>
		<div id="collapse_{$header|replace:' ':''}" class="panel-collapse collapse {$in}">
			<div class="panel-body" id="tbl_{$MODULE}_{$header|replace:' ':''}">

			</div>
		</div>
		{if $SELECTEDHEADERS neq '' && $header|in_array:$SELECTEDHEADERS}
		<script type='text/javascript'>
		if(typeof('Event') != 'undefined') {ldelim}
		{if $smarty.request.ajax neq 'true'}
			Event.observe(window, 'load', function(){ldelim}
				loadRelatedListBlock('module={$MODULE}&action={$MODULE}Ajax&file=DetailViewAjax&record={$ID}&ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$detail.relationId}&actions={$detail.actions}&parenttab={$CATEGORY}','tbl_{$MODULE}_{$header|replace:' ':''}','{$MODULE}_{$header|replace:' ':''}');
			{rdelim});
		{else}
			loadRelatedListBlock('module={$MODULE}&action={$MODULE}Ajax&file=DetailViewAjax&record={$ID}&ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$detail.relationId}&actions={$detail.actions}&parenttab={$CATEGORY}','tbl_{$MODULE}_{$header|replace:' ':''}','{$MODULE}_{$header|replace:' ':''}');
		{/if}
		{rdelim}
		</script>
		{/if}
	</div>
{/foreach}
