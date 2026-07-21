<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');

	global $adb, $currentModule, $current_user;

	$dataParley = array (
		'message'            => PlatzillaUtils::purify ($_REQUEST, 'message'),
		'name'               => PlatzillaUtils::purify ($_REQUEST, 'chatName'),
		'userid'             => $current_user->id,
		'userEmail'          => $current_user->email1,
		'src'                => PlatzillaUtils::purify ($_REQUEST, 'src'),
		'record'             => PlatzillaUtils::purify ($_REQUEST, 'record'),
		'module'             => PlatzillaUtils::purify ($_REQUEST, 'module'),
		'recordShare'        => PlatzillaUtils::purify ($_REQUEST, 'recordShare'),
		'typeShare'          => PlatzillaUtils::purify ($_REQUEST, 'typeShare'),
		'whomShare'          => PlatzillaUtils::purify ($_REQUEST, 'whomShare'),
		'parleyTitle'        => PlatzillaUtils::purify ($_REQUEST, 'parleyTitle'),
		'relatedUsers'       => PlatzillaUtils::purify ($_REQUEST, 'relatedUsers'),
		'newChat'            => PlatzillaUtils::purify ($_REQUEST,'newChat'),
		'sourceinstancecode' => $_SESSION ['platInstancia'],
	);

	NotificationHelper::saveParleyFromRecord ($adb, $dataParley);

	$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=?', array ($_REQUEST ['recordShare']));
	if ($adb->num_rows ($result) > 0) {
		$row = $adb->fetchByAssoc ($result, -1, false);
		if (in_array ($row ['setype'], array ('clientes', 'contactos', 'potenciales_clientes', 'proveedores'))) {
			$entity = PlatformUtils::getCrmEntity ($adb, $row ['setype'], $_REQUEST ['recordShare']);
			$fieldName = in_array ($row ['setype'], array ('contactos', 'proveedores')) ? 'email' : 'e_mail';
			if (!empty ($entity->column_fields [$fieldName])) {
				require_once ('modules/emailmanager/emailmanager.php');
				$status = emailmanager::getInstance ($adb, $_SESSION ['plat'])->addSender (
					'Platzilla',
					'no_reply@platzilla.com'
				)->send (
					$entity->column_fields [ $fieldName ],
					'es',
					'Mensaje enviado desde Platzilla',
					array ('MENSAJE' => $_REQUEST ['message'])
				);
			}
		}
	}
	exit ();
