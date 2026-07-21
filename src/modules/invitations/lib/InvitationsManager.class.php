<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/VtlibUtils.php');

	/**
	 * Gestiona el proceso de invitaciones
	 */
	class InvitationsManager {
		/**
		 * El invitado no tiene instancia asignada
		 */
		const RESULT_NO_TARGET_INSTANCE = 1;
		/**
		 * El módulo solicitado no está activo
		 */
		const RESULT_MODULE_NOT_ENABLED = 2;
		/**
		 * La entidad no ha sido compartida
		 */
		const RESULT_ENTITY_NOT_SHARED = 3;
		/** @var InvitationsManager */
		private static $INSTANCE = null;

		/**
		 * Asigna una instancia en stock
		 *
		 * @param array $arguments Argumentos necesarios para crear la instancia
		 *
		 * @return string El nombre de la instancia asignada
		 */
		private function asignStockInstance ($arguments) {
			require_once ('include/utils/InstanceCreator.class.php');
			return InstanceCreator::getCreator ()->assignStockInstance ($arguments, InstanceCreator::REGISTRATION_SOURCE_INVITATION);
		}

		/**
		 * Obtiene el objeto entidad de la base de datos
		 *
		 * @param string $moduleName Nombre del módulo
		 * @param string $entityId Id de la entidad
		 *
		 * @return CRMEntity|stdClass El objeto entidad
		 */
		private function getEntity ($moduleName, $entityId = null) {
			global $currentModule;

			$oldCurrentModule = $currentModule;
			$currentModule    = $moduleName;
			/** @var CRMEntity|stdClass $entity */
			$entity = CRMEntity::getInstance ($currentModule);
			if (($entity) && ($entityId)) {
				// Fix para evitar error en el cálculo del tabid en CRMEntity.php línea 781 => include/utils/CommonUtils.php línea 393
				if (isset ($_SESSION ['authenticated_user_menu'])) {
					$_SESSION ['authenticated_user_menu'] = array ();
				}
				$entity->retrieve_entity_info ($entityId, $currentModule);
			}
			$currentModule = $oldCurrentModule;

			return $entity;
		}

		/**
		 * Obtiene las entidades hijas de la entidad suministrada
		 *
		 * @param string $sourceInstanceName Nombre de la instancia de origen
		 * @param array $childModules Módulos hijos del módulo al cual pertenece la entidad suministrada
		 * @param CRMEntity|stdClass $sourceEntity Objeto entidad suministrada
		 *
		 * @return array|null Arreglo de entidades hijas o <i>null</i> si no existen
		 */
		private function getSourceChildEntities ($sourceInstanceName, $childModules, $sourceEntity) {
			if ((!$childModules) || (!is_array ($childModules)) || (count ($childModules) == 0)) {
				return null;
			}

			global $adb;

			$adb           = AdbManager::getInstance ()->getSourceInstanceAdb ($sourceInstanceName);
			$childEntities = array ();
			foreach ($childModules as $childModule) {
				$sql    = "SELECT
									t.{$childModule ['entityidfield']}
								FROM
									{$childModule ['tablename']} t
									INNER JOIN vtiger_crmentity crme ON crme.crmid=t.{$childModule ['entityidfield']} AND crme.deleted=0
								WHERE
									t.{$childModule ['fieldname']}=?";
				$result = $adb->pquery ($sql, array ($sourceEntity->column_fields ['record_id']), true);
				if ($adb->num_rows ($result) == 0) {
					continue;
				}
				while ($row = $adb->fetch_array ($result)) {
					$childEntities [] = array (
						'modulename' => $childModule ['modulename'],
						'fieldname'  => $childModule ['fieldname'],
						'entity'     => $this->getEntity ($childModule ['modulename'], $row [ $childModule ['entityidfield'] ]),
					);
				}
			}

			return count ($childEntities) != 0 ? $childEntities : null;
		}

		/**
		 * Obtiene un arreglo con información de los módulos HIJO del módulo suministrado como parámetro.
		 *
		 * Un módulo HIJO es aquel que contiene información referenciada por otro módulo en una jerarquía Padre - Hijo.
		 *
		 * Por ejemplo:
		 * a. Un plan de mantenimiento permite asociar una cuenta. 'Accounts' es un módulo PADRE de 'plan_mantenimiento'
		 * b. Un plan de mantemiento permite asociar intervenciones.
		 *      i. 'plan_mantenimiento' es un módulo PADRE de 'intervencion'.
		 *      ii. 'intervencion' es un módulo HIJO de 'plan_mantenimiento'
		 *
		 * Cada elemento del arreglo es a su vez un arreglo con la siguiente estructura:
		 *
		 * array (
		 *      'fieldname'  => Nombre del campo
		 *      'modulename' => Nombre del módulo relacionado
		 * )
		 *
		 * @param string $instanceName Nombre de la instancia de origen
		 * @param string $moduleName Nombre del módulo
		 *
		 * @return array|null Información de los módulos HIJO del módulo suministrado como parámetro o <i>null</i> si no hay módulos hijo.
		 */
		private function getChildModules ($instanceName, $moduleName) {
			global $adb;

			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$sql    = "SELECT DISTINCT
									f.fieldname,
									fmr.module AS modulename,
									f.tablename,
									en.entityidfield
								FROM
									vtiger_field f
									INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldid=f.fieldid
									INNER JOIN vtiger_entityname en ON en.tabid=f.tabid AND en.modulename=fmr.module AND en.tablename=f.tablename
								WHERE
									f.uitype='10' AND
									fmr.module NOT IN ('invitations') AND
									fmr.relmodule=?";
			$result = $adb->pquery ($sql, array ($moduleName), true);
			if ($adb->num_rows ($result) == 0) {
				return null;
			}

			$childModules = array ();
			while ($row = $adb->fetch_array ($result)) {
				$childModules [] = $row;
			}

			return $childModules;
		}

		/**
		 * Obtiene el objeto entidad con el Id suministrado en la instancia suministrada
		 *
		 * @param string $instanceName Nombre de la instancia
		 * @param $entityId Id de la entidad
		 *
		 * @return CRMEntity|stdClass El objeto entidad
		 * @throws Exception Si la entidad no existe en la base de datos de la instancia
		 */
		private function getSourceEntity ($instanceName, $entityId) {
			global $adb;

			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$sql    = 'SELECT crme.setype FROM vtiger_crmentity crme WHERE crme.deleted=0 AND crme.crmid=?';
			$result = $adb->pquery ($sql, array ($entityId));
			if ($adb->num_rows ($result) == 0) {
				throw new Exception ('La entidad asociada con la invitación no está registrada o ha sido eliminada');
			}
			$row        = $adb->fetch_row ($result);
			$moduleName = $row ['setype'];
			$entity     = $this->getEntity ($moduleName, $entityId);

			return $entity;
		}

		/**
		 * Decodifica el nombre de la instancia y obtiene el nombre de la misma
		 *
		 * @param string $encodedInstanceName El nombre de la instancia codificado como md5 ("[$instancia]")
		 *
		 * @return string El nombre de la instancia
		 * @throws Exception Si la instancia no está configurada en la base de datos
		 */
		private function getInstanceName ($encodedInstanceName) {
			require ('config.inc.php');
			global $platPrincipal;

			if ($encodedInstanceName == md5 ("[{$platPrincipal}]")) {
				return $platPrincipal;
			} else {
				$adb    = AdbManager::getInstance ()->getMasterAdb ();
				$sql    = "SELECT ins.code FROM vtiger_instances ins WHERE (MD5(CONCAT('[', ins.code, ']'))=?)";
				$result = $adb->pquery ($sql, array ($encodedInstanceName));
				if ($adb->num_rows ($result) == 0) {
					throw new Exception ('El identificador de instancia suministrado no se encuentra registrado');
				}
				$row = $adb->fetch_array ($result);
				return $row ['code'];
			}
		}

		/**
		 * Obtiene el objeto invitations de la instancia e ID suministrados.
		 *
		 * @param string $instanceName Nombre de la instancia
		 * @param string $invitationId ID de la instancia
		 *
		 * @return invitations Objeto que contiene la información de la instancia
		 * @throws Exception Si la invitación con el Id suministrado no está registrada en la base de datos de la instancia
		 */
		private function getInvitation ($instanceName, $invitationId) {
			global $adb;

			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$sql    = "SELECT i.invitationid FROM vtiger_invitations i INNER JOIN vtiger_crmentity crme ON crme.crmid=i.invitationid WHERE (crme.deleted=0) AND (MD5(CONCAT('[', i.invitationid, ']'))=?)";
			$result = $adb->pquery ($sql, array ($invitationId));
			if ($adb->num_rows ($result) == 0) {
				throw new Exception ('La invitación suministrada no se encuentra registrada');
			}
			$row        = $adb->fetch_row ($result);
			$invitation = $this->getEntity ('invitations', $row ['invitationid']);

			return $invitation;
		}

		/**
		 * Obtiene la información del módulo suministrado o <i>null</i> en caso de que el módulo no esté configurado o esté inactivo en la instancia suministrada
		 *
		 * @param string $instanceName Nombre de la instancia
		 * @param string $moduleName Nombre del módulo
		 *
		 * @return array|null La información del módulo suministrado o <i>null</i> en caso de que no esté configurado o esté inactivo en la instancia suministrada
		 */
		private function getModuleInformation ($instanceName, $moduleName) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$sql    = 'SELECT mi.tabid FROM vtiger_modules_instances mi WHERE (mi.deleted=0) AND (mi.isactive=1) AND (mi.codeinstance=?) AND (mi.modulename=?)';
			$result = $adb->pquery ($sql, array ($instanceName, $moduleName));
			if ($adb->num_rows ($result) == 0) {
				return null;
			}

			$adb    = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$sql    = 'SELECT t.*, en.* FROM vtiger_tab t LEFT JOIN vtiger_entityname en ON en.tabid=t.tabid WHERE t.name=?';
			$result = $adb->pquery ($sql, array ($moduleName));
			return $adb->num_rows ($result) == 0 ? null : $adb->fetch_array ($result);
		}

		/**
		 * Obtiene la entidad padre asociada a la entidad suministrada
		 *
		 * @param string $sourceInstanceName Nombre de la instancia de origen
		 * @param CRMEntity|stdClass $sourceEntity La entidad
		 * @param string $fieldName El nombre del campo que enlaza con el módulo padre
		 * @param string $parentModuleName El nombre del módulo padre
		 *
		 * @return CRMEntity|stdClass|null El objeto padre del módulo suministrado asociado a la entidad suministrada o <i>null</i> si no existe
		 */
		private function getSourceParentEntity ($sourceInstanceName, $sourceEntity, $fieldName, $parentModuleName) {
			global $adb;
			$adb          = AdbManager::getInstance ()->getSourceInstanceAdb ($sourceInstanceName);
			$parentEntity = $this->getEntity ($parentModuleName);
			if ((!$parentEntity) || (!isset ($parentEntity->table_name)) || (!$parentEntity->table_name) || (!isset ($parentEntity->table_index)) || (!$parentEntity->table_index)) {
				return null;
			}
			$result = $adb->pquery ("SELECT 1 FROM {$parentEntity->table_name} WHERE {$parentEntity->table_index}=?", array ($sourceEntity->column_fields [ $fieldName ]));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				// Fix para evitar error en el cálculo del tabid en CRMEntity.php línea 781 => include/utils/CommonUtils.php línea 393
				if (isset ($_SESSION ['authenticated_user_menu'])) {
					$_SESSION ['authenticated_user_menu'] = array ();
				}
				$parentEntity->retrieve_entity_info ($sourceEntity->column_fields [ $fieldName ], $parentModuleName);
			} else {
				$parentEntity = null;
			}

			return $parentEntity;
		}

		/**
		 * Obtiene las entidades padre asociadas a la entidad suministrada
		 *
		 * @param string $sourceInstanceName Nombre de la instancia de origen
		 * @param CRMEntity|stdClass $sourceEntity La entidad
		 * @param array $parentModules Los módulos padre asociados a la entidad suministrada
		 *
		 * @return array|null Los objetos padre de los módulos suministrados asociados a la entidad suministrada o <i>null</i> si no existen
		 */
		private function getSourceParentEntities ($sourceInstanceName, $sourceEntity, $parentModules) {
			if ((!$parentModules) || (!is_array ($parentModules)) || (count ($parentModules) == 0)) {
				return null;
			}

			$parentEntities = array ();
			foreach ($parentModules as $parentModule) {
				$parentEntities [] = array (
					'modulename' => $parentModule ['modulename'],
					'fieldname'  => $parentModule ['fieldname'],
					'entity'     => $this->getSourceParentEntity ($sourceInstanceName, $sourceEntity, $parentModule ['fieldname'], $parentModule ['modulename']),
				);
			}
			return $parentEntities;
		}

		/**
		 * Obtiene un arreglo con los nombres de los campos clave del módulo suministrado y los valores de esos campos en las entidades suministradas
		 *
		 * @param array $module La información del módulo
		 * @param array $entities Las entidades a buscar
		 *
		 * @return array Los nombres de los campos clave del módulo suministrado y los valores de esos campos en las entidades suministradas
		 */
		private function getEntityIdentifiersFromModule ($module, $entities) {
			$identifiers = array ();
			foreach ($entities as $entity) {
				if ($module ['modulename'] != $entity ['modulename']) {
					continue;
				}
				$identifiers [ $module ['fieldname'] ] = $entity ['entity']->column_fields ['record_id'];
			}
			return $identifiers;
		}

		/**
		 * Obtiene un arreglo con los nombres de los campos clave de los módulos suministrados y los valores de esos campos en las entidades suministradas
		 *
		 * @param array $modules Los módulos a buscar
		 * @param array $entities Las entidades a buscar
		 *
		 * @return array|null Los nombres de los campos clave de los módulos suministrados y los valores de esos campos en las entidades suministradas o <i>null</i>
		 *                    si no se encuentran los campos clave
		 */
		private function getEntityIdentifiersFromModules ($modules, $entities) {
			if ((!$modules) || (!is_array ($modules)) || (count ($modules) == 0) || (!$entities) || (!is_array ($entities)) || (count ($entities) == 0)) {
				return null;
			}
			$identifiers = array ();
			foreach ($modules as $module) {
				$identifiers = array_merge ($identifiers, $this->getEntityIdentifiersFromModule ($module, $entities));
			}
			return $identifiers;
		}

		/**
		 * Obtiene un arreglo con información de los módulos padre del módulo suministrado como parámetro.
		 *
		 * Un módulo padre es aquel que contiene información referenciada por otro módulo en una jerarquía Hijo - Padre.
		 *
		 * Por ejemplo:
		 * a. Un plan de mantenimiento permite asociar una cuenta. 'Accounts' es un módulo PADRE de 'plan_mantenimiento'
		 * b. Un plan de mantemiento permite asociar intervenciones.
		 *      i. 'plan_mantenimiento' es un módulo PADRE de 'intervencion'.
		 *      ii. 'intervencion' es un módulo HIJO de 'plan_mantenimiento'
		 *
		 * Cada elemento del arreglo es a su vez un arreglo con la siguiente estructura:
		 *
		 * array (
		 *      'fieldname'  => Nombre del campo
		 *      'modulename' => Nombre del módulo relacionado
		 * )
		 *
		 * @param string $instanceName Nombre de la instancia
		 * @param string $moduleName Nombre del módulo
		 *
		 * @return array|null Módulos padre del módulo suministrado o <i>null</i> si el módulo suministrado no tiene módulos padre
		 */
		private function getParentModules ($instanceName, $moduleName) {
			global $adb;

			$parentModules = array ();

			// Determinar si el módulo suministrado es hijo del módulo 'Accounts'. De ser así, agregarlo a la lista de módulos padre
			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$sql    = 'SELECT f.fieldname FROM vtiger_field f WHERE f.uitype IN (51) AND (f.tabid IN (SELECT t.tabid FROM vtiger_tab t WHERE t.name=?))';
			$result = $adb->pquery ($sql, array ($moduleName), true);
			if ($adb->num_rows ($result) > 0) {
				$row              = $adb->fetch_array ($result);
				$parentModules [] = array (
					'fieldname'  => $row ['fieldname'],
					'modulename' => 'Accounts',
				);
			}

			// Obtener los campos de tipo selección de entidades (uitype = 10) del módulo suministrado
			$sql    = "SELECT DISTINCT
							f.fieldname,
							fmr.relmodule AS modulename
						FROM
							vtiger_field f
							INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldid=f.fieldid
						WHERE
							f.uitype='10' AND
							fmr.module=?";
			$result = $adb->pquery ($sql, array ($moduleName), true);
			if (($adb->num_rows ($result) == 0) && (count ($parentModules) == 0)) {
				return null;
			}

			while ($row = $adb->fetch_array ($result)) {
				$parentModules [] = $row;
			}

			return $parentModules;
		}

		/**
		 * Obtiene el objeto entidad en la instancia destino o <i>null</i> si la misma no está compartida
		 *
		 * @param string $sourceInstanceName Nombre de la instancia de origen
		 * @param string $moduleName Nombre del módulo
		 * @param string $sourceEntityId Id de la entidad en la instancia de origen
		 * @param string $targetInstanceName Nombre de la instancia destino
		 *
		 * @return CRMEntity|null El objeto entidad en la instancia destino o <i>null</i> si la misma no está compartida
		 */
		private function getSharedEntity ($sourceInstanceName, $moduleName, $sourceEntityId, $targetInstanceName) {
			// TODO: Buscar la entidad en la suscripción
			return null;
//			global $adb;
//
//			// Determinar si existe una suscripción para esa entidad en la instancia de origen
//			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($sourceInstanceName);
//			$sql    = 'SELECT
//							s.targetentityid
//						FROM
//							vtiger_eventsubscriptions_subscriptions s
//						WHERE
//							(s.targetentityid IS NOT NULL) AND
//							(s.sourcemodule=?) AND
//							(s.sourceentityid=?) AND
//							(s.targetinstance=?) AND
//							(s.targetmodule=?)';
//			$result = $adb->pquery ($sql, array ($moduleName, $sourceEntityId, $targetInstanceName, $moduleName), true);
//			if ($adb->num_rows ($result) == 0) {
//				return null;
//			}
//			$row = $adb->fetch_array ($result);
//
//			// Determinar si la entidad está compartida en la instancia destino
//			$adb    = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceName);
//			$sql    = 'SELECT crme.crmid FROM vtiger_crmentity crme WHERE (crme.deleted=0) AND (crme.crmid=?)';
//			$result = $adb->pquery ($sql, array ($row ['targetentityid']), true);
//			if ($adb->num_rows ($result) == 0) {
//				return null;
//			}
//			$row = $adb->fetch_array ($result);
//
//			// Obtener el objeto entidad de la instancia destino
//			$sharedEntity = $this->getEntity ($moduleName, $row ['crmid']);
//
//			return $sharedEntity;
		}

		/**
		 * Obtiene el nombre de la instancia asignada al invitado o <i>null</i> si no tiene instancia asignada
		 *
		 * @param string $owner Nombre de usuario del invitado
		 *
		 * @return string|null El nombre de la instancia asignada o <i>null</i> si no tiene instancia asignada
		 */
		private function getTargetInstanceName ($owner) {
			global $adb;
			$adb    = AdbManager::getInstance ()->getMasterADB ();
			$sql    = 'SELECT ins.code FROM vtiger_instances ins WHERE (ins.administrator=?)';
			$result = $adb->pquery ($sql, array ($owner));
			if ($adb->num_rows ($result) == 0) {
				return null;
			}
			$row = $adb->fetch_row ($result);
			return $row ['code'];
		}

		/**
		 * Comparte los módulos padres o hijos, dependiendo de si hay entidades en esos módulos
		 *
		 * @param string $targetInstanceName Nombre de la instancia destino
		 * @param array $linkedModules Los módulos a compartir
		 * @param array $linkedEntities Las entidades a verificar
		 */
		private function shareLinkedModules ($targetInstanceName, $linkedModules, $linkedEntities) {
			if ((!$linkedModules) || (!$linkedEntities)) {
				return;
			}
			foreach ($linkedModules as $linkedModule) {
				if (!$this->getModuleInformation ($targetInstanceName, $linkedModule ['modulename'])) {
					$this->shareModule ($targetInstanceName, $linkedModule ['modulename']);
				}
			}
		}

		/**
		 * Comparte el módulo de la entidad a compartir
		 *
		 * @param string $instanceName Nombre de la instancia destino
		 * @param string $moduleName Nombre del módulo a compartir
		 */
		private function shareModule ($instanceName, $moduleName) {
			$adb    = AdbManager::getInstance ()->getMasterADB ();
			$sql    = 'SELECT mi.codeinstance FROM vtiger_modules_instances mi WHERE mi.codeinstance=? AND mi.modulename=?';
			$result = $adb->pquery ($sql, array ($instanceName, $moduleName));
			if ($adb->num_rows ($result) == 0) {
				$sql = "INSERT INTO vtiger_modules_instances (codeinstance, tabid, modulename, origin, transactiondate, deleted, isdemo, isactive)
						SELECT ?, t.tabid, t.name, 'platzilla', NOW(), 0, 2, 1 FROM vtiger_tab t WHERE t.name=?";
				$adb->pquery ($sql, array ($instanceName, $moduleName));
			} else {
				$row    = $adb->fetch_row ($result);
				$isDemo = $row ['isdemo'] == 1 ? 2 : $row ['isdemo'];
				$sql    = 'UPDATE vtiger_modules_instances SET transactiondate=NOW(), deleted=0, isdemo=?, isactive=1 WHERE codeinstance=? AND modulename=?';
				$adb->pquery ($sql, array ($isDemo, $instanceName, $moduleName));
			}

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$sql = 'UPDATE vtiger_tab SET presence=0, avaliable=1 WHERE name=?';
			$adb->pquery ($sql, array ($moduleName));
		}

		/**
		 * Comparte la entidad seleccionada
		 *
		 * @param string $moduleName Nombre del módulo al que pertenece la entidad
		 * @param CRMEntity|stdClass $sourceEntity Objeto entidad a compartir
		 * @param string $targetInstanceName Nombre de la instancia destino
		 * @param array|null $parentEntityIdentifiers Lista de identificadores de entidades padre
		 *
		 * @return array Información de la entidad recién creada en la instancia destino
		 */
		private function shareEntity ($moduleName, CRMEntity $sourceEntity, $targetInstanceName, $parentEntityIdentifiers = null) {
			global $adb;
			$adb          = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceName);
			$targetEntity = $this->getEntity ($moduleName);
			foreach ($sourceEntity->column_fields as $fieldName => $fieldValue) {
				if (($parentEntityIdentifiers) && (is_array ($parentEntityIdentifiers)) && (count ($parentEntityIdentifiers) > 0) && (array_key_exists ($fieldName, $parentEntityIdentifiers))) {
					$targetEntity->column_fields [ $fieldName ] = $parentEntityIdentifiers [ $fieldName ];
				} else {
					$targetEntity->column_fields [ $fieldName ] = vtlib_purify ($fieldValue);
				}
			}

			$targetEntity->column_fields ['record_id']        = 0;
			$targetEntity->column_fields ['assigned_user_id'] = 1;
			$targetEntity->saveentity ($moduleName);
			$targetEntity->column_fields ['record_id'] = $targetEntity->id;

			return array (
				'modulename' => $moduleName,
				'entity'     => $targetEntity,
			);
		}

		/**
		 * Comparte las entidades padre de la entidad seleccionada
		 *
		 * @param string $sourceInstanceName Nombre de la instancia de origen
		 * @param array $sourceParentEntities Lista de entidades padre a compartir
		 * @param string $targetInstanceName Nombre de la instancia destino
		 *
		 * @return array|null Información de las entidades recién creadas en la instancia destino o <i>null</i> si no se suministran entidades padre
		 */
		private function shareParentEntities ($sourceInstanceName, $sourceParentEntities, $targetInstanceName) {
			if ((!$sourceParentEntities) || (!is_array ($sourceParentEntities)) || (count ($sourceParentEntities) == 0)) {
				return null;
			}
			$targetParentEntities = array ();
			foreach ($sourceParentEntities as $sourceParentEntityData) {
				/** @var CRMEntity|stdClass $sourceParentEntity */
				$sourceParentEntity   = $sourceParentEntityData ['entity'];
				if (!$sourceParentEntity) {
					continue;
				}
				$sourceParentEntityId = $sourceParentEntity->column_fields ['record_id'];
				$moduleName           = $sourceParentEntityData ['modulename'];
				$targetParentEntity   = $this->getSharedEntity ($sourceInstanceName, $moduleName, $sourceParentEntityId, $targetInstanceName);
				if (!$targetParentEntity) {
					$targetParentEntityData = $this->shareEntity ($moduleName, $sourceParentEntity, $targetInstanceName);
					/** @var CRMEntity|stdClass $targetParentEntity */
					$targetParentEntity                             = $targetParentEntityData ['entity'];
					$targetParentEntity->column_names ['record_id'] = $targetParentEntity->id;
					$targetParentEntities []                        = $targetParentEntityData;
					// TODO: registrar suscripción
				} else {
					$targetParentEntities [] = array (
						'modulename' => $moduleName,
						'entity'     => $targetParentEntity,
					);
				}
			}
			return $targetParentEntities;
		}

		/**
		 * Comparte las entidades hijas de la entidad seleccionada
		 *
		 * @param string $sourceInstanceName Nombre de la instancia de origen
		 * @param array $sourceChildEntities Lista de entidades hijas a compartir
		 * @param string $targetInstanceName Nombre de la instancia destino
		 * @param array|null $entityIdentifiers Lista de identificadores de entidades hijas
		 */
		private function shareChildEntities ($sourceInstanceName, $sourceChildEntities, $targetInstanceName, $entityIdentifiers = null) {
			if ((!$sourceChildEntities) || (!is_array ($sourceChildEntities)) || (count ($sourceChildEntities) == 0)) {
				return;
			}
			foreach ($sourceChildEntities as $sourceChildEntityData) {
				/** @var CRMEntity|stdClass $sourceChildEntity */
				$sourceChildEntity   = $sourceChildEntityData ['entity'];
				$sourceChildEntityId = $sourceChildEntity->column_fields ['record_id'];
				$moduleName          = $sourceChildEntityData ['modulename'];
				$targetChildEntity   = $this->getSharedEntity ($sourceInstanceName, $moduleName, $sourceChildEntityId, $targetInstanceName);
				if ($targetChildEntity) {
					return;
				}
				$targetChildEntityData = $this->shareEntity ($moduleName, $sourceChildEntity, $targetInstanceName, $entityIdentifiers);
				/** @var CRMEntity|stdClass $targetChildEntity */
				$targetChildEntity                             = $targetChildEntityData ['entity'];
				$targetChildEntity->column_names ['record_id'] = $targetChildEntity->id;
				// TODO: Registrar suscripción
			}
		}

		/**
		 * Valida los argumentos de la invitación
		 *
		 * @param array $arguments Arreglo con los argumentos
		 *
		 * @throws Exception Si alguno de los argumentos no es válido
		 */
		private function validateArguments ($arguments) {
			if ((!isset ($arguments ['instance'])) || (!$arguments ['instance'])) {
				throw new Exception ('No se ha suministrado el identificador de la instancia');
			}
			if ((!isset ($arguments ['invitation'])) || (!$arguments ['invitation'])) {
				throw new Exception ('No se ha suministrado el identificador de la invitación');
			}
		}

		/**
		 * Analiza la invitación, con la finalidad de mostrar el asistente correcto, o un error. Las posibilidades son las siguientes:
		 *
		 * 1. El invitado no tiene instancia asignada
		 * 2. La instancia asignada no tiene activo el módulo al cual pertenece la entidad a compartir
		 * 3. La entidad no ha sido compartida
		 * 4. La entidad ya ha sido compartida con este invitado
		 *
		 * @param array $arguments Argumentos de la invitación
		 *
		 * @return array El resultado del análisis y la información adicional necesaria
		 * @throws Exception En caso de presentarse algún error
		 */
		public function analyzeInvitation ($arguments) {
			// Validar parámetros
			$this->validateArguments ($arguments);

			// Obtener información de la instancia desde la que se realiza la invitación
			$sourceInstanceName = $this->getInstanceName (vtlib_purify ($arguments ['instance']));

			// Obtener información de la invitación
			$invitation = $this->getInvitation ($sourceInstanceName, vtlib_purify ($arguments ['invitation']));
			$guest      = $invitation->column_fields ['guest'];
			$entityId   = $invitation->column_fields ['entityid'];

			// Obtener la entidad a compartir
			$sourceEntity = $this->getSourceEntity ($sourceInstanceName, $entityId);

			// Si el invitado NO tiene instancia, mostrar asistente
			$targetInstanceName = $this->getTargetInstanceName ($guest);
			if (!$targetInstanceName) {
				return array (
					'result' => self::RESULT_NO_TARGET_INSTANCE,
					'guest'  => $invitation->column_fields ['guest'],
				);
			}

			// Si el invitado NO tiene activo el módulo a compartir, mostrar asistente
			$moduleName        = $sourceEntity->column_fields ['record_module'];
			$moduleInformation = $this->getModuleInformation ($targetInstanceName, $moduleName);
			if ((!$moduleInformation) || ($moduleInformation ['presence'] == -1) || ($moduleInformation ['avaliable'] == 0)) {
				return array (
					'result' => self::RESULT_MODULE_NOT_ENABLED,
				);
			}

			// Si el invitado tiene compartida la entidad, producir excepción
			$sharedEntity = $this->getSharedEntity ($sourceInstanceName, $moduleName, $entityId, $targetInstanceName);
			if ($sharedEntity) {
				throw new Exception ('La entidad seleccionada ya está compartida');
			}

			// Si el invitado NO tiene compartida la entidad, mostrar asistente
			return array (
				'result' => self::RESULT_ENTITY_NOT_SHARED,
			);
		}

		/**
		 * Procesa la invitación, según sea necesario, según los siguientes pasos:
		 *
		 * 1. Si el invitado no tiene instancia asignada, se le asigna una.
		 * 2. Si la instancia no tiene activo el módulo al cual pertenece la entidad compartida (incluyendo los módulos padres y módulos hijos), se activan
		 * 3. Si la entidad ya ha sido compartida, se produce una excepción
		 * 4. Si la entidad NO ha sido compartida, se comparte la entidad, así como sus entidades padres e hijas
		 * 5. Se generan las suscripciones a eventos.
		 *
		 * @param array $arguments Los argumentos de la invitación
		 *
		 * @return string El nombre de la instancia generada
		 * @throws Exception En caso de presentarse algún error
		 */
		public function process ($arguments) {
			// Validar parámetros
			$this->validateArguments ($arguments);

			// Obtener información de la instancia desde la que se realiza la invitación
			$sourceInstanceName = $this->getInstanceName (vtlib_purify ($arguments ['instance']));

			// Obtener información de la invitación
			$invitation = $this->getInvitation ($sourceInstanceName, vtlib_purify ($arguments ['invitation']));
			$guest      = $invitation->column_fields ['guest'];
			$entityId   = $invitation->column_fields ['entityid'];

			// Obtener la entidad a compartir
			$sourceEntity = $this->getSourceEntity ($sourceInstanceName, $entityId);

			// Obtener información del módulo a compartir
			$moduleName    = $sourceEntity->column_fields ['record_module'];
			$parentModules = $this->getParentModules ($sourceInstanceName, $moduleName);
			$childModules  = $this->getChildModules ($sourceInstanceName, $moduleName);

			// Obtener las entidades relacionadas de la entidad de origen
			$sourceParentEntities = $this->getSourceParentEntities ($sourceInstanceName, $sourceEntity, $parentModules);
			$sourceChildEntities  = $this->getSourceChildEntities ($sourceInstanceName, $childModules, $sourceEntity);

			// Si el invitado NO tiene instancia, crearla
			$targetInstanceName = $this->getTargetInstanceName ($guest);
			if (!$targetInstanceName) {
				$targetInstanceName = $this->asignStockInstance ($arguments);
			}

			// Si la instancia NO tiene el módulo solicitado, agregarlo y activarlo junto con los módulos relacionados
			$moduleInformation = $this->getModuleInformation ($targetInstanceName, $moduleName);
			if (!$moduleInformation) {
				$this->shareModule ($targetInstanceName, $moduleName);
				$this->shareLinkedModules ($targetInstanceName, $parentModules, $sourceParentEntities);
				$this->shareLinkedModules ($targetInstanceName, $childModules, $sourceChildEntities);
				$moduleInformation = $this->getModuleInformation ($targetInstanceName, $moduleName);
			}

			// Si la entidad está compartida, producir excepción
			$targetEntity = $this->getSharedEntity ($sourceInstanceName, $moduleName, $entityId, $targetInstanceName);
			if ($targetEntity) {
				throw new Exception ('La entidad seleccionada ya está compartida');
			}

			// FIX para que el módulo intervencion no explote
			$_REQUEST ['return_module'] = $moduleName;

			// Compartir las entidades padre
			$targetParentEntities    = $this->shareParentEntities ($sourceInstanceName, $sourceParentEntities, $targetInstanceName);
			$targetParentIdentifiers = $this->getEntityIdentifiersFromModules ($parentModules, $targetParentEntities);

			// Compartir la entidad. Registrar suscripción a eventos
			$targetEntity = $this->shareEntity ($moduleName, $sourceEntity, $targetInstanceName, $targetParentIdentifiers);
			// TODO: Registrar suscripción
			$targetChildIdentifiers = $this->getEntityIdentifiersFromModules (array (array ('modulename' => $moduleName, 'fieldname' => $moduleInformation ['entityidfield'])), array ($targetEntity));

			// Compartir las entidades hijas
			$this->shareChildEntities ($sourceInstanceName, $sourceChildEntities, $targetInstanceName, $targetChildIdentifiers);

			// Actualizar el status de la invitación
			$this->updateStatus ($sourceInstanceName, $invitation->column_fields ['record_id'], 'Procesada');

			return $targetInstanceName;
		}

		/**
		 * Actualiza el status de la invitación
		 *
		 * @param string $instanceName Nombre de la instancia de origen
		 * @param string $invitationId ID de la invitación
		 * @param string $status Status de la invitación
		 */
		public function updateStatus ($instanceName, $invitationId, $status) {
			$adb = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$adb->pquery ('UPDATE vtiger_invitations SET invitationstatus=? WHERE invitationid=?', array ($status, $invitationId));
			$adb->pquery ('UPDATE vtiger_crmentity SET modifiedtime=NOW() WHERE crmid=?', array ($invitationId));
		}

		/**
		 * Determina si se ha alcanzado el límite de documentos creados en un módulo compartido
		 *
		 * @param string $instanceName Nombre de la instancia
		 * @param string $moduleName Nombre del módulo
		 *
		 * @throws Exception EN caso de alcanzarse el límite o de presentarse algún error
		 */
		public function checkNewDocumentsTotal ($instanceName, $moduleName) {
			return;
//			if ((!$instanceName) || (!$moduleName)) {
//				return;
//			}
//			$adb    = AdbManager::getInstance ()->getMasterADB ();
//			$sql    = 'SELECT mi.isdemo FROM vtiger_modules_instances mi WHERE (mi.codeinstance=?) AND (mi.modulename=?) AND (mi.deleted=0)';
//			$result = $adb->pquery ($sql, array ($instanceName, $moduleName));
//			if ($adb->num_rows ($result) == 0) {
//				throw new Exception ("El módulo $moduleName no está registrado en la instancia $instanceName");
//			}
//			$row    = $adb->fetch_array ($result);
//			$isDemo = $row ['isdemo'];
//			if ($isDemo != 2) {
//				return;
//			}
//			$result = $adb->query ("SELECT vi.varvalue FROM vtiger_variables_instancias vi WHERE vi.varname='MAX_NEW_RECORDS_BY_INVITATION'", true);
//			if ($adb->num_rows ($result) > 0) {
//				$row        = $adb->fetch_row ($result);
//				$maxRecords = is_numeric ($row ['varvalue']) ? intval ($row ['varvalue']) : 5;
//			} else {
//				$maxRecords = 5;
//			}
//
//			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
//			$sql    = 'SELECT crme.crmid FROM vtiger_crmentity crme WHERE (crme.deleted=0) AND (crme.smcreatorid>0) AND (crme.setype=?)';
//			$result = $adb->pquery ($sql, array ($moduleName));
//			if ($adb->num_rows ($result) >= $maxRecords) {
//				throw new Exception ("Sólo se permite generar un máximo de $maxRecords registros");
//			}
		}

		/**
		 * Obtiene una única instancia de la clase
		 *
		 * @return InvitationsManager La instancia de la clase
		 */
		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new InvitationsManager ();
			}
			return self::$INSTANCE;
		}

	}
