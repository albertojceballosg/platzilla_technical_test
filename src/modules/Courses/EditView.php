<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	require_once ('modules/Courses/lib/CoursesInterface.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	$isInstance = !empty ($_SESSION ['platInstancia']);

	$smarty = new vtigerCRM_Smarty();
	if (($isInstance) || (!is_admin ($current_user))) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$courseId = PlatzillaUtils::purify ($_GET, 'record');
	$course   = null;
	if (isset ($_SESSION ['flashmessage']['data'])) {
		$course = Course::getInstance ();
		$course->unserialize ($_SESSION ['flashmessage']['data']);
		unset ($_SESSION ['flashmessage']['data']);
	} else if (!empty ($courseId)) {
		$course = CoursesHelper::fetchCourseById ($adb, $courseId, null, $adb, $current_user->id);
	}
	
	$cm = CourseManager::getInstance ($adb);
	$smarty->assign ('CATEGORIES', $cm->fetchCategories ());
	$smarty->assign ('COURSE', $course);
	$smarty->assign ('SERIES',$cm->fetchSeries ());
	$smarty->assign ('LESSON_STATUS',CoursesInterface::LESSON_PUBLISH_STATUS);
	$smarty->assign ('TYPE_VIDEO',CoursesInterface::COURSE_TYPE_VIDEO);
	$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/Courses/EditView.tpl');
