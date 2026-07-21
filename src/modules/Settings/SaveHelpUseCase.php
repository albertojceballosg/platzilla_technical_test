<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $current_user;

	$id               = PlatzillaUtils::purify ($_POST, 'record');
	$applicationCodes = PlatzillaUtils::purify ($_POST, 'applicationcodes');
	$category         = PlatzillaUtils::purify ($_POST, 'category');
	$tags             = PlatzillaUtils::purify ($_POST, 'tags');
	$title            = PlatzillaUtils::purify ($_POST, 'title');
	$url              = PlatzillaUtils::purify ($_POST, 'url');

	$arguments = array (
		'id'               => $id,
		'applicationcodes' => $applicationCodes,
		'category'         => $category,
		'tags'             => $tags,
		'title'            => $title,
		'url'              => $url,
	);

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($category)) {
			throw new Exception ('No has suministrado la categoría');
		} else if (empty ($title)) {
			throw new Exception ('No has suministrado el título');
		} else if (empty ($url)) {
			throw new Exception ('No has suministrado el enlace');
		} else if (empty ($applicationCodes)) {
			throw new Exception ('No has suministrado las aplicaciones');
		} else if (empty ($tags)) {
			throw new Exception ('No has suministrado las etiquetas de búsqueda');
		}

		HelpSettingsHelper::saveHelpUseCase ($adb, $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El caso de uso ha sido almacenado',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=usecases');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $arguments,
		);
		header ("Location: index.php?module=Settings&action=HelpSettingsUseCaseEditView&record={$id}&parenttab=Settings");
	}
	exit ();