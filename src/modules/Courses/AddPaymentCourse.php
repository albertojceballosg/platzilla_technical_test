<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $current_user, $site_URL, $adb;
	setBugSnag ($site_URL);
	
	try {
		$courseId     = PlatzillaUtils::purify ($_POST, 'record');
		$returnModule = PlatzillaUtils::purify ($_POST, 'remodule');
		$returnAction = PlatzillaUtils::purify ($_POST, 'reaction');
		if (empty ($courseId)) {
			$courseId = PlatzillaUtils::purify ($_GET, 'record');
			if (empty ($courseId)) {
				throw new Exception ('Error! en el curso seleccionado');
			}
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		if (CoursesHelper::isPaidInstance ($masterAdb, $courseId, $_SESSION ['platInstancia'])) {
			$_SESSION ['flashmessage']['message'] = "Usted ya ha realizado el pago!&nbsp;<a href='index.php?module=Courses&action=CourseView&record={$courseId}' title='ver el curso' class='btn btn-success'>ver el curso <i class='fa fa-play'></i></a>";
			$_SESSION ['flashmessage']['iserror'] = true;
		}

		$course    = CoursesHelper::fetchCourseById ($masterAdb,$courseId, null, $adb, $current_user->id);

		// --- Usuarios activos de la instancia hija que NO han pagado el curso ---
		$instanceCode = $_SESSION['platInstancia'];
		$childDbName = "pg_crm_" . $instanceCode;
		$query = "
			SELECT iu.username
			FROM vtiger_instanceusers AS iu
			INNER JOIN {$childDbName}.vtiger_users AS us 
				ON us.user_name = iu.username AND us.status = 'Active'
			WHERE iu.instancecode = ?
			  AND iu.username NOT IN (
				SELECT cp.user_name FROM vtiger_courses_paid AS cp 
				WHERE cp.code = ? AND cp.courseid = ?
			  )
		";
		$result = $masterAdb->pquery($query, array($instanceCode, $instanceCode, $courseId));
		$usersNotPaid = array();
		if ($result && $masterAdb->num_rows($result) > 0) {
			while ($row = $masterAdb->fetchByAssoc($result)) {
				$usersNotPaid[] = $row['username'];
			}
		}
// --- FIN usuarios activos de la instancia hija que NO han pagado el curso ---

		$pgm       = PaymentGatewayManager::getInstance ();
		$countries = PlatzillaUtils::getCountries ();
		$token     = $pgm->generateClientToken ();
		$customer  = (!empty ($_SESSION ['platInstancia'])) ? $pgm->fetchInstanceCustomer ($_SESSION ['platInstancia']) : null;
		
		/* Cambio en la lógica: Si el usuario no tiene id=1, entonces siempre pide el método de pago 
		*  para evitar que use el medio de pago usado para la instancia que debe ser administrado 
		*  por el dueño de la instancia. (2025-03 / GGC)
		*/
		$authentic_user_id = $_SESSION["authenticated_user_id"];
		if (!empty ($customer) && $authentic_user_id === "1" ) {
			$creditCards = $customer->creditCards;
			$customerAddresses = $customer->addresses;
			if (!empty ($customerAddresses)) {
				foreach ($customerAddresses as $customerAddress) {
					$addresses [ $customerAddress->id ] = array (
						'city'            => $customerAddress->locality,
						'company'         => $customerAddress->company,
						// @codingStandardsIgnoreStart
						'countrycode'     => $customerAddress->countryCodeAlpha2,
						// @codingStandardsIgnoreEnd
						'countryname'     => $customerAddress->countryName,
						'extendedaddress' => $customerAddress->extendedAddress,
						'firstname'       => $customerAddress->firstName,
						'lastname'        => $customerAddress->lastName,
						'state'           => $customerAddress->region,
						'streetaddress'   => $customerAddress->streetAddress,
						'zipcode'         => $customerAddress->postalCode,
					);
				}
			} else {
				$addresses = null;
			}
		} else {
			$addresses   = null;
			$creditCards = null;
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ADDRESSES', $addresses);
		$smarty->assign ('COURSE', $course);
		$smarty->assign ('COUNTRIES', $countries);
		$smarty->assign ('CUSTOMER_CC', $creditCards);
		$smarty->assign ('RETUR_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		$smarty->assign ('TOKEN', $token);
		$smarty->assign('USERS_NOT_PAID', $usersNotPaid);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->assign ('USER_NAME', $_SESSION ['authenticated_user_email']);
		$smarty->display ('modules/Courses/AddPaymentMethodFromCourse.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module={$returnModule}&{$returnAction}");
		$smarty->display ('Message.tpl');
	}
