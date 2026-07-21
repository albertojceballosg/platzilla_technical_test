<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');

	global $mod_strings, $app_strings, $adb, $theme, $default_charset;

	$smarty = new vtigerCRM_Smarty;

	function getYoutubeIdFromUrl ($url) {
		if (stristr ($url, 'youtu.be/')) {
			preg_match ('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID);
			return $final_ID[4];
		} else {
			preg_match ('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD);
			return $IDD[5];
		}
	}

	$recordid = vtlib_purify ($_REQUEST['record']);
	if (isset($_REQUEST['record']) && $_REQUEST['record'] != '') {

		$sql = 'SELECT vtiger_ayudaconf.*, vtiger_config_applications.app_name FROM vtiger_ayudaconf INNER JOIN vtiger_config_applications ON (vtiger_ayudaconf.id_app = vtiger_config_applications.config_applicationsid) WHERE id_ayuda = ?';

		$result = $adb->pquery ($sql, array ($recordid));

		$row = $adb->fetchByAssoc ($result);

		$sqlTips    = 'SELECT * FROM vtiger_ayudaconf_platzitips WHERE id_ayuda = ?';
		$resultTips = $adb->pquery ($sqlTips, array ($recordid));
		$arrayTips  = array ();

		while ($tips = $adb->fetchByAssoc ($resultTips)) {
			$arrayTips[] = $tips;
		}

		$sqlQuestions    = 'SELECT * FROM vtiger_ayudaconf_preguntasf WHERE id_ayuda = ?';
		$resultQuestions = $adb->pquery ($sqlQuestions, array ($recordid));
		$arrayQuestions  = array ();

		while ($questions = $adb->fetchByAssoc ($resultQuestions)) {
			$arrayQuestions[] = $questions;
		}

		$sqlTutor          = 'SELECT * FROM vtiger_ayudaconf_tutoriales WHERE id_ayuda = ? AND tipo = ?';
		$resultTutorVideos = $adb->pquery ($sqlTutor, array ($recordid, 'video'));
		$arrayTutorVideos  = array ();

		while ($tutoriasVideos = $adb->fetchByAssoc ($resultTutorVideos)) {
			$tutoriasVideos['urlIframe'] = 'http://www.youtube.com/embed/' . getYoutubeIdFromUrl ($tutoriasVideos['enlace']) . '?rel=0';
			$arrayTutorVideos[]          = $tutoriasVideos;
		}

		$resultTutorArt = $adb->pquery ($sqlTutor, array ($recordid, 'articulo'));
		$arrayTutorArt  = array ();

		while ($tutoriasArt = $adb->fetchByAssoc ($resultTutorArt)) {
			$arrayTutorArt[] = $tutoriasArt;
		}

		$smarty->assign ('AYUDAINFO', $row);
		$smarty->assign ('TIPS', $arrayTips);
		$smarty->assign ('QUESTIONS', $arrayQuestions);
		$smarty->assign ('TUTORIAS_VIDEOS', $arrayTutorVideos);
		$smarty->assign ('TUTORIAS_ARTS', $arrayTutorArt);
	}

	// Aplicaciones
	$apps   = array ();
	$result = $adb->query ("SELECT config_applicationsid,app_code,app_name FROM vtiger_config_applications WHERE app_status = 'Activa' ORDER BY app_name");
	if (($result) && ($adb->num_rows ($result))) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$apps[] = $row;
		}
	}

	$fields = array ();
	$result = $adb->query ('SELECT a.tabid, a.name, b.fieldid, b.columnname, b.fieldlabel FROM vtiger_tab AS a INNER JOIN vtiger_field AS b ON a.tabid = b.tabid');
	if (($result) && ($adb->num_rows ($result))) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$fields[] = $row;
		}
	}

	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('APLICACIONES', $apps);
	$smarty->assign ('ID', $recordid);
	$smarty->assign ('FIELDS', $fields);
	$smarty->assign ('APPLICATIONS_MODULE_NAMES', ConfigApplicationsHelper::getApplicationsModuleNames ($adb));
	$smarty->display ('Settings/HelpSettingsDetailView.tpl');
