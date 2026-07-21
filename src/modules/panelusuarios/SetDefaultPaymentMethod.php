<?php
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	$paymentMethodId = PlatzillaUtils::purify ($_POST, 'paymentmethodid');

	try {
		if (empty ($paymentMethodId)) {
			throw new Exception ('No se ha suministrado el identificador del método de pago');
		}

		PaymentGatewayManager::getInstance ()->setInstanceDefaultPaymentMethod ($_SESSION ['platInstancia'], $paymentMethodId);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se han actualizado los métodos de pago',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Home&action=CustomerView&tab=subscription');
	exit ();
