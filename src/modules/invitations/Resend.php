<?php
	global $smarty;

	$recordId = isset ($_GET ['record']) ? vtlib_purify ($_GET ['record']) : null;

	try {
		require_once ('modules/invitations/lib/NotificationsManager.class.php');
		$result = NotificationsManager::getInstance ()->sendInvitation ($recordId);
		if ($result == 0) {
			$smarty->assign ('IS_ERROR', false);
			$smarty->assign ('MESSAGE', 'Se ha reenviado la invitación');
		} else {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', 'Imposible enviar la invitación. Notifique al administrador de la aplicación');
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
	}
	require_once ('modules/invitations/DetailView.php');
