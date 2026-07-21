<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();

	try {
		$courseId   = PlatzillaUtils::purify ($_GET, 'record');
		$isInstance = !empty ($_SESSION ['platInstancia']);
		if (empty ($courseId)) {
			throw new Exception ('No has suministrado el ID del curso');
		}
		
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$course    = CoursesHelper::fetchCourseById ($masterAdb, $courseId, $_SESSION ['platInstancia'],$adb, $current_user->id);
		if (empty ($course)) {
			throw new Exception ('El curso solicitado no existe');
		}
		
		$trakCourseId = CoursesHelper::setSeenCourse ($adb, $courseId, $current_user->id);
		$masterAdb = AdbManager::getInstance()->getMasterAdb();
		$userHasPaid = CourseManager::hasUserPaidForCourse($masterAdb, $courseId, $current_user->user_name);
		$smarty->assign ('COURSE', $course);
		$smarty->assign ('USERHASPAID', $userHasPaid);
		$smarty->assign ('STATUS_COLOR', CoursesInterface::LESSON_STATUS_COLOR);
		$smarty->assign ('STATUS_TITLE', CoursesInterface::LESSON_STATUS);
		$smarty->assign ('TRACK_COURSE_ID', $trakCourseId);
		$smarty->assign ('UI_COLORS', CoursesInterface::UI_COLORS);
		$smarty->assign ('FILE_ICONS', CoursesInterface::FILE_ICONS);
		if ($_SESSION ['flashmessage']['iserror']) {
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			$smarty->assign ('IS_ERROR', true);
			$_SESSION ['flashmessage']['iserror'] = false;
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/Courses/CourseView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Home&action=index&tab=TRAINING');
		$smarty->display ('Message.tpl');
	}
