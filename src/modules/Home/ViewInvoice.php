<?php
	require_once ('include/platzilla/Utils/PdfCreator.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200330
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200330

	global $app_strings, $current_user, $theme;

	if ((!is_admin ($current_user)) || (empty ($_SESSION ['platInstancia']))) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$invoiceId = PlatzillaUtils::purify ($_GET, 'record');

	try {
		$invoice = HomeUtils::getInvoice ($_SESSION ['platInstancia'], $invoiceId);
		if (empty ($invoice)) {
			throw new Exception ('La factura solicitada no está registrada');
		}

		$customer     = HomeUtils::getCustomer ($_SESSION ['platInstancia']);
		$invoiceItems = $invoice->getItems ();
		if (!empty ($invoiceItems)) {
			$items = array ();
			foreach ($invoiceItems as $invoiceItem) {
				$items [] = array (
					'PRODUCT_NAME'        => $invoiceItem->getName (),
					'PRODUCT_QUANTITY'    => number_format ($invoiceItem->getQuantity (), 0, ',', '.'),
					'PRODUCT_UNIT_PRICE'  => number_format ($invoiceItem->getPrice (), 2, ',', '.'),
					'PRODUCT_TAX_AMOUNT'  => number_format ($invoiceItem->getPrice () * $invoiceItem->getTaxPercentage () / 100, 2, ',', '.'),
					'PRODUCT_TOTAL_PRICE' => number_format ($invoiceItem->getPrice () * (1 + $invoiceItem->getTaxPercentage () / 100), 2, ',', '.'),
				);
			}
		} else {
			$items = null;
		}

		$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
		$logoImagePath           = "{$platzillaRootFolderPath}/test/logo/platzi.png";
		$type                    = pathinfo ($logoImagePath, PATHINFO_EXTENSION);
		$data                    = file_get_contents ($logoImagePath);
		$base64Data              = base64_encode ($data);
		$logoImageData           = "data:image/{$type};base64,{$base64Data}";

		$variables    = array (
			'CREATION_DATE'            => $invoice->getCreationDate ()->format ('d/m/Y'),
			'CUSTOMER_BILLING_ADDRESS' => join (', ', array_filter (array ($customer ['direccion'], $customer ['ciudad'], $customer ['provincia'], $customer ['codigo_postal'], $customer ['pais']))),
			'CUSTOMER_CIF'             => !empty ($customer ['numero_fiscal']) ? $customer ['numero_fiscal'] : '',
			'CUSTOMER_NAME'            => $customer ['nombre_comercial'],
			'DUE_DATE'                 => $invoice->getDueDate ()->format ('d/m/Y'),
			'FOOTER'                   => '&copy; Platzilla. Todos los derechos reservados',
			'INVOICE_ITEMS'            => $items,
			'INVOICE_NUMBER'           => $invoice->getNumber (),
			'INVOICE_TOTAL'            => number_format ($invoiceItem->getTotalPrice (), 2, ',', '.'),
			'LANGUAGE'                 => 'es',
			'LOGO_URI'                 => $logoImageData,
			'PROVIDER_BILLING_ADDRESS' => 'Calle Santa Hortensia 46C, 28002 Madrid, España',
			'PROVIDER_CIF'             => 'B88087168',
			'PROVIDER_NAME'            => 'Platzilla Software S. L.',
			'TERMS_AND_CONDITIONS'     => 'Protección de Datos: En cumplimiento de la L.O. 15/ 1999 de 13 de Diciembre de Protección de Datos de Carácter Personal, Platzilla le informa que su dirección de correo electrónico, así como el resto de los datos de carácter personal que nos facilite , se encuentran incorporados en un fichero del que es responsable esta empresa y cuya finalidad es gestionar nuestra agenda de contactos y el envío de comunicaciones electrónicas profesionales y/o personales, comerciales e informativas. Así mismo se le informa que podrá ejercitar el derecho de acceso, rectificación, cancelación y oposición en info@platzilla.com. Confidencialidad. El contenido de esta comunicación, así como el de toda la documentación anexa, es confidencial, puede estar protegido por disposiciones legales y va dirigido únicamente al destinatario del mismo. En el supuesto de que usted no fuera el destinatario, le solicitamos que nos lo indique y no comunique su contenido a terceros, procediendo a su destrucción.',
			'TITLE'                    => "Factura {$invoice->getSubject ()}",
		);
		$fileContents = PdfCreator::createFromHtmlTemplate (__DIR__ . '/templates/Invoice.html', $variables);
		if (empty ($fileContents)) {
			throw new Exception ('Se ha presentado un error al crear la factura');
		}

		header ('Content-Type: application/pdf');
		header ('Cache-Control: private');
		header ('Pragma: no-cache');
		header ("Content-Disposition: attachment; filename=invoice.pdf");
		header ('Content-Type: application/pdf');
		if (empty ($_SERVER ['HTTP_ACCEPT_ENCODING'])) {
			// don't use length if server using compression
			header ('Content-Length: ' . strlen ($fileContents));
		}
		header ('Content-disposition: inline; filename="invoice.pdf"');
		header ('Cache-Control: public, must-revalidate, max-age=0');
		header ('Pragma: public');
		header ('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT');
		echo $fileContents;
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Home&action=CustomerView&tab=invoices');
		$smarty->display ('Message.tpl');
	}

