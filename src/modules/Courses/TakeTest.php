<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	try {
		$lessonId = PlatzillaUtils::purify ($_GET, 'record');
		$courseId = PlatzillaUtils::purify ($_GET, 'course');
		if (empty ($lessonId)) {
			throw new Exception ('No has suministrado el ID de la lección');
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$test      = CoursesHelper::fetchTest ($masterAdb, $lessonId);
		if (empty ($test)) {
			throw new Exception ('La lección solicitada no existe');
		}
		$questions = $test->getQuestions ();

		$test->setQuestions ($questions);
		$smarty->assign ('COURSE_ID', $courseId);
		$smarty->assign ('TEST', $test);
		$smarty->display ('modules/Courses/TestView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Courses&action=index');
		$smarty->display ('Message.tpl');
	}
