<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/KanbanViewManager.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	/**
	 * Archivo que se encarga de ejecutar el save and update de vista Kanban
	 * desde el configurador Kanban
	 *
	 * @var PearDatabase $adb
	 * @var string $current_user
	 */
	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	$viewId = PlatzillaUtils::purify ($_POST, 'record');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No estás autorizado a realizar acciones de configuración');
		}

		$advancedFilterGroupsData = PlatzillaUtils::purify ($_POST, 'advancedfiltergroups');
		$aplicationCode           = PlatzillaUtils::purify ($_POST, 'codeApp');
		$standardFilterData       = PlatzillaUtils::purify ($_POST, 'standardfilter');
		$fieldsIds                = PlatzillaUtils::purify ($_POST, 'fieldsIds');
		$codeElementField         = PlatzillaUtils::purify ($_POST, 'codeElementField');
		$fieldCardId              = PlatzillaUtils::purify ($_POST, 'fieldcardId');
		$fieldname                = PlatzillaUtils::purify ($_POST, 'fieldname');
		$calculationField         = PlatzillaUtils::purify ($_POST,'calculationField');
		$operations	              = PlatzillaUtils::purify ($_POST,'calculationSelect');
		$ruleBackgroundColors     = PlatzillaUtils::purify ($_POST, 'rulebackgroundcolors');
		$ruleFieldIds             = PlatzillaUtils::purify ($_POST, 'pickfieldid');
		$ruleFieldLabel           = PlatzillaUtils::purify ($_POST, 'pickfieldLabel');
		$ruleIds                  = PlatzillaUtils::purify ($_POST, 'ruleids');
		$isInstance               = !empty ($_SESSION ['platInstancia']);
		$isLocked                 = intval (PlatzillaUtils::purify ($_POST, 'locked', 0));
		$label                    = PlatzillaUtils::purify ($_POST, 'label');
		$moduleName               = PlatzillaUtils::purify ($_POST, 'modulename');
		$moduletabid              = PlatzillaUtils::purify ($_POST, 'codeElement');
		$isVisibleInList          = (PlatzillaUtils::purify ($_POST, 'isIncluded')) ? 1 : 0;
		$setDefaultKV             = (PlatzillaUtils::purify ($_POST, 'setDefault')) ? 1 : 0;

		if ($isInstance && !empty ($viewId)) {
			$viewId = ($isLocked === 1) ? $viewId : null;
		}

		$fm = FieldManager::getInstance ($adb);

		// Crear el filtro estándar
		if ((!empty ($standardFilterData ['column'])) || (!empty ($standardFilterData ['period']))) {
			$dummy     = explode (':', $standardFilterData ['column']);
			$fieldName = $dummy [2];
			$field     = $fm->fetchFieldByName ($moduleName, $fieldName);
			if (!empty ($field)) {
				$standardFilter = ViewStandardFilter::getInstance ($field)
					->setEndDate (!empty ($standardFilterData ['enddate']) ? DateTime::createFromFormat ('Y/m/d', $standardFilterData ['enddate']) : null)
					->setPeriod ($standardFilterData ['period'])
					->setStartDate (!empty ($standardFilterData ['startdate']) ? DateTime::createFromFormat ('Y/m/d', $standardFilterData ['startdate']) : null)
					->setViewId ($viewId);
			} else {
				$standardFilter = null;
			}
		} else {
			$standardFilter = null;
		}

		// Crear los filtros avanzados
		if (!empty ($advancedFilterGroupsData)) {
			$advancedFilterGroupSequence = 0;
			$advancedFilterSequence      = 0;
			$advancedFilterGroups        = array ();
			foreach ($advancedFilterGroupsData as $advancedFilterGroupData) {
				if (empty ($advancedFilterGroupData)) {
					continue;
				}
				$advancedFilters = array ();
				foreach ($advancedFilterGroupData ['filters'] as $index => $advancedFilterData) {
					if (empty ($advancedFilterData)) {
						continue;
					}
					$columnName = $advancedFilterData ['column'];
					$dummy      = explode (':', $columnName);
					$fieldName  = $dummy [2];
					$field      = $fm->fetchFieldByName ($moduleName, $fieldName);
					if (empty ($field) && $dummy [0] != 'vtiger_subfields_values') {
						continue;
					} else if ($dummy [0] != 'vtiger_subfields_values') {
						unset($dummy);
						$dummy = null;
					} else {
						$field = null;
					}
					$comparator         = $advancedFilterData ['comparator'];
					$value              = $advancedFilterData ['value'];
					$operator           = isset ($advancedFilterData ['operator']) ? $advancedFilterData ['operator'] : null;
					$advancedFilters [] = ViewAdvancedFilter::getInstance ($field, $dummy)
						->setComparator ($comparator)
						->setGroupId ($advancedFilterGroupSequence)
						->setOperator ($operator)
						->setSequence ($advancedFilterSequence)
						->setValue ($value)
						->setViewId ($viewId);
					$advancedFilterSequence++;
				}

				if (!empty ($advancedFilters)) {
					$advancedFilterGroupOperator = $advancedFilterGroupData ['operator'];
					$advancedFilterGroups []     = ViewAdvancedFilterGroup::getInstance ()
						->setFilters ($advancedFilters)
						->setOperator ($advancedFilterGroupOperator)
						->setSequence ($advancedFilterGroupSequence)
						->setViewId ($viewId);
					$advancedFilterGroupSequence++;
				}
			}
		} else {
			$advancedFilterGroups = null;
		}

		// Crear la reglas del kanban
		if (!empty ($ruleIds)) {
			$fieldObj = $fm->fetchFieldById($codeElementField);
			foreach ($ruleIds as $index => $ruleId) {
				$rules [] = KanbanFieldConfig::getInstance()
					->setBackgroundColor ($ruleBackgroundColors[ $index ])
					->setFieldName ($ruleFieldLabel[ $index ])
					->setFieldNameOperation ($calculationField[ $index ])
					->setIdPickField ($ruleFieldIds[ $index ])
					->setOperation ($operations[ $index ]);
			}
		} else {
			$rules = null;
		}

		// Crear Campos de tarjetas
		if (count ($fieldsIds) && !empty ($codeElementField)) {
			$fieldsIds [] = $codeElementField;
			foreach ($fieldsIds as $fieldId) {
				$fieldObj = $fm->fetchFieldById ($fieldId);
				$fieldsCards [] = KanbanCardConfig::getInstance()
					->setIdField ($fieldId)
					->setFieldName ($fieldObj->getName ());
			}
		} else {
			$fieldsCards = null;
		}

		$kanbanView = KanbanView::getInstance()
			->setKanbanName (KanbanViewUtils::sanitizeString ($label))
			->setIdKanban (intval ($viewId))
			->setLabel ($label)
			->setModuleName ($moduleName)
			->setIdTabModule ($moduletabid)
			->setIdField ($codeElementField)
			->setFieldName ($fieldname)
			->setCodeApplication ($aplicationCode)
			->setInListView ($isVisibleInList)
			->setDefaultView ($setDefaultKV)
			->setLocked ((!empty ($_SESSION ['platInstancia'])) ? 1 : 0)
			->setAdvancedFilterGroups ($advancedFilterGroups)
			->setStandardFilter ($standardFilter)
			->setKanbanCard ($fieldsCards)
			->setKanbanField ($rules);
		$viewKanban = KanbanViewManager::getInstance($adb)->saveKanbanView($kanbanView);

		$arguments = array (
			'kanbanviewid'  => $viewKanban->getIdKanban (),
			'mode'          => PlatzillaUtils::purify ($_POST,'mode'),
			'modulename'    => $moduleName,
			'isdefaultview' => PlatzillaUtils::purify ($_POST, 'isDefaultView'),
			'defaultKV'     => $setDefaultKV,
			'userid'        => intval ($current_user->id),
			'prevView'      => PlatzillaUtils::purify ($_POST, 'prevDefaultView'),
		);

		KanbanViewUtils::updateView ($adb, $arguments);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La vista ha sido guardada',
		);
		header("Location: index.php?module={$moduleName}&action=ListView&tab=kanban&parenttab=");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($arguments) ? $arguments : null,
		);
		$recordUriPart             = !empty ($viewId) ? "&record={$viewId}" : '';
		$moduleNameUriPart         = !empty ($moduleName) ? "&modulename={$moduleName}" : '';
		if ($isInstance) {
			header ("Location: index.php?module={$moduleName}&action=index&tab=kanban&parenttab=kanbanSettings");
		} else {
			header ("Location: index.php?module=Settings&action=KanbanViewEditView{$moduleNameUriPart}{$recordUriPart}&parenttab=Settings");
		}

	}
	exit ();
