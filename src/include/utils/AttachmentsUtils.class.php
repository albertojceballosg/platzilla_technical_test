<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');

	abstract class AttachmentsUtils {

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param integer $fieldId
		 *
		 * @return array|null
		 */
		private function fetchAttachmentsByFieldId (PearDatabase $adb, $entityId, $fieldId) {
			$result = $adb->pquery (
				'SELECT
					a.attachmentsid,
					a.name,
					a.type,
					a.path,
					CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					INNER JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
					INNER JOIN vtiger_crmentity crme ON crme.crmid=sear.crmid AND crme.deleted=0 AND crme.crmid=?
				WHERE
					a.fieldid=?',
				array ($entityId, $fieldId)
			);
			if ($adb->num_rows ($result) > 0) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$attachments    = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!file_exists ("{$rootFolderPath}/{$row ['uri']}")) {
						continue;
					}
					$row ['size']   = (filesize ("{$rootFolderPath}/{$row ['uri']}") / 1024);
					$attachments [] = $row;
				}
			} else {
				$attachments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $attachments;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $moduleName
		 * @param integer $fieldId
		 * @param integer $ownerId
		 * @param array $fileData
		 * @param string[] $badFileExtensions
		 *
		 * @return integer
		 */
		private static function saveFile (PearDatabase $adb, $entityId, $moduleName, $fieldId, $ownerId, $fileData, $badFileExtensions) {
			$now         = date ('Y-m-d H:i:s');
			$fileName    = ltrim (basename (' ' . sanitizeUploadFileName ($fileData ['name'], $badFileExtensions)));
			$fileType    = $fileData ['type'];
			$fileOldPath = $fileData ['tmp_name'];

			$attachmentFolderPath = decideFilePath ();
			$attachmentId         = $adb->getUniqueID ('vtiger_crmentity');
			$attachmentFilePath   = "{$attachmentFolderPath}{$attachmentId}_{$fileName}";
			rename ($fileOldPath, $attachmentFilePath);

			$adb->pquery (
				'INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, setype, createdtime, modifiedtime) VALUES (?, ?, ?, ?, ?, ?)',
				array ($attachmentId, $ownerId, $ownerId, "{$moduleName} Attachment", $now, $now)
			);
			$adb->pquery (
				'INSERT INTO vtiger_attachments (attachmentsid, name, type, path, fieldid) VALUES (?, ?, ?, ?, ?)',
				array ($attachmentId, $fileName, $fileType, $attachmentFolderPath, $fieldId)
			);
			$adb->pquery (
				'INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid) VALUES (?, ?)',
				array ($entityId, $attachmentId)
			);

			return $attachmentId;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param integer $fieldId
		 */
		public static function deleteAttachments (PearDatabase $adb, $entityId, $fieldId) {
			$attachments = self::fetchAttachmentsByFieldId ($adb, $entityId, $fieldId);
			if (empty ($attachments)) {
				return;
			}

			foreach ($attachments as $attachment) {
				self::deleteEntityAttachment ($adb, $entityId, $attachment ['attachmentsid']);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param $entityId
		 * @param $fieldId
		 *
		 * @return null
		 */
		public static function deleteAttachmentsFromGrid (PearDatabase $adb, $entityId, $fieldId) {
			if ((empty ($entityId)) || (empty ($fieldId))) {
				return null;
			}
			$attachGroup = self::fetchAttachmentsByFieldId ($adb, $entityId, $fieldId);

			foreach ($attachGroup as $row) {
				$attachmentId       = $row ['attachmentsid'];
				$rootFolderPath     = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$attachmentFilePath = "{$rootFolderPath}/{$row ['attachmenturi']}";
				if (file_exists ($attachmentFilePath)) {
					unlink ($attachmentFilePath);
				}
				$adb->pquery ('DELETE FROM vtiger_seattachmentsrel WHERE crmid=? AND attachmentsid=?', array ($entityId, $attachmentId));
				$adb->pquery ('DELETE FROM vtiger_attachments WHERE attachmentsid=?', array ($attachmentId));
				$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($attachmentId));
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param integer $attachmentId
		 */
		public static function deleteEntityAttachment (PearDatabase $adb, $entityId, $attachmentId) {
			$attachment = self::fetchEntityAttachment ($adb, $entityId, $attachmentId);
			if (empty ($attachment)) {
				return;
			}

			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			if (file_exists ("{$rootFolderPath}/{$attachment ['uri']}")) {
				unlink ("{$rootFolderPath}/{$attachment ['uri']}");
			}

			$adb->pquery ('DELETE FROM vtiger_seattachmentsrel WHERE crmid=? AND attachmentsid=?', array ($entityId, $attachmentId));
			$adb->pquery ('DELETE FROM vtiger_attachments WHERE attachmentsid=?', array ($attachmentId));
			$adb->pquery ('DELETE FROM vtiger_process_cases2document WHERE attachmentsid=?', array ($attachmentId));
			$adb->pquery ('DELETE FROM vtiger_attachments2exercises WHERE attachmentsid=?', array ($attachmentId));
			$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($attachmentId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 */
		public static function deleteEntityAttachments (PearDatabase $adb, $entityId) {
			$attachments = self::fetchEntityAttachments ($adb, $entityId);
			if (empty ($attachments)) {
				return;
			}

			foreach ($attachments as $attachment) {
				self::deleteEntityAttachment ($adb, $entityId, $attachment ['attachmentsid']);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param integer $attachmentId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchEntityAttachment (PearDatabase $adb, $entityId, $attachmentId) {
			if ((empty ($entityId)) || (empty ($attachmentId))) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					a.attachmentsid,
					a.name,
					a.type,
					a.path,
					CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0 AND crmea.crmid=?
					INNER JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
					INNER JOIN vtiger_crmentity crme ON crme.crmid=sear.crmid AND crme.deleted=0 AND crme.crmid=?',
				array ($attachmentId, $entityId)
			);
			if ($adb->num_rows ($result) > 0) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$row = $adb->fetchByAssoc ($result, -1, false);
				if (file_exists ("{$rootFolderPath}/{$row ['uri']}")) {
					$row ['size']   = (filesize ("{$rootFolderPath}/{$row ['uri']}") / 1024);
					$attachment = $row;
				} else {
					$attachment = null;
				}
			} else {
				$attachment = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $attachment;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string|null $module
		 * @param integer|null $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchEntityAttachments (PearDatabase $adb, $entityId, $module = null, $userId = null) {
			if (empty ($entityId)) {
				return null;
			}
			if (empty ($module)) {
				$result = $adb->pquery (
					'SELECT
					a.attachmentsid,
					a.name,
					a.type,
					a.path,
					CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri,
       				crmea.createdtime
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					INNER JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
					INNER JOIN vtiger_crmentity crme ON crme.crmid=sear.crmid AND crme.deleted=0 AND crme.crmid=?',
					array($entityId)
				);
			} else if (($module == 'Courses') && !empty ($userId)) {
				$result = $adb->pquery (
					'SELECT
							a.attachmentsid,
							a.name,
							a.type,
							a.path,
							CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri,
				       		crmea.createdtime
						FROM
							vtiger_attachments a
							INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
							INNER JOIN vtiger_attachments2exercises sear ON sear.attachmentsid=a.attachmentsid
						WHERE 	sear.exercisesid=? AND sear.userid=?',
					array($entityId, $userId)
				);
			} else {
				return null;
			}
			if ($adb->num_rows ($result) > 0) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$attachments    = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!file_exists ("{$rootFolderPath}/{$row ['uri']}")) {
						continue;
					}
					$row ['size']   = (filesize ("{$rootFolderPath}/{$row ['uri']}") / 1024);
					$attachments [] = $row;
				}
			} else {
				$attachments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $attachments;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchFieldAttachments (PearDatabase $adb, $entityId, $moduleName) {
			if ((empty ($entityId)) || (empty ($moduleName))) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?) AND uitype=4096', array ($moduleName));
			if ($adb->num_rows ($result) > 0) {
				$attachments = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fieldId                    = $row ['fieldid'];
					$fieldName                  = $row ['fieldname'];
					$attachments [ $fieldName ] = self::fetchAttachmentsByFieldId ($adb, $entityId, $fieldId);
				}
			} else {
				$attachments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $attachments;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param integer $fieldId
		 *
		 * @return array|null
		 */
		public static function fetchAttachmentsFromGrid (PearDatabase $adb, $entityId, $fieldId) {
			if ((empty ($entityId)) || (empty ($fieldId))) {
				return null;
			}
			return self::fetchAttachmentsByFieldId ($adb, $entityId, $fieldId);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param integer $fieldId
		 *
		 * @return array|null
		 */
		public static function getAttachmentsNames (PearDatabase $adb, $entityId, $fieldId) {
			if ((empty ($entityId)) || (empty ($fieldId))) {
				return null;
			}
			$attachments = self::fetchAttachmentsByFieldId ($adb, $entityId, $fieldId);
			if ($attachments != null) {
				$documentsNames = array ();
				foreach ($attachments as $row) {
					$documentsNames [] = $row ['name'];
				}
				return $documentsNames;
			} else {
				return null;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $moduleName
		 * @param integer $fieldId
		 * @param integer $ownerId
		 * @param array $attachments
		 * @param string[] $badFileExtensions
		 */
		public static function saveAttachments (PearDatabase $adb, $entityId, $moduleName, $fieldId, $ownerId, $attachments, $badFileExtensions) {
			if (empty ($attachments)) {
				return;
			}
			$saveGrid               = false;
			$processedAttachmentIds = array ();
			foreach ($attachments as $index => $attachment) {
				if ($index >= 0) {
					$tempFilePath       = tempnam ('/tmp', 'attachment-');
					$attachmentType     = substr ($attachment ['data'], (strpos ($attachment ['data'], 'data:') + 5), (strpos ($attachment ['data'], ';base64,') - 5));
					$attachmentContents = base64_decode (str_replace (' ', '+', substr ($attachment ['data'], (strpos ($attachment ['data'], 'base64,') + 7))));
					file_put_contents ($tempFilePath, $attachmentContents);
					$fileData     = array (
						'name'     => $attachment ['filename'],
						'type'     => $attachmentType,
						'tmp_name' => $tempFilePath,
					);
					$attachmentId = self::saveFile ($adb, $entityId, $moduleName, $fieldId, $ownerId, $fileData, $badFileExtensions);
					$saveGrid     = (key_exists ('isGrid', $attachment));
				} else {
					$attachmentId = abs ($index);
				}
				$processedAttachmentIds [] = $attachmentId;
			}

			if (empty ($processedAttachmentIds) || $saveGrid) {
				return;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedAttachmentIds) - 1)) . '?';
			$result        = $adb->pquery (
				"SELECT
					a.*,
					CONCAT(a.path, a.attachmentsid, '_', a.name) AS attachmenturi
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					INNER JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
				WHERE
					a.fieldid=? AND
					sear.crmid=? AND
					sear.attachmentsid NOT IN ({$questionMarks})",
				array_merge (array ($fieldId, $entityId), $processedAttachmentIds)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$attachmentId       = $row ['attachmentsid'];
					$rootFolderPath     = PlatzillaUtils::getPlatzillaRootFolderPath ();
					$attachmentFilePath = "{$rootFolderPath}/{$row ['attachmenturi']}";
					if (file_exists ($attachmentFilePath)) {
						unlink ($attachmentFilePath);
					}
					$adb->pquery ('DELETE FROM vtiger_seattachmentsrel WHERE crmid=? AND attachmentsid=?', array ($entityId, $attachmentId));
					$adb->pquery ('DELETE FROM vtiger_attachments WHERE attachmentsid=?', array ($attachmentId));
					$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($attachmentId));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $moduleName
		 * @param integer $ownerId
		 * @param array $attachment
		 * @param array $badFileExtensions
		 *
		 * @return array
		 */
		public static function saveEntityAttachment (PearDatabase $adb, $entityId, $moduleName, $ownerId, $attachment, $badFileExtensions) {
			$tempFilePath       = tempnam ('/tmp', 'attachment-');
			$attachmentType     = substr ($attachment ['data'], (strpos ($attachment ['data'], 'data:') + 5), (strpos ($attachment ['data'], ';base64,') - 5));
			$attachmentContents = base64_decode (str_replace (' ', '+', substr ($attachment ['data'], (strpos ($attachment ['data'], 'base64,') + 7))));
			file_put_contents ($tempFilePath, $attachmentContents);

			$now         = date ('Y-m-d H:i:s');
			$fileName    = ltrim (basename (' ' . sanitizeUploadFileName ($attachment ['filename'], $badFileExtensions)));
			$attachmentFolderPath = decideFilePath ();
			$attachmentId         = $adb->getUniqueID ('vtiger_crmentity');
			$attachmentFilePath   = "{$attachmentFolderPath}{$attachmentId}_{$fileName}";
			rename ($tempFilePath, $attachmentFilePath);

			$adb->pquery (
				'INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, setype, createdtime, modifiedtime) VALUES (?, ?, ?, ?, ?, ?)',
				array ($attachmentId, $ownerId, $ownerId, "{$moduleName} Attachment", $now, $now)
			);
			$adb->pquery (
				'INSERT INTO vtiger_attachments (attachmentsid, name, type, path, fieldid) VALUES (?, ?, ?, ?, ?)',
				array ($attachmentId, $fileName, $attachmentType, $attachmentFolderPath, null)
			);
			if (!in_array ($moduleName, array ('Courses'))) {
				$adb->pquery (
					'INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid) VALUES (?, ?)',
					array($entityId, $attachmentId)
				);
			}
			$platzillaRootUri = PlatzillaUtils::getPlatzillaRootUri ();
			return array (
				'attachmentid' => $attachmentId,
				'url' => "{$platzillaRootUri}/{$attachmentFilePath}",
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $moduleName
		 * @param integer $ownerId
		 * @param array $attachments
		 * @param array $badFileExtensions
		 *
		 * @throws Exception
		 */
		public static function saveEntityAttachments (PearDatabase $adb, $entityId, $moduleName, $ownerId, $attachments, $badFileExtensions) {
			$attachments = is_array ($attachments) ? $attachments : array ();
			$processedAttachmentIds = array ();
			foreach ($attachments as $attachment) {
				$savedAttachment = self::saveEntityAttachment ($adb, $entityId, $moduleName, $ownerId, $attachment, $badFileExtensions);
				$processedAttachmentIds [] = $savedAttachment ['attachmentid'];
			}

			if (empty ($processedAttachmentIds)) {
				return;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedAttachmentIds) - 1)) . '?';
			$result        = $adb->pquery (
				"SELECT
					a.*,
					CONCAT(a.path, a.attachmentsid, '_', a.name) AS attachmenturi
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					INNER JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
				WHERE
					sear.crmid=? AND
					sear.attachmentsid NOT IN ({$questionMarks})",
				array_merge (array ($entityId), $processedAttachmentIds)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$attachmentId       = $row ['attachmentsid'];
					$rootFolderPath     = PlatzillaUtils::getPlatzillaRootFolderPath ();
					$attachmentFilePath = "{$rootFolderPath}/{$row ['attachmenturi']}";
					if (file_exists ($attachmentFilePath)) {
						unlink ($attachmentFilePath);
					}
					$adb->pquery ('DELETE FROM vtiger_seattachmentsrel WHERE crmid=? AND attachmentsid=?', array ($entityId, $attachmentId));
					$adb->pquery ('DELETE FROM vtiger_attachments WHERE attachmentsid=?', array ($attachmentId));
					$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($attachmentId));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Fetch attachments associated with a specific activity report
		 * 
		 * @param PearDatabase $adb
		 * @param integer $reportId Activity report ID
		 * @return array|null
		 */
		public static function fetchActivityReportAttachments (PearDatabase $adb, $reportId) {
			if (empty ($reportId)) {
				return null;
			}
			
			$result = $adb->pquery (
				'SELECT
					a.attachmentsid,
					a.name,
					a.type,
					a.path,
					CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri,
					crmea.createdtime
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					INNER JOIN vtiger_activityreport2attachments ara ON ara.attachmentsid=a.attachmentsid
				WHERE
					ara.activityreportid=?
				ORDER BY crmea.createdtime DESC',
				array($reportId)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$attachments = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!file_exists ("{$rootFolderPath}/{$row ['uri']}")) {
						continue;
					}
					$row ['size'] = (filesize ("{$rootFolderPath}/{$row ['uri']}") / 1024);
					$attachments [] = $row;
				}
			} else {
				$attachments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $attachments;
		}

		/**
		 * Save an attachment and associate it with an activity report
		 * 
		 * @param PearDatabase $adb
		 * @param integer $activityId The task/activity ID
		 * @param integer $reportId The activity report ID
		 * @param string $moduleName Module name (usually 'Calendar' or 'grid_view')
		 * @param integer $ownerId User ID
		 * @param array $attachment Attachment data
		 * @param array $badFileExtensions Bad file extensions
		 * @return array
		 */
		public static function saveActivityReportAttachment (PearDatabase $adb, $activityId, $reportId, $moduleName, $ownerId, $attachment, $badFileExtensions) {
			// Save the attachment using the standard method
			$savedAttachment = self::saveEntityAttachment ($adb, $activityId, $moduleName, $ownerId, $attachment, $badFileExtensions);
			
			// Create the relationship between the report and the attachment
			if (!empty($reportId) && !empty($savedAttachment['attachmentid'])) {
				$adb->pquery (
					'INSERT INTO vtiger_activityreport2attachments (activityreportid, attachmentsid) VALUES (?, ?)',
					array($reportId, $savedAttachment['attachmentid'])
				);
			}
			
			return $savedAttachment;
		}

		/**
		 * Delete an attachment associated with an activity report
		 * 
		 * @param PearDatabase $adb
		 * @param integer $reportId Activity report ID
		 * @param integer $attachmentId Attachment ID
		 */
		public static function deleteActivityReportAttachment (PearDatabase $adb, $reportId, $attachmentId) {
			if (empty($reportId) || empty($attachmentId)) {
				return;
			}
			
			// First, remove the relationship
			$adb->pquery (
				'DELETE FROM vtiger_activityreport2attachments WHERE activityreportid=? AND attachmentsid=?',
				array($reportId, $attachmentId)
			);
			
			// Check if this attachment is used by other reports
			$result = $adb->pquery (
				'SELECT COUNT(*) as count FROM vtiger_activityreport2attachments WHERE attachmentsid=?',
				array($attachmentId)
			);
			
			$row = $adb->fetchByAssoc ($result, -1, false);
			$isUsedElsewhere = ($row['count'] > 0);
			DatabaseUtils::closeResult ($result);
			
			// Only delete the physical file and database records if not used elsewhere
			if (!$isUsedElsewhere) {
				// Get attachment info
				$attachResult = $adb->pquery (
					'SELECT a.*, CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri
					FROM vtiger_attachments a
					WHERE a.attachmentsid=?',
					array($attachmentId)
				);
				
				if ($adb->num_rows($attachResult) > 0) {
					$attachment = $adb->fetchByAssoc ($attachResult, -1, false);
					$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
					if (file_exists ("{$rootFolderPath}/{$attachment ['uri']}")) {
						unlink ("{$rootFolderPath}/{$attachment ['uri']}");
					}
					DatabaseUtils::closeResult ($attachResult);
					
					$adb->pquery ('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid=?', array ($attachmentId));
					$adb->pquery ('DELETE FROM vtiger_attachments WHERE attachmentsid=?', array ($attachmentId));
					$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($attachmentId));
				}
			}
		}

	}
