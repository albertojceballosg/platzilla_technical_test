<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/AddGridFieldsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;

	$fieldId            = SettingsUtils::purify ($_REQUEST, 'fieldId');
	$fieldName          = SettingsUtils::purify ($_REQUEST, 'fieldname');
	$moduleName         = SettingsUtils::purify ($_REQUEST, 'modulename');
	$idSelectedReg      = SettingsUtils::purify ($_REQUEST, 'idEditSelected');
	$listSelected       = SettingsUtils::purify ($_REQUEST, 'listaCampo');
	$listSelectedActual = SettingsUtils::purify ($_REQUEST, 'actualLista');

	if (empty($listSelected)) {
		$listSelected = $listSelectedActual;
	}

	$fieldToColorId  = SettingsUtils::purify ($_REQUEST, 'fieldToColor');
	$fieldToFilterId = SettingsUtils::purify ($_REQUEST, 'fieldToFilter');
	$fieldToColor    = str_replace ('_' . $fieldId, '', $fieldToColorId);
	$fieldToFilter   = str_replace ('_' . $fieldId, '', $fieldToFilterId);

	$fieldsDataFilter = array (
		'joinCondition' => SettingsUtils::purify ($_REQUEST, 'joinCondition'),
		'selectedColor' => SettingsUtils::purify ($_REQUEST, 'selectedColor'),
		'fieldToFilter' => $fieldToFilter,
		'fieldToColor'  => $fieldToColor,
		'actionFilter'  => SettingsUtils::purify ($_REQUEST, 'actionFilter'),
		'selectedValue' => SettingsUtils::purify ($_REQUEST, 'selectedValue'),
	);

	$nombreCampo      = SettingsUtils::purify ($_REQUEST, 'nombreCampo');
	$destinationField = SettingsUtils::purify ($_REQUEST, 'destinationField');
	$checkFieldDest   = SettingsUtils::purify ($_REQUEST, 'checkFieldDest');
	$selectName       = SettingsUtils::purify ($_REQUEST, 'selectNameField');
	$checkName        = SettingsUtils::purify ($_REQUEST, 'checkNameField');
	$fieldSummary     = SettingsUtils::purify ($_REQUEST, 'summaryField');
	$columnsToImport  = SettingsUtils::purify ($_REQUEST, 'columnRS');
	$modulesReference = explode (';',SettingsUtils::purify ($_REQUEST, 'importModuleReference'));
	$fieldToImport    = array ();

	foreach ($modulesReference as $moduleRef) {
		foreach ($columnsToImport [ $moduleRef ] as $columnToImport) {
			$fieldToImport [ $moduleRef ] [] = str_replace ('_' . $fieldId, '', $columnToImport);
		}
	}

	$checkDestination = str_replace ('_' . $fieldId, '', $checkFieldDest);
	$fieldDestination = str_replace ('_' . $fieldId, '', $destinationField);
	$fieldNames       = str_replace ('_' . $fieldId, '', $nombreCampo);
	$selectNameField  = str_replace ('_' . $fieldId, '', $selectName);
	$checkNameField   = str_replace ('_' . $fieldId, '', $checkName);
	$summaryField     = str_replace ('_' . $fieldId, '', $fieldSummary);

	$fieldsData = array (
		'labels'             => SettingsUtils::purify ($_REQUEST, 'etiquetaCampo'),
		'lengths'            => SettingsUtils::purify ($_REQUEST, 'tamanoCampo'),
		'modules'            => SettingsUtils::purify ($_REQUEST, 'moduloCampo'),
		'listSelected'       => $listSelected,
		'names'              => $fieldNames,
		'precisions'         => SettingsUtils::purify ($_REQUEST, 'precisionCampo'),
		'types'              => SettingsUtils::purify ($_REQUEST, 'tipoCampo'),
		'values'             => SettingsUtils::purify ($_REQUEST, 'valoresCampo'),
		'fieldImportId'      => SettingsUtils::purify ($_REQUEST, 'fieldImportId'),
		'fieldImportRegId'   => SettingsUtils::purify ($_REQUEST, 'fieldImportRegId'),
		'subfieldsid'        => SettingsUtils::purify ($_REQUEST, 'subfieldsid'),
		'sequence'           => SettingsUtils::purify ($_REQUEST, 'fieldSequence'),
		'fieldToColor'       => SettingsUtils::purify ($_REQUEST, 'fieldToColor'),
		'checkField'         => SettingsUtils::purify ($_REQUEST, 'checkField'),
		'checkValue'         => SettingsUtils::purify ($_REQUEST, 'checkValue'),
		'checkFieldDest'     => $checkDestination,
		'checkNameField'     => $checkNameField,
		'selectNameField'    => $selectNameField,
		'selectField'        => SettingsUtils::purify ($_REQUEST, 'selectField'),
		'selectValue'        => SettingsUtils::purify ($_REQUEST, 'selectValue'),
		'actionFieldId'      => SettingsUtils::purify ($_REQUEST, 'actionFieldId'),
		'destinationField'   => $fieldDestination,
		'calculatedSystemId' => SettingsUtils::purify ($_REQUEST, 'calculatedSystemId'),
		'summaryActionField' => SettingsUtils::purify ($_REQUEST, 'summaryActionField'),
		'summaryField'       => $summaryField,
		'fieldToExport'      => SettingsUtils::purify ($_REQUEST, 'moduleRS'),
		'fieldToImport'      => $fieldToImport,
		'moduleReference'    => $modulesReference,
		'moduleName'         => $moduleName,
		'defaultValues'      => SettingsUtils::purify ($_REQUEST, 'valorCampo'),
	);

	$responseMessage = '';
	$listSubField    = obtieneListaSubCamposCampoGrid ($fieldId, false, false);

	foreach ($listSubField as $rowField) {
		if ((!in_array ($rowField['subfieldsid'], $fieldsData['subfieldsid'])) && ($rowField['uitype'] != 2203)) {
			$adb->pquery ('DELETE FROM vtiger_subfields_special WHERE subfieldsid=?', array ($rowField['subfieldsid']));
			$responseMessage .= '- Se ha eliminado la columna ' . $rowField['label'] . count ($summaryField) . '@';
		} else if (($rowField['uitype'] == 2203) && (empty($summaryField))) {
			$adb->pquery ('DELETE FROM vtiger_subfields_special WHERE subfieldsid=?', array ($rowField['subfieldsid']));
			$fieldsData ['summaryField'] = array ();
			$responseMessage .= '- Se ha eliminado la columna ' . $rowField['label'] . '@';
		}
	}

	$responseMessage .= AddGridFieldsHelper::updateDataGrid ($adb, $fieldsData, $fieldId, $idSelectedReg, $fieldsDataFilter);

	if (!empty($responseMessage)) {
		echo 'Acciones realizados:@' . $responseMessage;
	} else {
		echo 'La tabla no ha sido actualizada.@';
	}
