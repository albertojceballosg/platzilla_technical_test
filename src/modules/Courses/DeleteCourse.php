<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	try {
		$isInstance = !empty ($_SESSION ['platInstancia']);
		if (($isInstance) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$courseId = PlatzillaUtils::purify ($_POST, 'record');
		CoursesHelper::deleteCourse ($adb, $courseId);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El curso ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Home&action=index&tab=TRAINING');
	exit ();
