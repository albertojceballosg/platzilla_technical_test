<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/repercusiones_prensa/lib/RepercussionUtils.class.php');

	global $adb, $currentModule, $current_user;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$mode            = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$record          = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$assignType      = isset ($_REQUEST ['assigntype']) ? vtlib_purify ($_REQUEST ['assigntype']) : null;
	$assignedUserId  = isset ($_REQUEST ['assigned_user_id']) ? vtlib_purify ($_REQUEST ['assigned_user_id']) : null;
	$assignedGroupId = isset ($_REQUEST ['assigned_group_id']) ? vtlib_purify ($_REQUEST ['assigned_group_id']) : null;
	$search          = isset ($_REQUEST ['search_url']) ? vtlib_purify ($_REQUEST ['search_url']) : '';
	$returnAction    = (isset ($_REQUEST ['return_action'])) && ($_REQUEST ['return_action'] != '') ? vtlib_purify ($_REQUEST ['return_action']) : 'DetailView';
	$returnModule    = (isset ($_REQUEST ['return_module'])) && ($_REQUEST ['return_module'] != '') ? vtlib_purify ($_REQUEST ['return_module']) : $currentModule;
	$returnId        = (isset ($_REQUEST ['return_id'])) && ($_REQUEST ['return_id'] != '') ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$urlPlatDb       = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? '&platdb=' . vtlib_purify ($_REQUEST ['platdb']) : '';
	$pageNumber      = isset ($_REQUEST ['pagenumber']) ? vtlib_purify ($_REQUEST ['pagenumber']) : 1;
	$attachments     = isset ($_REQUEST ['attachments']) ? vtlib_purify ($_REQUEST ['attachments']) : null;
	$parentTab       = getParentTab ();

	/** @var CRMEntity|stdClass $focus */
	$focus = new $currentModule ();
	setObjectValuesFromRequest ($focus);
	if ($mode) {
		$focus->mode = $mode;
	}
	if ($record) {
		$focus->id = $record;
	}
	if ($assignType == 'U') {
		$focus->column_fields ['assigned_user_id'] = $assignedUserId;
	} else if ($assignType == 'T') {
		$focus->column_fields ['assigned_user_id'] = $assignedGroupId;
	}
	$focus->save ($currentModule);

	if (isset ($attachments ['old'])) {
		RepercussionUtils::deleteAttachmentsNotInList ($adb, $record, $attachments ['old']);
	}

	if (isset ($attachments ['new'])) {
		$attachmentKeys = array_keys ($attachments ['new']['filename']);
		foreach ($attachmentKeys as $attachmentKey) {
			$fileName = $attachments ['new']['filename'][ $attachmentKey ];
			$data     = $attachments ['new']['data'][ $attachmentKey ];
			$tempFile = tempnam ('/tmp', 'attachment-');
			file_put_contents ($tempFile, base64_decode (str_replace (' ', '+', substr ($data, strpos ($data, 'base64,') + 7))));

			$_FILES ['filename']['name']     = $fileName;
			$_FILES ['filename']['size']     = filesize ($tempFile);
			$_FILES ['filename']['error']    = 0;
			$_FILES ['filename']['type']     = 'image/jpg';
			$_FILES ['filename']['tmp_name'] = $tempFile;

			/** @var Documents|stdClass $document */
			$document                                     = CRMEntity::getInstance ('Documents');
			$document->column_fields ['notes_title']      = $fileName;
			$document->column_fields ['filename']         = $fileName;
			$document->column_fields ['filesize']         = filesize ($fileName);
			$document->column_fields ['filestatus']       = 1;
			$document->column_fields ['filelocationtype'] = 'I';
			$document->column_fields ['folderid']         = 1;
			$document->column_fields ['assigned_user_id'] = $current_user->id;
			$document->parentid                           = $focus->id;
			$document->save ('Documents');

			unlink ($tempFile);
		}
	}

	if ((!$returnId) && (!$mode)) {
		$returnId = $focus->id;
	}

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&start={$pageNumber}{$search}{$urlPlatDb}");
