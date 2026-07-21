<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL, $masterAdb;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	
	$name         = PlatzillaUtils::purify ($_POST, 'name');
	$record       = PlatzillaUtils::purify ($_POST, 'record', null);
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$tab          = PlatzillaUtils::purify ($_POST, 'tab');
	
	try {
		if (empty ($name)) {
			throw new Exception ('Información incompleta!');
		}

		$category = CourseCategory::getInstance ()
			->setName ($name)
			->setStatus ($status)
			->setId ($record);
		CoursesHelper::saveCategory ($masterAdb, $category);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty($record)) ? 'Se ha actualizado la Categoría' : 'Se ha creado la Categoría!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module=Settings&action=CourseListView&parenttab=Settings&tab={$tab}");
