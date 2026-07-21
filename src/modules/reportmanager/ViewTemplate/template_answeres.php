<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	
	global $adb;
	
	try {
		if (empty ($record)) {
			throw new Exception ('Uoops! algo salio mal');
		}
		
		$result = $adb->pquery ('SELECT image FROM vtiger_question2image WHERE queston2answereid=?', array ($record));
		if ($adb->num_rows ($result) > 0) {
			$row = $adb->fetchByAssoc ($result, -1, false);
			$photo = $row['image'];
			$adb->pquery ('DELETE FROM vtiger_question2image WHERE queston2answereid=?',array ($record));
			DatabaseUtils::closeResult ($result);
			$result = null;
		} else {
			throw new Exception ('Uoops! Evaluación de respuesta no encontrada');
		}
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('PHOTO', $photo);
		$html      = $smarty->fetch ('modules/answers/Answer_options/AnswereToPdf.tpl');
		$pdfSerial = 'answeres_' . rand (100, 1000) .'.pdf';
		if (!$html) {
			throw new Exception ('Uoops! imposible exportar PDF');
		}
		
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
	}
