<?php
	require_once ('modules/invitations/lib/InvitationsManager.class.php');
	try {
		global $currentModule;
		InvitationsManager::getInstance ()->checkNewDocumentsTotal (isset ($_SESSION ['plat']) ? $_SESSION ['plat'] : null, $currentModule);
	} catch (Exception $e) {
		global $smarty;
		$smarty->assign ('ERRORMESSAGE', $e->getMessage ());
		$smarty->display ('modules/invitations/MaxNewDocumentsReached.tpl');
	}
