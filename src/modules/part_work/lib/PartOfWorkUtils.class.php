<?php
	require_once ('modules/operating_modes/lib/ManagementModeHelper.class.php');
	require_once ('modules/part_work/Objects/PartOfWorkInterface.php');
	
	class PartOfWorkUtils extends ManagementModeHelper implements PartOfWorkInterface {
	
		const WORK_CLIENTE = 'Cliente';
		/** @var PearDatabase */
		private $adb;
		
		/**
		 * @param PearDatabase $adb
		 */
		public function __construct ($adb) {
			self::$recordPerPage = 1500;
			$this->adb = $adb;
		}
		
		/**
		 * @param string|array $userId
		 * @param array $range
		 * @param array $wokIds
		 *
		 * @return array|null
		 */
		private function fetchSimpleWorks ($userId, $range, $wokIds) {
			if (empty ($wokIds)) {
				return null;
			}
			if (is_array($userId)) {
				$whereUsers = "(crm.smownerid IN{$this->adb->sql_expr_datalist ($userId)} OR crm.smcreatorid IN{$this->adb->sql_expr_datalist ($userId)}) AND";
			} else {
				$whereUsers = "(crm.smownerid IN({$userId}) OR crm.smcreatorid IN({$userId})) AND";
			}
			$whereWork = "(ot.orden_de_trabajoid NOT IN{$this->adb->sql_expr_datalist ($wokIds)}) AND";
			
			$result = $this->adb->pquery (
				"SELECT
					crm.crmid,
					crm.smcreatorid,
					crm.smownerid,
       				crm.modifiedtime,
			       	crm.setype AS tab_name,
       				CONCAT(user.first_name, '',	user.last_name) AS username,
       				user.imagename,
       				ot.orden_de_trabajoid,
       				ot.orden_de_trabajoid AS entity_id,
       				ot.titulo,
       				ot.descripcion,
       				ot.fecha_real_de_ci,
       				ot.estado_de_la_orden,
       				ot.importance_work,
       				ot.work_priority,
       				ot.priority_index,
       				ot.fecha_de_inicio,
       				ot.fecha_real_de_ci,
       				ot.asociar_a,
       				ot.cliente
				FROM
					vtiger_orden_de_trabajo ot
				INNER JOIN vtiger_crmentity crm ON crm.crmid = ot.orden_de_trabajoid
				INNER JOIN vtiger_users user ON user.id = crm.smownerid
				WHERE
					crm.deleted = ? AND
					{$whereUsers}
					{$whereWork}
					validityRecordByDate (ot.fecha_de_inicio, ot.fecha_real_de_ci, crm.modifiedtime, ?, ?) = ?",
				array (0, $range ['startdate'], $range ['enddate'], 1)
			);
			
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$simpleWorks[]    = $row;
				}
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($simpleWorks)) ? $simpleWorks : null;
		}
		
		/**
		 * @param $workId
		 *
		 * @return string|null
		 * @throws Exception
		 */
		private function getClientByWork (&$work) {
			$workId = $work ['entity_id'];
			if (empty($workId)) {
				return null;
			}
			$result = $this->adb->pquery (
				"SELECT
       					clt.alias,
       					clt.direccion,
       					clt.telefono
					FROM  vtiger_clientes clt
					INNER JOIN vtiger_crmentity crm ON crm.crmid = clt.clientesid
					INNER JOIN vtiger_orden_de_trabajo ot ON ot.cliente = clt.clientesid
					WHERE
					    crm.deleted = ? AND
					    ot.orden_de_trabajoid = ?",
				array (0, $workId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$work['client']  = $row ['alias'];
				$work['address'] = $row ['direccion'];
				$work['phone']   = $row ['telefono'];
			} else {
				$work['client']  = null;
				$work['address'] = null;
				$work['phone']   = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}
		
		/**
		 * @param array $data
		 * @param integer $id
		 * @param array $resultArray
		 * @return void
		 */
		private function mergeTaskArrays ($data, $id, &$resultArray) {
			foreach ($data as $row) {
				if (isset ($row ['entity_id']) && $row ['entity_id'] == $id) {
					if (!isset ($row ['activityid'])) {
						$row ['activityid'] = $row['entity_id'];
					}
					if (!isset ($row ['subject'])) {
						$row ['subject'] = $row['title'];
					}
					$resultArray ['task'][$id][] = $row;
				}
				
			}
		}
		
		/**
		 * @param array $workAndTask
		 * @param array $simpleWorks
		 * @param array $otherTask
		 *
		 * @return array
		 */
		private function mergeWorkArrays ($workAndTask, $simpleWorks, $otherTask) {
			$resultArray = array ();
			if (!empty ($workAndTask)) {
				$idFound = array();
				foreach ($workAndTask as $work) {
					if (!in_array ($work['orden_de_trabajoid'], $idFound)) {
						if (!empty ($work['cliente'])) {
							$this->getClientByWork ($work);
						} else {
							$work['client']  = null;
							$work['address'] = null;
							$work['phone']   = null;
						}
						
						$resultArray []  = $work;
						if (key_exists ('activityid', $work)) {
							$this->mergeTaskArrays ($workAndTask, $work ['orden_de_trabajoid'], $resultArray);
						}
						$idFound [] = $work['orden_de_trabajoid'];
					}
				}
			}
			unset ($idFound);
			if (!empty ($simpleWorks)) {
				foreach ($simpleWorks as $work) {
					if (!empty ($work['cliente'])) {
						$this->getClientByWork ($work);
					} else {
						$work['client']  = null;
						$work['address'] = null;
						$work['phone']   = null;
					}
					$resultArray []  = $work;
					
				}
			}
			if (!empty ($otherTask)) {
				$idFound = array();
				foreach (self::QUADRANTS as $quadrant) {
					foreach ($otherTask[ $quadrant ] as $activityTask) {
						if (empty ($activityTask)) {
							continue;
						} elseif (!in_array ($activityTask['entity_id'], $idFound)) {
							$resultArray [] = $activityTask;
							$idFound []     = $activityTask['entity_id'];
							$this->mergeTaskArrays ($otherTask[ $quadrant ] , $activityTask['entity_id'], $resultArray);
						}
					}
				}
			}
			return $resultArray;
		}
		
		/**
		 * @param string|array $userId
		 * @param array $range
		 * @param integer $starRecord
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public  function fetchPartOfWork ($userId, $range, $starRecord) {
			$workAndTask = self::fetchWorkInProgress ($this->adb, $userId, $range, $starRecord)[0];
			$workIds     = array_column ($workAndTask, 'orden_de_trabajoid');
			$simpleWorks = $this->fetchSimpleWorks ($userId, $range, $workIds);
			$otherTask   =  array (); //self::fetchActivityTask ($this->adb, $userId, $range, $starRecord);
			return $this->mergeWorkArrays ($workAndTask, $simpleWorks, $otherTask);
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return PartOfWorkUtils
		 */
		public static function getInstance ($adb) {
			return new self ($adb);
		}
	}
