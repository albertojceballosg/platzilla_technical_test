<?php
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName       = PlatzillaUtils::purify ($_POST, 'modulename');
	$relatedListsData = PlatzillaUtils::purify ($_POST, 'relatedlists');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		}

		$mrm = ModuleRelationshipManager::getInstance ($adb);
		if (empty ($relatedListsData)) {
			$mrm->deleteRelationships ($moduleName);
		} else {
			$hasChanges   = false;
			$relatedLists = array ();
			$relatedFields = array();
			$relatedImport = array();
			$totalFields  = 0;
			foreach ($relatedListsData as $relatedListData) {
				$functionName = $relatedListData ['relatedmodulename'] == 'Documents' ? 'get_attachments' : 'get_related_list';
				$relatedList  = ModuleRelationship::getInstance ()
					->setActions ($relatedListData ['actions'])
					->setFunction ($functionName)
					->setLabel ($relatedListData ['label'])
					->setModuleName ($moduleName)
					->setPresence (ModuleRelationship::PRESENCE_VISIBLE)
					->setRelatedModuleName ($relatedListData ['relatedmodulename'])
					->setSequence (intval ($relatedListData ['sequence']));
				
				if(!empty ($relatedListData[ $relatedListData['relatedmodulename'] ]['field_label'][0])) {
					$hasChanges = true;
					$totalFields = count ($relatedListData[ $relatedListData['relatedmodulename'] ]['field_label']);
					for ($f = 0; $f < $totalFields; $f++) {
						if (!count ($relatedFields)) {
							$relatedFields = array (
								$relatedListData[ $relatedListData['relatedmodulename'] ]['field_label'][ $f ] => array (
									$relatedListData['relatedmodulename'],
									$relatedListData[ $relatedListData['relatedmodulename'] ]['field_name'][ $f ],
								)
							);
						} else {
							$relatedFieldsToMerge = array (
								$relatedListData[ $relatedListData['relatedmodulename'] ]['field_label'][ $f ] => array (
									$relatedListData['relatedmodulename'],
									$relatedListData[ $relatedListData['relatedmodulename'] ]['field_name'][ $f ],
								)
							);
							$relatedFields = array_merge ($relatedFields, $relatedFieldsToMerge);
							unset ($relatedFieldsToMerge);
						}
					}
				}
				
				if(!empty ($relatedListData[ $relatedListData['relatedmodulename'] ]['field_import'][0])) {
					$hasChanges = true;
					$totalFields = count ($relatedListData[ $relatedListData['relatedmodulename'] ]['field_import']);
					for ($f = 0; $f < $totalFields; $f++) {
						if (!count ($relatedImport)) {
							$relatedImport = array (
								$relatedListData[ $relatedListData['relatedmodulename'] ]['field_import'][ $f ] => array (
									$relatedListData[ $relatedListData['relatedmodulename'] ]['field_type'][ $f ],
									$relatedListData[ $relatedListData['relatedmodulename'] ]['field_home'][ $f ],
								)
							);
						} else {
							$relatedFieldsToMerge = array (
								$relatedListData[ $relatedListData['relatedmodulename'] ]['field_import'][ $f ] => array (
									$relatedListData[ $relatedListData['relatedmodulename'] ]['field_type'][ $f ],
									$relatedListData[ $relatedListData['relatedmodulename'] ]['field_home'][ $f ],
								)
							);
							$relatedImport = array_merge ($relatedImport, $relatedFieldsToMerge);
							unset ($relatedFieldsToMerge);
						}
					}
				}
				if (count ($relatedFields) || count ($relatedImport)) {
					$relatedList->setRelatedFields (
						ModuleRelationshipFields::getInstance ()
							->setFieldList ($relatedFields)
							->setFieldImport ($relatedImport)
							->setRelationId (null)
							->setModuleName ($moduleName)
							->setLocked (!empty ($_SESSION ['platInstancia']))
					);
					unset ($relatedFields);
					unset ($relatedImport);
				} else {
					$relatedList->setRelatedFields (null);
				}
				
				$existingRelatedList = $mrm->fetchRelationship ($moduleName, $relatedListData ['relatedmodulename'], $functionName);
				if ((!isset ($existingRelatedList)) || (!$existingRelatedList->isEqualTo ($relatedList))) {
					$hasChanges = true;
					$relatedList->setLocked (!empty ($_SESSION ['platInstancia']));
					$relatedLists [] = $relatedList;
				} else {
					$existingRelatedList->setRelatedFields ($relatedList->getRelatedFields ());
					$relatedLists [] = $existingRelatedList;
				}
			}

			if ($hasChanges) {
				$mrm->saveRelationships ($moduleName, $relatedLists);
			}
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		if ($e->getCode () == 401) {
			header ('HTTP/1.1 401 Access denied');
		} else {
			header ('HTTP/1.1 400 Bad request');
		}
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
