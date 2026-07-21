<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$boxes         = PlatzillaUtils::purify ($_POST, 'gridbox');
	$gridViewLabel = PlatzillaUtils::purify ($_POST, 'label');
	$gridViewName  = PlatzillaUtils::purify ($_POST, 'gridviewname');
	$moduleName    = PlatzillaUtils::purify ($_POST, 'modulename');
	$record        = PlatzillaUtils::purify ($_POST, 'record');
	$status        = PlatzillaUtils::purify ($_POST, 'viewstatus');
	$position      = PlatzillaUtils::purify ($_POST, 'position');
	try {
		if (empty ($moduleName)) {
			throw new Exception ('No se encontró el modulo para la vista!');
		}

		if (empty ($boxes)) {
			throw new Exception ('Se debe seleccionar al menos una cuadricula ');
		}

		if (empty ($gridViewName)) {
			if (!empty ($gridViewLabel)) {
				$gridViewName = GridViewHelper::getGridViewName ($gridViewLabel);
			} else {
				$gridViewName = GridViewHelper::getGridViewName ('GRID-VIEW');
			}
		}

		$gridViewLabel = (empty ($gridViewLabel)) ? 'default view' : $gridViewLabel;

		if ($status == 'ENABLED') {
			GridViewHelper::setDisabledLastActiveView ($adb, $moduleName);
		}

		$gridViewBoxes = array ();
		foreach ($boxes as $index => $boxType) {
			$gridViewBoxes [] = GridViewBox::getInstance()
				->setGridViewName ($gridViewName)
				->setBoxType ($boxType)
				->setPresence (1)
				->setSequence ($index);
		}
		GridViewManager::getInstance ($adb)->saveGridView (
			GridView::getInstance()
				->setId ($record)
				->setGridViewBox ($gridViewBoxes)
				->setLabel ($gridViewLabel)
				->setTabName ($moduleName)
				->setGridViewName ($gridViewName)
				->setStatus ($status)
				->setPosition ($position)
				->setLocked (!empty ($_SESSION ['platInstancia']) ? 1 : 0)
		);
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'http://homeplatzilla.local.com/index.php?module=grid_view&action=EditView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
	header ('Location: index.php?module=grid_view&action=ListView&parenttab=Settings');
