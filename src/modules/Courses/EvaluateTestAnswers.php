<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $site_URL, $adb, $current_user, $mod_strings;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	try {
		$courseId  = PlatzillaUtils::purify ($_POST, 'course');
		$lessonId  = PlatzillaUtils::purify ($_POST, 'record');
		$questions = PlatzillaUtils::purify ($_POST, 'questions');
		
		if (empty ($lessonId)) {
			throw new Exception ('No has suministrado el ID de la lección');
		} else if (empty ($questions)) {
			throw new Exception ('No has suministrado las preguntas');
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$test = CoursesHelper::fetchTest ($masterAdb, $lessonId);
		if (empty ($test)) {
			throw new Exception ('La lección solicitada no existe');
		}
		CoursesHelper::evaluateTestAnswers (
			array (
				'masterAdb' => $masterAdb,
				'adb'       => $adb,
				'user'      => $current_user->id,
				'course'    => $courseId,
				'lesson'    => $lessonId,
			),
			$questions
		);
		$questionsResults = $questions['evaluated'];
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('COURSE_ID', $courseId);
		$smarty->assign ('LESSON_ID', $lessonId);
		$smarty->assign ('LESSON_STATUS', (!empty ($questions['lessonStatus'])) ? $questions['lessonStatus'] : 'TEST_NOT_PASSED');
		$smarty->assign ('TEST_STATUS', (!empty ($questions['evaluationStatus'])) ? $questions['evaluationStatus'] : 'TEST_NOT_PASSED');
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('QUESTION_FEEDBACKS', null);
		$smarty->assign ('QUESTION_IDS', array_column ($questions, 'id'));
		$smarty->assign ('QUESTION_RESULTS', (count ($questionsResults) == 0) ? null : $questionsResults);
		$smarty->assign ('TEST', $test);
		$smarty->display ('modules/Courses/TestResults.tpl');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Courses&action=LessonView&course={$courseId}&record={$lessonId}");
	}
