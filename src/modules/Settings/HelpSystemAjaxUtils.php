<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
    require_once ('modules/Settings/lib/HowToHelper.class.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);
	
	$smarty   = new vtigerCRM_Smarty ();
	$function = PlatzillaUtils::purify ($_POST, 'function');
	
	if ($function == 'CHANGE_STATUS_FIELD_HELP') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			$statusHelpField = array_keys (HelpFieldConstants::$HELP_FIELD_STATUS);
			if (empty ($record)) {
				throw new Exception ('Ayuda no encontrada');
			} else if (empty($status) || !in_array ($status, $statusHelpField)) {
				throw new Exception ('Imposible cambiar el estatus');
			}
			
			$status = ($status == $statusHelpField [0]) ? $statusHelpField [1] : $statusHelpField [0];
			
			HelpSettingsHelper::changeStatusFieldHelp ($adb, $record, $status);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } else if ($function == 'CHANGE_STATUS_HOW_TO') {
        try {
            $record = PlatzillaUtils::purify ($_POST, 'record', null);
            $status = PlatzillaUtils::purify ($_POST, 'status');
            $statusHelpField = array_keys (HowToInterface::HOW_TO_STATUS);
            if (empty ($record)) {
                throw new Exception ('HowTo no encontrado');
            } else if (empty($status) || !in_array ($status, $statusHelpField)) {
                throw new Exception ('Imposible cambiar el estatus');
            }
        			
            $status = ($status == $statusHelpField [0]) ? $statusHelpField [1] : $statusHelpField [0];
            
            HowToHelper::changeHowToStatus ($adb, $record, $status);
        			
            header ('Access-Control-Allow-Origin: *');
            header ('HTTP/1.1 200 OK');
            header ('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header ('Access-Control-Allow-Origin: *');
            header ('HTTP/1.1 200 OK');
            header ('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
	} else if ($function == 'CHANGE_EDITABLE_FIELD_HELP') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			$editableHelpField = array_keys (HelpFieldConstants::$HELP_FIELD_EDITABLE);
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			} else if (empty($status) || !in_array ($status, $editableHelpField)) {
				throw new Exception ('Imposible cambiar el estatus');
			}
			
			$status = ($status == $editableHelpField [0]) ? $editableHelpField [1] : $editableHelpField [0];
			
			HelpSettingsHelper::changeEditableFieldHelp ($adb, $record, $status);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'DELETE_FIELD_HELP') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			if (empty ($record)) {
				throw new Exception ('Ayuda no encontrada');
			}
			
			HelpSettingsHelper::deleteFieldHelp ($adb, $record);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array ('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } else if ($function == 'DELETE_HOW_TO') {
        try {
            $record = PlatzillaUtils::purify ($_POST, 'record', null);
            if (empty ($record)) {
                throw new Exception ('Ayuda HowTo no encontrada');
            }
            
        	HowToHelper::deleteHowTo ($adb, $record);
            header ('Access-Control-Allow-Origin: *');
            header ('HTTP/1.1 200 OK');
            header ('Content-Type: application/json; charset=utf-8');
            echo json_encode(array ('error' => 'OK'));
        } catch (Exception $e) {
            header ('Access-Control-Allow-Origin: *');
            header ('HTTP/1.1 200 OK');
            header ('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    
	}
	exit();
