<?php
	require_once ('include/utils/AdbManager.class.php');

	function getInvoiceDescription ($countApp, $usersCounter) {
		$applicationsText = $countApp == 1 ? '1 aplicación' : "$countApp aplicaciones";
		$description      = "Pago suscripción de $applicationsText ";
		if (($usersCounter > 1) && ($countApp == 0)) {
			$description .= "Pago suscripción de $usersCounter usuarios \r\n";
		} else if (($usersCounter > 1) && ($countApp >= 1)) {
			$description .= "y de $usersCounter usuarios \r\n";
		}
		return $description;
	}

	function generaFacturaApps () {
		require_once ('modules/myinvoice/myinvoice.php');

		$today = date ('Y-m-d H:i:s');
		$adb   = AdbManager::getInstance ()->getMasterAdb ();
		$adb->query ('START TRANSACTION');

		// Inserción del registro en facturas
		$result    = $adb->query ('SELECT id FROM vtiger_crmentity_seq');
		$id        = $adb->query_result ($result, 0, 'id');
		$invoiceId = ($id + 10);
		$subject   = "Pago plataforma {$_SESSION ['plat']}";

		$result      = $adb->query ("SELECT prefix, cur_id FROM vtiger_modentity_num WHERE semodule='myinvoice'");
		$prefix      = $adb->query_result ($result, 0, 'prefix');
		$curId       = $adb->query_result ($result, 0, 'cur_id');
		$invoiceCode = "{$prefix}{$curId}";
		$contactId   = $_SESSION ['customerid'];
		$adb->pquery (
			'INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, modifiedby, setype, createdtime, modifiedtime, presence) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
			array ($invoiceId, 1, 1, 1, 'myinvoice', $adb->formatDate ($today, true), $adb->formatDate ($today, true), 1)
		);
		$adb->pquery (
			'INSERT INTO vtiger_myinvoice (myinvoiceid, subject, invoice_no, invoicedate, duedate, account_id, invoicestatus, adjustment, salescommission, exciseduty, taxtype) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array ($invoiceId, $subject, $invoiceCode, date ('Y-m-d'), date ('Y-m-d'), $contactId, 'Paid', 0.00, 0.00, 0.00, 'individual')
		);
		$adb->pquery (
			'INSERT INTO vtiger_myinvoicecf (myinvoiceid) VALUES (?)',
			array ($invoiceId)
		);

		$sequence     = 1;
		$totalPrice   = 0;
		$usersCounter = $_SESSION ['usersCounter'];
		if ($usersCounter > 1) {
			$result    = $adb->pquery ('SELECT serviceid,unit_price FROM vtiger_service WHERE servicename=?', array ('Suscripción de Usuarios'));
			$serviceId = $adb->query_result ($result, 0, 'serviceid');
			$unitPrice = $adb->query_result ($result, 0, 'unit_price');
			$adb->pquery (
				'INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_percent, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?)',
				array ($invoiceId, $serviceId, $sequence, $usersCounter, $unitPrice, 0.00, 0.00)
			);
			$totalPrice += ($unitPrice * $usersCounter);
			$sequence++;
		}

		$countApp = 0;
		foreach ($_SESSION ['appsToContract'] as $application) {
			$result    = $adb->pquery ('SELECT productid, unit_price FROM vtiger_product WHERE aplicationid=?', array ($application ['appId']));
			$productId = $adb->query_result ($result, 0, 'productid');
			$unitPrice = $adb->query_result ($result, 0, 'unit_price');
			if ((empty ($productId)) && (empty ($unitPrice))) {
				continue;
			}
			$totalPrice += $unitPrice;
			$adb->pquery (
				'INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_percent, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?)',
				array ($invoiceId, $productId, $sequence, 1, $unitPrice, 0.00, 0.00)
			);
			$sequence++;
			$countApp++;
		}
		$adb->pquery (
			'UPDATE vtiger_myinvoice SET subtotal=?, total=?, description=? WHERE myinvoiceid=?',
			array ($totalPrice, $totalPrice, getInvoiceDescription ($countApp, $usersCounter), $invoiceId)
		);
		$adb->pquery (
			'INSERT INTO vtiger_documentsbillship (documentsbillshipid, bill_city, bill_code, bill_country, bill_state, bill_street, bill_pobox, ship_city, ship_code, ship_country, ship_state, ship_street, ship_pobox)
				SELECT
					?, b.bill_city, b.bill_code, b.bill_country, b.bill_state, b.bill_street, b.bill_pobox, s.ship_city, s.ship_code, s.ship_country, s.ship_state, s.ship_street, s.ship_pobox
				FROM
					vtiger_accountbillads b
					LEFT JOIN vtiger_accountshipads s ON s.accountaddressid=b.accountaddressid
				WHERE
					b.accountaddressid = ?',
			array ($invoiceId, $contactId)
		);
		$adb->pquery ('UPDATE vtiger_crmentity_seq SET id=?', array ($id + 10));
		$adb->pquery ("UPDATE vtiger_modentity_num SET cur_id=? WHERE semodule='myinvoice'", array ($curId + 1));
		$adb->pquery ('UPDATE vtiger_instancias SET numusuarios=? WHERE code=?', array ($_SESSION ['usersCounter'], $_SESSION ['plat']));
		$adb->query ('COMMIT');
	}
