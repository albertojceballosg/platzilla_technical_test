<?php

	/**
	 * Class CreateCustomButtonHelper
	 */
	abstract class CreateCustomButtonHelper {

		/**
		 * @param $fields
		 * @param $values
		 * @param $operators
		 *
		 * @return string
		 */
		private static function getEquation ($fields, $values, $operators) {
			$equation   = '';
			$typeofdata = array (
				'V'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"', 'in' => ' IS @', 'inn' => ' IS @'),
				'N'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= ', 'in' => ' IS @', 'inn' => ' IS @'),
				'T'  => array ('e' => ' DATE( * ) = DATE( "@" )', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * ) > DATE( "@" )', 'in' => ' IS @', 'inn' => ' IS @'),
				'I'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= ', 'in' => ' IS @', 'inn' => ' IS @'),
				'C'  => array ('e' => ' = ', 'n' => ' != ', 'in' => ' IS @', 'inn' => ' IS @'),
				'D'  => array ('e' => ' DATE( * ) = DATE( "@" )', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * ) > DATE( "@" )', 'in' => ' IS @', 'inn' => ' IS @'),
				'DT' => array ('e' => ' DATE( * ) = DATE( "@" )', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * )  > DATE( "@" )', 'in' => ' IS @', 'inn' => ' IS @'),
				'NN' => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= ', 'in' => ' IS @', 'inn' => ' IS @'),
				'E'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"', 'in' => ' IS @', 'inn' => ' IS @'),
			);

			list($fieldType, $fieldName, $tablaAlias) = explode ('@', $fields);
			$fieldName = $tablaAlias . $fieldName;
			list($min, $max) = explode (',', $values);

			$operated = $typeofdata[ $fieldType ][ $operators ];

			$posValue = strripos ($operated, '@');
			if ($posValue !== false) {
				$operated = str_replace ('@', $min, $operated);
				if (!empty($max)) {
					$operated = str_replace ('_', $max, $operated);
				}
			}
			$posField = strripos ($operated, '*');
			if ($posField !== false) {
				$equation .= str_replace ('*', $fieldName, $operated);
			} else if ($posValue === false) {
				$equation .= $fieldName . $operated . $min;
			} else {
				$equation .= $fieldName . $operated;
			}
			return $equation;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param null $keyword
		 *
		 * @return array
		 */
		public static function getVisibleModulesData (PearDatabase $adb, $keyword = null) {
			$sql = 'SELECT
						t.name,
						t.tabid,
						t.tablabel
					FROM
						vtiger_tab t
						INNER JOIN vtiger_entityname e ON e.modulename=t.name
					WHERE
						t.isentitytype=1 AND
						t.presence IN (0, 2) AND
						t.customized IN (0, 1, 2)';
			if ($keyword) {
				$sql .= ' AND t.name=?';
				$result = $adb->pquery ($sql, array ($keyword));
			} else {
				$result = $adb->query ($sql);
			}
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			return $modules;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $buttonId
		 *
		 * @return array|mixed|null
		 */
		public static function getCustomButtonData (PearDatabase $adb, $buttonId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_custombuttons WHERE custombuttonid=?', array ($buttonId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		/**
		 * @param $customButton
		 *
		 * @return null|string
		 */
		public static function getBackgroundTaskName ($customButton) {
			if ($customButton ['type'] != 'link') {
				return null;
			}

			$urlParts = parse_url ($customButton ['link']);
			if ($urlParts === false) {
				return null;
			}

			$queryStringParts = explode ('&', $urlParts ['query']);
			if (empty ($queryStringParts)) {
				return null;
			}

			$backgroundTaskName = null;
			$queryString = array ();
			foreach ($queryStringParts as $queryStringPart) {
				$dummy = explode ('=', $queryStringPart);
				$queryString [ $dummy [0] ] = !empty ($dummy [1]) ? $dummy [1] : null;
			}

			if (empty ($queryString)) {
				return null;
			}

			if (
				(!empty ($queryString ['module'])) && ($queryString ['module'] == 'backgroundtasks') &&
				(!empty ($queryString ['action'])) && ($queryString ['action'] == 'RunTask') &&
				(!empty ($queryString ['taskname']))
			) {
				return urldecode ($queryString ['taskname']);
			} else {
				return null;
			}
		}

		/**
		 *
		 * @return array
		 */
		public static function getTypeOfData () {
			return array (
				'V'  => array ('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene','in' => 'es nulo','inn' => 'no es nulo'),
				'N'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual','in' => 'es nulo','inn' => 'no es nulo'),
				'T'  => array ('e' => 'igual',  'b' => 'antes', 'a' => 'después','in' => 'es nulo','inn' => 'no es nulo'),
				'I'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual','in' => 'es nulo','inn' => 'no es nulo'),
				'C'  => array ('e' => 'igual', 'n' => 'no igual a','in' => 'es nulo','inn' => 'no es nulo'),
				'D'  => array ('e' => 'igual',  'b' => 'antes', 'a' => 'después','in' => 'es nulo','inn' => 'no es nulo'),
				'DT' => array ('e' => 'igual',  'b' => 'antes', 'a' => 'después','in' => 'es nulo','inn' => 'no es nulo'),
				'NN' => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual','in' => 'es nulo','inn' => 'no es nulo'),
				'E'  => array ('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene','in' => 'es nulo','inn' => 'no es nulo'),
			);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array|null
		 */
		public static function getModuleColumnsData (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					f.fieldname,
					f.fieldlabel,
					f.tablename,
					f.uitype,
					f.typeofdata
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.presence IN (0, 2) AND
					f.uitype NOT IN (10, 11, 13, 17, 19, 52, 69, 257, 2202) AND
					t.name=?',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldtype  = explode ('~', $row ['typeofdata']);
				$columns [] = array (
					'fieldname'  => $row ['fieldname'],
					'label'      => html_entity_decode (getTranslatedString ($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
					'tablename'  => $row ['tablename'],
					'uitype'     => $row ['uitype'],
					'typeofdata' => $fieldtype[0],
				);
			}

			usort (
				$columns,
				function ($columnA, $columnB) {
					return strcmp ($columnA ['label'], $columnB ['label']);
				}
			);
			return $columns;
		}

		/**
		 * @param $fields
		 * @param $fieldData
		 */
		public static function getFieldDataType (&$fields, $fieldData) {
			$totalFields = count ($fields);
			foreach ($fieldData as $field) {
				for ($k = 0; $k < $totalFields; $k++) {
					if ($fields[ $k ] == $field['fieldname']) {
						$fields[ $k ] = $field['typeofdata'] . '@' . $fields[ $k ];
						if (($field['uitype'] == '70') || (intval ($field['uitype']) == 70)) {
							$fields[ $k ] .= '@crm.';
						} else {
							$fields[ $k ] .= '@tq.';
						}
					}
				}
			}
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $filterData
		 *
		 * @return string
		 */
		public static function getSqlFilter (PearDatabase $adb, $filterData) {
			$fields          = $filterData ['filterField'];
			$operators       = $filterData ['filterOperator'];
			$values          = $filterData ['filterValue'];
			$joins           = $filterData ['filterJoin'];
			$groupJoins      = $filterData ['filterGroupJoin'];
			$moduleFilter    = $filterData ['moduleFilter'];
			$grupoIndex      = $filterData ['indexGrupo'];


			$fieldData = self::getModuleColumnsData ($adb, $moduleFilter);
			self::getFieldDataType ($fields, $fieldData);

			$totalOperations = count ($fields);
			$totalGroup      = count ($groupJoins);
			$myGroup = $grupoIndex[0];
			$nextOper = 0;
			$equation   = '( ';
			$indexGroup = 0;
			$indexJoin  = 0;

			if ($totalOperations > 0) {
				for ($op = 0; $op < $totalOperations; $op++) {
					$equation .= self::getEquation($fields[$op], $values[$op],$operators[$op]);

					$nextOper = ($nextOper < $totalOperations) ? ($nextOper + 1) : $op;

					if ($grupoIndex[ $nextOper ] != $myGroup) {
						$myGroup = $grupoIndex[ $nextOper ];
						if ($op < ($totalOperations - 1)) {
							$equation .= ' )';
							if ($indexGroup == 0) {
								$equation = '( ' . $equation;
							}
							$equation = $equation . ' ) ' . $groupJoins[ $indexGroup ] . ' ( ( ';
							$indexGroup++;
						} else if($totalGroup > 0) {
							$equation = $equation . ' ))';
						} else {
							$equation = $equation . ' )';
						}
					} else {
						if ($op < ($totalOperations - 1)) {
							$equation = $equation . ' ) ' . $joins[ $indexJoin ] .  ' ( ';

							$indexJoin++;
						} else {
							if ($indexGroup == 0) {
								$equation = $equation . ' ) ';
							} else {
								$equation = $equation . ' ) )';
							}
						}
					}
				}
				return $equation;
			} else {
				return '';
			}
		}

		/**
		 * @return array
		 */
		public static function getTypesAvailable () {
			return array (
				array ('name' => 'js', 'label' => 'JavaScript'),
				array ('name' => 'link', 'label' => 'Enlace'),
				array ('name' => 'backgroundtask', 'label' => 'Tarea en segundo plano'),
			);
		}

		/**
		 * @return array
		 */
		public static function getViewsAvailable () {
			return array (
				array ('name' => 'DetailView', 'label' => 'Detalle de registros'),
				// array ('name' => 'ListView', 'label' => 'Listas de registros'), EB 20200104 - Para quitar esta opcion que no funciona
				array ('name' => 'ActionButton', 'label' => 'Botón de acción en listas de registros' ),
			);
		}

	}
