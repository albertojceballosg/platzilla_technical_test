<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $current_user;

	$id          = PlatzillaUtils::purify ($_POST, 'record');
	$blockName   = PlatzillaUtils::purify ($_POST, 'blockname');
	$sectionName = PlatzillaUtils::purify ($_POST, 'sectionname');
	$tabName     = PlatzillaUtils::purify ($_POST, 'tabname');
	$title       = PlatzillaUtils::purify ($_POST, 'title');
	$type        = PlatzillaUtils::purify ($_POST, 'type');
	$url         = PlatzillaUtils::purify ($_POST, 'url');

	$arguments = array (
		'id'          => $id,
		'blockname'   => $blockName,
		'sectionname' => $sectionName,
		'tabname'     => !empty ($tabName) ? $tabName : null,
		'title'       => $title,
		'type'        => $type,
		'url'         => $url,
	);

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($type)) {
			throw new Exception ('No has suministrado el tipo');
		} else if (empty ($sectionName)) {
			throw new Exception ('No has suministrado la sección');
		} else if (empty ($blockName)) {
			throw new Exception ('No has suministrado el bloque');
		} else if (empty ($title)) {
			throw new Exception ('No has suministrado el título');
		} else if (empty ($url)) {
			throw new Exception ('No has suministrado el enlace');
		}

		HelpSettingsHelper::saveHelpConfiguration ($adb, $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El tutorial ha sido almacenado',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=configuration');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $arguments,
		);
		header ("Location: index.php?module=Settings&action=HelpSettingsConfigurationEditView&record={$id}&parenttab=Settings");
	}
	exit ();