<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $current_user;

	$id              = PlatzillaUtils::purify ($_POST, 'record');
	$answer          = PlatzillaUtils::purify ($_POST, 'description');
	$applicationCode = PlatzillaUtils::purify ($_POST, 'applicationcode');
	$fieldName       = PlatzillaUtils::purify ($_POST, 'fieldname');
	$moduleName      = PlatzillaUtils::purify ($_POST, 'modulename');
	$tags = PlatzillaUtils::purify ($_POST, 'tags');
	$title           = PlatzillaUtils::purify ($_POST, 'title');

	$arguments = array (
		'id'              => $id,
		'applicationcode' => $applicationCode,
		'description'     => $answer,
		'fieldname'       => $fieldName,
		'modulename'      => $moduleName,
		'tags' => $tags,
		'title'           => $title,
	);

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($applicationCode)) {
			throw new Exception ('No has suministrado el código de la aplicación');
		} else if (empty ($title)) {
			throw new Exception ('No has suministrado la pregunta');
		} else if (empty ($answer)) {
			throw new Exception ('No has suministrado la respuesta');
		} else if (empty ($tags)) {
			throw new Exception ('No has suministrado las etiquetas de búsqueda');
		}

		HelpSettingsHelper::saveHelpQuestion ($adb, $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La pregunta frecuente ha sido almacenada',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=questions');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $arguments,
		);
		header ("Location: index.php?module=Settings&action=HelpSettingsQuestionEditView&record={$id}&parenttab=Settings");
	}
	exit ();