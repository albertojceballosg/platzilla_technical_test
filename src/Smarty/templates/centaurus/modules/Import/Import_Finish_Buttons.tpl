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

<div class="col-lg-12">
	<div class="pull-right">
		<input type="button" name="next" value="{'LBL_IMPORT_MORE'|@getTranslatedString:$MODULE}" class="btn btn-success btn-mini"
			   onclick="location.href='index.php?module={$FOR_MODULE}&action=Import&return_module={$FOR_MODULE}&return_action=index'" />
		&nbsp;&nbsp;
		{if $IMPORT_RESULT.CREATED > 0}
			<a data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload"
			   data-title="{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@getTranslatedString:$MODULE}"
			   href="index.php?module={$FOR_MODULE}&action=Import&mode=listview&start=1&foruser={$OWNER_ID}&Ajax=true"
			   class="btn btn-success btn-mini"
			   title="{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@getTranslatedString:$MODULE}">
				{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@getTranslatedString:$MODULE}
			</a>
		{* <input type="button" name="next" value="{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@getTranslatedString:$MODULE}" class="btn btn-success btn-mini"
			   onclick="return window.open('index.php?module={$FOR_MODULE}&action={$FOR_MODULE}Ajax&file=Import&mode=listview&start=1&foruser={$OWNER_ID}','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');" />
		*}
		{/if}
		&nbsp;
		{if $IMPORT_RESULT.SKIPPED > 0}
			<a data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload"
			   data-title="{'LBL_VIEW_LAST_SKIPPED_RECORDS'|@getTranslatedString:$MODULE}"
			   href="index.php?module={$FOR_MODULE}&action=Import&mode=discarded_records&start=1&foruser={$OWNER_ID}&pdf=no&Ajax=true"
			   class="btn btn-danger btn-mini"
			   title="{'LBL_VIEW_LAST_SKIPPED_RECORDS'|@getTranslatedString:$MODULE}">
				{'LBL_VIEW_LAST_SKIPPED_RECORDS'|@getTranslatedString:$MODULE}
			</a>
			{*
			<input type="button" name="next" value="{'LBL_VIEW_LAST_SKIPPED_RECORDS'|@getTranslatedString:$MODULE}" class="btn btn-success btn-mini"
						   onclick="return window.open('index.php?module={$FOR_MODULE}&action={$FOR_MODULE}Ajax&file=Import&mode=listview&start=1&foruser={$OWNER_ID}','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');" />
			*}
			&nbsp;&nbsp;
		{/if}
		{if $MERGE_ENABLED eq '0'}
		<input type="button" name="next" value="{'LBL_UNDO_LAST_IMPORT'|@getTranslatedString:$MODULE}" class="btn btn-warning btn-mini"
			   onclick="location.href='index.php?module={$FOR_MODULE}&action=Import&mode=undo_import&foruser={$OWNER_ID}'" />
		&nbsp;&nbsp;
		{/if}
		<input type="button" name="cancel" value="{'LBL_FINISH_BUTTON_LABEL'|@getTranslatedString:$MODULE}" class="btn btn-success btn-mini"
			   onclick="location.href='index.php?module={$FOR_MODULE}&action=index'" />
	   
	</div>
</div>