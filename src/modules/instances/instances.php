<?php
	require_once ('data/CRMEntity.php');

	/**
	 * Clase vacía, útil para simular que las instancias son entidades del CRM
	 */
	class instances extends CRMEntity {
		/** @var integer */
		public $id;

		public function save ($moduleName, $fileid = '') {
			// Las instancias no se guardarán por esta vía
		}

		public static function getInstance () {
			return new self ();
		}
	}