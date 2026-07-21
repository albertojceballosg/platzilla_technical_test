<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $current_user;

	$id          = PlatzillaUtils::purify ($_POST, 'record');
	$description = PlatzillaUtils::purify ($_POST, 'description');
	$tags        = PlatzillaUtils::purify ($_POST, 'tags');
	$title       = PlatzillaUtils::purify ($_POST, 'title');

	$arguments = array (
		'id'          => $id,
		'description' => $description,
		'tags'        => $tags,
		'title'       => $title,
	);

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($title)) {
			throw new Exception ('No has suministrado el título');
		} else if (empty ($description)) {
			throw new Exception ('No has suministrado la descripción');
		} else if (empty ($tags)) {
			throw new Exception ('No has suministrado las etiquetas de búsqueda');
		}

		HelpSettingsHelper::saveHelpTip ($adb, $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El tip ha sido almacenado',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=tips');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $arguments,
		);
		header ("Location: index.php?module=Settings&action=HelpSettingsTipEditView&record={$id}&parenttab=Settings");
	}
	exit ();