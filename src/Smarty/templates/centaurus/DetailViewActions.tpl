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

{if $MODULE && (
	   $TODO_PERMISSION eq 'true'
	|| $EVENT_PERMISSION eq 'true'
	|| $CONTACT_PERMISSION eq 'true'
	|| ($MODULE eq 'Leads' && $CONVERTLEAD eq 'permitted')
	|| ($MODULE eq 'HelpDesk' && $CONVERTASFAQ eq 'permitted')
	|| ($MODULE eq 'Potentials' && $CONVERTINVOICE eq 'permitted')
	|| (($MODULE eq 'Leads' || $MODULE eq 'Contacts' || $MODULE eq 'Accounts') && $SENDMAILBUTTON eq 'permitted')
	|| !empty($ACCIONES_PERSONALIZADAS_MODULO)
	|| !empty($CUSTOM_LINKS.DETAILVIEW)|| !empty($CUSTOM_LINKS.DETAILVIEWBASIC)|| !empty($CUSTOM_LINKS.DETAILVIEWWIDGET)
	|| (($MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'Quotes' || $MODULE eq 'Invoice') &&
		($TAG_CLOUD_DISPLAY eq 'true' || $MERGEBUTTON eq 'permitted' || $ALLOW_EXPORT neq 'false' || !empty($HERRAMIENTAS_PERSONALIZADAS)))
	)}



		{if $ACCIONES_PERSONALIZADAS_MODULO}
			<li class="divider"></li>
			<li>
				{$ACCIONES_PERSONALIZADAS_MODULO}
			<li>
		{/if}

		{*
		{if $CUSTOM_LINKS && $CUSTOM_LINKS.DETAILVIEWBASIC}
		<li class="divider"></li>
			{foreach item=CUSTOMLINK from=$CUSTOM_LINKS.DETAILVIEWBASIC}
			<li>
					{assign var="customlink_href" value=$CUSTOMLINK->linkurl}
					{assign var="customlink_label" value=$CUSTOMLINK->linklabel}
					{if $customlink_label eq ''}
						{assign var="customlink_label" value=$customlink_href}
					{else}

						{assign var="customlink_label" value=$customlink_label|@getTranslatedString:$CUSTOMLINK->module()}
					{/if}
						<a href="{$customlink_href}" {if $CUSTOMLINK->handler_class} {$CUSTOMLINK->handler_class} {/if}>
						{if $CUSTOMLINK->linkicon}
							<i class="fa {$CUSTOMLINK->linkicon}"></i>
						{/if}
							{$customlink_label}
						</a>

			</li>
			{/foreach}
		{/if}
		*}

		{if $CUSTOM_LINKS}
			{if !empty($CUSTOM_LINKS.DETAILVIEW)}
				<li class="divider"></li>
				<li>
					<a href="javascript:;" onmouseover="fnvshobj(this,'vtlib_customLinksLay');" onclick="fnvshobj(this,'vtlib_customLinksLay');"><b>{$APP.LBL_MORE} {$APP.LBL_ACTIONS} &#187;</b></a>
					<div style="display: none; left: 193px; top: 106px;width:155px; position:absolute;" id="vtlib_customLinksLay"
						onmouseout="fninvsh('vtlib_customLinksLay')" onmouseover="fnvshNrm('vtlib_customLinksLay')">
						<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr><td style="border-bottom: 1px solid rgb(204, 204, 204); padding: 5px;"><b>{$APP.LBL_MORE} {$APP.LBL_ACTIONS} &#187;</b></td></tr>
						<tr>
							<td>
								{foreach item=CUSTOMLINK from=$CUSTOM_LINKS.DETAILVIEW}
									{assign var="customlink_href" value=$CUSTOMLINK->linkurl}
									{assign var="customlink_label" value=$CUSTOMLINK->linklabel}
									{if $customlink_label eq ''}
										{assign var="customlink_label" value=$customlink_href}
									{else}

										{assign var="customlink_label" value=$customlink_label|@getTranslatedString:$CUSTOMLINK->module()}
									{/if}
									<a href="{$customlink_href}" class="drop_down">{$customlink_label}</a>
								{/foreach}
							</td>
						</tr>
						</table>
					</div>
				</li>
			{/if}

			{if !empty($CUSTOM_LINKS.DETAILVIEWWIDGET)}
			{foreach key=CUSTOMLINK_NO item=CUSTOMLINK from=$CUSTOM_LINKS.DETAILVIEWWIDGET}
				{assign var="customlink_href" value=$CUSTOMLINK->linkurl}
				{assign var="customlink_label" value=$CUSTOMLINK->linklabel}

				{if !preg_match("/^block:\/\/.*/", $customlink_href)}
					{if $customlink_label eq ''}
						{assign var="customlink_label" value=$customlink_href}
					{else}

						{assign var="customlink_label" value=$customlink_label|@getTranslatedString:$CUSTOMLINK->module()}
					{/if}
					<li>
						<table>
							<tr>
								<td class="rightMailMergeHeader">
									<b>{$customlink_label}</b>
									{*<img id="detailview_block_{$CUSTOMLINK_NO}_indicator" style="display:none;" src="{'vtbusy.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle">*}
									<i class="fa fa-spinner fa-spin" id="detailview_block_{$CUSTOMLINK_NO}_indicator" style="display:none;"></i>
								</td>
							</tr>
							<tr style="height:25px">
								<td class="rightMailMergeContent"><div id="detailview_block_{$CUSTOMLINK_NO}"></div></td>
							</tr>
						</table>
						<script>
							vtlib_loadDetailViewWidget("{$customlink_href}", "detailview_block_{$CUSTOMLINK_NO}", "detailview_block_{$CUSTOMLINK_NO}_indicator");
						</script>
					</li>
				{/if}
			{/foreach}
			{/if}
		{/if}
		{*
		{if $MERGEBUTTON eq 'permitted'}
			<li class="divider"></li>
			<li>
				<form action="index.php" method="post" name="TemplateMerge" id="form" style="padding-left: 35px;">
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="parenttab" value="{$CATEGORY}">
					<input type="hidden" name="record" value="{$ID}">
					<input type="hidden" name="action">
					{if $TEMPLATECOUNT neq 0}
						<select name="mergefile" style="color: #707070;font-size: 0.875em;">{foreach key=templid item=tempflname from=$TOPTIONS}<option value="{$templid}">{$tempflname}</option>{/foreach}</select>
						<input class="crmbutton small create" value="{$APP.LBL_MERGE_BUTTON_LABEL}" onclick="this.form.action.value='Merge';" type="submit"></input>
					{else}
						<a href="index.php?module=Settings&action=upload&tempModule={$MODULE}&parenttab=Settings" style="color: #707070;font-size: 0.875em;">{$APP.LBL_CREATE_MERGE_TEMPLATE}</a>
					{/if}
				</form>
			</li>
		{/if}
		*}


		{if $MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'Quotes' || $MODULE eq 'Invoice'}

			{if $MODULE eq 'SalesOrder'}
				{assign var=export_pdf_action value="CreateSOPDF"}
			{else}
				{assign var=export_pdf_action value="CreatePDF"}
			{/if}
			{if $ALLOW_EXPORT neq 'false'}
				<li>
					<a href="index.php?module={$MODULE}&action={$export_pdf_action}&return_module={$MODULE}&return_action=DetailView&record={$ID}&return_id={$ID}" class="webMnu" title="{$APP.LBL_EXPORT_TO_PDF}" data-toggle="tooltip">
					<i class="fa fa-file-pdf-o"></i></a>
				</li>
			{/if}

			{if $MODULE eq 'PurchaseOrder' || $MODULE eq 'SalesOrder' || $MODULE eq 'Quotes' || $MODULE eq 'Invoice'}
			<!-- Added to give link to  send Invoice PDF through mail -->
				{if $ALLOW_EXPORT neq 'false'}
				<li>
					<a href="javascript: document.DetailView.return_module.value='{$MODULE}'; document.DetailView.return_action.value='DetailView'; document.DetailView.module.value='{$MODULE}'; document.DetailView.action.value='SendPDFMail'; document.DetailView.record.value='{$ID}'; document.DetailView.return_id.value='{$ID}'; sendpdf_submit();" class="webMnu" title="{$APP.LBL_SEND_EMAIL_PDF}" data-toggle="tooltip">
					<i class="fa fa-file-pdf-o"></i></a>
				</li>
				{/if}
			{/if}
			{if $HERRAMIENTAS_PERSONALIZADAS}
			<li>
				<table>
					{$HERRAMIENTAS_PERSONALIZADAS}
				</table>
			</li>
			{/if}
		{/if}
		{if $TODO_PERMISSION eq 'true' || $EVENT_PERMISSION eq 'true' || $CONTACT_PERMISSION eq 'true'|| $MODULE eq 'Contacts' || ($MODULE eq 'Documents')}
			{if $MODULE eq 'Contacts'}
				{assign var=subst value="contact_id"}
				{assign var=acc value="&account_id=$accountid"}
			{else}
				{assign var=subst value="parent_id"}
				{assign var=acc value=""}
			{/if}




			{if $MODULE eq 'Contacts' || $EVENT_PERMISSION eq 'true'}
				<li>
					<a href="index.php?module=Calendar&action=EditView&return_module={$MODULE}&return_action=DetailView&activity_mode=Events&return_id={$ID}&{$subst}={$ID}{$acc}&parenttab={$CATEGORY}" class="webMnu" title="{$APP.LBL_ADD_NEW} {$APP.Event}" data-toggle="tooltip">
						<i class="fa fa-calendar"></i>
					</a>
				</li>
			{/if}

			{if $TODO_PERMISSION eq 'true' && ($MODULE eq 'Accounts' || $MODULE eq 'Leads')}

			{/if}

			{if $MODULE eq 'Contacts' && $CONTACT_PERMISSION eq 'true'}
				<li>
					<a href="index.php?module=Calendar&action=EditView&return_module={$MODULE}&return_action=DetailView&activity_mode=Task&return_id={$ID}&{$subst}={$ID}{$acc}&parenttab={$CATEGORY}" class="webMnu" title="{$APP.LBL_ADD_NEW} {$APP.Todo}" data-toggle="tooltip">
						<i class="fa fa-calendar"></i>
					</a>
				</li>
			{/if}

			<!-- Start: Actions for Documents Module -->
			{if $MODULE eq 'Documents'}
				<li>
					{if $DLD_TYPE eq 'I' && $FILE_STATUS eq '1' && $FILE_EXIST eq 'yes'}
						<a href="index.php?module=uploads&action=downloadfile&fileid={$FILEID}&entityid={$NOTESID}"  onclick="javascript:dldCntIncrease({$NOTESID});" class="webMnu" title="{$MOD.LBL_DOWNLOAD_FILE}" data-toggle="tooltip">
							<i class="fa fa-cloud-download"></i>
						</a>
					{elseif $DLD_TYPE eq 'E' && $FILE_STATUS eq '1'}
						<a target="_blank" href="{$DLD_PATH}" onclick="javascript:dldCntIncrease({$NOTESID});" title="{$MOD.LBL_DOWNLOAD_FILE}" data-toggle="tooltip">
							<i class="fa fa-cloud-download"></i>
						</a>
					{/if}
				</li>
				{if $CHECK_INTEGRITY_PERMISSION eq 'yes'}
					<li>
						<a href="javascript:;" onClick="checkFileIntegrityDetailView({$NOTESID});" title="{$MOD.LBL_CHECK_INTEGRITY}" data-toggle="tooltip">
							<i class="fa fa-check-circle fa-fw fa-lg green"></i>
						</a>&nbsp;
						<input type="hidden" id="dldfilename" name="dldfilename" value="{$FILEID}-{$FILENAME}">
						<span id="vtbusy_integrity_info" style="display:none;"><i class="fa fa-spinner fa-spin"></i></span>
						<span id="integrity_result" style="display:none"></span>
					</li>
				{/if}
			{/if}

		{/if}

{/if}


{literal}
<script type='text/javascript'>
function sendpdf_submit()
{
	// Submit the form to get the attachment ready for submission
	document.DetailView.submit();
{/literal}

	{if $MODULE eq 'Invoice'}
		OpenCompose('{$INV_NO}','Invoice');
	{elseif $MODULE eq 'Quotes'}
		OpenCompose('{$QUO_NO}','Quote');
	{elseif $MODULE eq 'PurchaseOrder'}
		OpenCompose('{$PO_NO}','PurchaseOrder');
	{elseif $MODULE eq 'SalesOrder'}
		OpenCompose('{$SO_NO}','SalesOrder');
	{/if}
{literal}
}
</script>
{/literal}