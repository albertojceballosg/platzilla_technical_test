<?php
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/platzilla/Managers/PaymentManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/instances/instances.php');

	global $platPrincipal;

	try {
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$result    = $masterAdb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($_SESSION ['platInstancia']));
		if ((!$result) || ($masterAdb->num_rows ($result) == 0)) {
			throw new Exception ('La instancia no se encuentra registrada');
		}

		$instanceData                   = $masterAdb->fetchByAssoc ($result, -1, false);
		$instanceData ['record_id']     = $instanceData ['instanceid'];
		$instanceData ['record_module'] = 'instances';
		$instance                       = instances::getInstance ();
		$instance->id                   = $instanceData ['instanceid'];
		$instance->column_fields        = $instanceData;

		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'MANUAL PAYMENT',
			BackgroundTaskInterface::EVENT_INSTANT_BEFORE,
			$instance
		);

		$pm       = PaymentManager::getInstance ($masterAdb);
		$payments = $pm->fetchPendingPayments ($_SESSION ['platInstancia'], date_create ('today'));
		if (!empty ($payments)) {
			$results = PaymentGatewayManager::getInstance ()->chargeInstanceCustomerPayments ($_SESSION ['platInstancia'], $payments);
			$errors  = array ();
			foreach ($results as $result) {
				if (!empty ($result->getLastErrorMessage ())) {
					$errors [] = $result->getLastErrorMessage ();
				}
				$pm->savePayment ($result);
			}

			$_SESSION ['flashmessage'] = array (
				'iserror' => count ($errors) > 0,
				'message' => count ($errors) > 0 ? 'Se han presentado errores:<br />' . join ('<br />', $errors) : 'Se han realizado los cargos a tu método de pago activo',
			);
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => 'No tienes deudas pendientes. no se realizó ningún cargo',
			);
		}

		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'MANUAL PAYMENT',
			BackgroundTaskInterface::EVENT_INSTANT_AFTER,
			$instance
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Home&action=CustomerView&tab=subscription');
	exit ();
