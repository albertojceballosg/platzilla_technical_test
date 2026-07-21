<?php
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/ModuleManagerHelper.class.php');

	global $adb, $current_user;

	$moduleData = PlatzillaUtils::purify ($_POST, 'moduledata');

	try {
		ModuleManagerHelper::createModule ($adb, $moduleData, $current_user);
		create_tab_data_file ();
		create_parenttab_data_file ();
		if (count ($moduleData ['relatedlists']) && !empty ($moduleData ['name'])) {
			$mrm = ModuleRelationshipManager::getInstance ($adb);
			$hasChanges   = false;
			$relatedLists = array ();
			$sequence     = 0;
			foreach ($moduleData ['relatedlists'] as $relatedListData) {
				$sequence++;
				$functionName = $relatedListData ['modulename'] == 'Documents' ? 'get_attachments' : 'get_related_list';
				$relatedList  = ModuleRelationship::getInstance ()
					->setActions ($relatedListData ['actions'])
					->setFunction ($functionName)
					->setLabel ($relatedListData ['label'])
					->setModuleName ($moduleData ['name'])
					->setPresence (ModuleRelationship::PRESENCE_VISIBLE)
					->setRelatedModuleName ($relatedListData ['modulename'])
					->setSequence ($sequence);
				$existingRelatedList = $mrm->fetchRelationship ($moduleData ['name'], $relatedListData ['modulename'], $functionName);
				if ((!isset ($existingRelatedList)) || (!$existingRelatedList->isEqualTo ($relatedList))) {
					$hasChanges = true;
					$relatedList->setLocked (!empty ($_SESSION ['platInstancia']));
					$relatedLists [] = $relatedList;
				} else {
					$relatedLists [] = $existingRelatedList;
				}
			}

			if ($hasChanges) {
				$mrm->saveRelationships ($moduleData ['name'], $relatedLists);
			}
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		try {
			ModuleManagerHelper::deleteModule ($adb, $moduleData ['name']);
		} catch (Exception $ignored) {
			// Esto se hace para limpiar lo que hizo el creador de módulos. Como dió una falla, no importa si el limpiador falla
		}
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
