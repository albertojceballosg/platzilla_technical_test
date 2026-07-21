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

	$scores = Picklist::getInstance ()
		->setName ('puntuacion')
		->setValues (array (
			PicklistValue::getInstance ()->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue (0),
			PicklistValue::getInstance ()->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue (1),
			PicklistValue::getInstance ()->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue (2),
			PicklistValue::getInstance ()->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue (3),
			PicklistValue::getInstance ()->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue (4),
			PicklistValue::getInstance ()->setPresence (PicklistValue::PRESENCE_VISIBLE)->setValue (5),
		));

	$fields = array (
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_formacion_de')->setName ('cod_formacion_de')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 1024)->setColumnName ('titulo')->setName ('titulo')->setLabel ('Título')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('descripcion')->setName ('descripcion')->setLabel ('Descripción')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_IMAGE_DISPLAY)->setColumnName ('imagen')->setName ('imagen')->setLabel ('Imagen')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PICKLIST, 255)->setColumnName ('puntuacion')->setName ('puntuacion')->setLabel ('Puntuación')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE)->setPicklist ($scores),
	);

	ModuleManager::getInstance ($adb)->saveModule (
		Module::getInstance (true, 'FOR-', '00001')
			->setEntityIdentifier ('titulo')
			->setLabel ('Cursos de formación')
			->setMenuLabel ('Entradas')
			->setName ('formacion_de_cursos')
			->setPresence (ModuleInterface::PRESENCE_VISIBLE)
			->setShowInAdminConsole (true)
			->setType (ModuleInterface::TYPE_USER)
			->setBlocks (
				array (
					Block::getInstance ()->setLabel ('Información general')->setFields ($fields),
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
								ViewColumn::getInstance ($fields [0])->setSequence (0),
								ViewColumn::getInstance ($fields [1])->setSequence (1),
								ViewColumn::getInstance ($fields [2])->setSequence (2),
							)
						),
				)
			)
	);

	echo 'Módulo instalado correctamente' . PHP_EOL;
