<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$providerName = PlatzillaUtils::purify ($_POST, 'name');
		if (empty ($providerName)) {
			throw new Exception ('No has suministrado el nombre del proveedor a eliminar');
		}

		WebmailUtils::deleteEmailProvider ($adb, $providerName);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El proveedor ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=webmail&action=ProviderListView');
