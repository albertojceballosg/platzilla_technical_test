<?php
	require_once ('data/CRMEntity.php');
	/**
	 * Clase vacía, útil para simular que las notificaciones son entidades del CRM
	 */
	class notifications extends CRMEntity {
		/** @var integer */
		public $id;
		public function save () {
			// Las notificaciones no se guardarán por esta vía
		}

		public static function getInstance () {
			return new self ();
		}

	}
