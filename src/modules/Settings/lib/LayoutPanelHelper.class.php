<?php
	require_once ('include/utils/PanelUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	abstract class LayoutPanelHelper {

		private static function getColumnParameters ($axisColumn, $fieldOp, $label, $graficar) {
			$parameters           = new stdClass ();
			$parameters->titulo   = $label;
			$parameters->graficar = $graficar == 1 ? 'yes' : 'no';

			if ($axisColumn == 1) {
				$dummy               = explode (':', $fieldOp);
				$table               = $dummy [0];
				$field               = $dummy [1];
				$typedata            = $dummy [4];
				$parameters->table   = $table;
				$parameters->field   = $field;
				$parameters->groupby = ($typedata == 'D') || ($typedata == 'DT') || ($field == 'createdtime') || ($field == 'modifiedtime') ? 'month' : 'value';
			}
			return $parameters;
		}

		public static function getPanelDashColumnParameters ($columnIndex, $panelId) {
			if (!$columnIndex) {
				return null;
			}
			$columns = getColumnsPanelDash ($panelId, $columnIndex);
			if (empty ($columns)) {
				return null;
			}

			$parameters = null;
			foreach ($columns as $column) {
				$parameters = json_decode (html_entity_decode ($column));
			}
			return $parameters;
		}

		public static function getColumns ($panelId, $customView, $relatedModule, $moduleColumns) {
			if (!$moduleColumns) {
				return null;
			}

			$columns = array ();
			for ($i = 0; $i < 10; $i++) {
				$selected = getFieldSelectedByPanelId ($panelId, $i);
				$list     = explode (':', $selected);
				if (isset ($list [5])) {
					unset ($list [5]);
					$selected = join (':', $list);
				}
				$columns [] = getByModule_ColumnsList ($customView, $relatedModule, $moduleColumns, $selected);
			}
			return $columns;
		}

		public static function getPanelEntries ($moduleName) {
			$entries = getListPanelGraph (getTabid ($moduleName));
			$keys    = array_keys ($entries);
			foreach ($keys as $key) {
				$entries [ $key ]['relatedmodule']     = getTabname ($entries [ $key ]['reltabid']);
				$entries [ $key ]['relatedmodulename'] = getTabModuleName ($entries [ $key ]['reltabid']);
			}
			return $entries;
		}

		public static function savePanelOrGraph ($moduleName, $arguments) {
			$icon     = SettingsUtils::purify ($arguments, 'icon');
			$label    = SettingsUtils::purify ($arguments, 'label');
			$legend   = SettingsUtils::purify ($arguments, 'legend');
			$recordId = SettingsUtils::purify ($arguments, 'record');
			$subType  = SettingsUtils::purify ($arguments, 'subtype');
			$url      = SettingsUtils::purify ($arguments, 'url');

			if (!$recordId) {
				return;
			}

			deleteColumnsPanelOrGraph ($recordId);
			for ($i = 1; $i < 10; $i++) {
				$opColumn = SettingsUtils::purify ($arguments, "opcolumn{$i}");
				$column   = SettingsUtils::purify ($arguments, "column{$i}");
				if ((!$opColumn) || (!$column)) {
					continue;
				}
				saveColumnsPanelOrGraph ($recordId, $i, "{$column}:{$opColumn}");
			}

			deleteConditionsPanelOrGraph ($recordId);
			for ($i = 0; $i < 10; $i++) {
				$fcol = SettingsUtils::purify ($arguments, "fcol{$i}");
				$fop  = SettingsUtils::purify ($arguments, "fop{$i}");
				$fval = SettingsUtils::purify ($arguments, "fval{$i}");
				$fcon = SettingsUtils::purify ($arguments, "fcon{$i}");
				saveConditionsPanelOrGraph ($recordId, $i, 0, $fcol, $fop, $fval, 1, $fcon);
			}

			deleteLinksPanelOrGraph ($moduleName, $recordId);
			saveLinksPanelOrGraph ($moduleName, $recordId, $label, $url, $icon);

			if (($subType) && ($legend)) {
				saveGraphSettings ($subType, $legend, $recordId);
			}
		}

		public static function savePanelColumnProperties ($recordId, $arguments) {
			$axisColumn  = SettingsUtils::purify ($arguments, 'axiscolumn');
			$columnIndex = SettingsUtils::purify ($arguments, 'columnindex');
			$fieldOp     = SettingsUtils::purify ($arguments, 'fieldop');
			$graficar    = SettingsUtils::purify ($arguments, 'graficar');
			$label       = SettingsUtils::purify ($arguments, 'label');
			$opColumn    = SettingsUtils::purify ($arguments, 'opcolumn');

			if ($columnIndex === '') {
				$columnIndex = getLastColumnIndex ($recordId);
			}
			deleteConditionsPanelOrGraph ($recordId, $columnIndex);

			for ($i = 0; $i < 10; $i++) {
				$fcol = SettingsUtils::purify ($arguments, "fcol{$i}");
				$ffie = SettingsUtils::purify ($arguments, "ffie{$i}");
				$fop  = SettingsUtils::purify ($arguments, "fop{$i}");
				$fval = SettingsUtils::purify ($arguments, "fval{$i}");
				$fcon = SettingsUtils::purify ($arguments, "fcon{$i}");
				$val  = $fcol == 'other:other:other:other:N' ? $ffie : $fcol;
				saveConditionsPanelOrGraph ($recordId, $columnIndex, $i, $val, $fop, $fval, 1, $fcon);
			}

			$parameters = self::getColumnParameters ($axisColumn, $fieldOp, $label, $graficar);
			saveColumnParameters ($recordId, $columnIndex, json_encode ($parameters));

			if ($opColumn) {
				deleteColumnsPanelOrGraph ($recordId, $columnIndex);
				$fieldOp = "{$fieldOp}:{$opColumn}";
				saveColumnsPanelOrGraph ($recordId, ($columnIndex + 1), $fieldOp);
			}
		}

	}
