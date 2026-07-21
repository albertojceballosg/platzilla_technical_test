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

<input type="hidden" name="folderPadre" id="folderPadre" value="{$FOLDERPADRE}"/> 

<div class="row">
{foreach item=folder from=$FOLDERSAUX}
	<!-- folder division starts -->
	{assign var=foldercount value=$FOLDERSAUX|@count}
	{assign var=folderID value=$folder.folderid}

	{if $IS_ADMIN neq 1 && $PERMISOLOGIACARPETAS.$folderID.delete_act neq 1}
		<script>
			jQuery('#deletefolder').hide();
		</script>
	{/if}

	{if $PERMISOLOGIACARPETAS.$folderID.read_act eq 1}
	<div class="col-lg-3 col-sm-6 col-xs-12 openfolder" onclick="openFolder({$folder.folderid},'')" data-folderid="{$folder.folderid}">
		<div class="main-box infographic-box">
			<!--i class="fa fa-folder emerald-bg"></i-->
			<i class="fa fa-folder-open emerald-bg"></i>
			 
			<span class="headline">{$folder.foldername} {* {$folderID} | {$PERMISOLOGIACARPETAS.$folderID.read_act} | {$PERMISOLOGIACARPETAS.$folderID.edit_act} | {$PERMISOLOGIACARPETAS.$folderID.delete_act}  *}</span>
			<span class="value">
				<span class="timer" data-from="0" data-to="{$folder.TotalrecordListRange}" data-speed="1000" data-refresh-interval="150">{$folder.TotalrecordListRange}</span>
			</span>
		</div>
	</div>
	{else}
	<div class="col-lg-3 col-sm-6 col-xs-12" onclick="" data-folderid="{$folder.folderid}">
		<div class="main-box infographic-box">
			<!--i class="fa fa-eye-slash red-bg"></i-->
			<i class="fa fa-lock red-bg"></i>
			
			<span class="headline">{$folder.foldername} </span>
			<span class="value">
				<span class="timer" data-from="0" data-to="{$folder.TotalrecordListRange}" data-speed="1000" data-refresh-interval="150">{$folder.TotalrecordListRange}</span>
			</span>
		</div>
	</div>
	{/if}


{/foreach}
</div>
<div class="row">
{foreach item=folder key=modulename from=$MOD_FOLDERS}
	<!-- folder division starts -->
	{assign var=foldercount value=$folder|@count}
	<div class="col-lg-3 col-sm-6 col-xs-12 openfolder" onclick="openFolder(0,'{$modulename}')" data-folderid="{$modulename}">
		<div class="main-box infographic-box">
			<i class="fa fa-bars green-bg"></i>
			<span class="headline">{$modulename|@getTranslatedString}</span>
			<span class="value">
				<span class="timer" data-from="0" data-to="{$foldercount}" data-speed="1000" data-refresh-interval="150">{$foldercount}</span>
			</span>
		</div>
	</div>
{/foreach}
</div>