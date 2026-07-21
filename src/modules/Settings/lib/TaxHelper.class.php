<?php

	abstract class TaxHelper {
		const ERROR_LABEL_ALREADY_REGISTERED = 1;
		const ERROR_DUPLICATED_TAB_LABELS    = 2;
		const ERROR_ADDING_TAX               = 3;

		public static function addTax (PearDatabase $adb, $label, $value, $shipping = false) {
			// First we will check whether the tax is already available or not
			if ($shipping) {
				$sql = 'SELECT taxlabel FROM vtiger_shippingtaxinfo WHERE taxlabel=?';
			} else {
				$sql = 'SELECT taxlabel FROM vtiger_inventorytaxinfo WHERE taxlabel=?';
			}
			$result = $adb->pquery ($sql, array ($label));
			if ($adb->num_rows ($result) > 0) {
				throw new Exception (self::ERROR_LABEL_ALREADY_REGISTERED);
			}

			// if the tax is not available then add this tax.
			// Add this tax as a column in related table
			if ($shipping) {
				$taxId   = $adb->getUniqueID ('vtiger_shippingtaxinfo');
				$taxName = "shtax{$taxId}";
				$sql     = "ALTER TABLE vtiger_inventoryshippingrel ADD COLUMN {$taxName} DECIMAL(7,3) DEFAULT NULL";
			} else {
				$taxId   = $adb->getUniqueID ('vtiger_inventorytaxinfo');
				$taxName = "tax{$taxId}";
				$sql     = "ALTER TABLE vtiger_inventoryproductrel ADD COLUMN {$taxName} DECIMAL(7,3) DEFAULT NULL";
			}
			$result = $adb->query ($sql);
			if (!$result) {
				throw new Exception (self::ERROR_ADDING_TAX);
			}

			// if the tax is added as a column then we should add this tax in the list of taxes
			if ($shipping) {
				$sql = 'INSERT INTO vtiger_shippingtaxinfo VALUES (?, ?, ?, ?, ?)';
			} else {
				$sql = 'INSERT INTO vtiger_inventorytaxinfo VALUES (?, ?, ?, ?, ?)';
			}
			$result = $adb->pquery ($sql, array ($taxId, $taxName, $label, $value, 0));
			if (!$result) {
				throw new Exception (self::ERROR_ADDING_TAX);
			}
		}

		public static function deleteTax (PearDatabase $adb, $name, $shipping = false) {
			if ($shipping) {
				$adb->pquery ('DELETE FROM vtiger_shippingtaxinfo WHERE taxname=?', array ($name));
			} else {
				$adb->pquery ('DELETE FROM vtiger_inventorytaxinfo WHERE taxname=?', array ($name));
			}
		}

		public static function markAsDeleted (PearDatabase $adb, $name, $deleted, $shipping = false) {
			if ($shipping) {
				$adb->pquery ('UPDATE vtiger_shippingtaxinfo SET deleted=? WHERE taxname=?', array ($deleted, $name));
			} else {
				$adb->pquery ('UPDATE vtiger_inventorytaxinfo SET deleted=? WHERE taxname=?', array ($deleted, $name));
			}
		}

		public static function updateTaxLabels (PearDatabase $adb, $labels, $shipping = false) {
			$duplicatedTaxLabels = 0;
			foreach ($labels as $taxId => $label) {
				if (empty ($label)) {
					continue;
				}

				if ($shipping) {
					$sql = 'SELECT taxlabel FROM vtiger_shippingtaxinfo WHERE taxlabel=? AND taxid<>?';
				} else {
					$sql = 'SELECT taxlabel FROM vtiger_inventorytaxinfo WHERE taxlabel=? AND taxid<>?';
				}
				$result = $adb->pquery ($sql, array ($label, $taxId));
				if ($adb->num_rows ($result) > 0) {
					$duplicatedTaxLabels++;
					continue;
				}

				if ($shipping) {
					$sql = 'UPDATE vtiger_shippingtaxinfo SET taxlabel=? WHERE taxid=?';
				} else {
					$sql = 'UPDATE vtiger_inventorytaxinfo SET taxlabel=? WHERE taxid=?';
				}
				$adb->pquery ($sql, array ($label, $taxId));
			}
			if ($duplicatedTaxLabels > 0) {
				throw new Exception (self::ERROR_DUPLICATED_TAB_LABELS);
			}
		}

		public static function updateTaxPercentages (PearDatabase $adb, $percentages, $shipping = false) {
			foreach ($percentages as $taxId => $percentage) {
				if (empty ($percentage)) {
					continue;
				}

				if ($shipping) {
					$sql = 'UPDATE vtiger_shippingtaxinfo SET percentage=? WHERE taxid=?';
				} else {
					$sql = 'UPDATE vtiger_inventorytaxinfo SET percentage =? WHERE taxid=?';
				}
				$adb->pquery ($sql, array ($percentage, $taxId));
			}
		}

	}
