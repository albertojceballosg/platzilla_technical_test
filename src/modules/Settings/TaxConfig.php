<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/InventoryUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/TaxHelper.class.php');

	global $adb, $app_strings, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$addInventoryTax     = SettingsUtils::purify ($_REQUEST, 'add_tax_type');
	$addShippingTax      = SettingsUtils::purify ($_REQUEST, 'sh_add_tax_type');
	$deleteInventoryTax = SettingsUtils::purify ($_REQUEST, 'delete');
	$disableInventoryTax = SettingsUtils::purify ($_REQUEST, 'disable');
	$disableShippingTax  = SettingsUtils::purify ($_REQUEST, 'sh_disable');
	$editInventoryTax    = SettingsUtils::purify ($_REQUEST, 'edit_tax');
	$editShippingTax     = SettingsUtils::purify ($_REQUEST, 'sh_edit_tax');
	$enableInventoryTax  = SettingsUtils::purify ($_REQUEST, 'enable');
	$enableShippingTax   = SettingsUtils::purify ($_REQUEST, 'sh_enable');
	$saveInventoryTax    = SettingsUtils::purify ($_REQUEST, 'save_tax');
	$saveShippingTax     = SettingsUtils::purify ($_REQUEST, 'sh_save_tax');

	$sw     = '';
	$swEdit = '';
	$error  = '';
	if ($addInventoryTax == 'true') {
		try {
			TaxHelper::addTax ($adb, SettingsUtils::purify ($_REQUEST, 'addTaxLabel'), SettingsUtils::purify ($_REQUEST, 'addTaxValue'));
		} catch (Exception $e) {
			if ($e->getMessage () == TaxHelper::ERROR_LABEL_ALREADY_REGISTERED) {
				$sw = 1;
			} else if ($e->getMessage () == TaxHelper::ERROR_ADDING_TAX) {
				$error = 3;
			}
		}
	} else if ($addShippingTax == 'true') {
		try {
			TaxHelper::addTax ($adb, SettingsUtils::purify ($_REQUEST, 'sh_addTaxLabel'), SettingsUtils::purify ($_REQUEST, 'sh_addTaxValue'), true);
		} catch (Exception $e) {
			if ($e->getMessage () == TaxHelper::ERROR_LABEL_ALREADY_REGISTERED) {
				$sw = 1;
			} else if ($e->getMessage () == TaxHelper::ERROR_ADDING_TAX) {
				$error = 3;
			}
		}
	} else if ($deleteInventoryTax == 'true') {
		TaxHelper::deleteTax ($adb, SettingsUtils::purify ($_REQUEST, 'taxname'));
	} else if ($disableInventoryTax == 'true') {
		TaxHelper::markAsDeleted ($adb, SettingsUtils::purify ($_REQUEST, 'taxname'), 1);
	} else if ($disableShippingTax == 'true') {
		TaxHelper::markAsDeleted ($adb, SettingsUtils::purify ($_REQUEST, 'sh_taxname'), 1, true);
	} else if ($enableInventoryTax == 'true') {
		TaxHelper::markAsDeleted ($adb, SettingsUtils::purify ($_REQUEST, 'taxname'), 0);
	} else if ($enableShippingTax == 'true') {
		TaxHelper::markAsDeleted ($adb, SettingsUtils::purify ($_REQUEST, 'sh_taxname'), 0, true);
	} else if ($saveInventoryTax == 'true') {
		try {
			$taxes       = getAllTaxes ();
			$labels      = array ();
			$percentages = array ();
			foreach ($taxes as $tax) {
				$labels [ $tax ['taxid'] ]      = SettingsUtils::purify ($_REQUEST, bin2hex ($tax ['taxlabel']));
				$percentages [ $tax ['taxid'] ] = SettingsUtils::purify ($_REQUEST, $tax ['taxname']);
			}
			TaxHelper::updateTaxLabels ($adb, $labels);
			TaxHelper::updateTaxPercentages ($adb, $percentages);
		} catch (Exception $e) {
			$swEdit = $e->getMessage ();
		}
	} else if ($saveShippingTax == 'true') {
		try {
			$taxes       = getAllTaxes ('all', 'sh');
			$labels      = array ();
			$percentages = array ();
			foreach ($taxes as $tax) {
				$labels [ $tax ['taxid'] ]      = SettingsUtils::purify ($_REQUEST, bin2hex ($tax ['taxlabel']));
				$percentages [ $tax ['taxid'] ] = SettingsUtils::purify ($_REQUEST, $tax ['taxname']);
			}
			TaxHelper::updateTaxLabels ($adb, $labels, true);
			TaxHelper::updateTaxPercentages ($adb, $percentages, true);
		} catch (Exception $e) {
			$swEdit = $e->getMessage ();
		}
	}

	$inventoryTaxes = getAllTaxes ();
	$shippingTaxes  = getAllTaxes ('all', 'sh');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('EDIT_MODE', $editInventoryTax);
	$smarty->assign ('ERROR', $error);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('SH_EDIT_MODE', $editShippingTax);
	$smarty->assign ('SH_TAX_COUNT', count ($shippingTaxes));
	$smarty->assign ('SH_TAX_VALUES', $shippingTaxes);
	$smarty->assign ('SW', $sw);
	$smarty->assign ('SW_EDIT', $swEdit);
	$smarty->assign ('TAX_COUNT', count ($inventoryTaxes));
	$smarty->assign ('TAX_VALUES', $inventoryTaxes);
	$smarty->assign ('THEME', $theme);
	$smarty->display ('Settings/TaxConfig.tpl');
