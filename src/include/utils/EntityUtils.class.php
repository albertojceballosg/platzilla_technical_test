<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class EntityUtils {

		/**
		 * Retorna la información básica de la tabla vtiger_entityname para el módulo suministrado como parámetro
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchModuleData (PearDatabase $adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $adb->pquery ('SELECT fieldname, modulename, tablename, entityidfield, tabid FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ($adb->num_rows ($result) > 0) {
				$data = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$data = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $data;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 *
		 * @return array|null
		 */
		public static function fetchCrmEntityHeaders (PearDatabase $adb, $entityId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=?', array ($entityId));
			if ($adb->num_rows ($result) > 0) {
				$data = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$data = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $data;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $relatedModuleName
		 * @param integer[]|integer $relatedEntityIds
		 *
		 * @return array|null
		 */
		public static function fetchReferencedCrmEntitiesData (PearDatabase $adb, $relatedModuleName, $relatedEntityIds) {
			if ((empty ($relatedModuleName)) || (empty ($relatedEntityIds))) {
				return null;
			} else if ($relatedModuleName == 'Events') {
				$relatedModuleName = 'Calendar';
			}

			$relatedModuleData = self::fetchModuleData ($adb, $relatedModuleName);
			if (empty ($relatedModuleData)) {
				return null;
			}

			$arguments             = !is_array ($relatedEntityIds) ? array ($relatedEntityIds) : $relatedEntityIds;
			$questionMarks         = str_repeat ('?, ', (count ($arguments) - 1)) . '?';
			$additionalWhereClause = "AND vtiger_crmentity.crmid IN ({$questionMarks})";
			/** @var CRMEntity $entity */
			$entity = CRMEntity::getInstance ($relatedModuleName);
			if ($relatedModuleName == 'Users') {
				$sql = $entity->getListQuery ($relatedModuleName, $additionalWhereClause);
			} else {
				$sql = $entity->listQueryNonAdminChange ($entity->getListQuery ($relatedModuleName, $additionalWhereClause));
			}

			$result = $adb->pquery ($sql, $arguments);
			if ($adb->num_rows ($result) > 0) {
				$data = null;
				$relatedFieldNames        = explode (',', $relatedModuleData ['fieldname']);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$displayValues = array ();
					foreach ($relatedFieldNames as $relatedFieldName) {
						$displayValues [] = $row [ $relatedFieldName ];
					}
					$data = join (' ', $displayValues);
				}
			} else {
				$data = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $data;
		}

	}
