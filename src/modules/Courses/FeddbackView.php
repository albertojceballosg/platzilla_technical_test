<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	
	global $adb, $app_strings, $current_user ,$mod_strings;
	$smarty = new vtigerCRM_Smarty ();
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	try {
		$courseId = PlatzillaUtils::purify ($_GET, 'course');
		$lessonId = PlatzillaUtils::purify ($_GET, 'record');
		
		if (empty ($lessonId)) {
			throw new Exception ('No has suministrado el ID de la lección');
		} else if (empty ($courseId)) {
			throw new Exception ('No has suministrado el ID del curso');
		}
		
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$test      = CoursesHelper::fetchTest ($masterAdb, $lessonId);
		if (empty ($test)) {
			throw new Exception ('La lección solicitada no existe');
		}
		
		$questions = CoursesHelper::getTestResults ($masterAdb, $courseId, $lessonId, $current_user->id);
		if (empty ($questions)) {
			// Verificar si el usuario nunca ha presentado el test
			$checkEvaluated = $masterAdb->pquery(
				"SELECT 1 FROM vtiger_lesson_evaluated2user WHERE courseid=? AND lessonid=? AND userid=?",
				array($courseId, $lessonId, $current_user->id)
			);
			if ($masterAdb->num_rows($checkEvaluated) == 0) {
				$_SESSION ['flashmessage'] = array (
					'iserror' => false,
					'message' => 'No se encuentran almacenados resultados del usuario para esta evaluación.'
				);
				header ("Location: index.php?module=Courses&action=LessonView&course={$courseId}&record={$lessonId}");
				exit;
			} else {
				throw new Exception('No se han encontrado resultados para la lección solicitada');
			}
		}
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('COURSE_ID', $courseId);
		$smarty->assign ('LESSON_ID', $lessonId);
		$smarty->assign ('TEST_STATUS', (!empty ($questions[0]['test_status'])) ? $questions[0]['test_status'] : 'TEST_NOT_PASSED');
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('QUESTION_FEEDBACKS', (count ($questions) == 0) ? null : $questions);
		$smarty->assign ('QUESTION_RESULTS', null);
		$smarty->assign ('TEST', $test);
		$smarty->display ('modules/Courses/TestResults.tpl');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Courses&action=LessonView&course={$courseId}&record={$lessonId}");
	}
