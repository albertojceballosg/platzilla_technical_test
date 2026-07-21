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

<style type="text/css">
.filename{ldelim}
	text-align: center;
{rdelim}
.descripcion{ldelim}
	text-align: center;
	display: none;
	position: relative;
	top:-110px;
	border:2px solid #888;
	border-radius: 3px;
	font-weight:bold;
	background-color: #524F4F;
	color: #fff;
{rdelim}

.descripcion.privado{ldelim}
	background-color: #A81625;
	color: #fff;
{rdelim}

.table-link.badge.privado{ldelim}
	background-color: #A81625;
{rdelim}




</style>

<div class="col-lg-12">
	<div class="main-box">
	{foreach item=folder from=$FOLDERS}
		<!-- folder division starts -->
		{assign var=foldercount value=$FOLDERS|@count}
		<header class="main-box-header">
			<div class="pull-right">
				{$folder.navigation}
			</div>
		</header>
		<div class="main-box-body">
			<div id="gallery-photos-lightbox">
				<ul class="clearfix gallery-photos ">
					{foreach item=entity key=entity_id from=$folder.entries}

						{assign var=crmID value=$entity.records.filename.value.crmid}
						{assign var=privado value=$PERMISOLOGIADOCUMENTOS.privado.$crmID}

						{* Si no es privado *}
						{if $privado neq 1}
						<li class="col-md-3 col-sm-3 col-xs-6">
							{if $entity.records.filename.value.isimage}
								<aimg href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" class="photo-box image-link" style="background-image: url('{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}');"></aimg>
							{elseif !$entity.records.filename.value.path && $entity.records.filename.href}
								<a href="{$entity.records.filename.href}" style="margin-left: 20%;"><i class="fa fa-file-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'docx' || $entity.records.filename.fileicon.ext eq 'doc'}
								<a href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-word-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'xls' || $entity.records.filename.fileicon.ext eq 'xlsx' || $entity.records.filename.fileicon.ext eq 'csv'}
								<a href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-excel-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'zip' || $entity.records.filename.fileicon.ext eq 'gz' || $entity.records.filename.fileicon.ext eq 'rar'}
								<a href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-zip-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'pdf'}
								<a href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-pdf-o" style="font-size:12em;"></i></a>
							{else}
								<a href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file" style="font-size:12em;"></i></a>
							{/if}

							<div class="actions">
								<a title="Ver detalle" href="{$entity.records.filename.href}" class="table-link badge">
									<i class="fa fa-pencil"></i>
								</a>
								{if $entity.records.filename.value.path}
								<a title="Descargar" href="{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}" target="_blank" class="table-link badge">
									<i class="fa fa-download"></i>
								</a>
								{/if}
							</div>
							
							<div class="filename">
								<p class="" style=""> {$entity.records.filename.value.name}</p>
							</div>
							<div class="descripcion">
								<p class="" style=""> {$entity.records.filename.value.name}</p>
							</div>
						</li>
						{else}      {* Si es privado *}
						<li class="col-md-3 col-sm-3 col-xs-6">
							{if $entity.records.filename.value.isimage}
								<aimg href="#" class="photo-box image-link" style="background-image: url('{$entity.records.filename.value.path}{$entity.records.filename.value.attachmentsid}_{$entity.records.filename.value.name}');"></aimg>
							{elseif !$entity.records.filename.value.path && $entity.records.filename.href}
								<a href="#" onclick="return false;"><i class="fa fa-file-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'docx' || $entity.records.filename.fileicon.ext eq 'doc'}
								<a href="#" onclick="return false;" style="margin-left: 20%;"><i class="fa fa-file-word-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'xls' || $entity.records.filename.fileicon.ext eq 'xlsx' || $entity.records.filename.fileicon.ext eq 'csv'}
								<a href="#" onclick="return false;" style="margin-left: 20%;"><i class="fa fa-file-excel-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'zip' || $entity.records.filename.fileicon.ext eq 'gz' || $entity.records.filename.fileicon.ext eq 'rar'}
								<a href="#" onclick="return false;" style="margin-left: 20%;"><i class="fa fa-file-zip-o" style="font-size:12em;"></i></a>
							{elseif $entity.records.filename.fileicon.ext eq 'pdf'}
								<a href="#" onclick="return false;" style="margin-left: 20%;"><i class="fa fa-file-pdf-o" style="font-size:12em;"></i></a>
							{else}
								<a href="#" style="margin-left: 20%;"><i class="fa fa-file" style="font-size:12em;"></i></a>
							{/if}

							<div class="actions">
								<a title="Archivo Privado" href="#" onclick="return false;" class="table-link badge privado">
									<i class="fa fa-eye-slash"></i>
								</a>
							</div>

							<div class="filename">
								<p class="" style=""> {$entity.records.filename.value.name}</p>
							</div>
							<div class="descripcion privado">
								<p class="" style=""> Archivo Privado</p>
							</div>
						</li>
						{/if}
					{/foreach}
				</ul>
			</div>
		</div>
	{/foreach}
	
	{foreach item=folder key=modulename from=$MOD_FOLDERS}
		<!-- folder division starts -->
		{assign var=foldercount value=$FOLDERS|@count}
		<header class="main-box-header">
			<div class="pull-right">
				&nbsp;
			</div>
		</header>
		<div class="main-box-body">
			<div id="gallery-photos-lightbox">
				<ul class="clearfix gallery-photos">
					{foreach item=entity key=entity_id from=$folder}
						<li class="col-md-3 col-sm-3 col-xs-6">
							{if $entity.isimage}
								<aimg href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" class="photo-box image-link" style="background-image: url('{$entity.path}{$entity.attachmentsid}_{$entity.name}');"></aimg>
							{elseif $entity.ext eq 'docx' || $entity.ext eq 'doc'}
								<a href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-word-o" style="font-size:12em;"></i></a>
							{elseif $entity.ext eq 'xls' || $entity.ext eq 'xlsx' || $entity.ext eq 'csv'}
								<a href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-excel-o" style="font-size:12em;"></i></a>
							{elseif $entity.ext eq 'zip' || $entity.ext eq 'gz' || $entity.ext eq 'rar'}
								<a href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-zip-o" style="font-size:12em;"></i></a>
							{elseif $entity.ext eq 'pdf'}
								<a href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-pdf-o" style="font-size:12em;"></i></a>
							{else}
								<a href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" target="_blank" style="margin-left: 20%;"><i class="fa fa-file-o" style="font-size:12em;"></i></a>
							{/if}
							<div class="actions">
								<a title="Ver detalle" href="index.php?module={$entity.setype}&action=DetailView&record={$entity.crmid}" class="table-link badge">
									<i class="fa fa-pencil"></i>
								</a>
								{if $entity.path}
								<a title="Descargar" href="{$entity.path}{$entity.attachmentsid}_{$entity.name}" target="_blank" class="table-link badge">
									<i class="fa fa-download"></i>
								</a>
								{/if}
							</div>
							<span class="thumb-meta-time" style="background-color: #888;opacity: 0.5;margin-left: 19%;"><i class="fa fa-clock-o"></i> {$entity.modifiedtime}</span>
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	{/foreach}
	</div>
</div>








<script language="javascript">

jQuery(document).ready(function() {ldelim}

            jQuery('li').hover(
				
               function () {ldelim}
                  jQuery(this).find( "div.descripcion" ).css({ldelim}"display":"block"{rdelim});
                  //jQuery(this).css({ldelim}"background-color":"red"{rdelim});
               {rdelim}, 
				
               function () {ldelim}
                  jQuery(this).find( "div.descripcion" ).css({ldelim}"display":"none"{rdelim});
                  //jQuery(this).css({ldelim}"background-color":"blue"{rdelim});
               {rdelim}
            );
         {rdelim});
</script>













	{*
<pre>{$FOLDERS|@print_r}</pre>	
	<div id='{$folder.folderid}' class="documentModuleFolderView">
		<table class="reportsListTable" align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="mailSubHeader" width="40%" align="left">
					<b>{$folder.foldername}</b>
					&nbsp;&nbsp;
					{if $folder.description neq ''}
						<font class="copy">[<i>{$folder.description}</i>]</font>
					{/if}
				</td>
				<td class="mailSubHeader small" align="center" nowrap>{$folder.recordListRange}</td>
				{$folder.record_count}&nbsp;&nbsp;&nbsp;&nbsp;{$folder.navigation}
			</tr>
			<tr>
				<td colspan="4" >
					<div id="FileList_{$folder.folderid}">
						<!-- File list table for a folder starts -->
						<table border=0 cellspacing=1 cellpadding=3 width=100%>
						<!-- Table Headers -->
							{assign var="header_count" value=$folder.header|@count}
								<tr>
									<td class="lvtCol" width="10px"><input type="checkbox"  name="selectall{$folder.folderid}" id="currentPageRec_selectall{$folder.folderid}" onClick='toggleSelect_ListView(this.checked,"selected_id{$folder.folderid}","selectall{$folder.folderid}");'></td>
									{foreach name="listviewforeach" item=header from=$folder.header}
										<td class="lvtCol">{$header}</td>
									{/foreach}
								</tr>
								<tr>
									<td id="linkForSelectAll_selectall{$folder.folderid}" class="linkForSelectAll" style="display:none;" colspan=10>
										<span id="selectAllRec_selectall{$folder.folderid}" class="selectall" style="display:inline;" onClick="toggleSelectDocumentRecords('{$MODULE}',true,'selected_id{$folder.folderid}','selectall{$folder.folderid}')">{$APP.LBL_SELECT_ALL} <span class="folder" id="count_selectall{$folder.folderid}"> </span> {$APP.LBL_RECORDS_IN} <span class="folder">{$folder.foldername}</span> {$APP.LBL_FOLDER}</span>
										<span id="deSelectAllRec_selectall{$folder.folderid}" class="selectall" style="display:none;" onClick="toggleSelectDocumentRecords('{$MODULE}',false,'selected_id{$folder.folderid}','selectall{$folder.folderid}')">{$APP.LBL_DESELECT_ALL} <span class="folder">{$folder.foldername}</span> {$APP.LBL_FOLDER}</span>
									</td>
								</tr>

								<!-- Table Contents -->

								{foreach item=entity key=entity_id from=$folder.entries}
								<tr class="lvtColData" bgcolor=white onMouseOver="this.className='lvtColDataHover'" onMouseOut="this.className='lvtColData'" id="row_{$entity_id}">
									<td width="2%"><input type="checkbox" name="selected_id{$folder.folderid}" id="{$entity_id}" value= '{$entity_id}' onClick='check_object(this,"selectall{$folder.folderid}")'></td>
									{foreach item=recordid key=record_id from=$entity}
										{foreach item=data from=$recordid}
												<td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))">{$data}</td>
											
										{/foreach}
								</tr>
								{/foreach}

								<!-- If there are no entries for a folder -->
								{foreachelse}
									{if $foldercount eq 1}
										<tr>
											<td align="center" style="background-color:#efefef;height:340px" colspan="{$header_count+1}">
												<div style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 45%; position: relative;">
													{assign var=vowel_conf value='LBL_A'}
													{assign var=MODULE_CREATE value=$SINGLE_MOD}
													{if $CHECK.EditView eq 'yes'}
														<table border="0" cellpadding="5" cellspacing="0" width="98%">
															<tr>
																<td rowspan="2" width="25%"><img src="{'empty.jpg'|@vtiger_imageurl:$THEME}" height="60" width="61"></td>
																<td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%">
																	<span class="genHeaderSmall">
																		
																			{$APP.LBL_NO} {if $APP.$MODULE_CREATE}{$APP.$MODULE_CREATE}{else}{$MODULE_CREATE}{/if} {$APP.LBL_FOUND} !
																	</span>
																</td>
															</tr>
															<tr>
																<td class="small" align="left" nowrap="nowrap">{$APP.LBL_YOU_CAN_CREATE} {$APP.$vowel_conf}
																	
																	{if $APP.$MODULE_CREATE}
																		{$APP.$MODULE_CREATE}
																	{else}
																		{$MODULE_CREATE}
																		{/if}
																		{$APP.LBL_NOW}. {$APP.LBL_CLICK_THE_LINK}:<br>
																		&nbsp;&nbsp;-<a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}">{$APP.LBL_CREATE} {$APP.$vowel_conf} {$MOD.$MODULE_CREATE}</a>
																</td>
															</tr>
														</table>
													{else}
														<table border="0" cellpadding="5" cellspacing="0" width="98%">
															<tr>
																<td rowspan="2" width="25%"><img src="{'denied.gif'|@vtiger_imageurl:$THEME}"></td>
																<td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%"><span class="genHeaderSmall">
																{if $MODULE_CREATE eq 'SalesOrder' || $MODULE_CREATE eq 'PurchaseOrder' || $MODULE_CREATE eq 'Invoice' || $MODULE_CREATE eq 'Quotes'}
																	{$APP.LBL_NO} {$APP.$MODULE_CREATE} {$APP.LBL_FOUND} !</span></td>
																{else}
																	
																	{$APP.LBL_NO} {if $APP.$MODULE_CREATE}{$APP.$MODULE_CREATE}{else}{$MODULE_CREATE}{/if} {$APP.LBL_FOUND} !</span></td>
																{/if}
															</tr>
															<tr>
																<td class="small" align="left" nowrap="nowrap">{$APP.LBL_YOU_ARE_NOT_ALLOWED_TO_CREATE} {$APP.$vowel_conf}
																	{if $MODULE_CREATE eq 'SalesOrder' || $MODULE_CREATE eq 'PurchaseOrder' || $MODULE_CREATE eq 'Invoice' || $MODULE_CREATE eq 'Quotes'}
																		{$MOD.$MODULE_CREATE}
																	{else}
																		
																		{if $APP.$MODULE_CREATE}{$APP.$MODULE_CREATE}{else}{$MODULE_CREATE}{/if}
																	{/if}
																		<br>
																</td>
															</tr>
														</table>
													{/if}
												</div>
											</td>
										</tr>
									{/if}
								{/foreach}
						</table>
					</div>
					<!-- File list table for a folder ends -->
				</td>
			</tr>
		</table>
	</div>
	*}
