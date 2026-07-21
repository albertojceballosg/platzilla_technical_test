<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class DemoDataManager {

		public static function create ($instanceCode, Users $currentUser = null) {
			$sourceAdb = AdbManager::getInstance ()->getSourceInstanceAdb ('appdemo');
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$result    = $sourceAdb->query ('SELECT * FROM vtiger_crmentity_seq');
			if ($sourceAdb->num_rows ($result) > 0) {
				$row = $sourceAdb->fetchByAssoc ($result, -1, false);
				$targetAdb->pquery ('UPDATE vtiger_crmentity_seq SET id=?', array ($row ['id']));
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			if (empty ($currentUser)) {
				$currentUser = PlatformUtils::getCrmEntity ($targetAdb, 'Users', 1);
			}

			$result = $sourceAdb->pquery (
				'SELECT
					crme.*
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_tab t ON t.name=crme.setype
				WHERE
					crme.deleted=0 AND
					crme.setype NOT IN (
						SELECT name FROM vtiger_settings_field WHERE blockid=(SELECT blockid FROM vtiger_settings_blocks WHERE label=?)
					)',
				array ('LBL_APPLICATIONS_SETTINGS')
			);
			if ($sourceAdb->num_rows ($result) > 0) {
				while ($row = $sourceAdb->fetchByAssoc ($result, -1, false)) {
					$entityId                         = $row ['crmid'];
					$moduleName                       = $row ['setype'];
					$sourceEntity                     = PlatformUtils::loadCrmEntity ($sourceAdb, $moduleName, $entityId, $currentUser);
					$targetEntity                     = CRMEntity::getInstance ($moduleName);
					$targetEntity->column_fields      = $sourceEntity->column_fields;
					$oldDieOnError                    = $targetAdb->dieOnError;
					$targetAdb->dieOnError            = false;
					$targetEntity                     = PlatformUtils::saveCrmEntity ($targetAdb, $targetEntity, $moduleName);
					$targetAdb->pquery ('UPDATE vtiger_crmentity SET crmid=?, demo=1 WHERE crmid=?', array ($entityId, $targetEntity->column_fields ['record_id']));
                $targetAdb->setDieOnError ($oldDieOnError);
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}

			$result = $sourceAdb->pquery (
				'SELECT
					crme.setype,
					a.attachmentsid,
					a.name,
					a.type,
					a.path,
					a.fieldid,
					sear.crmid AS parententityid
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_attachments a ON a.attachmentsid=crme.crmid
					LEFT JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
				WHERE
					crme.deleted=0 AND
					crme.setype LIKE ?',
				array ('%Attach%')
			);
			if ($sourceAdb->num_rows ($result) > 0) {
				while ($row = $sourceAdb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fieldid'])) {
						$field = FieldManager::getInstance ($sourceAdb)->fetchFieldById ($row ['fieldid']);
						if (!empty ($field)) {
							$field   = FieldManager::getInstance ($targetAdb)->fetchFieldByName ($field->getModuleName (), $field->getName ());
							$fieldId = !empty ($field) ? $field->getId () : null;
						} else {
							$fieldId = null;
						}
					} else {
						$fieldId = null;
					}
					$now = date ('Y-m-d H:i:s');
					$targetAdb->pquery (
						'INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, setype, createdtime, modifiedtime) VALUES (?, ?, ?, ?, ?, ?)',
						array ($row ['attachmentsid'], 1, 1, $row ['setype'], $now, $now)
					);
					$targetAdb->pquery (
						'INSERT INTO vtiger_attachments (attachmentsid, name, type, path, fieldid) VALUES (?, ?, ?, ?, ?)',
						array ($row ['attachmentsid'], $row ['name'], $row ['type'], $row ['path'], $fieldId)
					);
					if (!empty ($row ['parententityid'])) {
						$targetAdb->pquery (
							'INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid) VALUES (?, ?)',
							array ($row ['parententityid'], $row ['attachmentsid'])
						);
					}
					$targetAdb->pquery ('UPDATE vtiger_crmentity SET demo=1 WHERE crmid=?', array ($row ['attachmentsid']));
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			
			$result = $sourceAdb->query ('SELECT * FROM vtiger_seactivityrel WHERE 1');
			if ($sourceAdb->num_rows ($result) > 0) {
				while ($row = $sourceAdb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['crmid']) && !empty ($row ['activityid'])) {
						$targetAdb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($row ['crmid'], $row ['activityid']));
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
		}

	}
