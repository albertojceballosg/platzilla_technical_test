<?php
	require_once ('Smarty_setup.php');
	require_once ('include/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/XlsxFileExporter.class.php');

	global $adb, $allow_exports, $app_strings, $current_user, $currentModule;

	if (isPermitted ($currentModule, 'Export') == 'no') {
		$allow_exports = 'none';
	}
	if (($allow_exports == 'none') || (($allow_exports == 'admin') && (!is_admin ($current_user)))) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $app_strings ['NOT_PERMITTED_TO_EXPORT']);
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=&action=index');
		$smarty->display ('Message.tpl');
		exit ();
	}

	$template = PlatzillaUtils::purify ($_GET, 'template', false);
	$objPHPExcel = XlsxFileExporter::getInstance ($adb)->export ($currentModule, $current_user, $template == 'true' ? true : false);
	// Redirect output to a client’s web browser (Excel5)
	header ('Content-Type: application/vnd.ms-excel');
	header ("Content-Disposition: attachment;filename={$currentModule}.xls");
	header ('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header ('Cache-Control: max-age=1');
	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter ($objPHPExcel, 'Excel5');
	$objWriter->save ('php://output');
	exit ();
