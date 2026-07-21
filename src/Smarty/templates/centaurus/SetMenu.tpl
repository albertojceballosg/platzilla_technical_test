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
{php}
	//add the settings page values
	$this->assign("BLOCKS",getSettingsBlocks());
	$this->assign("FIELDS",getSettingsFields());
{/php}

{if $MENU neq 'false'}
<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav" style="width:300px;background-color:#f1f3f7">	
<ul class="nav nav-pills nav-stacked">
	{foreach key=BLOCKID item=BLOCKLABEL from=$BLOCKS}
		{if $BLOCKLABEL neq 'LBL_MODULE_MANAGER'}
			{assign var=blocklabel value=$BLOCKLABEL|@getTranslatedString:'Settings'}
			<li class="">
				<a href="#" class="dropdown-toggle" >
					<span>{$blocklabel}</span>
				</a>
				<ul class="submenu">
				{foreach item=data from=$FIELDS.$BLOCKID}
					{if $data.link neq ''}
						{assign var=label value=$data.name|@getTranslatedString:'Settings'}
						{if ($smarty.request.action eq $data.action && $smarty.request.module eq $data.module)}
							<li class="active">
						{else}
							<li class="">
						{/if}
						<a class="" href="{$data.link}">{$label}</a></li>
					{/if}
				{/foreach}
				</ul>
			</li>
		{/if}
	{/foreach}
</ul>
</div>
{/if}
