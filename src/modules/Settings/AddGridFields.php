<?php
	require_once ('modules/Settings/lib/AddGridFieldsHelper.class.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;

	$moduleName = SettingsUtils::purify ($_REQUEST, 'fldmodule');
	$gridLabel  = SettingsUtils::purify ($_REQUEST, 'etiquetaGrid');
	$idSelectedReg  = SettingsUtils::purify ($_REQUEST, 'idSelected');

	$fieldsData = array (
		'labels'             => SettingsUtils::purify ($_REQUEST, 'etiquetaCampo'),
		'lengths'            => SettingsUtils::purify ($_REQUEST, 'tamanoCampo'),
		'modules'            => SettingsUtils::purify ($_REQUEST, 'moduloCampo'),
		'names'              => SettingsUtils::purify ($_REQUEST, 'nombreCampo'),
		'precisions'         => SettingsUtils::purify ($_REQUEST, 'precisionCampo'),
		'types'              => SettingsUtils::purify ($_REQUEST, 'tipoCampo'),
		'values'             => SettingsUtils::purify ($_REQUEST, 'valoresCampo'),
		'column'             => SettingsUtils::purify ($_REQUEST, 'column'),
		'selectNameField'    => SettingsUtils::purify ($_REQUEST, 'selectNameField'),
		'fieldImportId'      => SettingsUtils::purify ($_REQUEST, 'fieldImportId'),
		'selectValue'        => SettingsUtils::purify ($_REQUEST, 'selectValue'),
		'actionFieldId'      => SettingsUtils::purify ($_REQUEST, 'actionFieldId'),
		'destinationField'   => SettingsUtils::purify ($_REQUEST, 'destinationField'),
		'checkNameField'     => SettingsUtils::purify ($_REQUEST, 'checkNameField'),
		'checkValue'         => SettingsUtils::purify ($_REQUEST, 'checkValue'),
		'checkFieldDest'     => SettingsUtils::purify ($_REQUEST, 'checkFieldDest'),
		'listSelected'       => SettingsUtils::purify ($_REQUEST, 'listaCampo'),
		'joinCondition'      => SettingsUtils::purify ($_REQUEST, 'joinCondition'),
		'selectedColor'      => SettingsUtils::purify ($_REQUEST, 'selectedColor'),
		'fieldToFilter'      => SettingsUtils::purify ($_REQUEST, 'fieldToFilter'),
		'fieldToColor'       => SettingsUtils::purify ($_REQUEST, 'fieldToColor'),
		'actionFilter'       => SettingsUtils::purify ($_REQUEST, 'actionFilter'),
		'selectedValue'      => SettingsUtils::purify ($_REQUEST, 'selectedValue'),
		'calculatedSystemId' => SettingsUtils::purify ($_REQUEST, 'calculatedSystemId'),
		'summaryActionField' => SettingsUtils::purify ($_REQUEST, 'summaryActionField'),
		'summaryField'       => SettingsUtils::purify ($_REQUEST, 'summaryField'),
		'fieldToExport'      => SettingsUtils::purify ($_REQUEST, 'moduleRS'),
		'fieldToImport'      => SettingsUtils::purify ($_REQUEST, 'columnRS'),
		'moduleReference'    => SettingsUtils::purify ($_REQUEST, 'importModuleReference'),
	);

	try {
		$isInstance = !empty ($_SESSION ['platInstancia']);
		$afterBlock = LayoutBlockListHelper::getModuleBlocks ($adb, $moduleName);
		$duplicate = LayoutBlockListHelper::addBlock (
			$adb,
			array (
				'blocklabel'      => $gridLabel,
				'blocktype'       => 0,
				'fieldmodulename' => $moduleName,
				'iscustom'        => 1,
				'previousblockid' => $afterBlock[0]['blockid'],
			),
			$isInstance
		);

		$result = $adb->query ('SELECT IFNULL(id, 0) AS id FROM vtiger_blocks_seq');
		if(!$result) {
			$blockId = -1;
		} else {
			$row = $adb->fetchByAssoc ($result);
			$blockId = $row ['id'];
		}

		AddGridFieldsHelper::setGridField ($adb, $moduleName, $gridLabel, $blockId, $fieldsData,$idSelectedReg);
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
