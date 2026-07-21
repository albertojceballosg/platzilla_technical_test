{if $MODULE eq 'Calendar'}
	<input type="hidden" name="activity_mode" value="{$ACTIVITY_MODE}" />
{elseif $MODULE eq 'Documents'}
	<form name="EditView" method="POST" ENCTYPE="multipart/form-data" action="index.php" onsubmit="VtigerJS_DialogBox.block();" role="form">
	<input type="hidden" name="max_file_size" value="{$MAX_FILE_SIZE}">
	<input type="hidden" name="form">
	<input type="hidden" name="email_id" value="{$EMAILID}">
	<input type="hidden" name="ticket_id" value="{$TICKETID}">
	<input type="hidden" name="fileid" value="{$FILEID}">
	<input type="hidden" name="old_id" value="{$OLD_ID}">
	<input type="hidden" name="parentid" value="{$PARENTID}">
{else}
	{$ERROR_MESSAGE}
	<form name="EditView" method="POST" action="index.php" ENCTYPE="multipart/form-data" onsubmit="VtigerJS_DialogBox.block();" role="form">
{/if}
<input type="hidden" name="pagenumber" value="{$smarty.request.start|@vtlib_purify}">
<input type="hidden" name="module" value="{$MODULE}">
<input type="hidden" name="record" value="{$ID}">
<input type="hidden" name="mode" value="{$MODE}">
<input type="hidden" name="action">
<input type="hidden" name="parenttab" value="{$CATEGORY}">
<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
<input type="hidden" name="return_id" value="{$RETURN_ID}">
<input type="hidden" name="return_action" value="{$RETURN_ACTION}">
<input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}">
<input type="hidden" name="createmode" value="{$CREATEMODE}" />
{if $smarty.request.frontendsid}
<input type="hidden" name="frontendsid" value="{$smarty.request.frontendsid}" />
{/if}
