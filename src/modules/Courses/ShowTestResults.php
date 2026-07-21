<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $site_URL;
	setBugSnag ($site_URL);

	$courseId = PlatzillaUtils::purify ($_GET, 'course');
	$smarty = new vtigerCRM_Smarty ();
	if (!isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', 'Ruta equivocada');
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php');
		$smarty->display ('Message.tpl');
	} else if ($_SESSION ['flashmessage']['iserror']) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php');
		$smarty->display ('Message.tpl');
		unset ($_SESSION ['flashmessage']);
	} else {
		$smarty->assign ('COURSE_ID', $courseId);
		$smarty->assign ('FEEDBACK', $_SESSION ['flashmessage']['data']['feedback']);
		$smarty->assign ('LESSON_ID', $_SESSION ['flashmessage']['data']['lessonid']);
		$smarty->assign ('REQUIRED_CORRECT_ANSWERS', $_SESSION ['flashmessage']['data']['requiredcorrectanswers']);
		$smarty->assign ('STATUS', $_SESSION ['flashmessage']['data']['status']);
		$smarty->assign ('TOTAL_CORRECT_ANSWERS', $_SESSION ['flashmessage']['data']['totalcorrectanswers']);
		$smarty->display ('modules/Courses/TestResults.tpl');
		unset ($_SESSION ['flashmessage']);
	}