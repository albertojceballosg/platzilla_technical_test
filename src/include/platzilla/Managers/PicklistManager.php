<?php
	require_once ('include/platzilla/Managers/RoleManager.php');
	require_once ('include/platzilla/Objects/Picklist.php');
	require_once ('include/platzilla/Objects/PicklistValue.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class PicklistManager {
		/** @var PicklistManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param integer $picklistValueId
		 *
		 * @return Role[]|null
		 */
		private function fetchPicklistValueRoles ($picklistValueId) {
			if (empty ($picklistValueId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_role2picklist WHERE picklistvalueid=?', array ($picklistValueId));
			if ($this->adb->num_rows ($result) > 0) {
				$rm    = RoleManager::getInstance ($this->adb);
				$roles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$roles [] = $rm->fetchRole ($row ['roleid'], true);
				}
			} else {
				$roles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $roles;
		}

		/**
		 * Obtiene los valores asociados al picklist
		 *
		 * @param string $picklistName
		 * @param boolean $forAnInstance
		 *
		 * @return PicklistValue[]|null
		 */
		private function fetchPicklistValues ($picklistName, $forAnInstance = false) {
			$picklistTableName       = "vtiger_{$picklistName}";
			$picklistIdColumnName    = "{$picklistName}id";
			$picklistValueColumnName = $picklistName;
			if (
				(!DatabaseUtils::checkIfTableExists ($this->adb, $picklistTableName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistValueColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistIdColumnName))
			) {
				return null;
			}

			$result = $this->adb->query ("SELECT * FROM {$this->adb->sql_escape_string ($picklistTableName)} ORDER BY {$this->adb->sql_escape_string ($picklistIdColumnName)}");
			if ($this->adb->num_rows ($result) > 0) {
				$values = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($forAnInstance) {
						$value = PicklistValue::getInstance (true);
					} else {
						$value = PicklistValue::getInstance (false)
							->setRoles ($this->fetchPicklistValueRoles (intval ($row ['picklist_valueid'])));
					}
					$value->setId (intval ($row [ $picklistIdColumnName ]))
						->setLocked ($row ['locked'] == 1)
						->setPresence (intval ($row ['presence']))
						->setValue ($row [ $picklistValueColumnName ]);
					$values [] = $value;
				}
			} else {
				$values = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $values;
		}

		/**
		 * @param integer $picklistId
		 * @param integer $picklistValueId
		 * @param PicklistValue $picklistValue
		 * @param integer $order
		 */
		private function savePicklistValueRoles ($picklistId, $picklistValueId, $picklistValue, $order) {
			if ((empty ($picklistValue)) || (!($picklistValue instanceof PicklistValue))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_role2picklist WHERE picklistvalueid=?', array ($picklistValueId));

			$isAvailableForAllRoles = $picklistValue->isAvailableForAllRoles ();
			$roles                  = $picklistValue->getRoles ();
			if ($isAvailableForAllRoles) {
				$this->adb->pquery (
					'INSERT INTO vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid) SELECT roleid, ?, ?, ? FROM vtiger_role',
					array ($picklistValueId, $picklistId, $order)
				);
			} else if (!empty ($roles)) {
				foreach ($roles as $role) {
					$result = $this->adb->pquery ('SELECT MAX(sortid) AS sequence FROM vtiger_role2picklist WHERE roleid=? AND picklistid=?', array ($role->getId (), $picklistId));
					if ($this->adb->num_rows ($result) > 0) {
						$row   = $this->adb->fetchByAssoc ($result, -1, false);
						$order = (intval ($row['sequence']) + 1);
					} else {
						$order = 1;
					}
					DatabaseUtils::closeResult ($result);
					$result = null;

					$this->adb->pquery (
						'INSERT INTO vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid) VALUES (?, ?, ?, ?)',
						array ($role->getId (), $picklistValueId, $picklistId, $order)
					);
				}
			}
		}

		/**
		 * Sincroniza los valores del Picklist suministrado
		 *
		 * @param integer $picklistId
		 * @param PicklistValue[] $picklistValues
		 * @param string $tableName
		 * @param string $fieldIdName
		 * @param string $fieldName
		 * @param boolean $ignoreLock
		 */
		private function savePicklistValues ($picklistId, $picklistValues, $tableName, $fieldIdName, $fieldName, $ignoreLock) {
			if (empty ($picklistValues)) {
				/** @noinspection SqlResolve */
				$this->adb->query ("DELETE FROM vtiger_role2picklist WHERE picklistvalueid IN (SELECT picklist_valueid FROM `{$tableName}`)");
				/** @noinspection SqlResolve */
				$this->adb->query ("DELETE FROM `{$tableName}`");
				return;
			}

			$order        = 1;
			$processedIds = array ();
			foreach ($picklistValues as $picklistValue) {
				$isLocked        = false;
				$picklistValueId = null;
				$id              = $picklistValue->getId ();
				if (!empty ($id)) {
					/** @noinspection SqlResolve */
					$result = $this->adb->pquery ("SELECT * FROM `{$tableName}` WHERE `{$fieldIdName}`=?", array ($id));
					if ($this->adb->num_rows ($result) > 0) {
						$row             = $this->adb->fetchByAssoc ($result, -1, false);
						$isLocked        = $row ['locked'];
						$picklistValueId = intval ($row ['picklist_valueid']);
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				}

				if (empty ($picklistValueId)) {
					$picklistValueId = $this->adb->getUniqueID ('vtiger_picklistvalues');
					$id              = intval ($this->adb->getUniqueID ($tableName));
					/** @noinspection SqlResolve */
					$this->adb->pquery (
						"INSERT INTO `{$tableName}` (`{$fieldIdName}`, `{$fieldName}`, presence, picklist_valueid, locked) VALUES (?, ?, ?, ?, ?)",
						array ($id, $picklistValue->getValue (), $picklistValue->getPresence (), $picklistValueId, $picklistValue->isLocked ())
					);
					$this->savePicklistValueRoles ($picklistId, $picklistValueId, $picklistValue, $order);
				} else if (($ignoreLock) || (!$isLocked)) {
					/** @noinspection SqlResolve */
					$this->adb->pquery (
						"UPDATE `{$tableName}` SET presence=?, {$fieldName}=?, locked=? WHERE picklist_valueid=?",
						array ($picklistValue->getPresence (), $picklistValue->getValue (), $picklistValue->isLocked (), $picklistValueId)
					);
					$this->savePicklistValueRoles ($picklistId, $picklistValueId, $picklistValue, $order);
				}
				$picklistValue->setId ($id);
				$order++;
				$processedIds [] = $id;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedIds) - 1)) . '?';
			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			/** @noinspection SqlResolve */
			$this->adb->pquery ("DELETE FROM `{$tableName}` WHERE `{$fieldIdName}` NOT IN ({$questionMarks}) {$whereClause}", $processedIds);
			/** @noinspection SqlResolve */
			$this->adb->pquery (
				"DELETE
					r2p
				FROM
					vtiger_role2picklist r2p
					INNER JOIN vtiger_picklist pl ON pl.picklistid=r2p.picklistid AND pl.name=?
				WHERE
					r2p.picklistvalueid NOT IN (SELECT picklist_valueid FROM `{$tableName}`)",
				array ($fieldName)
			);
		}

		/**
		 * Intenta eliminar un Picklist de la base de datos.
		 * 1. Si el picklist no está asociado a ningún campo, el picklist se elimina de la tabla vtiger_picklist, junto con sus tablas asociadas
		 * 2. Si hay al menos un campo con uitype = UI_TYPE_PICKLIST o UI_TYPE_MULTI_SELECT que esté asociado al Picklist suministrado, no se elimina nada
		 *
		 * @param Picklist $picklist
		 */
		public function deletePicklist ($picklist) {
			if ((empty ($picklist)) || (!($picklist instanceof Picklist))) {
				return;
			}

			$result = $this->adb->pquery (
				'SELECT
					*
				FROM
					vtiger_field f
				WHERE
					f.uitype IN (?, ?) AND
					f.fieldname=?',
				array (Field::UI_TYPE_PICKLIST, Field::UI_TYPE_MULTI_SELECT, $picklist->getName ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				$tableName = $this->adb->sql_escape_string ("vtiger_{$picklist->getName ()}");
				$this->adb->pquery ('DELETE FROM vtiger_picklist WHERE name=?', array ($picklist->getName ()));
				if ($tableName != 'vtiger_crmentity') {
					$this->adb->query ("DROP TABLE IF EXISTS `{$tableName}`");
					$this->adb->query ("DROP TABLE IF EXISTS `{$tableName}_seq`");
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Obtiene un objeto Picklist con la información de la base de datos o <code>null</code> si no existe
		 *
		 * @param string $picklistName
		 * @param boolean $forAnInstance
		 *
		 * @return Picklist|null
		 */
		public function fetchPicklistByName ($picklistName, $forAnInstance = false) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($picklistName));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$picklist = Picklist::getInstance ()
					->setId (intval ($row ['picklistid']))
					->setName ($row ['name'])
					->setValues ($this->fetchPicklistValues ($row ['name'], $forAnInstance));
			} else {
				$picklist = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $picklist;
		}

		/**
		 * @param string $picklistName
		 * @param boolean $forAnInstance
		 *
		 * @return string[]|null
		 */
		public function fetchPicklistRawValues ($picklistName, $forAnInstance = false) {
			$picklistValues = $this->fetchPicklistValues ($picklistName, $forAnInstance);
			if (empty ($picklistValues)) {
				return null;
			}

			$rawValues  = array ();
			foreach ($picklistValues as $picklistValue) {
				$rawValues [] = $picklistValue->getValue ();
			}
			return $rawValues;
		}

		/**
		 * Obtiene un objeto PicklistValue especificado por el valor crudo a partir de un picklist suministrado como parámetro
		 *
		 * @param string $picklistName
		 * @param string $picklistRawValue
		 *
		 * @return PicklistValue|null
		 */
		public function fetchPicklistValue ($picklistName, $picklistRawValue) {
			$picklistTableName       = "vtiger_{$picklistName}";
			$picklistIdColumnName    = "{$picklistName}id";
			$picklistValueColumnName = $picklistName;
			if (
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistIdColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistValueColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, 'presence'))
			) {
				return null;
			}

			if ($picklistRawValue !== null) {
				$result = $this->adb->pquery ("SELECT * FROM {$this->adb->sql_escape_string ($picklistTableName)} WHERE {$this->adb->sql_escape_string ($picklistValueColumnName)}=?", array ($picklistRawValue));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					return null;
				}

				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$roles    = $this->fetchPicklistValueRoles ($row ['picklist_valueid']);
				$valueId  = intval ($row [ $picklistIdColumnName ]);
				$presence = intval ($row ['presence']);
				$isLocked = $row ['locked'] == 1;
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$roles    = null;
				$valueId  = 0;
				$presence = 0;
				$isLocked = false;
			}

			return PicklistValue::getInstance (false)
				->setId ($valueId)
				->setLocked ($isLocked)
				->setPresence ($presence)
				->setRoles ($roles)
				->setValue ($picklistRawValue);
		}

		/**
		 * Almacena un Picklist en la base de datos:
		 * 1. Si el picklist con el nombre suministrado no existe, lo inserta en la tabla vtiger_picklist, genera las tablas correspondientes y guarda los valores
		 * 2. Si el picklist existe, actualiza los valores en las tablas correspondientes
		 *
		 * @param Picklist $picklist
		 * @param boolean $ignoreLock
		 *
		 * @return Picklist|null
		 */
		public function savePicklist ($picklist, $ignoreLock = true) {
			if ((empty ($picklist)) || (!($picklist instanceof Picklist))) {
				return null;
			}
			$picklist->validate ();

			$this->adb->startTransaction ();
			$picklistName = $picklist->getName ();
			$result       = $this->adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($picklistName));
			if ($this->adb->num_rows ($result) == 0) {
				$picklistId = $this->adb->getUniqueID ('vtiger_picklist');
				$this->adb->pquery ('INSERT INTO vtiger_picklist (picklistid, name) VALUES (?, ?)', array ($picklistId, $picklist->getName ()));
			} else {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$picklistId = intval ($row ['picklistid']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$tableName   = $this->adb->sql_escape_string ("vtiger_{$picklist->getName ()}");
			$fieldIdName = $this->adb->sql_escape_string ("{$picklist->getName ()}id");
			$fieldName   = $this->adb->sql_escape_string ($picklist->getName ());
			if (!DatabaseUtils::checkIfTableExists ($this->adb, $tableName)) {
				$this->adb->query (
					"CREATE TABLE `{$tableName}` (
						`{$fieldIdName}` INT(19) NOT NULL AUTO_INCREMENT,
						`{$fieldName}` VARCHAR(200) NOT NULL,
						`presence` INT(1) NOT NULL,
						`picklist_valueid` INT(19) NOT NULL,
						`locked` TINYINT(1) NOT NULL DEFAULT '0',
						PRIMARY KEY (`{$fieldIdName}`)
					) ENGINE=InnoDB"
				);
				$this->adb->query (
					"CREATE TABLE IF NOT EXISTS `{$tableName}_seq` (
						`id` INT(11) NOT NULL
					) ENGINE=InnoDB"
				);
				/** @noinspection SqlResolve */
				$this->adb->pquery ("INSERT INTO `{$tableName}_seq` (id) VALUES (?)", array (0));
			}
			$this->savePicklistValues ($picklistId, $picklist->getValues (), $tableName, $fieldIdName, $fieldName, $ignoreLock);
			$this->adb->completeTransaction ();

			$picklist->setId ($picklistId);
			return $picklist;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return PicklistManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
