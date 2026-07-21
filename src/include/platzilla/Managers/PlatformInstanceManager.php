<?php
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Managers/RoleManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Objects/PlatformInstance.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/platzilla/Utils/InstanceDatabaseUtils.php');
	require_once ('include/platzilla/Utils/PlatformTableNamesProvider.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatformUtils.class.php');

	class PlatformInstanceManager {
		/** @var PlatformInstanceManager[]|null */
		private static $INSTANCE = null;

		/**
		 * @param ModuleRelationship $platformModuleRelationship
		 * @param ModuleRelationship $instanceModuleRelationship
		 *
		 * @return boolean
		 */
		private function areModuleRelationshipKeysEqual ($platformModuleRelationship, $instanceModuleRelationship) {
			return ($platformModuleRelationship->getFunction () == $instanceModuleRelationship->getFunction ()) &&
				   ($platformModuleRelationship->getModuleName () == $instanceModuleRelationship->getModuleName ()) &&
				   ($platformModuleRelationship->getRelatedModuleName () == $instanceModuleRelationship->getRelatedModuleName ());
		}

		/**
		 * @param PlatformInstance $instance
		 * @param array $data
		 */
		private function createEntities ($instance, $data) {
			if ((!is_array ($data)) || (empty ($data))) {
				return;
			}

			$instanceCode = $instance->getCode ();
			$adb          = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			foreach ($data as $moduleName => $entitiesData) {
				foreach ($entitiesData as $entityData) {
					$entity                = PlatformUtils::getCrmEntity ($adb, $moduleName);
					$entity->column_fields = $entityData;
					PlatformUtils::saveCrmEntity ($adb, $entity, $moduleName);
				}
			}
		}

        /**
         * Actualiza las funciones y trigger de la instancia
         * @param PearDatabase $adb
         */
		private function createFunctionsAndTriggers ($adb) {
            $adb->query (
                "CREATE FUNCTION `ExtractNumber`(`in_string` TEXT CHARSET utf8) RETURNS varchar(25) CHARSET utf8
                            NO SQL
                        BEGIN
                            DECLARE ctrNumber VARCHAR(250);
                            DECLARE finNumber VARCHAR(250) DEFAULT '';
                            DECLARE sChar VARCHAR(1);
                            DECLARE inti INTEGER DEFAULT 1;
                            IF LENGTH(in_string) > 0 THEN
                                WHILE(inti <= LENGTH(in_string)) DO
                                    SET sChar = SUBSTRING(in_string, inti, 1);
                                    SET ctrNumber = FIND_IN_SET(sChar, '0,1,2,3,4,5,6,7,8,9'); 
                                    IF (ctrNumber > 0) THEN
                                        SET finNumber = CONCAT(finNumber,sChar);
                                    END IF;
                                    SET inti = inti + 1;
                                END WHILE;
                                RETURN finNumber;
                            ELSE
                                RETURN 0;
                            END IF;    
                        END"
            );
            $adb->query ('DO SLEEP(1)');
            $adb->query (
                "CREATE FUNCTION `taskToMatrix`(`date_start` DATE, `due_date` DATE, `eventstatus` VARCHAR(200) CHARSET utf8, `createdtime` DATETIME, `range_init` DATE, `range_end` DATE) RETURNS tinyint(1)
                NO SQL
	            BEGIN
	                DECLARE dateStart VARCHAR(250)  DEFAULT '';
	                DECLARE isValid   TINYINT(250)  DEFAULT 0;
	                IF ((LENGTH(date_start) <= 1) OR (date_start IS NULL)) AND
	                   ((LENGTH(due_date) <= 1) OR (due_date IS NULL))
	                THEN
	                    SET isValid = 0;
	                 END IF;
	                 SET dateStart = date_start;
	                IF LENGTH(dateStart) = 0
	                    THEN
	                    SET dateStart = createdtime;
	                END IF;
	                
	                IF DATE(dateStart) >= DATE(range_end) THEN
	                  SET isValid = 0;
	                 END IF;
	                
	                IF eventstatus = 'Planned' OR eventstatus = 'Not Held' OR eventstatus = 'Postponed' THEN
	                     IF DATE(dateStart) <= DATE(range_end) THEN
	                    SET isValid = 1;
	                    END IF;
	                ELSEIF eventstatus = 'Held' THEN
	                    BEGIN
	                        IF DATE(dateStart) >= DATE(range_init) AND
	                           DATE(dateStart) <= DATE(range_end) THEN
	                           SET isValid = 1;
	                        END IF;
	                        
	                        IF DATE(due_date) >= DATE(range_init) AND
	                           DATE(due_date) <= DATE(range_end) THEN
	                           SET isValid = 1;
	                        END IF;
	                        
	                        IF DATE(dateStart) < DATE(range_init) AND
	                          DATE(due_date) > DATE(range_end) THEN
	                        SET isValid = 1;
	                      END IF;
	                  END;
	                ELSE
	                    SET isValid = 0;
	                END IF;
	                RETURN isValid;
	            END"
            );
            $adb->query ('DO SLEEP(1)');
            $adb->query ("CREATE FUNCTION `ExtractSum`(`in_string` TEXT CHARSET utf8) RETURNS int(10) unsigned
            BEGIN
			    DECLARE ctrNumber VARCHAR(250);
			    DECLARE finNumber VARCHAR(250) DEFAULT '';
				DECLARE SUMA INT(10) DEFAULT 0;
			    DECLARE sChar VARCHAR(1);
			    DECLARE inti INTEGER DEFAULT 1;
			    IF LENGTH(in_string) > 0 THEN
			        WHILE(inti <= LENGTH(in_string)) DO
			            SET sChar = SUBSTRING(in_string, inti, 1);
			            SET ctrNumber = FIND_IN_SET(sChar, '0,1,2,3,4,5,6,7,8,9,;');
			            IF (ctrNumber > 0) THEN
			                IF (sChar = ';') THEN
							SET SUMA = SUMA + CAST(finNumber AS UNSIGNED);
							SET finNumber = '';
							ELSE
			                SET finNumber = CONCAT(finNumber,sChar);
							END IF;
			            END IF;
			            SET inti = inti + 1;
			        END WHILE;
				RETURN SUMA;
	                    ELSE
	                    RETURN 0;
	                END IF;
	            END"
            );
			$adb->query ('DO SLEEP(1)');
			$adb->query ("CREATE FUNCTION `validityRecordByDate`(
			    `date_start` VARCHAR(15),
			    `due_date` VARCHAR(15),
			    `createdtime` DATETIME,
			    `range_init` DATE,
			    `range_end` DATE
				) RETURNS TINYINT(1)
				NO SQL
				BEGIN
				   DECLARE
				        dateStart VARCHAR(250) DEFAULT '';
						DECLARE isValid TINYINT(1) DEFAULT 0;
						
					IF((LENGTH(date_start) <= 5) OR (date_start IS NULL)) AND
						((LENGTH(due_date) <= 5) OR (due_date IS NULL))
					THEN
						SET isValid = 0;
					END IF;
					IF(due_date IS NULL)
					THEN
						SET due_date = '';
					END IF;
					SET dateStart = date_start;
					IF LENGTH(dateStart) < 5 THEN
						SET dateStart = createdtime;
					END IF;
					#Start_date must be before Until else false
					IF DATE(dateStart) > DATE(range_end) THEN
						SET isValid = 0;
					END IF;
					#Start date is within the range
					IF DATE(dateStart) >= DATE(range_init) AND DATE(dateStart) <= DATE(range_end) THEN
						SET isValid = 1;
					END IF;
					#Within the date range
					IF DATE(due_date) >= DATE(range_init) AND DATE(due_date) <= DATE(range_end) THEN
						SET isValid = 1;
					END IF;
					#Active within the range
					IF (DATE(dateStart) <= DATE(range_init)) AND ((DATE(due_date) > DATE(range_end)) OR (LENGTH(due_date) < 5) OR (due_date IS NULL)) THEN
						SET isValid = 1;
					END IF;
					IF ((DATE(createdtime) >= DATE(range_init)) AND (DATE(createdtime) <= DATE(range_end)) AND (due_date IS NULL)  AND (date_start IS NULL)) THEN
						SET isValid = 1;
					END IF;
					RETURN isValid;
				END"
			);
            $adb->query ('DO SLEEP(1)');
            $adb->query (
                "CREATE TRIGGER `after_question_insert` AFTER INSERT ON `vtiger_question` FOR EACH ROW BEGIN
                    IF (NOT EXISTS (SELECT * FROM vtiger_question2group_range WHERE theme_name = NEW.questionstageid AND questionid = NEW.questionid)) THEN
                        INSERT INTO vtiger_question2group_range(questionid, theme_name, minimum, maximum)
                        VALUES(NEW.questionid, NEW.questionstageid, 0, IFNULL((SELECT SUM(`value`) FROM `vtiger_question2answeres` WHERE `questionid` = NEW.questionid ),0));
                    END IF;
                END"
            );
            $adb->query (
                "CREATE TRIGGER `after_question_update` AFTER UPDATE ON `vtiger_question` FOR EACH ROW BEGIN
                    IF (OLD.questionstageid != NEW.questionstageid)  THEN
                        UPDATE `vtiger_question2group_range` SET `theme_name` = NEW.questionstageid WHERE `theme_name` = OLD.questionstageid AND `questionid` = NEW.questionid;
                    END IF;
                END"
            );
        }

		/**
		 * @param PearDatabase $adb
		 * @param Module[] $modules
		 * @param ModuleRelationship[] $relationships
		 *
		 * @throws Exception
		 */
		private function createInstanceModules (PearDatabase $adb, $modules, $relationships, $systemAlerts) {
			$mm = ModuleManager::getInstance ($adb);
			foreach ($modules as $module) {
				$instanceModule = $module->duplicate (true, false, true);
				$mm->saveModule ($instanceModule);
			}

			if (empty ($relationships)) {
				return;
			}
			$mrm = ModuleRelationshipManager::getInstance ($adb);
			foreach ($relationships as $relationship) {
				$mrm->saveRelationship ($relationship);
			}
			 if (empty($systemAlerts)) {
				return;
			 }
			foreach ($systemAlerts as $systemAlert) {
				SystemAlertsManager::getInstance ($adb)->saveSystemAlert ($systemAlert);
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return integer
		 * @throws Exception
		 */
		private function fetchUsageTime (PearDatabase $adb) {
			$usageTime = 0;
			$result    = $adb->query ('SELECT sessionid, MAX(actiondate) - MIN(actiondate) AS usagetime FROM vtiger_audit_trial GROUP BY sessionid');
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$usageTime += intval ($row ['usagetime']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $usageTime;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $administratorUserName
		 *
		 * @return array|null
		 */
		private function fetchUsers (PearDatabase $adb, $administratorUserName) {
			$result = $adb->query ('SELECT * FROM vtiger_users ORDER BY id');
			if ($adb->num_rows ($result) > 0) {
				$administrator = null;
				$users         = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['user_name'] == $administratorUserName) {
						$administrator = User::getInstance ()
							->setAdministrator ($row ['is_admin'] == 'on')
							->setEmail ($row ['email1'])
							->setFirstName ($row ['first_name'])
							->setId (intval ($row ['id']))
							->setLastName ($row ['last_name'])
							->setStatus ($row ['status'])
							->setUserName ($administratorUserName);
					} else {
						$users [] = User::getInstance ()
							->setAdministrator ($row ['is_admin'] == 'on')
							->setEmail ($row ['email1'])
							->setFirstName ($row ['first_name'])
							->setId (intval ($row ['id']))
							->setLastName ($row ['last_name'])
							->setStatus ($row ['status'])
							->setUserName ($row ['user_name']);
					}
				}
				$data = array (
					'administrator' => $administrator,
					'regular'       => !empty ($users) ? $users : null,
				);
			} else {
				$data = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $data;
		}

		/**
		 * @param PlatformInstance $instance
		 * @param Application[] $platformApplications
		 */
		private function updateInstanceApplications ($instance, $platformApplications) {
			$instanceApplications = $instance->getApplications ();
			if ((empty ($platformApplications)) || (empty ($instanceApplications))) {
				return;
			}

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			$am  = ApplicationManager::getInstance ($adb);
			foreach ($instanceApplications as $index => $instanceApplication) {
				$selectedPlatformApplication = null;
				foreach ($platformApplications as $platformApplication) {
					if ($platformApplication->getCode () == $instanceApplication->getCode ()) {
						$selectedPlatformApplication = $platformApplication;
						break;
					}
				}
				if ((!empty ($selectedPlatformApplication)) && (!$instanceApplication->isEqualTo ($selectedPlatformApplication))) {
					$instanceApplication->copyValuesFrom ($selectedPlatformApplication);
					$am->saveApplication ($instanceApplication, false, true);
					$instanceApplications [ $index ] = $instanceApplication;
				}
			}
		}

		/**
		 * @param PlatformInstance $instance
		 * @param Module[] $platformModules
		 */
		private function updateInstanceModules ($instance, $platformModules) {
			$instanceModules = $instance->getModules ();
			if ((empty ($platformModules)) && (empty ($instanceModules))) {
				return;
			}

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			$mm  = ModuleManager::getInstance ($adb);
			if (empty ($platformModules)) {
				usort ($instanceModules, array ('PlatformInstanceManager', 'sortModulesByIdDesc'));
				foreach ($instanceModules as $instanceModule) {
					$mm->deleteModule ($instanceModule);
				}
				$instance->setModules (null);
			} else if (empty ($instanceModules)) {
				$instanceModules = array ();
				foreach ($platformModules as $platformModule) {
					$instanceModule = $platformModule->duplicate (true, false, true);
					$mm->saveModule ($instanceModule);
					$instanceModules [] = $instanceModule;
				}
				$instance->setModules ($instanceModules);
			} else {
				$instanceModules = $this->updateModules ($mm, $instanceModules, $platformModules);
				$instance->setModules ($instanceModules);
			}
			$mm->updateToolsPresence ();
		}

		/**
		 * @param PlatformInstance $instance
		 * @param ModuleRelationship[] $platformModuleRelationships
		 */
		private function updateInstanceModuleRelationships ($instance, $platformModuleRelationships) {
			$instanceModuleRelationships = $instance->getModuleRelationships ();
			if ((empty ($platformModuleRelationships)) && (empty ($instanceModuleRelationships))) {
				return;
			}

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			$mrm = ModuleRelationshipManager::getInstance ($adb);
			if (empty ($platformModuleRelationships)) {
				$mrm->deleteRelationships ();
				$instance->setModuleRelationships (null);
			} else if (empty ($instanceModuleRelationships)) {
				$instanceModuleRelationships = array ();
				foreach ($platformModuleRelationships as $platformModuleRelationship) {
					$instanceModuleRelationship = $platformModuleRelationship->duplicate ();
					$mrm->saveRelationship ($instanceModuleRelationship);
					$instanceModuleRelationships [] = $instanceModuleRelationship;
				}
				$instance->setModuleRelationships ($instanceModuleRelationships);
			} else {
				$instanceModuleRelationships = $this->updateModuleRelationships ($mrm, $instanceModuleRelationships, $platformModuleRelationships);
				$instance->setModuleRelationships ($instanceModuleRelationships);
			}
		}

		/**
		 * @param ModuleRelationshipManager $mrm
		 * @param ModuleRelationship[] $instanceModuleRelationships
		 * @param ModuleRelationship[] $platformModuleRelationships
		 *
		 * @return ModuleRelationship[]
		 */
		private function updateModuleRelationships ($mrm, $instanceModuleRelationships, $platformModuleRelationships) {
			foreach ($platformModuleRelationships as $platformModuleRelationship) {
				$found = false;
				foreach ($instanceModuleRelationships as $index => $instanceModuleRelationship) {
					if (!$this->areModuleRelationshipKeysEqual ($platformModuleRelationship, $instanceModuleRelationship)) {
						continue;
					} else if ((!$instanceModuleRelationship->isDeleted ()) && (!$instanceModuleRelationship->isEqualTo ($platformModuleRelationship))) {
						$instanceModuleRelationship->copyValuesFrom ($platformModuleRelationship);
						$mrm->saveRelationship ($instanceModuleRelationship);
						$instanceModuleRelationships [ $index ] = $instanceModuleRelationship;
					}
					$found = true;
					break;
				}
				if (!$found) {
					// Hay una relación en la plataforma que no está en la instancia. hay que crearla
					$instanceModuleRelationship = $platformModuleRelationship->duplicate ();
					$mrm->saveRelationship ($instanceModuleRelationship);
					$instanceModuleRelationships [] = $instanceModuleRelationship;
				}
			}

			foreach ($instanceModuleRelationships as $index => $instanceModuleRelationship) {
				$found = false;
				foreach ($platformModuleRelationships as $platformModuleRelationship) {
					if (
						($platformModuleRelationship->getFunction () == $instanceModuleRelationship->getFunction ()) &&
						($platformModuleRelationship->getModuleName () == $instanceModuleRelationship->getModuleName ()) &&
						($platformModuleRelationship->getRelatedModuleName () == $instanceModuleRelationship->getRelatedModuleName ())
					) {
						$found = true;
						break;
					}
				}
				if ((!$found) && (!$instanceModuleRelationship->isLocked ())) {
					// Hay una relación en la instancia que no está en la plataforma. Eliminarla
					$mrm->deleteRelationship ($instanceModuleRelationship);
					unset ($instanceModuleRelationships [ $index ]);
				}
			}

			return $instanceModuleRelationships;
		}

		/**
		 * @param PlatformInstance $instance
		 */
		private function updateInstanceTables ($instance) {
			$instanceCode = $instance->getCode ();
			$tableNames   = array (
				'application'    => PlatformTableNamesProvider::getApplicationTableNames (),
				'base'           => PlatformTableNamesProvider::getBaseTableNames (),
				'catalog'        => PlatformTableNamesProvider::getCatalogTableNames (),
				'core'           => PlatformTableNamesProvider::getCoreTableNames (),
				'profile'        => PlatformTableNamesProvider::getProfileTableNames (),
				'security'       => PlatformTableNamesProvider::getSecurityTableNames (),
				'sequence'       => PlatformTableNamesProvider::getSequenceTableNames (),
				'settings'       => PlatformTableNamesProvider::getSettingsTableNames (),
				'specialmodules' => PlatformTableNamesProvider::getSpecialModuleTableNames (),
			);
			$adb          = AdbManager::getInstance ()->getMasterAdb ();
			InstanceDatabaseUtils::updateInstanceDatabaseTables ($adb, $instanceCode, $tableNames);
		}

		/**
		 * @param ModuleManager $mm
		 * @param Module[] $instanceModules
		 * @param Module[] $platformModules
		 *
		 * @return array
		 */
		private function updateModules ($mm, $instanceModules, $platformModules) {
			$processedModuleNames = array ();
			foreach ($platformModules as $platformModule) {
				if (($platformModule->getType () == Module::TYPE_ADMIN) && (!in_array ($platformModule->getName (), array ('ModTracker', 'Tooltip', 'RecycleBin', 'Documents')))) {
					continue;
				}
				$found = false;
				foreach ($instanceModules as $index => $instanceModule) {
					if (($platformModule->getName () != $instanceModule->getName ())) {
						continue;
					} else if (!$instanceModule->isEqualTo ($platformModule)) {
						$instanceModule->copyValuesFrom ($platformModule);
						$mm->saveModule ($instanceModule, false, false);
						$instanceModules [ $index ] = $instanceModule;
					}
					$found = true;
					break;
				}
				if (!$found) {
					// Hay un módulo en la plataforma que no está en la instancia. hay que crearlo
					$instanceModule = $platformModule->duplicate (true, true, true);
					$mm->saveModule ($instanceModule);
					$instanceModules [] = $instanceModule;
				}
				$processedModuleNames [] = $platformModule->getName ();
			}
			foreach ($instanceModules as $index => $instanceModule) {
				if (!in_array ($instanceModule->getName (), $processedModuleNames)) {
					// Hay un módulo en la instancia que no está en la plataforma. Borrarlo.
					$mm->deleteModule ($instanceModule);
					unset ($instanceModules [ $index ]);
				}
			}
			return $instanceModules;
		}

		/**
		 * @param PlatformInstance $instance
		 *
		 * @throws PlatformInstanceException
		 */
		private function validate ($instance) {
			if (empty ($instance)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_EMPTY);
			} else if (!($instance instanceof PlatformInstance)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID);
			}
			$instance->validate ();
		}

		/**
		 * @param Module $moduleA
		 * @param Module $moduleB
		 *
		 * @return integer
		 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
		 * NOTA: PHP Mess Detector reporta este método como no usado. Falso. Se usa para ordenar los módulos en el método updateInstanceModules. Se deshabilita el warning
		 */
		private static function sortModulesByIdDesc ($moduleA, $moduleB) {
			if ($moduleA->getId () < $moduleB->getId ()) {
				return 1;
			} else if ($moduleA->getId () > $moduleB->getId ()) {
				return -1;
			} else {
				return 0;
			}
		}

		/**
		 * @param PlatformInstance $instance
		 *
		 * @throws Exception
		 */
		public function assignInstance ($instance) {
			$instanceCode = $instance->getCode ();
			$adb          = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$adb->startTransaction ();
			// Crear usuario administrador
			$administrator = $instance->getAdministrator ()->setId (1)->setRoles (array (RoleManager::getInstance ($adb)->fetchRole ('H2')));
			UserManager::getInstance ($adb, $instanceCode)->saveUser ($administrator);

			$company = !empty ($instance->getName ()) ? $instance->getName () : trim ("{$administrator->getFirstName ()} {$administrator->getLastName ()}");
			$adb->pquery ('INSERT INTO vtiger_organizationdetails (organization_id, organizationname, logoname) VALUES (1, ?, NULL)', array ($company));
			$adb->pquery ('INSERT INTO vtiger_organizationdetails_seq (id) VALUES (?)', array (1));
			$adb->query ('INSERT INTO vtiger_emailmanager_templates (templatename, language, subject, body, adddefaultheader, adddefaultfooter, attachments, scope) VALUES (\'Invitación a compartir contenido\', \'es\', \'Has recibido una invitación de <var>ENVIADA_POR</var> para compartir contenido de <var>TIPO_CONTENIDO</var>\', \'<p>Hola <var>ENVIAR_A</var></p><p>Has recibido una invitación de <var>ENVIADA_POR</var> a compartir contenido de <var>TIPO_CONTENIDO</var>.</p><p>Para empezar a aprovechar esta información, haz click en el <a href="<var>URL</var>">enlace</a> y sigue los pasos indicados.</p><p>Muchas gracias.</p><p><var>ENVIADA_POR</var> y el equipo de Platzilla</p>\', 1, 1, NULL, \'SYSTEM\')');
			$adb->completeTransaction ();
		}

        /**
         * @param PearDatabase $adb
         * @param string $instance
         * @param string $databasesHostName
         * @param string $databasesUserName
         * @param string $databasesPassword
         * @param string $httpHostName
         * @param null $initialData
         *
         * @throws DatabaseException
         */
        public function createFastEmptyInstance ($adb, $instance, $databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $initialData = null) {
            try {
                InstanceDatabaseUtils::createInstanceDatabase ($databasesHostName, $databasesUserName, $databasesPassword, $instance);
                InstanceDatabaseUtils::createInstanceDatabaseUser ($databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $instance);
                $instanceDatabaseName = "pg_crm_{$instance}";
                $adb->query ('DO SLEEP(20)');
                $adb->query ("CALL _create_structure('{$instanceDatabaseName}')");
                $adb->query ('DO SLEEP(10)');
                $adb->query ("CALL _foreign_key_constraints('{$instanceDatabaseName}')");
                $adb->query ('DO SLEEP(40)');
                $adb->query ("CALL _create_data('{$instanceDatabaseName}')");
                $adb->query ('DO SLEEP(10)');
                $targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance);
                $this->createFunctionsAndTriggers ($targetAdb);
                $targetAdb->query ('UPDATE `vtiger_crmentity` SET `demo`=1 WHERE 1');
            } catch (Exception $e) {
                InstanceDatabaseUtils::deleteInstanceDatabase ($databasesHostName, $databasesUserName, $databasesPassword, $instance);
                InstanceDatabaseUtils::deleteInstanceUser ($databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $instance);
                throw $e;
            }
        }

		/**
		 * @param PlatformInstance $instance
		 * @param string $databasesHostName
		 * @param string $databasesUserName
		 * @param string $databasesPassword
		 * @param string $httpHostName
		 * @param array $initialData
		 *
		 * @throws Exception
		 */
		public function createEmptyInstance ($instance, $databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $initialData = null) {
			$instanceCode = $instance->getCode ();
			try {
				InstanceDatabaseUtils::createInstanceDatabase ($databasesHostName, $databasesUserName, $databasesPassword, $instanceCode);
				InstanceDatabaseUtils::createInstanceDatabaseUser ($databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $instanceCode);

				$tableNames = array (
					'application'    => PlatformTableNamesProvider::getApplicationTableNames (),
					'base'           => PlatformTableNamesProvider::getBaseTableNames (),
					'catalog'        => PlatformTableNamesProvider::getCatalogTableNames (),
					'core'           => PlatformTableNamesProvider::getCoreTableNames (),
					'profile'        => PlatformTableNamesProvider::getProfileTableNames (),
					'security'       => PlatformTableNamesProvider::getSecurityTableNames (),
					'sequence'       => PlatformTableNamesProvider::getSequenceTableNames (),
					'settings'       => PlatformTableNamesProvider::getSettingsTableNames (),
					'specialmodules' => PlatformTableNamesProvider::getSpecialModuleTableNames (),
				);
				$adb        = AdbManager::getInstance ()->getMasterAdb ();
				InstanceDatabaseUtils::createInstanceDatabaseTables ($adb, $instanceCode, $tableNames);

				$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);

				// Crear perfil de administrador
				$profile = Profile::getInstance ()
					->setDescription ('Admin profile')
					->setEditPermission (ProfileInterface::PERMISSION_ALLOW)
					->setName ('Administrator')
					->setViewPermission (ProfileInterface::PERMISSION_ALLOW);
				ProfileManager::getInstance ($adb)->saveProfile ($profile);

				// Crear roles
				$rm           = RoleManager::getInstance ($adb);
				$organization = Role::getInstance ()
					->setId ('H1')
					->setName ('Organización')
					->setProfiles (array ($profile));
				$rm->saveRole ($organization);
				$director = Role::getInstance ()
					->setId ('H2')
					->setName ('Director')
					->setParent ($organization)
					->setProfiles (array ($profile));
				$rm->saveRole ($director);
				$manager = Role::getInstance ()
					->setId ('H3')
					->setName ('Responsable')
					->setParent ($director)
					->setProfiles (null);
				$rm->saveRole ($manager);
				$technician = Role::getInstance ()
					->setId ('H4')
					->setName ('Técnico')
					->setParent ($manager)
					->setProfiles (null);
				$rm->saveRole ($technician);

				// Crear módulos
				$this->createInstanceModules ($adb, $instance->getModules (), $instance->getModuleRelationships (), $instance->getSystemAlerts ());

				// Registrar aplicaciones
				$applications = $instance->getApplications ();
				if (!empty ($applications)) {
					$am       = ApplicationManager::getInstance ($adb);
					$profiles = array ();
					foreach ($applications as $application) {
						$am->saveApplication ($application, false, true);
						$profiles [] = $application->getProfile ();
					}
				} else {
					$profiles = null;
				}

				$manager = Role::getInstance ()
					->setId ('H3')
					->setName ('Responsable')
					->setParent ($director)
					->setProfiles ($profiles);
				$rm->saveRole ($manager);
				$technician = Role::getInstance ()
					->setId ('H4')
					->setName ('Técnico')
					->setParent ($manager)
					->setProfiles ($profiles);
				$rm->saveRole ($technician);

				$this->createEntities ($instance, $initialData);
			} catch (Exception $e) {
				InstanceDatabaseUtils::deleteInstanceDatabase ($databasesHostName, $databasesUserName, $databasesPassword, $instanceCode);
				InstanceDatabaseUtils::deleteInstanceUser ($databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $instanceCode);
				throw $e;
			}
		}

		/**
		 * @param string $code
		 * @param string $databasesHostName
		 * @param string $databasesUserName
		 * @param string $databasesPassword
		 * @param string $httpHostName
		 *
		 * @throws Exception
		 */
		public function deleteInstance ($code, $databasesHostName, $databasesUserName, $databasesPassword, $httpHostName) {
			InstanceDatabaseUtils::deleteInstanceDatabase ($databasesHostName, $databasesUserName, $databasesPassword, $code);
			InstanceDatabaseUtils::deleteInstanceUser ($databasesHostName, $databasesUserName, $databasesPassword, $httpHostName, $code);
			$rootFolderPath     = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$instanceFolderPath = "{$rootFolderPath}/{$code}";
			PlatzillaUtils::deleteFolder ($instanceFolderPath);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return integer
		 * @throws Exception
		 */
		private function fetchTotalRecords (PearDatabase $adb) {
			$result = $adb->pquery (
				'SELECT
					COUNT(*) AS totalrecords
				FROM
					vtiger_crmentity
				WHERE
					setype NOT LIKE ? AND
					setype NOT IN (SELECT name FROM vtiger_settings_field WHERE tab IS NULL) AND
					deleted=? AND
					demo=?',
				array ('%Attachment', 0, 0)
			);
			if ($adb->num_rows ($result) > 0) {
				$row          = $adb->fetchByAssoc ($result, -1, false);
				$totalRecords = intval ($row ['totalrecords']);
			} else {
				$totalRecords = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $totalRecords;
		}

		/**
		 * @param string $code
		 * @param string $administratorUserName
		 * @param boolean $headersOnly
		 * @param boolean $includeStatistics
		 *
		 * @return PlatformInstance
		 * @throws PlatformException
		 */
		public function fetchInstance ($code, $administratorUserName, $headersOnly = false, $includeStatistics = false) {
			if (empty ($code)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_INSTANCE_CODE);
			}

			$adb   = AdbManager::getInstance ()->getTargetInstanceAdb ($code);
			$users = $this->fetchUsers ($adb, $administratorUserName);
			$am    = ApplicationManager::getInstance ($adb);
			return PlatformInstance::getInstance ()
				->setAdministrator ($users ['administrator'])
				->setApplications (!$headersOnly ? $am->fetchApplications (true) : $am->fetchApplicationHeaders ())
				->setCode ($code)
				->setModuleRelationships (!$headersOnly ? ModuleRelationshipManager::getInstance ($adb)->fetchRelationships () : null)
				->setModules (!$headersOnly ? ModuleManager::getInstance ($adb)->fetchModules (false, null, true) : null)
				->setTotalRecords ($includeStatistics ? $this->fetchTotalRecords ($adb) : null)
				->setUsageTime ($includeStatistics ? $this->fetchUsageTime ($adb) : null)
				->setUsers ($users ['regular']);
		}

		/**
		 * @param PlatformInstance $instance
		 * @param Application $application
		 */
		public function installInstanceApplication ($instance, $application) {
			$this->validate ($instance);
			$adb   = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			$am    = ApplicationManager::getInstance ($adb);
			$dummy = ApplicationManager::getInstance ($adb)->fetchApplication ($application->getCode (), true);
			if (!empty ($dummy)) {
				$am->deleteApplication ($dummy, true);
			}
			$am->saveApplication ($application->duplicate (null, $application->getName (), $application->getDescription ()), false, true);
		}

		/**
		 * @param PlatformInstance $instance
		 * @param Application $application
		 */
		public function uninstallInstanceApplication ($instance, $application) {
			$this->validate ($instance);
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			ApplicationManager::getInstance ($adb)->deleteApplication ($application, true);
		}

		/**
		 * @param PlatformInstance $instance
		 *
		 * @param Application[] $platformApplications
		 * @param Module[] $platformModules
		 * @param ModuleRelationship[] $platformModuleRelationships
		 */
		public function updateInstance ($instance, $platformApplications, $platformModules, $platformModuleRelationships) {
			$this->updateInstanceTables ($instance);
			$this->updateInstanceModules ($instance, $platformModules);
			$this->updateInstanceModuleRelationships ($instance, $platformModuleRelationships);
			$this->updateInstanceApplications ($instance, $platformApplications);
		}

		/**
		 * @param PlatformInstance $instance
		 * @param Application $application
		 *
		 * @throws PlatformInstanceException
		 */
		public function updateInstanceApplication ($instance, $application) {
			$this->validate ($instance);
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			ApplicationManager::getInstance ($adb)->saveApplication ($application, false, true);
		}

		/**
		 * @param PlatformInstance $instance
		 * @param BackgroundTaskConfiguration $platformConfiguration
		 */
		public function updateInstanceConfiguration ($instance, $platformConfiguration) {
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			BackgroundTaskConfigurationManager::getInstance ($adb)->saveConfiguration ($platformConfiguration);
		}

		/**
		 * @return PlatformInstanceManager
		 */
		public static function getInstance () {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}
