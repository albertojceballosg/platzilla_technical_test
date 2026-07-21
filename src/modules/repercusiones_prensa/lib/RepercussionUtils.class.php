<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class RepercussionUtils {
		private static function getAttachments (PearDatabase $adb, $repercussionId, $platzillaRootUri) {
			$result = $adb->pquery (
				'SELECT
						a.attachmentsid,
						a.name,
						a.path
					FROM
						vtiger_senotesrel senr
						INNER JOIN vtiger_seattachmentsrel sear ON sear.crmid=senr.notesid
						INNER JOIN vtiger_attachments a ON a.attachmentsid=sear.attachmentsid
						INNER JOIN vtiger_crmentity crmen ON crmen.crmid=senr.notesid AND crmen.deleted=0
						INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					WHERE
						senr.crmid=?',
				array ($repercussionId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();

			$attachments = array ();
			while ($attachment = $adb->fetchByAssoc ($result, -1, false)) {
				$path           = rtrim ($attachment ['path'], '/');
				$attachments [] = array (
					'filepath' => "{$platzillaRootFolderPath}/{$path}/{$attachment ['attachmentsid']}_{$attachment ['name']}",
					'name'     => $attachment ['name'],
					'url'      => "{$platzillaRootUri}/{$path}/{$attachment ['attachmentsid']}_{$attachment ['name']}",
				);
			}
			return $attachments;
		}

		public static function buildTinyUrl ($url) {
			$url = trim ($url);
			if (empty ($url)) {
				return null;
			}
			$protocol = !empty ($_SERVER ['SERVER_PROTOCOL']) ? strtolower (substr ($_SERVER ['SERVER_PROTOCOL'], 0, strpos ($_SERVER ["SERVER_PROTOCOL"], '/'))) : 'http';
			if (!preg_match ('/^http/i', $url)) {
				$url = "{$protocol}://{$url}";
			}

			$ch      = curl_init ();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, "http://tinyurl.com/api-create.php?url={$url}");
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$tinyUrl = curl_exec ($ch);
			curl_close ($ch);
			return $tinyUrl;
		}

		public static function deleteAttachmentsNotInList (PearDatabase $adb, $repercussionId, array $attachmentIds) {
			if (empty ($attachmentIds)) {
				return;
			}

			$questionMarks = str_repeat ('?, ', count ($attachmentIds) - 1) . '?';
			$result        = $adb->pquery (
				"SELECT
					nr.notesid,
					ar.attachmentsid
				FROM
					vtiger_senotesrel nr
					INNER JOIN vtiger_seattachmentsrel ar ON ar.crmid=nr.notesid
				WHERE
					nr.crmid=? AND
					ar.attachmentsid NOT IN ({$questionMarks})",
				array_merge (array ($repercussionId), $attachmentIds)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$adb->startTransaction ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$adb->pquery ('DELETE FROM vtiger_attachments WHERE attachmentsid=?', array ($row ['attachmentsid']));
				$adb->pquery ('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid=?', array ($row ['attachmentsid']));
				$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($row ['attachmentsid']));

				$adb->pquery ('DELETE FROM vtiger_notes WHERE notesid=?', array ($row ['notesid']));
				$adb->pquery ('DELETE FROM vtiger_senotesrel WHERE notesid=?', array ($row ['notesid']));
				$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($row ['notesid']));
			}
			$adb->completeTransaction ();
		}

		public static function getRepercussionById (PearDatabase $adb, $repercussionId, $platzillaRootUri) {
			$result = $adb->pquery (
				'SELECT
					r.*,
					m.nombre_de_la_entidad AS medio
				FROM
					vtiger_repercusiones_prensa r
					INNER JOIN vtiger_crmentity crmer ON crmer.crmid=r.repercusiones_prensaid AND crmer.deleted=0
					LEFT JOIN vtiger_medios_bdi m ON m.medios_bdiid=r.medio_donde_apar
				WHERE
					r.repercusiones_prensaid=?',
				array ($repercussionId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row                 = $adb->fetchByAssoc ($result, -1, false);
			$row ['attachments'] = self::getAttachments ($adb, $row ['repercusiones_prensaid'], $platzillaRootUri);
			$row ['tinyurl']     = self::buildTinyUrl ($row ['url']);
			return $row;
		}

		public static function getRepercussionsByCustomerId (PearDatabase $adb, $customerId, array $repercussionIds, $platzillaRootUri) {
			if ((empty ($repercussionIds)) || (empty ($customerId))) {
				return null;
			}

			$questionMarks = str_repeat ('?, ', (count ($repercussionIds) - 1)) . '?';
			$result        = $adb->pquery (
				"SELECT
					r.*,
					m.nombre_de_la_entidad AS medio
				FROM
					vtiger_repercusiones_prensa r
					INNER JOIN vtiger_crmentity crmer ON crmer.crmid=r.repercusiones_prensaid AND crmer.deleted=0
					INNER JOIN vtiger_clientes_bdi c ON c.clientes_bdiid=r.relacionado_con
					LEFT JOIN vtiger_medios_bdi m ON m.medios_bdiid=r.medio_donde_apar
				WHERE
					c.clientes_bdiid=? AND
					r.repercusiones_prensaid IN ({$questionMarks})",
				array_merge (array ($customerId), $repercussionIds)
			);
			if ((!$result) && ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$repercussions = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['attachments'] = self::getAttachments ($adb, $row ['repercusiones_prensaid'], $platzillaRootUri);
				$row ['tinyurl']     = self::buildTinyUrl ($row ['url']);
				$repercussions []    = $row;
			}
			return $repercussions;
		}

	}
