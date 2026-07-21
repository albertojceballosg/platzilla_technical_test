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

	$fields = array (
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_formacion_le')->setName ('cod_formacion_le')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 1024)->setColumnName ('titulo')->setName ('titulo')->setLabel ('Título')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('descripcion')->setName ('descripcion')->setLabel ('Descripción')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('introduccion')->setName ('introduccion')->setLabel ('Introducción')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('actividades')->setName ('actividades')->setLabel ('Actividades')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_IMAGE_DISPLAY, 1024)->setColumnName ('materiales')->setName ('materiales')->setLabel ('Materiales')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('contenido')->setName ('contenido')->setLabel ('Contenido')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_URL)->setColumnName ('url_video')->setName ('url_video')->setLabel ('URL Video')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
		Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_URL)->setColumnName ('url_pagina')->setName ('url_pagina')->setLabel ('URL Página')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_VISIBLE)->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)->setReadOnly (FieldInterface::READ_WRITE),
	);

	ModuleManager::getInstance ($adb)->saveModule (
		Module::getInstance (true, 'LEC-', '00001')
			->setEntityIdentifier ('titulo')
			->setLabel ('Lecciones')
			->setMenuLabel ('Entradas')
			->setName ('formacion_lecciones')
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
								ViewColumn::getInstance ($fields [1])->setSequence (0),
								ViewColumn::getInstance ($fields [2])->setSequence (1),
							)
						),
				)
			)
	);

	echo 'Módulo instalado correctamente' . PHP_EOL;
