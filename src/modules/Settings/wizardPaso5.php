<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/platzilla/Managers/PlatformFreeBillingPlanLimitManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/HtmlGenerator.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');

	global $adb, $current_user, $platPrincipal;

	$protectedVariables = array ('module', 'action', 'Ajax', 'plat');
	$keys               = array_keys ($_POST);
	foreach ($keys as $key) {
		if (!in_array ($key, $protectedVariables)) {
			$_SESSION [ $key ] = SettingsUtils::purify ($_POST, $key);
		}
	}

	foreach ($_SESSION ['columnasFiltro'] as $key => $value) {
		if ($value === 'Seleccionar') {
			unset ($_SESSION ['columnasFiltro'][ $key ]);
		}
	}

	if ((!is_admin ($current_user)) && (isset ($_SESSION ['plat']))) {
		$platform = SettingsUtils::purify ($_SESSION, 'plat');
	} else {
		$platform = '';
	}

	$mm            = ModuleManager::getInstance ($adb);
	$newModuleName = strtolower ($_SESSION ['nombreCodigo']);
	if ((isset ($_SESSION ['duplicar'])) && (strtolower ($_SESSION ['duplicar']) == 'duplicar')) {
		$oldModule = $mm->fetchModuleById ($_SESSION ['moduloaduplicar']);
		$newModule = $mm->duplicateModule ($oldModule, $newModuleName, $_SESSION ['nombrePublico'], $_SESSION ['moduloPadre']);
		create_tab_data_file ();
		$executedAction = 'duplicado';
	} else {
		$allFields   = array ();
		$blocks      = array ();
		$totalBlocks = count ($_SESSION ['nombreBloque']);
		for ($i = 0; $i < $totalBlocks; $i++) {
			$blockSequence = intval ($_SESSION ['numeroBloque'][ $i ]);
			$fields        = array ();
			$totalFields   = count ($_SESSION ['nombreCampo']);
			$fieldSequence = 1;
			for ($j = 0; $j < $totalFields; $j++) {
				if ($_SESSION ['numeroBloqueCampo'][ $j ] != $blockSequence) {
					continue;
				}

				$uiType = $_SESSION ['tipoCampo'][ $j ];
				if (in_array ($uiType, array (Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE))) {
					$references = array (FieldModuleReference::getInstance ()->setFieldName ($_SESSION ['nombreCampo'][ $j ])->setModuleName ($newModuleName)->setReferencedModuleName ($_SESSION ['moduloCampo'][ $j ])->setSequence (1));
				} else {
					$references = null;
				}

				if (in_array ($uiType, array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
					$rawValues      = explode ("\n", $_SESSION ['valoresCampo'][ $j ]);
					$picklistValues = array ();
					foreach ($rawValues as $rawValue) {
						$picklistValues [] = PicklistValue::getInstance ()
							->setPresence (PicklistValue::PRESENCE_VISIBLE)
							->setValue ($rawValue);
					}
					$picklist = Picklist::getInstance ()
						->setName ($_SESSION ['nombreCampo'][ $j ])
						->setValues ($picklistValues);
				} else {
					$picklist = null;
				}


				$length    = !empty ($_SESSION ['tamanoCampo'][ $j ]) ? $_SESSION ['tamanoCampo'][ $j ] : null;
				$precision = !empty ($_SESSION ['precisionCampo'][ $j ]) ? $_SESSION ['precisionCampo'][ $j ] : null;

				$field                                         = Field::getInstance ()
					->setColumnName ($_SESSION ['nombreCampo'][ $j ])
					->setDisplayType (Field::DISPLAY_TYPE_ALL)
					->setGeneratedType (Field::GENERATED_TYPE_CUSTOM)
					->setLabel ($_SESSION ['etiquetaCampo'][ $j ])
					->setMandatory (false)
					->setMassEditable (Field::MASS_EDITABLE_USER_DEFINED)
					->setModuleName ($newModuleName)
					->setModuleReferences ($references)
					->setName ($_SESSION ['nombreCampo'][ $j ])
					->setPicklist ($picklist)
					->setPresence (Field::PRESENCE_USER_DEFINED)
					->setQuickCreate (Field::QUICK_CREATE_ENABLED)
					->setReadOnly (Field::READ_WRITE)
					->setQuickCreateSequence ($j)
					->setSequence ($fieldSequence)
					->setUiType ($uiType, $length, $precision);
				$fields []                                     = $field;
				$allFields [ $_SESSION ['nombreCampo'][ $j ] ] = $field;
				$fieldSequence++;
			}
			$visibility = $_SESSION ['visibilidadBloque'][ $i ] == 1 ? Block::VISIBILITY_VISIBLE : Block::VISIBILITY_HIDDEN;
			$blocks []  = Block::getInstance ()
				->setFields ($fields)
				->setIsCustom (Block::IS_CUSTOM_YES)
				->setLabel ($_SESSION ['nombreBloque'][$i])
				->setModuleName ($newModuleName)
				->setSequence ($blockSequence)
				->setShowTitle (Block::SHOW_TITLE_YES)
				->setVisibility ($visibility);
		}

		$columns        = array ();
		$columnSequence = 1;
		foreach ($_SESSION ['columnasFiltro'] as $fieldName) {
			if (empty ($allFields [ $fieldName ])) {
				continue;
			}

			$columns [] = ViewColumn::getInstance ($allFields [ $fieldName ])->setSequence ($columnSequence);
			$columnSequence++;
		}

		$view = View::getInstance ()
			->setColumns ($columns)
			->setDefault (View::DEFAULT_YES)
			->setModuleName ($newModuleName)
			->setName ('All')
			->setOwner ($current_user->id)
			->setShowCountInMenu (View::SHOW_COUNT_YES)
			->setStatus (View::STATUS_PUBLIC);

		$newModule = Module::getInstance (true, $_SESSION ['prefijoCampo'][0], $_SESSION ['secuenciaCampo'][0])
			->setBlocks ($blocks)
			->setEntityIdentifier ($_SESSION ['campoIdentificador'])
			->setLabel ($_SESSION ['nombrePublico'])
			->setMenuLabel ($_SESSION ['moduloPadre'])
			->setName ($newModuleName)
			->setPresence (Module::PRESENCE_VISIBLE)
			->setShowInAdminConsole (true)
			->setType (Module::TYPE_USER)
			->setViews (array ($view));
		ModuleManager::getInstance ($adb)->saveModule ($newModule, true);
		create_tab_data_file ();

		$mrm                = ModuleRelationshipManager::getInstance ($adb);
		$totalRelationships = count ($_SESSION ['listaModulos']);
		for ($i = 0; $i < $totalRelationships; $i++) {
			$actions = array ();
			if ($_SESSION ['listaAccionAdd'][ $i ] == 1) {
				$actions [] = ModuleRelationship::ACTION_ADD;
			}
			if ($_SESSION ['listaAccionSelect'][ $i ] == 1) {
				$actions [] = ModuleRelationshipInterface::ACTION_SELECT;
			}

			$relationship = ModuleRelationship::getInstance ()
				->setActions ($actions)
				->setFunction ('get_related_list')
				->setLabel ($_SESSION ['labelModulos'][ $i ])
				->setModuleName ($newModuleName)
				->setPresence (ModuleRelationship::PRESENCE_VISIBLE)
				->setRelatedModuleName ($_SESSION ['listaModulos'][ $i ])
				->setSequence ($i + 1);
			$mrm->saveRelationship ($relationship);
		}
		$executedAction = 'creado';
	}

	if ((!isset ($_SESSION ['platInstancia'])) || (empty ($_SESSION ['platInstancia'])) || ($_SESSION ['plat'] == $platPrincipal)) {
		$pfbplm = PlatformFreeBillingPlanLimitManager::getInstance ($adb);
		$limit  = PlatformFreeBillingPlanLimit::getInstance ()
			->setMaxRecords ($pfbplm->fetchDefaultMaxRecordsLimit ())
			->setModuleName ($newModuleName);
		$pfbplm->saveLimit ($limit);
	}

	PlatformUtils::clearModuleDuplicationSessionVariables ();

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('MODULE_NAME', $newModule->getName ());
	$smarty->assign ('SHOW_PANEL_CONFIGURATION_BUTTON', false);
	$smarty->display ('Settings/ModuleManager/ModuleCreated.tpl');
