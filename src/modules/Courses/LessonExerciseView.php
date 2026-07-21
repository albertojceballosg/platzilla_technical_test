<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	require_once ('modules/Courses/lib/CourseResource.php');
	require_once ('modules/Courses/lib/ExerciseTracker.php');
	
	global $adb, $app_strings, $current_module, $mod_strings, $current_user, $masterAdb;
	
	$cuourseId  = PlatzillaUtils::purify ($_REQUEST, 'course');
	$exerciseId = PlatzillaUtils::purify ($_REQUEST, 'record');
	$lessonId   = PlatzillaUtils::purify ($_REQUEST, 'lesson');
	try {
		if (empty ($exerciseId)) {
			throw new Exception ('Ejercicio prático no encontrado');
		}
		if (empty ($lessonId)) {
			throw new Exception ('Lección no encontrada');
		}
		
		$exercises = CoursesHelper::fetchLessonExercises ($adb, $lessonId, $exerciseId);
		if (empty ($exercises)) {
			throw new Exception ('Ejercicio prático no encontrado');
		}

		// Register exercise visit
		ExerciseTracker::registerExerciseVisit($adb, $lessonId, $exerciseId, $current_user->id);
		
		// Check and update exercise status (in case there are attachments)
		ExerciseTracker::checkAndUpdateExerciseStatus($adb, $exerciseId, $lessonId, $current_user->id);

		// Get current exercise status for UI
		$exerciseStatus = ExerciseTracker::getExerciseStatus($adb, $lessonId, $current_user->id);

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('COURSE_ID', $cuourseId);
		$smarty->assign ('ENTITY_ATTACHMENTS', AttachmentsUtils::fetchEntityAttachments ($adb, $exerciseId, 'Courses', $current_user->id));
		$smarty->assign ('EXERCISE', $exercises[0]);
		$smarty->assign ('LESSON_ID', $lessonId);
		$smarty->assign ('MODULE', 'Courses');
		$smarty->assign ('RECORD', $exerciseId);
		$smarty->assign ('EXERCISE_STATUS', $exerciseStatus);
		$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
		$smarty->display ('modules/Courses/ExerciseDetailView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=');
		$smarty->display ('Message.tpl');
	}
