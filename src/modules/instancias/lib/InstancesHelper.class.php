<?php

	abstract class InstancesHelper {

		/**
		 * @param CRMEntity|stdClass $entity
		 * @param array $data
		 */
		public static function setValuesFromArray (CRMEntity $entity, array $data) {
			if (isset ($data ['record'])) {
				$entity->id = $data ['record'];
			}
			if (isset($data ['mode'])) {
				$entity->mode = $data ['mode'];
			}
			$fieldNames = array_keys ($entity->column_fields);
			foreach ($fieldNames as $fieldName) {
				if (isset($data [ $fieldName ])) {
					if (is_array ($data [ $fieldName ])) {
						$value = $data [ $fieldName ];
					} else {
						$value = trim ($data [ $fieldName ]);
					}
					$entity->column_fields [ $fieldName ] = $value;
				}
			}
		}

	}
