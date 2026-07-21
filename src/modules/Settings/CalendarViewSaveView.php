<?php
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$viewId     = PlatzillaUtils::purify ($_POST, 'record');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No estás autorizado a realizar acciones de configuración');
		}

		$ruleBackgroundColors = PlatzillaUtils::purify ($_POST, 'rulebackgroundcolors');
		$ruleFieldNames       = PlatzillaUtils::purify ($_POST, 'rulefieldnames');
		$ruleGlues            = PlatzillaUtils::purify ($_POST, 'ruleglues');
		$ruleIds              = PlatzillaUtils::purify ($_POST, 'ruleids');
		$ruleModuleNames      = PlatzillaUtils::purify ($_POST, 'rulemodulenames');
		$ruleOperators        = $_POST ['ruleoperators'];
		$ruleValues           = PlatzillaUtils::purify ($_POST, 'rulevalues');

		$isInstance = !empty ($_SESSION ['platInstancia']);

		if (!empty ($ruleIds)) {
			$rules = array ();
			foreach ($ruleIds as $thisIndex => $thiRuleIds) {
				if (!count ($thiRuleIds)) {
					continue;
				}
				foreach ($thiRuleIds as $index => $ruleId) {
					$rule[] = array(
						'ruleid'          => $ruleIds [$thisIndex][$index],
						'backgroundcolor' => $ruleBackgroundColors [$thisIndex][$index],
						'fieldname'       => $ruleFieldNames [$thisIndex][$index],
						'glue'            => isset ($ruleGlues [$thisIndex][$index]) ? $ruleGlues[$thisIndex][$index] : null,
						'modulename'      => $ruleModuleNames [$thisIndex][$index],
						'operator'        => html_entity_decode ($ruleOperators[$thisIndex][$index], ENT_QUOTES, 'UTF-8'),
						'value'           => $ruleValues [$thisIndex][$index],
					);
				}
				$rules [] = $rule;
				unset ($rule);
			}
		} else {
			$rules = null;
		}
		
		$arguments = array (
			'applicationcodes'  => PlatzillaUtils::purify ($_POST, 'applicationcodes'),
			'backgroundcolor'   => PlatzillaUtils::purify ($_POST, 'backgroundcolor', '#FFFFFF'),
			'calendarviewid'    => $viewId,
			'fromfieldname'     => PlatzillaUtils::purify ($_POST, 'fromfieldname'),
			'frommodulename'    => PlatzillaUtils::purify ($_POST, 'frommodulename'),
			'label'             => PlatzillaUtils::purify ($_POST, 'label'),
			'modulename'        => $moduleName,
			'titlefieldname'    => PlatzillaUtils::purify ($_POST, 'titlefieldname'),
			'subtitlefieldname' => PlatzillaUtils::purify ($_POST, 'subtitlefieldname'),
			'titlemodulename'   => PlatzillaUtils::purify ($_POST, 'titlemodulename'),
			'tofieldname'       => PlatzillaUtils::purify ($_POST, 'tofieldname'),
			'tomodulename'      => PlatzillaUtils::purify ($_POST, 'tomodulename'),
			'rules'             => $rules,
		);
		
		CalendarViewUtils::updateView ($adb, $arguments, $isInstance);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La vista ha sido guardada',
		);
		header ('Location: index.php?module=Settings&action=CalendarViewListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($arguments) ? $arguments : null,
		);
		$recordUriPart             = !empty ($viewId) ? "&record={$viewId}" : '';
		$moduleNameUriPart         = !empty ($moduleName) ? "&modulename={$moduleName}" : '';
		header ("Location: index.php?module=Settings&action=CalendarViewEditView{$moduleNameUriPart}{$recordUriPart}&parenttab=Settings");
	}
	exit ();
