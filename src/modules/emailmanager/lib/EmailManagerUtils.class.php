<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class EmailManagerUtils {
		const RECORDS_PER_PAGE = 25;

		private static function purify ($array, $key, $valueIfEmpty) {
			if ((!isset ($array [ $key ])) || (empty ($array [ $key ]))) {
				return $valueIfEmpty;
			} else {
				return $array [ $key ];
			}
		}

		private static function createTemplate (PearDatabase $adb, array $templateData, $platform) {
			$adb->pquery (
				'INSERT INTO vtiger_emailmanager_templates (templatename, language, subject, body, adddefaultheader, adddefaultfooter) VALUES (?, ?, ?, ?, ?, ?)',
				array ($templateData ['templatename'], $templateData ['language'], strip_tags ($templateData ['subject'], '<var></var>'), trim ($templateData ['body']), $templateData ['adddefaultheader'], $templateData ['adddefaultfooter'])
			);
			$templateId  = $adb->getLastInsertID ();
			$attachments = self::createTemplateAttachments ($templateId, $templateData, $platform);
			$adb->pquery ('UPDATE vtiger_emailmanager_templates SET attachments=? WHERE templateid=?', array (!empty ($attachments) ? json_encode ($attachments) : null, $templateId));
		}

		private static function createTemplateAttachments ($templateId, array $templateData, $platform) {
			if (!isset ($templateData ['attachments']['new'])) {
				return null;
			}

			$createdAttachments = array ();
			$attachments        = $templateData ['attachments']['new'];
			$attachmentIndexes  = array_keys ($attachments ['data']);
			$storageFolderPath  = self::getTemplateStorageFolderPath ($platform);
			foreach ($attachmentIndexes as $attachmentIndex) {
				$fileName = $attachments ['filename'][ $attachmentIndex ];
				$fileData = $attachments ['data'][ $attachmentIndex ];
				if ((empty ($fileName)) || (empty ($fileData))) {
					continue;
				}
				$attachmentFilePath = "{$storageFolderPath}/{$templateId}_{$fileName}";
				file_put_contents ($attachmentFilePath, base64_decode (str_replace (' ', '+', substr ($fileData, (strpos ($fileData, 'base64,') + 7)))));
				$createdAttachments [] = $fileName;
			}
			return count ($createdAttachments) > 0 ? $createdAttachments : null;
		}

		private static function deleteTemplateAttachments (PearDatabase $adb, $templateId, array $templateData, $platform) {
			$result = $adb->pquery ('SELECT attachments FROM vtiger_emailmanager_templates WHERE templateid=?', array ($templateId));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				if (!empty ($row ['attachments'])) {
					$existingAttachments = json_decode ($row ['attachments']);
				} else {
					$existingAttachments = array ();
				}
			} else {
				$existingAttachments = array ();
			}

			if (!empty ($templateData ['attachments']['old'])) {
				$attachmentsToKeep = $templateData ['attachments']['old']['filename'];
			} else {
				$attachmentsToKeep = array ();
			}

			$attachmentsToDelete = array_diff ($existingAttachments, $attachmentsToKeep);
			if (!empty ($attachmentsToDelete)) {
				$storageFolderPath = self::getTemplateStorageFolderPath ($platform);
				foreach ($attachmentsToDelete as $fileName) {
					$attachmentFilePath = "{$storageFolderPath}/{$templateId}_{$fileName}";
					if (file_exists ($attachmentFilePath)) {
						unlink ($attachmentFilePath);
					}
				}
			}

			return count ($attachmentsToKeep) > 0 ? $attachmentsToKeep : null;
		}

		private static function getEmailHistorySqlWhereClause (array $criteria = null) {
			if (empty ($criteria)) {
				return array ('', array ());
			}

			$whereClauses = array ();
			$arguments    = array ();
			if (!empty ($criteria ['date'])) {
				$whereClauses [] = 'emeh.createdon BETWEEN ? AND ?';
				$arguments []    = date_format (date_create ($criteria ['date']), 'Y-m-d 00:00:00');
				$arguments []    = date_format (date_create ($criteria ['date']), 'Y-m-d 23:59:59');
			}
			if (!empty ($criteria ['email'])) {
				$whereClauses [] = '(emeh.from LIKE ? OR emeh.to LIKE ?)';
				$arguments []    = "{$criteria ['email']}%";
				$arguments []    = "{$criteria ['email']}%";
			}
			if (!empty ($criteria ['subject'])) {
				$whereClauses [] = 'emeh.subject=?';
				$arguments []    = $criteria ['subject'];
			}
			if (!empty ($criteria ['status'])) {
				$whereClauses [] = 'emeh.status=?';
				$arguments []    = $criteria ['status'];
			}
			if (!empty ($criteria ['templatename'])) {
				$whereClauses [] = "CONCAT(emeh.templatename, '-', emeh.language)=?";
				$arguments []    = $criteria ['templatename'];
			}
			if (!empty ($criteria ['language'])) {
				$whereClauses [] = 'emeh.language=?';
				$arguments []    = $criteria ['language'];
			}
			return array ('WHERE ' . join (' AND ', $whereClauses), $arguments);
		}

		private static function getTemplateStorageFolderPath ($platform) {
			$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			if (!empty ($platform)) {
				$folderPath = "{$platzillaRootFolderPath}/{$platform}/storage/emailmanagertemplates";
			} else {
				$folderPath = "{$platzillaRootFolderPath}/storage/emailmanagertemplates";
			}

			if (!is_dir ($folderPath)) {
				$oldumask = umask (0);
				mkdir ($folderPath, 0777, true);
				umask ($oldumask);
			}
			return $folderPath;
		}

		private static function updateTemplate (PearDatabase $adb, array $templateData, $platform) {
			$adb->pquery (
				'UPDATE vtiger_emailmanager_templates SET templatename=?, language=?, subject=?, body=?, adddefaultheader=?, adddefaultfooter=? WHERE templateid=?',
				array ($templateData ['templatename'], $templateData ['language'], trim (strip_tags ($templateData ['subject'], '<var></var>')), trim ($templateData ['body']), $templateData ['adddefaultheader'], $templateData ['adddefaultfooter'], $templateData ['templateid'])
			);
			$oldAttachments = self::deleteTemplateAttachments ($adb, $templateData ['templateid'], $templateData, $platform);
			$newAttachments = self::createTemplateAttachments ($templateData ['templateid'], $templateData, $platform);
			$attachments    = array_merge (
				!empty ($oldAttachments) ? $oldAttachments : array (),
				!empty ($newAttachments) ? $newAttachments : array ()
			);
			$adb->pquery ('UPDATE vtiger_emailmanager_templates SET attachments=? WHERE templateid=?', array (!empty ($attachments) ? json_encode ($attachments) : null, $templateData ['templateid']));
		}

		private static function validateTemplateData (PearDatabase $adb, array $templateData) {
			if (empty ($templateData)) {
				throw new Exception ('No has suministrado la información de la plantilla');
			}
			if (empty ($templateData ['templatename'])) {
				throw new Exception ('No has suministrado el nombre de la plantilla');
			}
			if (empty ($templateData ['language'])) {
				throw new Exception ('No has suministrado el idioma de la plantilla');
			}
			if (empty ($templateData ['subject'])) {
				throw new Exception ('No has suministrado el asunto de la plantilla');
			}
			if (empty ($templateData ['body'])) {
				throw new Exception ('No has suministrado el contenido de la plantilla');
			}

			if (empty ($templateData ['templateid'])) {
				$result = $adb->pquery ('SELECT * FROM vtiger_emailmanager_templates WHERE templatename=? AND language=?', array ($templateData ['templatename'], $templateData ['language']));
				if (($result) && ($adb->num_rows ($result) > 0)) {
					throw new Exception ("Ya existe una plantilla con nombre {$templateData ['templatename']} para el idioma {$templateData ['language']}");
				}
			}
		}

		public static function deleteTemplate (PearDatabase $adb, $templateId) {
			$adb->pquery ('DELETE FROM vtiger_emailmanager_templates WHERE templateid=?', array ($templateId));
		}

		public static function getAvailableLanguages () {
			return array ('es', 'en', 'pt');
		}

		public static function getEmailById (PearDatabase $adb, $emailId) {
			if (empty ($emailId)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					emeh.*,
					emt.templateid
				FROM
					vtiger_emailmanager_emailhistory emeh
					LEFT JOIN vtiger_emailmanager_templates emt ON emt.templatename=emeh.templatename AND emt.language=emeh.language
				WHERE
					emailid=?',
				array ($emailId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			if (!empty ($row ['attachments'])) {
				$row ['attachments'] = json_decode ($row ['attachments'], true);
			}
			return $row;
		}

		public static function getEmailHistory (PearDatabase $adb, array $criteria = null, $page = null) {
			list ($whereClause, $arguments) = self::getEmailHistorySqlWhereClause ($criteria);

			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$result = $adb->pquery (
				"SELECT
					emeh.*,
					total.__total_records__
				FROM
					vtiger_emailmanager_emailhistory emeh
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_emailmanager_emailhistory) AS total
				{$whereClause}
				ORDER BY
					emeh.createdon DESC
				LIMIT {$startRecord}, {$limit}",
				$arguments
			);
			if ($adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					if (!empty ($row ['attachments'])) {
						$row ['attachments'] = json_decode ($row ['attachments'], true);
					}
					$records [] = $row;
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / self::RECORDS_PER_PAGE);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		public static function getNonPagedTemplates (PearDatabase $adb) {
			$result = $adb->query ('SELECT emt.* FROM vtiger_emailmanager_templates emt ORDER BY emt.templatename');
			if ($adb->num_rows ($result) == 0) {
				return null;
			}

			$templates = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['attachments'] = !empty ($row ['attachments']) ? json_decode ($row ['attachments'], true) : null;
				$templates []        = $row;
			}

			return $templates;
		}

		public static function getTemplateAttachmentFilePath (PearDatabase $adb, $templateId, $attachmentFileName, $platform) {
			if ((empty ($templateId)) || (empty ($attachmentFileName))) {
				return null;
			}

			$result = $adb->pquery ('SELECT attachments FROM vtiger_emailmanager_templates WHERE templateid=?', array ($templateId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$template = $adb->fetchByAssoc ($result, -1, false);
			if (empty ($template ['attachments'])) {
				return null;
			}

			$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$storageFolderPath       = self::getTemplateStorageFolderPath ($platform);
			$attachmentFilePath      = "{$storageFolderPath}/{$templateId}_{$attachmentFileName}";
			$attachments             = json_decode ($template ['attachments'], true);
			if ((!in_array ($attachmentFileName, $attachments)) || (!file_exists ($attachmentFilePath))) {
				return null;
			}

			return 'home' . ltrim ($attachmentFilePath, "{$platzillaRootFolderPath}/");
		}

		public static function getTemplateById (PearDatabase $adb, $templateId) {
			if (empty ($templateId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_emailmanager_templates WHERE templateid=?', array ($templateId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$template                 = $adb->fetchByAssoc ($result, -1, false);
			$template ['attachments'] = !empty ($template ['attachments']) ? json_decode ($template ['attachments'], true) : null;
			return $template;
		}

		public static function getTemplateByNameAndLanguage (PearDatabase $adb, $templateName, $language, $platform) {
			if ((empty ($templateName)) || (empty ($language))) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_emailmanager_templates WHERE templatename=? AND language=?', array ($templateName, $language));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$template = $adb->fetchByAssoc ($result, -1, false);
			if (!empty ($template ['attachments'])) {
				$attachments         = array ();
				$templateAttachments = json_decode ($template ['attachments'], true);
				foreach ($templateAttachments as $templateAttachment) {
					$attachments [] = array (
						'file' => $templateAttachment,
						'path' => self::getTemplateAttachmentFilePath ($adb, $template ['templateid'], $templateAttachment, $platform),
					);
				}
				$template ['attachments'] = $attachments;
			} else {
				$template ['attachments'] = null;
			}
			return $template;
		}

		public static function getTemplates (PearDatabase $adb, $keyword = null, $page = null) {
			if (!empty ($keyword)) {
				$whereClause = 'WHERE emt.templatename LIKE ? OR emt.subject LIKE ?';
				$arguments   = array ("%{$keyword}%", "%{$keyword}%");
			} else {
				$whereClause = '';
				$arguments   = array ();
			}

			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$result = $adb->pquery (
				"SELECT
					emt.*,
					total.__total_records__
				FROM
					vtiger_emailmanager_templates emt
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_emailmanager_templates) AS total
				{$whereClause}
				ORDER BY
					emt.templatename
				LIMIT {$startRecord}, {$limit}",
				$arguments
			);
			if ($adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					$records [] = $row;
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / self::RECORDS_PER_PAGE);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		public static function getTemplateVariableNames (PearDatabase $adb, $templateName, $language) {
			if ((empty ($templateName)) || (empty ($language))) {
				return null;
			}

			$result = $adb->pquery ('SELECT subject, body FROM vtiger_emailmanager_templates WHERE templatename=? AND language=?', array ($templateName, $language));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row     = $adb->fetchByAssoc ($result, -1, false);
			$subject = htmlspecialchars_decode ($row ['subject'], ENT_QUOTES);
			$body    = htmlspecialchars_decode ($row ['body'], ENT_QUOTES);
			$result  = preg_match_all ("'<var>(.*?)</var>'si", "{$subject}\n{$body}", $matches);
			if (!$result) {
				return null;
			}

			$variables = array ();
			foreach ($matches [1] as $match) {
				$variables [ $match ] = $match;
			}
			$variables = array_unique ($variables);
			asort ($variables);
			return $variables;
		}

		public static function registerEmailHistory (PearDatabase $adb, array $emailData = null) {
			if (empty ($emailData)) {
				return;
			}

			$templateName = self::purify ($emailData, 'templatename', null);
			$language     = self::purify ($emailData, 'language', null);
			$from         = self::purify ($emailData, 'from', '');
			$to           = self::purify ($emailData, 'to', '');
			$subject      = self::purify ($emailData, 'subject', '');
			$body         = self::purify ($emailData, 'body', '');
			$status       = self::purify ($emailData, 'status', null);
			$errorMessage = self::purify ($emailData, 'errormessage', null);
			if (!empty ($emailData ['attachments'])) {
				$attachments = json_encode ($emailData ['attachments']);
			} else {
				$attachments = null;
			}

			$adb->pquery (
				'INSERT INTO vtiger_emailmanager_emailhistory (templatename, language, createdon, `from`, `to`, subject, body, attachments, status, errormessage) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)',
				array ($templateName, $language, $from, $to, $subject, $body, $attachments, $status, $errorMessage)
			);
		}

		public static function saveTemplate (PearDatabase $adb, array $templateData, $platform) {
			self::validateTemplateData ($adb, $templateData);
			if (empty ($templateData ['templateid'])) {
				self::createTemplate ($adb, $templateData, $platform);
			} else {
				self::updateTemplate ($adb, $templateData, $platform);
			}
		}

	}
