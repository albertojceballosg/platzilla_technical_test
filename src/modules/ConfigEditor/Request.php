<?php
	class ConfigEditor_Request {
		protected $valuemap;

		public function __construct ($values) {
			$this->valuemap = $values;
		}

		public function get ($key, $defvalue = '') {
			$value = $defvalue;
			if (isset($this->valuemap[ $key ])) {
				$value = $this->valuemap[ $key ];
			}
			if (!empty($value)) {
				$value = vtlib_purify ($value);
			}
			return $value;
		}

		public function values () {
			return $this->valuemap;
		}

	}
