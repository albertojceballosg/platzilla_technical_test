<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $adb, $currentModule;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$mode                 = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$record               = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$assignType           = isset ($_REQUEST ['assigntype']) ? vtlib_purify ($_REQUEST ['assigntype']) : null;
	$assignedUserId       = isset ($_REQUEST ['assigned_user_id']) ? vtlib_purify ($_REQUEST ['assigned_user_id']) : null;
	$assignedGroupId      = isset ($_REQUEST ['assigned_group_id']) ? vtlib_purify ($_REQUEST ['assigned_group_id']) : null;
	$search               = isset ($_REQUEST ['search_url']) ? vtlib_purify ($_REQUEST ['search_url']) : '';
	$relatedNotesFolders  = isset ($_REQUEST ['relatednotesfolders']) ? vtlib_purify ($_REQUEST ['relatednotesfolders']) : null;
	$relatedNotesIds      = isset ($_REQUEST ['relatednotesids']) ? vtlib_purify ($_REQUEST ['relatednotesids']) : null;
	$relatedNotesSubjects = isset ($_REQUEST ['relatednotessubjects']) ? vtlib_purify ($_REQUEST ['relatednotessubjects']) : null;
	$returnAction         = (isset ($_REQUEST ['return_action'])) && ($_REQUEST ['return_action'] != '') ? vtlib_purify ($_REQUEST ['return_action']) : 'DetailView';
	$returnModule         = (isset ($_REQUEST ['return_module'])) && ($_REQUEST ['return_module'] != '') ? vtlib_purify ($_REQUEST ['return_module']) : $currentModule;
	$returnId             = (isset ($_REQUEST ['return_id'])) && ($_REQUEST ['return_id'] != '') ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$urlPlatDb            = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? '&platdb=' . vtlib_purify ($_REQUEST ['platdb']) : '';
	$pageNumber           = isset ($_REQUEST ['pagenumber']) ? vtlib_purify ($_REQUEST ['pagenumber']) : 1;
	$parentTab            = getParentTab ();

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

	$noteIds = array ();
	$result  = $adb->pquery (
		'SELECT
				n.notesid
			FROM
				vtiger_notes n
				INNER JOIN vtiger_senotesrel nr ON nr.notesid=n.notesid
				INNER JOIN vtiger_crmentity crmen on crmen.crmid=n.notesid
			WHERE
				crmen.deleted=0 AND
				nr.crmid=?',
		array ($focus->id)
	);
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$noteIds [] = $row ['notesid'];
		}
	}

	$focus->save ($currentModule);

	$keepNoteIds = array ();
	$files = $_FILES;
	if (!empty ($relatedNotesIds)) {
		foreach ($relatedNotesIds as $relatedNotesId) {
			if ($relatedNotesId) {
				$keepNoteIds [] = $relatedNotesId;
			}
		}

		$deleteNoteIds = array_diff ($noteIds, $keepNoteIds);
	} else {
		$deleteNoteIds = $noteIds;
	}

	if (!empty ($deleteNoteIds)) {
		$focus->delete_related_module (null, $focus->id, 'Documents', $deleteNoteIds);
	}

	if (!empty ($relatedNotesIds)) {
		$fileIndex = 0;
		foreach ($relatedNotesIds as $relatedNotesId) {
			if (!$relatedNotesId) {
				$_FILES ['relatednotesfiles']['name']     = $files ['relatednotesfiles']['name'][ $fileIndex ];
				$_FILES ['relatednotesfiles']['size']     = $files ['relatednotesfiles']['size'][ $fileIndex ];
				$_FILES ['relatednotesfiles']['error']    = $files ['relatednotesfiles']['error'][ $fileIndex ];
				$_FILES ['relatednotesfiles']['type']     = $files ['relatednotesfiles']['type'][ $fileIndex ];
				$_FILES ['relatednotesfiles']['tmp_name'] = $files ['relatednotesfiles']['tmp_name'][ $fileIndex ];

				/** @var Documents|stdClass $document */
				$document                                     = CRMEntity::getInstance ('Documents');
				$document->column_fields ['notes_title']      = $relatedNotesSubjects [ $fileIndex ];
				$document->column_fields ['filename']         = $files ['relatednotesfiles']['name'][ $fileIndex ];
				$document->column_fields ['filesize']         = $files ['relatednotesfiles']['size'][ $fileIndex ];
				$document->column_fields ['filestatus']       = 1;
				$document->column_fields ['filelocationtype'] = 'I';
				$document->column_fields ['folderid']         = $relatedNotesFolders [ $fileIndex ];
				$document->column_fields ['assigned_user_id'] = $assignedUserId;
				$document->parentid                           = $focus->id;
				$document->save ('Documents');
				$fileIndex++;
			}
		}
	}

	if ((!$returnId) && (!$mode)) {
		$returnId = $focus->id;
	}

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&start={$pageNumber}{$search}{$urlPlatDb}");
