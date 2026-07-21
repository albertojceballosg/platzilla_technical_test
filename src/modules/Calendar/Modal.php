<?php
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/Pagination.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;

	$keywordField         = PlatzillaUtils::purify ($_GET, 'field');
	$keywordValue         = PlatzillaUtils::purify ($_GET, 'keyword');
	$forFieldName         = PlatzillaUtils::purify ($_GET, 'forfieldname');
	$forModuleName        = PlatzillaUtils::purify ($_GET, 'formodulename');
	$page                 = PlatzillaUtils::purify ($_GET, 'page');
	$requestedFieldValues = PlatzillaUtils::purify ($_GET, 'requestedfiltervalues');

	$recordsPerPage = 25;

	try {
		$thisModule = ModuleManager::getInstance ($adb)->fetchModule ($currentModule, true);
		if ((empty ($thisModule)) || (!in_array ($thisModule->getPresence (), array (Module::PRESENCE_USER_DEFINED, Module::PRESENCE_VISIBLE)))) {
			throw new Exception ('El módulo solicitado no está instalado');
		}

		if ((empty ($page)) || ($page <= 0)) {
			$startRecord = 0;
		} else {
			$startRecord = (($page - 1) * $recordsPerPage);
		}

		$query  = DataViewUtils::buildDefaultModalViewQueryParts ($adb, $currentModule, $forModuleName, $forFieldName, $keywordField, $keywordValue, $requestedFieldValues, $current_user);
		$result = $adb->query (
			"SELECT
				{$query ['select']},
				total.__total_records__
			FROM
				{$query ['from']}
				CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']} WHERE {$query ['where']}) AS total
			WHERE
				{$query ['where']}
			LIMIT {$startRecord}, {$recordsPerPage}"
		);
		if ($adb->num_rows ($result) > 0) {
			$startRecord++;
			$totalRecords = null;
			$records      = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if ($totalRecords === null) {
					$totalRecords = intval ($row ['__total_records__']);
				}
				$fieldNames = array_keys ($row);
				foreach ($fieldNames as $fieldName) {
					if ($row [ $fieldName ] === null) {
						$row [ $fieldName ] = '';
					}
				}
				unset ($row ['__total_records__']);
				$row ['activitytype'] = getTranslatedString ($row ['activitytype'], $currentModule);
				$row ['taskstatus']   = getTranslatedString ($row ['taskstatus'], $currentModule);
				$row ['eventstatus']  = getTranslatedString ($row ['eventstatus'], $currentModule);
				$records []           = $row;
			}
			$endRecord  = count ($records);
			$totalPages = ceil ($totalRecords / $recordsPerPage);
		} else {
			$totalRecords = 0;
			$records      = null;
			$endRecord    = 0;
			$totalPages   = 0;
		}
		DatabaseUtils::closeResult ($result);
		$result = null;
		$paginator       = Pagination::getInstance();
		$paginatorConfig = array (
			'totalRows'       => $totalRecords,
			'perPage'         => $recordsPerPage,
			'numLinks'        => 5,
			'attributes'      => array('class' => 'linkPag', 'onclick' => 'RelatedModuleModalUtils.goToPage (event, this);'),
			'firstTagOpen'    => "<li class='Pages'>",
			'firstTagClose'   => '</li>',
			'lastTagOpen'     => "<li class='Pages'>",
			'lastTagClose'    => '</li>',
			'currentTagOpen'  => "<li class='Pages'><a href='#'><strong>",
			'currentTagClose' => '</strong></a></li>',
			'numTagOpen'      => "<li class='Pages'>",
			'numTagClose'     => '</li>',
			'prevTagOpen'     => "<li class='Pages'>",
			'prevTagClose'    => '</li>',
			'nextTagOpen'     => "<li class='Pages'>",
			'nextTagClose'    => '</li>',
		);

		$paginator->initialize ($paginatorConfig);

		$result = $adb->pquery (
			"SELECT
				fmrr.*,
				f.tablename AS fieldtablename,
				f.columnname AS fieldcolumnname,
				frel.tablename AS referencedfieldtablename,
				frel.columnname AS referencedfieldcolumnname
			FROM
				vtiger_fieldmodulerel_relationships fmrr
				INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldpk=fmrr.referenceid AND fmr.module=? AND fmr.relmodule=?
				INNER JOIN vtiger_field ffmr ON ffmr.fieldid=fmr.fieldid AND ffmr.fieldname=?
				INNER JOIN vtiger_field f ON f.fieldname=fmrr.fieldname AND f.tabid IN (SELECT tabid FROM vtiger_tab WHERE name=fmr.module)
				INNER JOIN vtiger_field frel ON frel.fieldname=fmrr.relfieldname AND frel.tabid IN (SELECT tabid FROM vtiger_tab WHERE name=fmr.relmodule)",
			array ($forModuleName, $currentModule, $forFieldName)
		);
		if ($adb->num_rows ($result) > 0) {
			$relationships = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$relationships [ $row ['relfieldname'] ] = $row ['fieldname'];
			}
		} else {
			$relationships = null;
		}
		DatabaseUtils::closeResult ($result);
		$result = null;

		$data = array (
			'startRecord'   => $startRecord,
			'endRecord'     => $endRecord,
			'totalRecords'  => $totalRecords,
			'page'          => empty ($page) ? 1 : intval ($page),
			'totalPages'    => $totalPages,
			'records'       => $records,
			'fields'        => $query ['fields'],
			'relationships' => $relationships,
			'pagination'    => $paginator->createLinks (),
		);
	} catch (Exception $e) {
		$applications = ApplicationManager::getInstance ($masterAdb)->fetchApplicationHeadersByModuleName ($currentModule);
		if (!empty ($applications)) {
			$applicationNames = array ();
			foreach ($applications as $application) {
				$applicationNames [] = $application->getName ();
			}
		} else {
			$applicationNames = null;
		}
		$data = array (
			'startRecord'   => 0,
			'endRecord'     => 0,
			'totalRecords'  => 0,
			'page'          => 1,
			'totalPages'    => 0,
			'records'       => 0,
			'applications'  => $applicationNames,
			'fields'        => null,
			'relationships' => null,
		);
	}
	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();
