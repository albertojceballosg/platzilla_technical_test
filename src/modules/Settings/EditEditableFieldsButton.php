<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/EditableFieldsHelper.class.php');

	global $adb, $current_user;

	$buttonName = PlatzillaUtils::purify ($_POST, 'buttonname');
	$isInstance = !empty ($_SESSION ['platInstancia']);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		if (empty($buttonName)) {
			throw new Exception ('No has suministrado el botón a editar');
		}

		$button = EditableFieldsManager::getInstance($adb)->fetchEditableButtom($buttonName);

		if (empty ($button)) {
			throw new Exception ('Se ha presentado un error. Intenta más tarde');
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('EDITABLE_FIELDS', EditableFieldsHelper::getEditableFields ($adb, $button->getModuleName ()));
		$smarty->assign('EDITABLE_BUTTOM', $button);
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('MOD', $mod_strings);
		echo $smarty->fetch('Settings/LayoutEditor/EditableFieldsEditView.tpl');
	} catch (Exception $e) {
		echo $e->getMessage ();
	}
	exit ();
