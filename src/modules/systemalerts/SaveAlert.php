<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/systemalerts/systemalerts.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('modules/systemalerts/lib/SystemAlertFilterUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;
	try {
		$data['codeApp']             = PlatzillaUtils::purify ($_REQUEST, 'codeApp');
		$data['titleAlert']          = PlatzillaUtils::purify ($_REQUEST, 'titleAlert');
		$data['description']         = PlatzillaUtils::purify ($_REQUEST, 'description');
		$data['codetype']            = PlatzillaUtils::purify ($_REQUEST, 'codetype');
		$data['codeElement']         = PlatzillaUtils::purify ($_REQUEST, 'codeElement');
		$data['codeElementOperator'] = PlatzillaUtils::purify ($_REQUEST, 'codeElementOperator');
		$data['codeElementValue']    = PlatzillaUtils::purify ($_REQUEST, 'codeElementValue');
		$data['scale']               = PlatzillaUtils::purify ($_REQUEST, 'scale', 'Month');
		$mode                        = PlatzillaUtils::purify ($_REQUEST, 'mode');
		$data['status']              = PlatzillaUtils::purify ($_REQUEST, 'status');
		$data['users_ids']           = PlatzillaUtils::purify ($_REQUEST, 'assigned_user_id');
		$data['locked']              = !empty ($_SESSION ['platInstancia']) ? 1 : 0;
		$htmlOutput                  = '¡Se ha creado la alerta!';
		
		if ($data['codetype'] == 'Indicators') {
			$data['boxScoreId']   = PlatzillaUtils::purify ($_REQUEST, 'boxscoreid');
			$data['datarel']      = PlatzillaUtils::purify ($_REQUEST, 'datarel');
			$data['bxdatarel']    = PlatzillaUtils::purify ($_REQUEST, 'bxdatarel');
			$data['scaledatarel'] = PlatzillaUtils::purify ($_REQUEST, 'scaledatarel');
	
			if ($mode == 'edit') {
				$htmlOutput       = '¡Se ha actualizado la alerta!';
				$systemAlertId    = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
				$systemAlertIdRel = PlatzillaUtils::purify ($_REQUEST, 'systemAlertIdRel');
				$systemAlertIds   = "'{$systemAlertId}','{$systemAlertIdRel}'";
				SystemAlertsHelper::deleteAlerts ($adb, $systemAlertId, '');
			}
			$data['scale']       = $data['scaledatarel'];
			$data['codeElement'] = $data['datarel'];
			$data['boxScoreId']  = $data['bxdatarel'];
			SystemAlertsHelper::creatingAlertIndicator ($adb, $data, '0', $data['titleAlert'], $data['codetype']);
		} else if (($data['codetype'] == 'Task_object_no_cump') || ($data['codetype'] == 'Task_prog')) {
			$data['scale']            = (empty($data['scale'])) ? 'Month' : $data['scale'];
			$filterGroupsData         = PlatzillaUtils::purify ($_POST, 'filtergroups');
			$data['codeElementName']  = PlatzillaUtils::purify ($_REQUEST, 'elementName');
			$data['codeElementLabel'] = PlatzillaUtils::purify ($_REQUEST, 'elementLabel');
			$data['fieldElementName'] = PlatzillaUtils::purify ($_REQUEST, 'fieldName');
			$data['field']            = SystemAlertsHelper::getFieldId ($adb, $data['fieldElementName']);
			$moduleName               = ($data['codetype'] == 'Task_prog') ? 'calendar' : $data['codeElementName'];
			if ($mode == 'edit') {
				$htmlOutput     = '¡Se ha actualizado la alerta!';
				$systemAlertId  = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
				$systemAlertIds = "'{$systemAlertId}'";
				SystemAlertsHelper::deleteAlerts ($adb, $systemAlertId, $moduleName);
			}
			$alertId = SystemAlertsHelper::creatingAlertModule ($adb, $data);
			
			if (!empty($filterGroupsData) && !empty($alertId)) {
				$groupId = 1;
				$groups  = array ();
				foreach ($filterGroupsData as $filterGroupData) {
					if (empty ($filterGroupData ['filters'])) {
						continue;
					}
					
					$filterId = 1;
					$filters  = array ();
					foreach ($filterGroupData ['filters'] as $filterData) {
						$filters [] = ModuleEditPermissionCondition::getInstance ()
							->setComparator ($filterData ['comparator'])
							->setFieldName ($filterData ['fieldname'])
							->setGroupId ($groupId)
							->setLabel ($filterData ['fieldname'])
							->setModuleName ($moduleName)
							->setOperator (!empty ($filterData ['operator']) ? $filterData ['operator'] : null)
							->setSequence ($filterId)
							->setValue ($filterData ['value']);
						$filterId++;
					}
					
					$groups [] = ModuleEditPermissionConditionGroup::getInstance ()
						->setId ($groupId)
						->setFilters ($filters)
						->setModuleName ($moduleName)
						->setOperator (!empty ($filterGroupData ['operator']) ? $filterGroupData ['operator'] : null);
					$groupId++;
				}
				
				SystemAlertFilterUtils::getInstance ($adb)->saveFilterGroups ($moduleName, $groups, $alertId);
			}
		}
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
		
	}

