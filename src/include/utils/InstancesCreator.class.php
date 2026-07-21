<?php

	class InstancesCreator {
		const MIN_UNASSIGNED_STOCK_INSTANCES = 2;

		public static function run () {
			global $adb;

			$sql    = "SELECT COUNT(*) AS total FROM vtiger_instancias i WHERE i.status='unassigned'";
			$result = $adb->query ($sql, true);
			$row    = $adb->fetch_array ($result);
			$total  = intval ($row ['total']);
			if ($total >= self::MIN_UNASSIGNED_STOCK_INSTANCES) {
				return;
			}
			require_once ('include/utils/InstanceCreator.class.php');
			$instanceCreator = InstanceCreator::getCreator ();
			try {
				for ($i = $total; $i < self::MIN_UNASSIGNED_STOCK_INSTANCES; $i++) {
					$now        = date_create ();
					$instanceID = $instanceCreator->createStockInstance ();
					echo "Instancia $instanceID ha sido creada en {$now->diff (date_create ())->format ('%I min %S seg')}" . PHP_EOL;
				}
			} catch (Exception $e) {
				echo $e->getMessage () . PHP_EOL;
				echo $e->getTraceAsString ();
			}
		}
	}