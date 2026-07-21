<?php
    require_once ('include/platzilla/Utils/DatabaseUtils.php');
    require_once ('include/utils/AdbManager.class.php');
    require_once ('include/utils/ImageUtils.class.php');
    require_once ('modules/Settings/Objects/HowToMaster.php');
    require_once ('Smarty_setup.php');
    abstract class HowToHelper {
    
        const IMAGEN_TYPE   = array ('png', 'jpg', 'jpeg', 'gif');
        const IMAGEN_WIDTH  = 800;
        const IMAGEN_HEIGHT = 600;
        
        /**
         * @param PearDatabase $adb
         * @param integer $howToId
         *
         * @return HowToEntity[]|null
         * @throws Exception
         */
        private static function fetchHowToEntity ($adb, $howToId) {
            $result = $adb->pquery(
                'SELECT he.*,
                    en.tablename,
                    en.entityidcolumn
                FROM vtiger_howto_entity he
                INNER JOIN vtiger_crmentity crm ON crm.crmid = he.crmid
                INNER JOIN vtiger_entityname en ON en.modulename = crm.setype
                WHERE
                    crm.deleted=? AND
                    he.howtoid=?',
                array (0, $howToId)
            );
            if ($adb->num_rows ($result) > 0) {
                $howToEntity = array ();
                while ($row = $adb->fetchByAssoc ($result, -1, false)) {
                    $howToEntity[] = HowToEntity::getInstance()
                        ->setCrmId ($row['crmid'])
                        ->setEntityTitle (self::getEntityIdentifier ($adb, $row))
                        ->setFile ($row['file'])
                        ->setHowToId ($row['howtoid'])
                        ->setId ($row['howtoentityd'])
                        ->setTabName ($row['tabname']);
                }
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return (isset($howToEntity)) ? $howToEntity : null;
        }
    
        /**
         * @param PearDatabase $adb
         * @param array $row
         *
         * @return string|null
         * @throws Exception
         */
        private static function getEntityIdentifier ($adb, $row) {
            if (
                empty ($row['crmid']) ||
                empty ($row['tablename']) ||
                empty ($row['entityidcolumn'])
            ) {
                return null;
            }
			if ($row['tabname'] == 'Calendar') {
				$mainClass       = "modules/Calendar/Activity.php";
				$row['tabname']  = 'Activity';
			} else {
				$mainClass = "modules/{$row['tabname']}/{$row['tabname']}.php";
			}
            
            $fieldIdentifier = null;
            if (file_exists ($mainClass)) {
                require_once ($mainClass);
                $entity          = new $row['tabname'];
                $fieldIdentifier = $entity->list_link_field;
            }
            $fieldIdentifier = (empty ($fieldIdentifier)) ? $row ['entityidcolumn'] : $fieldIdentifier;
            $result          = $adb->query ("SELECT {$fieldIdentifier} FROM {$row['tablename']} WHERE {$row['entityidcolumn']}={$row['crmid']}");
            if ($adb->num_rows ($result) > 0) {
                $data = $adb->fetchByAssoc ($result, -1, false);
                  $identifier = $data[ $fieldIdentifier ];
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return (isset ($identifier)) ? $identifier : null;
        }
    
        /**
         * @param PearDatabase $adb
         * @param HowToMaster $howTo
         *
         * @return void
         * @throws SmartyException
         */
        private static function getHowToLink ($adb, $howTo) {
            if (!$howTo instanceof HowToMaster) {
                return;
            }
            
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign ('HOW_TO_ID', $howTo->getId ());
            $smarty->assign ('HOW_TO_TITLE', $howTo->getTitle ());
            $htmlOutput = $smarty->fetch ('Settings/include/HowToGeneralLink.tpl');
            
            $adb->pquery (
                'UPDATE vtiger_howto_master SET url_howto=? WHERE howtoid=?',
                array ($htmlOutput, $howTo->getId())
            );
        }
        
        /**
         * @param PearDatabase $adb
         * @param HowToMaster $howTo
         * @return void
         */
        private static function saveHowToEntity($adb, $howTo) {
            if (!is_array ($howTo->getEntity())) {
                return;
            }
            foreach ($howTo->getEntity() as $howToEntity) {
                if (!$howToEntity instanceof HowToEntity) {
                    continue;
                }
                $adb->pquery (
                    'INSERT INTO vtiger_howto_entity (howtoid, crmid, tabname, file) VALUES (?, ?, ?, ?)',
                    array ($howTo->getId (), $howToEntity->getCrmId (), $howToEntity->getTabName(), $howToEntity->getFile ())
                );
            }
            
        }
        
        /**
         * @param $assignEntities
         *
         * @return HowToEntity[]|null
         */
        public static function buildHowToEntity ($assignEntities) {
            if (!is_array ($assignEntities) || !key_exists ('module', $assignEntities)) {
                return null;
            }
            $totalAssign = count ($assignEntities['module']);
            for ($k = 0; $k < $totalAssign; $k++) {
                $howToEntities [] = HowToEntity::getInstance ()
                    ->setCrmId ($assignEntities['record'][$k])
                    ->setFile ($assignEntities['file'][$k])
                    ->setTabName ($assignEntities['module'][$k]);
            }
            return isset ($howToEntities) ? $howToEntities : null;
        }
    
        /**
         * @param PearDatabase $adb
         * @param integer $codId
         * @param string $newStatus
         *
         * @return void
         * @throws Exception
         */
        public static function changeHowToStatus ($adb, $codId, $newStatus) {
            if (!is_numeric ($codId) || empty($codId)) {
                throw new Exception ('Imposible actualizar, Error en datos!');
            }
            $adb->pquery ('UPDATE vtiger_howto_master SET howto_status=? WHERE howtoid=?',array ($newStatus, $codId));
        }
    
        /**
         * @param PearDatabase $adb
         * @param integer $howToId
         *
         * @return void
         * @throws Exception
         */
        public static function deleteHowTo ($adb, $howToId) {
            if (!is_numeric ($howToId) || empty ($howToId)) {
                throw new Exception ('Imposible eliminar howTo!');
            }
            $adb->pquery ('DELETE FROM vtiger_howto_entity WHERE howtoid=?', array ($howToId));
            $adb->pquery ('DELETE FROM vtiger_howto_master WHERE howtoid=?', array ($howToId));
        }
        
        /**
         * @param PearDatabase $adb
         * @param string $status
         *
         * @return HowToMaster[]|null
         * @throws Exception
         */
        public static function fetchHowTo ($adb, $status = null) {
            $where = 1;
            if (!empty ($status)) {
                $where = "howto_status='{$status}'";
            }
            $result = $adb->query ("SELECT * FROM vtiger_howto_master WHERE {$where}");
            if ($adb->num_rows ($result) > 0) {
                $howTo = array ();
                while ($row = $adb->fetchByAssoc ($result, -1, false)) {
                    $howTo[] = HowToMaster::getInstance()
                        ->setEntity (self::fetchHowToEntity ($adb, $row['howtoid']))
                        ->setHtml ($row['html'])
                        ->setId ($row['howtoid'])
                        ->setImage ($row['image'])
                        ->setStatus ($row['howto_status'])
                        ->setTitle ($row['title'])
                        ->setUrl ($row['url_howto'])
                        ->setVideo ($row['url_video']);
                }
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return (isset ($howTo)) ? $howTo : null;
        }
    
        /**
         * @param PearDatabase $adb
         * @param integer $howToId
         *
         * @return HowToMaster|null
         * @throws Exception
         */
        public static function fetchHowToById ($adb, $howToId) {
            if (empty ($howToId)) {
                throw new Exception('Imposible encontar el HowTo');
            }
            $result = $adb->pquery ('SELECT * FROM vtiger_howto_master WHERE howtoid=?', array($howToId));
            if ($adb->num_rows ($result) > 0) {
                $row   = $adb->fetchByAssoc ($result, -1, false);
                $howTo = HowToMaster::getInstance()
                    ->setEntity (self::fetchHowToEntity ($adb, $row['howtoid']))
                    ->setHtml ($row['html'])
                    ->setId ($row['howtoid'])
                    ->setImage ($row['image'])
                    ->setStatus ($row['howto_status'])
                    ->setTitle ($row['title'])
                    ->setUrl ($row['url_howto'])
                    ->setVideo ($row['url_video'])
                    ->setVideoType ($row['video_type']);
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return (isset ($howTo)) ? $howTo : null;
        }
    
        /**
         * @return string|null
         * @throws Exception
         */
        public static function getImageResponse () {
            if(!isset ($_FILES['howto_image_upload'])) {
                return null;
            }
        			
            if ($_FILES['howto_image_upload']['error'] > 0) {
                return null;
            }
            $uploadMax = (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024);
            $fileSize  = $_FILES ['howto_image_upload']['size'];
            $fileTmp   = $_FILES ['howto_image_upload']['tmp_name'];
            $dummyName = explode ('.', $_FILES['howto_image_upload']['name']);
            $fileExt   = strtolower (end ($dummyName));
            if (!in_array ($fileExt, self::IMAGEN_TYPE)) {
                throw new Exception (HowToException::ERROR_EXTENSION_NO_ALLOWED);
            }
            if ($fileSize > $uploadMax) {
                throw new Exception(HowToException::ERROR_FILE_TOO_BIG);
            }
        			
            $idPhoto = rand ();
            $fileExt = '.' . $fileExt;
        			
            move_uploaded_file ($fileTmp, 'Image/Source_' . $idPhoto . $fileExt);
        			
            $config = array();
            $config ['imageLibrary']  = 'gd2';
            $config ['sourceImage']   = 'Image/Source_' . $idPhoto . $fileExt;
            $config ['createThumb']   = false;
            $config ['maintainRatio'] = true;
            $config ['width']         = self::IMAGEN_WIDTH;
            $config ['height']        = self::IMAGEN_HEIGHT;
        			
            $imagLibrary = new ImageUtils ($config);
        			
            $resizeStatus = $imagLibrary->resize ();
            if ($resizeStatus) {
                $data = file_get_contents ('Image/Source_' . $idPhoto . $fileExt);
                $data = base64_encode ($data);
                unlink ('Image/Source_' . $idPhoto . $fileExt);
            }
            return (isset ($data)) ? $data : null;
        }
    
        /**
         * @param PearDatabase $adb
         * @param string $tabName
         * @param integer|null $crmId
         * @param string $view
         *
         * @return integer|null
         * @throws Exception
         */
        public static function hasHowTo ($adb, $tabName, $crmId, $view) {
            if (empty ($tabName) || empty ($view)) {
                return null;
            }
            $result = $adb->pquery(
                'SELECT
                        htm.howtoid,
                        hte.crmid
                    FROM
                        vtiger_howto_entity hte
                    INNER JOIN vtiger_howto_master htm ON htm.howtoid = hte.howtoid
                    WHERE
                        htm.howto_status=?  AND
                        hte.tabname=? AND
                        hte.file=?
                    ORDER BY
                        hte.howtoentityd DESC',
                array ('ENABLED',$tabName, $view)
            );
            $howToFound = 0;
            if ($adb->num_rows ($result) > 0) {
                while ($row = $adb->fetchByAssoc ($result, -1, false)) {
                    if ($row['crmid'] == $crmId) {
                        $howToFound = $row ['howtoid'];
                        break;
                    } elseif (empty ($row['crmid'])) {
                        $howToFound = $row ['howtoid'];
                    }
                }
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return $howToFound;
        }
        
        /**
         * @param PearDatabase $adb
         * @param HowToMaster $howTo
         *
         * @throws Exception
         */
        public static function saveHowTo ($adb, $howTo) {
            if (!$howTo instanceof HowToMaster) {
                return;
            }
            $howTo->validate ();
            $adb->startTransaction ();
            if (empty ($howTo->getId ())) {
                $adb->pquery (
                    'INSERT INTO vtiger_howto_master (title, html, url_video, video_type, image, url_howto, howto_status) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    array ($howTo->getTitle (), $howTo->getHtml (), $howTo->getVideo (), $howTo->getVideoType (), $howTo->getImage (), $howTo->getUrl(), $howTo->getStatus ())
                );
                $diagnosticBuilderId = intval ($adb->getLastInsertID ());
                $howTo->setId ($diagnosticBuilderId);
            } else {
                $adb->pquery (
                    'UPDATE vtiger_howto_master SET title=?, html=?, url_video=?, video_type=?, image=?, howto_status=? WHERE howtoid=?',
                    array ($howTo->getTitle (), $howTo->getHtml (), $howTo->getVideo (), $howTo->getVideoType (), $howTo->getImage (), $howTo->getStatus (), $howTo->getId())
                );
                $adb->pquery (
                    'DELETE FROM vtiger_howto_entity WHERE howtoid=?',
                    array ($howTo->getId ())
                );
            }
            self::saveHowToEntity ($adb, $howTo);
            $adb->completeTransaction ();
            self::getHowToLink ($adb, $howTo);
        }
    }
