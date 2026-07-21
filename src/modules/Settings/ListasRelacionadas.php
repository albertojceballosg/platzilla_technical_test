<?php
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;

	$actionsAdd                           = SettingsUtils::purify ($_REQUEST, 'listaAccionAdd');
	$actionsAutomaticRelationship         = SettingsUtils::purify ($_REQUEST, 'listaAccionRelacionAutomatica');
	$actionsPattern                       = SettingsUtils::purify ($_REQUEST, 'listaAccionPatron');
	$actionsSelect                        = SettingsUtils::purify ($_REQUEST, 'listaAccionSelect');
	$deleteRelatedList                    = SettingsUtils::purify ($_REQUEST, 'deleteRelatedlist');
	$fieldModuleName                      = SettingsUtils::purify ($_REQUEST, 'fld_module');
	$relatedModuleLabel                   = SettingsUtils::purify ($_REQUEST, 'labelrel');
	$relatedModuleLabels                  = SettingsUtils::purify ($_REQUEST, 'labelModulos');
	$relatedModuleNames                   = SettingsUtils::purify ($_REQUEST, 'listaModulos');
	$relatedTabId                         = SettingsUtils::purify ($_REQUEST, 'related_tabid');
	$sourceAutomaticRelationshipFieldName = SettingsUtils::purify ($_REQUEST, 'campoOrigenRelacionAutomatica');
	$targetAutomaticRelationshipFieldName = SettingsUtils::purify ($_REQUEST, 'campoRelacionAutomatica');
	$tabId                                = SettingsUtils::purify ($_REQUEST, 'tabid');

	if ($deleteRelatedList == '1') {
		$sql    = getQueryRelatedlist ($tabId, $relatedTabId);
		$result = $adb->query ($sql);

		if (($result) && ($adb->num_rows ($result) > 0)) {
			echo json_encode ('relatedlist_recordsfound');
		} else {
			$function = 'get_related_list';
			if ($relatedModule->name == 'Documents') {
				$function = 'get_attachments';
			}
			if ($relatedModule->name == 'notificaciones') {
				$function = 'get_notifications';
			}
			$result = $adb->pquery (
				'SELECT
					rl.*,
					t.name AS modulename,
					rt.name AS relatedmodulename
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab t ON t.tabid=rl.tabid
					INNER JOIN vtiger_tab rt ON rt.tabid=rl.related_tabid
				WHERE
					rl.tabid=? AND
					rl.related_tabid=? AND
					rl.name=?',
				array ($tabId, $relatedTabId, $function)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$mrm = ModuleRelationshipManager::getInstance ($adb);
				$relationship = $mrm->fetchRelationship ($row ['modulename'], $row ['relatedmodulename'], $row ['name']);
				$mrm->deleteRelationship ($relationship);
			}
		}
	} else if (($relatedModuleNames) && (is_array ($relatedModuleNames))) {
		$fieldModuleId = getTabid ($fieldModuleName);
		$n             = count ($relatedModuleNames);
		$mrm = ModuleRelationshipManager::getInstance ($adb);
		for ($i = 0; $i < $n; $i++) {
			$relatedModuleId = getTabid ($relatedModuleNames [ $i ]);
			$result          = $adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND (related_tabid=? OR label=?)', array ($fieldModuleId, $relatedModuleId, $relatedModuleLabels [ $i ]));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				echo 'relatedlist_duplicate';
			} else if (!empty ($relatedModuleNames [ $i ])) {
				$function = 'get_related_list';
				if ($relatedModuleNames [ $i ] == 'Documents') {
					$function = 'get_attachments';
				}
				if ($relatedModuleNames [ $i ] == 'notificaciones') {
					$function = 'get_notifications';
				}

				$lstAction = array ();
				if ((isset ($actionsAdd [ $i ])) && (!empty ($actionsAdd [ $i ]))) {
					$lstAction [] = 'ADD';
				}
				if ((isset ($actionsSelect [ $i ])) && (!empty ($actionsSelect [ $i ]))) {
					$lstAction [] = 'SELECT';
				}

				$relationship = ModuleRelationship::getInstance ()
					->setActions ($lstAction)
					->setFunction ($function)
					->setLabel ($relatedModuleLabels [ $i ])
					->setLocked (!empty ($_SESSION ['platInstancia']))
					->setModuleName ($fieldModuleName)
					->setPresence (ModuleRelationshipInterface::PRESENCE_VISIBLE)
					->setRelatedModuleName ($relatedModuleNames [ $i ]);
				$mrm->saveRelationship ($relationship);
				echo '-';
			} else {
				echo '-';
			}
		}
	}
	exit ();
