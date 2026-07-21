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
<script type='text/javascript' src='include/js/Mail.js'></script>
{'RelatedLists'|writeJSOnce:true}


<ul class="nav nav-tabs">
{foreach key=header item=detail from=$RELATEDLISTS name=menu}
	{assign var=rel_mod value=$header}
	{assign var="HEADERLABEL" value=$header|@getTranslatedString:$rel_mod}
	{if $SELECTEDHEADERS neq '' && $header|in_array:$SELECTEDHEADERS}
		{assign var="active" value=''}
		{assign var="in" value='in'}
	{else}
		{assign var="active" value='active'}
		{assign var="in" value=''}
	{/if}

	{if $smarty.foreach.menu.iteration eq 1}
		{assign var="active" value='active'}
		{assign var="in" value=''}
	{/if}

	<li class="{$active}">
		<a href="#tab-{$header|replace:' ':''}" data-toggle="tab" onclick="loadRelatedListBlock('module={$MODULE}&action={$MODULE}Ajax&file=DetailViewAjax&record={$ID}&ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$detail.relationId}&actions={$detail.actions}&parenttab={$CATEGORY}',
						'tab-{$MODULE}_{$header|replace:' ':''}','{$MODULE}_{$header|replace:' ':''}');">
			{$HEADERLABEL} 
		</a>
	</li>
{/foreach}
</ul>



<div class="tab-content">
{foreach key=header item=detail from=$RELATEDLISTS name=tabs}
	{assign var=rel_mod value=$header}
	{assign var="HEADERLABEL" value=$header|@getTranslatedString:$rel_mod}
	{if $SELECTEDHEADERS neq '' && $header|in_array:$SELECTEDHEADERS}
		{assign var="active" value=''}
		{assign var="in" value='in'}
	{else}
		{assign var="active" value='active in'}
		{assign var="in" value=''}
	{/if}

	{if $smarty.foreach.tabs.iteration eq 1}
		{assign var="active" value='active'}
		{assign var="in" value=''}
	{/if}

	<div class="tab-pane fade {$active} {$in}" id="tab-{$header|replace:' ':''}">
		{$HEADERLABEL}
	</div>
													
{/foreach}
</div>














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

		
		{if $SELECTEDHEADERS neq '' && $header|in_array:$SELECTEDHEADERS}
		<script type='text/javascript'>
		if(typeof('Event') != 'undefined') {ldelim}
		{if $smarty.request.ajax neq 'true'}
			Event.observe(window, 'load', function(){ldelim}
				loadRelatedListBlock('module={$MODULE}&action={$MODULE}Ajax&file=DetailViewAjax&record={$ID}&ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$detail.relationId}&actions={$detail.actions}&parenttab={$CATEGORY}','tab-{$header|replace:' ':''}','{$MODULE}_{$header|replace:' ':''}');
			{rdelim});
		{else}
			loadRelatedListBlock('module={$MODULE}&action={$MODULE}Ajax&file=DetailViewAjax&record={$ID}&ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$detail.relationId}&actions={$detail.actions}&parenttab={$CATEGORY}','tab-{$header|replace:' ':''}','{$MODULE}_{$header|replace:' ':''}');
		{/if}
		{rdelim}
		</script>
		{/if}
{/foreach}
