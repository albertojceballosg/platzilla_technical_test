<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PDFCreator.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/entidades/lib/EntityUtils.class.php');

	global $adb, $currentModule;

	$addCover        = PlatzillaUtils::purify ($_GET, 'addcover', false);
	$addIndex        = PlatzillaUtils::purify ($_GET, 'addindex', false);
	$entityId        = PlatzillaUtils::purify ($_GET, 'record');
	$from            = PlatzillaUtils::purify ($_GET, 'from');
	$onlyIndex       = PlatzillaUtils::purify ($_GET, 'onlyindex', false);
	$repercussionIds = PlatzillaUtils::purify ($_GET, 'ids', '');
	$to              = PlatzillaUtils::purify ($_GET, 'to');

	$from = date_create ($from);
	$to   = date_create ($to);

	$platzillaRootUri = PlatzillaUtils::getPlatzillaRootUri ();
	$logoURI          = "{$platzillaRootUri}/modules/repercusiones_prensa/images/logo-bdi-150.jpg";

	$resizedAttachments = null;
	$smarty             = new vtigerCRM_Smarty ();
	try {
		if ((!file_exists (__DIR__ . '/../repercusiones_prensa')) || (!is_dir (__DIR__ . '/../repercusiones_prensa'))) {
			throw new Exception  ('Módulo repercusiones de prensa no está instalado. Notifica al administrador de la aplicación');
		}
		require_once ('modules/repercusiones_prensa/lib/RepercussionUtils.class.php');

		if (empty ($entityId)) {
			throw new Exception ('No has seleccionado una entidad');
		}

		$entity = EntityUtils::getEntityById ($adb, $entityId);
		if (!$entity) {
			throw new Exception ('La entidad que suministraste no está registrada');
		}

		$repercussionIds    = explode (',', $repercussionIds);
		$orderings          = str_split ($orderings);
		$repercussions      = RepercussionUtils::getRepercussionsByEntityId ($adb, $entityId, $repercussionIds, $platzillaRootUri);
		$resizedAttachments = RepercussionUtils::resizeAttachments ($repercussions);

		$smarty->assign ('ADD_COVER', $addCover);
		$smarty->assign ('ADD_INDEX', $addIndex);
		$smarty->assign ('ENTITY', $entity);
		$smarty->assign ('LOGO_URI', $logoURI);
		$smarty->assign ('ONLY_INDEX', $onlyIndex);
		$smarty->assign ('REPERCUSSIONS', $repercussions);
		$htmlContents = $smarty->fetch ('modules/repercusiones_prensa/PDFReport.tpl');
		$fileName     = strtolower ("repercusiones-{$entity ['nombre_de_la_entidad']}-{$from->format ('Y_m_d')}-{$to->format ('Y_m_d')}.pdf");
	} catch (Exception $e) {
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$htmlContents = $smarty->fetch ('modules/repercusiones_prensa/PDFError.tpl');
		$fileName     = 'error.pdf';
	}

//	echo $htmlContents;
	PDFCreator::getInstance ()->createPDFFromHTML ($htmlContents, $fileName);
	PlatzillaUtils::deleteFiles ($resizedAttachments);