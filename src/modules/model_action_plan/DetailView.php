<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/model_action_plan/lib/ModelActionPlanHelper.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	global $adb, $app_strings, $current_user, $mod_strings;
	
	$destinationId = PlatzillaUtils::purify ($_REQUEST, 'record', null);
	try {
		if (empty ($destinationId)) {
			throw new Exception ('Destino desconocido');
		}
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('DESTINATION_ID', $destinationId);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODEL', ModelActionPlanHelper::getInstance ($adb, $_SESSION['plat'])->fetchModelByDestinationId ($destinationId));
		$smarty->display ('modules/model_action_plan/DetailView.tpl');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
		$smarty->assign ('MODEL', null);
		$smarty->display ('modules/model_action_plan/DetailView.tpl');
	}