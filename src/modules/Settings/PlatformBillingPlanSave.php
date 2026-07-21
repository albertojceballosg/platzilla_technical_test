<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Managers/PlatformBillingPlanManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$basePrice = PlatzillaUtils::purify ($_POST, 'baseprice');
		$description       = PlatzillaUtils::purify ($_POST, 'description');
		$planId            = PlatzillaUtils::purify ($_POST, 'record');
		$planName          = PlatzillaUtils::purify ($_POST, 'planname');
		$status            = PlatzillaUtils::purify ($_POST, 'status');
		$totalApplications = PlatzillaUtils::purify ($_POST, 'totalapplications');
		$totalDiskSpace    = PlatzillaUtils::purify ($_POST, 'totaldiskspace');
		$totalUsers        = PlatzillaUtils::purify ($_POST, 'totalusers');

		$pbpm = PlatformBillingPlanManager::getInstance ($adb);
		if (!empty ($planId)) {
			$plan = $pbpm->fetchPlan ($planId);
			$product = $plan->getProduct ();
		} else {
			$plan = PlatformBillingPlan::getInstance ();
			$product = Product::getInstance ();
		}
		if (empty ($plan)) {
			throw new Exception ('El plan suministrado no está registrado');
		}

		$product->setBasePrice ($basePrice)
			->setName ($planName)
			->setType (Product::TYPE_SERVICE);
		$plan->setDescription ($description)
			->setId ($planId)
			->setName ($planName)
			->setProduct ($product)
			->setStatus ($status)
			->setTotalApplications ($totalApplications)
			->setTotalDiskSpace ($totalDiskSpace)
			->setTotalUsers ($totalUsers);
		$pbpm->savePlan ($plan);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El plan ha sido guardado',
		);
		header ('Location: index.php?module=Settings&action=PlatformBillingPlanListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($plan) ? $plan->serialize () : null,
		);
		$recordUriPart             = !empty ($planId) ? "&record={$planId}" : '';
		header ("Location: index.php?module=Settings&action=PlatformBillingPlanEditView{$recordUriPart}&parenttab=Settings");
	}
	exit ();
