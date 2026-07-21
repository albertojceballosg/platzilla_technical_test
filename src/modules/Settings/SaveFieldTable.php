<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/TableFieldHelper.class.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
	try {
		if ($isInstance) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}
		}
		
		// Get start data table field
		$blockName       = PlatzillaUtils::purify ($_POST, 'block_name');
		$blockId         = PlatzillaUtils::purify ($_POST, 'blockid');
		$dataSource      = PlatzillaUtils::purify ($_POST, 'datasource');
		$moduleData      = PlatzillaUtils::purify ($_POST, 'moduledata');
		$moduleName      = PlatzillaUtils::purify ($_POST, 'modulename');
		$sequence        = PlatzillaUtils::purify ($_POST, 'sequence');
		$tableLabel      = PlatzillaUtils::purify ($_POST, 'table_name');
		$fieldTableName  = PlatzillaUtils::purify ($_POST,'tablafieldname');
		$summaryRow      = PlatzillaUtils::purify ($_POST, 'summry');

		$buildData = array (
			'moduleName'     => $moduleName,
			'fieldTableName' => $fieldTableName,
			'moduleData'     => $moduleData['blocks'][0]['fields'],
			'activation'     => PlatzillaUtils::purify ($_POST, 'activation'),
			'appearance'     => PlatzillaUtils::purify ($_POST, 'appearance'),
			'linkages'       => PlatzillaUtils::purify ($_POST, 'linkages'),
			'summaryRow'     => $summaryRow,
			'operationRow'   => PlatzillaUtils::purify ($_POST, 'opertion'),
			'locked'         => ($isInstance) ? 1 : 0,
		);
		
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (empty ($blockName)) {
			throw new Exception ('No has suministrado el nombre del bloque');
		} else if ($sequence === null) {
			throw new Exception ('No has suministrado el bloque siguiente');
		}
		
		$bm     = BlockManager::getInstance ($adb);
		$blocks = $bm->fetchBlocks ($moduleName);
		if (empty ($blocks)) {
			$blocks = array ();
		} else if ($sequence != -1) {
			foreach ($blocks as $block) {
				if ($block->getSequence () >= $sequence) {
					$block->setSequence ($block->getSequence () + 1)
						->setLocked (!empty ($_SESSION ['platInstancia']));
				}
			}
		} else if ($sequence == -1) {
			$sequence = null;
		}
		
		$tableName    = TableFieldHelper::getVtigerTableName ($adb, $moduleName, $fieldTableName);
		
		$block = Block::getInstance ()
			->setId ($blockId)
			->setDeleted (false)
			->setIsCustom (Block::IS_CUSTOM_YES)
			->setLabel ($blockName)
			->setLocked (!empty ($_SESSION ['platInstancia']))
			->setModuleName ($moduleName)
			->setSequence ($sequence);
		
		$bm->updateBlockHeader ($block);
		
		$field = Field::getInstance ('X~O')
			->setId (null)
			->setDeleted (false)
			->setModuleName ($moduleName)
			->setColumnName ($fieldTableName)
			->setTableName ($tableName)
			->setGeneratedType ('1')
			->setUiType (FieldInterface::UI_TYPE_TABLE_FIELD)
			->setName ($fieldTableName)
			->setLabel ($tableLabel)
			->setReadOnly (1)
			->setPresence (2)
			->setDefaultValue ('')
			->setSequence (2)
			->setBlockId ($blockId)
			->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
			->setQuickCreate (1)
			->setMassEditable (1)
			->setMandatory (false)
			->setLocked (false);
		
		FieldManager::getInstance ($adb)->saveField ($field);
		
		TableFieldHelper::buildTableField ($adb, $fieldTableName, $buildData, 'UPDATE');
		TableFieldHelper::buildTableFieldData ($adb, $buildData, $tableName, $summaryRow, 'UPDATE');
		
		if ($dataSource == 'wizard') {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode ('OK');
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => 'El campo tabla ha sido creado',
			);
			header ("index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$moduleName}&return_module={$moduleName}");
		}
	} catch (Exception $e) {
		if ($dataSource == 'wizard') {
			header ('HTTP/1.1 400 Bad request');
			header ('Content-Type: application/json');
			echo json_encode ($e->getMessage ());
		}
	}
	exit();
