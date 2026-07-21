<?php
	if (php_sapi_name () !== 'cli') {
		echo 'Sólo ejecutable desde la línea de comandos';
		exit ();
	}

	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set ('display_errors', 1);
	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));

	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/AdbManager.class.php');

	$adb = AdbManager::getInstance ()->getMasterAdb ();

	require (__DIR__ . '/Uninstall.php');

	$testType = Picklist::getInstance ()
		->setName ('tipo_prueba')
		->setValues (array (
			PicklistValue::getInstance (true)->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue ('Otro'),
			PicklistValue::getInstance (true)->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue ('Diagnostico'),
		));

	$fields = array (
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_formacion_pr')->setName ('cod_formacion_pr')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 1024)->setColumnName ('titulo')->setName ('titulo')->setLabel ('Título')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('descripcion')->setName ('descripcion')->setLabel ('Descripción')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 20)->setColumnName ('ponderacion')->setName ('ponderacion')->setLabel ('Ponderación')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 20)->setColumnName ('puntaje_minimo')->setName ('puntaje_minimo')->setLabel ('Puntaje mínimo')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PICKLIST)->setColumnName ('tipo_prueba')->setName ('tipo_prueba')->setLabel ('Tipo')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE)->setPicklist ($testType),
	);

	ModuleManager::getInstance ($adb)->saveModule (
		Module::getInstance (true, 'PRU-', '00001')
			->setEntityIdentifier ('titulo')
			->setLabel ('Formación Pruebas')
			->setMenuLabel ('Revisión')
			->setName ('formacion_pruebas')
			->setPresence (ModuleInterface::PRESENCE_VISIBLE)
			->setShowInAdminConsole (true)
			->setType (ModuleInterface::TYPE_USER)
			->setBlocks (
				array (
					Block::getInstance ()->setLabel ('Exámenes')->setFields ($fields),
				)
			)
			->setViews (
				array (
					View::getInstance ()
						->setDefault (ViewInterface::DEFAULT_YES)
						->setName ('All')
						->setOwner (1)
						->setStatus (ViewInterface::STATUS_PUBLIC)
						->setColumns (
							array (
								ViewColumn::getInstance ($fields [1])->setSequence (0),
								ViewColumn::getInstance ($fields [3])->setSequence (1),
								ViewColumn::getInstance ($fields [4])->setSequence (2),
							)
						),
				)
			)
	);

	echo 'Módulo instalado correctamente' . PHP_EOL;
