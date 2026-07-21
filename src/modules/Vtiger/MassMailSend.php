<?php
	require_once ('include/platzilla/Configuration/BackgroundTaskParameterConfigurationInterface.php');
	require_once ('include/utils/MassMailUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('modules/emailmanager/emailmanager.php');

	global $adb, $currentModule;

	$language      = PlatzillaUtils::purify ($_POST, 'language');
	$recipients    = PlatzillaUtils::purify ($_POST, 'recipients');
	$recordIds     = PlatzillaUtils::purify ($_POST, 'recordids');
	$relatedModule = PlatzillaUtils::purify ($_POST, 'module_related_record');
	$relatedRecord = PlatzillaUtils::purify ($_POST, 'related_record_id');
	$templateId    = PlatzillaUtils::purify ($_POST, 'templatename');
	$variables     = PlatzillaUtils::purify ($_POST, 'variables');
	
	$results = array ();
	try {
		if (empty ($language)) {
			throw new Exception ('No has suministrado el idioma');
		} else if (empty ($templateId)) {
			throw new Exception ('No has suministrado el nombre de la plantilla');
		} else if (empty ($recipients)) {
			throw new Exception ('No has suministrado los destinatarios');
		} else if (empty ($recipients)) {
			throw new Exception ('No has suministrado los registros seleccionados');
		}

		$templateData = MassMailUtils::getEmailManagerTemplatesById ($adb, $templateId);
		foreach ($recordIds as $recordId) {
			try {
				/** @var CRMEntity $entity */
				$entity = CRMEntity::getInstance ($currentModule);
				$entity->retrieve_entity_info ($recordId, $currentModule);
				$dataSourceValues = $entity->column_fields;

				$mailRecipients = array ();
				switch ($recipients ['type']) {
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD:
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD:
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD:
						$mailRecipients [] = isset ($dataSourceValues [ $recipients ['value'] ]) ? $dataSourceValues [ $recipients ['value'] ] : null;
						break;
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS:
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL:
						$mailRecipients [] = $recipients ['value'];
						break;
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA:
					case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE:
						$mailRecipients [] = MassMailUtils::evaluateVariables ($adb, $recipients ['value'], $dataSourceValues);
						break;
					default:
						throw new Exception ('El tipo de los destinatarios suministrado no es válido');
				}

				$variableValues = array ();
				if (!empty ($variables)) {
					foreach ($variables as $variableName => $variableData) {
						switch ($variableData ['type']) {
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD:
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD:
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD:
								$variableValues [ $variableName ] = isset ($dataSourceValues [ $variableData ['value'] ]) ? $dataSourceValues [ $variableData ['value'] ] : null;
								break;
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS:
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL:
								$variableValues [ $variableName ] = $variableData ['value'];
								break;
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA:
							case BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE:
								$variableValues [ $variableName ] = MassMailUtils::evaluateVariables ($adb, $variableData ['value'], $dataSourceValues);
								break;
							case 'SOURCE MODULE':
								$variableValues [ $variableName ] = MassMailUtils::getVariablesFromSourceModule ($relatedModule, $relatedRecord, $variableData ['value']);
								break;
							default:
								throw new Exception ("El tipo de la variable {$variableName} no es válido");
						}
					}
				}

				$status = emailmanager::getInstance ($adb, $_SESSION ['plat'])->addSender (
					'Platzilla',
					'no_reply@platzilla.com'
				)->send (
					$mailRecipients,
					$language,
					$templateData ['templatename'],
					$variableValues
				);
				if ($status != emailmanager::STATUS_SENT) {
					throw new Exception ("Se ha presentado un error al enviar el correo: código {$status}");
				}

				$results [ $recordId ] = 'OK';
			} catch (Exception $ie) {
				$results [ $recordId ] = $ie->getMessage ();
			}
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($results);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
