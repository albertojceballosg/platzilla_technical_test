<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/diagnostic_report_builder/lib/DiagnosticReportBuilderHelper.class.php');
	
	global $adb, $current_user;
	
	$record            = PlatzillaUtils::purify ($_POST, 'record', null);
	$reportBuilder     = PlatzillaUtils::purify ($_POST, 'block');
	$questionnaireId   = PlatzillaUtils::purify ($_POST, 'questionnaire');
	$questionnaireName = PlatzillaUtils::purify ($_POST, 'diagnosticname');
    $platform          = $_SESSION ['plat'];
	try {
		if (empty($reportBuilder)) {
			throw new Exception('Uoops no ha relacionado preguntas y respuestas');
		}
		$diagnosticBuilder = DiagnosticReportBuilder::getInstance ()
			->setId ($record)
			->setQuestionnaireId ($questionnaireId)
			->setName ($questionnaireName)
			->setStatus ((empty ($record) ? 'ENABLED' : null));
		
		$diagnosticBuilder->validate ();
		$rows               = array_keys ($reportBuilder);
		$questionAndAnswers = array ();
		$drbh               = DiagnosticReportBuilderHelper::getInstance ($adb, $platform);
		$ranges             = null;
		foreach ($rows as $blockId) {
			$row           = $reportBuilder [$blockId];
			$totalQuestion = count ($row ['question']);
			for ($k = 0; $k < $totalQuestion; $k++) {
				$questionJoin = null;
                $handler      = null;
                $elementField = null;
				if (!empty($row ['join'][$k]) && $k < $totalQuestion) {
					$questionJoin = $row ['question'][$k + 1];
				}
				if ($row ['element'][$k] == DiagnosticReportBuilderInterface::ELEMENT_TYPE_IMAGE) {
                    $elementField = $drbh->getImageResponse($blockId);
                    if (empty($elementField) && isset($row['element-field']['old'])) {
                        $elementField = $row['element-field']['old'];
                    }
                } else if ($row ['element'][$k] == DiagnosticReportBuilderInterface::ELEMENT_TYPE_MANAGEMENT_LEVEL) {
                    $ranges       = $row ['element-field'];
				    $elementField = json_encode($row ['element-field']);
                    $row ['question'][$k] = 0;
                } else if ($row ['question'][$k] == DiagnosticReportBuilderInterface::ELEMENT_TYPE_SELECTED_TOPIC) {
                    $handler              = $drbh->getTopicRange ($ranges, $row ['answer'][$k]);
                    $row ['question'][$k] = 0;
                    $elementField         = (isset($row ['element-field'][$k])) ? $row ['element-field'][$k] : null;
				} else {
					$elementField = (isset($row ['element-field'][$k])) ? $row ['element-field'][$k] : null;
				}
				$attributes             = (isset($row ['attributes'][$k])) ? $row ['attributes'][$k] : null;
				$questionAndAnswers [] = DiagnosticReportToAnswer::getInstance ()
					->setAnswerName ($row ['answer'][$k])
					->setAttributes ($attributes)
					->setElementType ($row ['element'][$k])
                    ->setHandler ($handler)
					->setJoinType ($row ['join'][$k])
					->setQuestionId ($row ['question'][$k])
					->setQuestionJoin ($questionJoin)
					->setReportBlock ($row['report-tab'])
					->setResult ($elementField);
			}
			unset($row);
		}
		
		$diagnosticBuilder->setReportsToAnswer ($questionAndAnswers);
		$drbh->saveDiagnosticReportBuilder ($diagnosticBuilder);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (empty ($record)) ? 'El reporte de diagnostico ha sido guardado con éxito' : 'El reporte de diagnostico ha sido actualizada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($arguments) ? $arguments : null,
		);
		
	}
	header ("Location: index.php?module=diagnostic_report_builder&action=index&parenttab=Settings");
