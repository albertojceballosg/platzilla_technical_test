<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PDFCreator.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/repercusiones_prensa/lib/RepercussionUtils.class.php');

	global $adb, $currentModule;

	$repercussionId = PlatzillaUtils::purify ($_GET, 'record');

	$platzillaRootUri = PlatzillaUtils::getPlatzillaRootUri ();
	$logoURI          = "{$platzillaRootUri}/modules/repercusiones_prensa/images/logo-bdi-150.jpg";

	$resizedAttachments = null;
	$smarty             = new vtigerCRM_Smarty ();
	try {
		if (empty ($repercussionId)) {
			throw new Exception ('No has seleccionado una repercusión');
		}

		$repercussion = RepercussionUtils::getRepercussionById ($adb, $repercussionId, $platzillaRootUri);
		if (!$repercussion) {
			throw new Exception ('La repercusión de prensa que suministraste no está registrada');
		}

		$smarty->assign ('LOGO_URI', $logoURI);
		$smarty->assign ('REPERCUSSION', $repercussion);
		$htmlContents = $smarty->fetch ('modules/repercusiones_prensa/PDFRepercussion.tpl');
		$fileName     = strtolower ("repercusion-{$repercussion ['cod_repercusione']}-{$repercussion ['titular']}.pdf");
	} catch (Exception $e) {
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$htmlContents = $smarty->fetch ('modules/repercusiones_prensa/PDFError.tpl');
		$fileName     = 'error.pdf';
	}

	PDFCreator::getInstance ()->createPDFFromHTML ($htmlContents, $fileName);
