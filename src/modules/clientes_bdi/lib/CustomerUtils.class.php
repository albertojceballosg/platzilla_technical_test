<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class CustomerUtils {

		public static function getCustomerById (PearDatabase $adb, $customerId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_clientes_bdi WHERE clientes_bdiid=?', array ($customerId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$customer             = $adb->fetchByAssoc ($result, -1, false);
			$customer ['logourl'] = self::getLogoUri ($adb, $customerId);
			return $customer;
		}

		public static function getLogoUri (PearDatabase $adb, $customerId) {
			$result = $adb->pquery (
				"SELECT
						a.attachmentsid,
						a.name,
						a.path
					FROM
						vtiger_senotesrel senr
						INNER JOIN vtiger_notes n ON n.notesid=senr.notesid
						INNER JOIN vtiger_seattachmentsrel sear ON sear.crmid=senr.notesid
						INNER JOIN vtiger_attachments a ON a.attachmentsid=sear.attachmentsid
						INNER JOIN vtiger_crmentity crmen ON crmen.crmid=senr.notesid AND crmen.deleted=0
						INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					WHERE
						n.title='Logo' AND
						senr.crmid=?",
				array ($customerId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$platzillaRootUri = PlatzillaUtils::getPlatzillaRootUri ();
			$attachment       = $adb->fetchByAssoc ($result, -1, false);
			$path             = rtrim ($attachment ['path'], '/');

			return "{$platzillaRootUri}/{$path}/{$attachment ['attachmentsid']}_{$attachment ['name']}";
		}

	}
