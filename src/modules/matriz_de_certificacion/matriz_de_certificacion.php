<?php

	function getUserC () {
		global $adb;

		$result = $adb->query ('SELECT id FROM vtiger_users');
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$users = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$users [] = $row;
		}
		return $users;
	}

	function getUserNombres () {
		global $adb;

		$result = $adb->query ('SELECT id, CONCAT(last_name,\', \',first_name) AS nombres FROM vtiger_users');
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$users = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$users [] = $row;
		}
		return $users;
	}

	function getCursosTitulos () {
		global $adb;

		$result = $adb->query ('SELECT formacion_de_cursosid, titulo FROM vtiger_formacion_de_cursos');
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$cursos = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$cursos [] = $row;
		}
		return $cursos;
	}

	function getCursos () {
		global $adb;

		$result = $adb->query ('SELECT formacion_de_cursosid FROM vtiger_formacion_de_cursos');
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$cursos = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$cursos [] = $row;
		}
		return $cursos;
	}

	function getUsuariosActCurs () {
		global $adb;

		$result = $adb->pquery (
			'SELECT
				vfc.formacion_de_cursosid,
				vfc.titulo,
				crm.crmid,
				crm.setype,
				crmrel.*
			FROM
				vtiger_formacion_de_cursos vfc
		  		INNER JOIN vtiger_crmentity crm ON crm.crmid=vfc.formacion_de_cursosid AND crm.deleted=0
		  		INNER JOIN vtiger_crmentityrel crmrel ON crmrel.relmodule=? AND crm.crmid=crmrel.crmid
		  	ORDER BY
		  		vfc.titulo',
			array ('Users')
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$usrCurs = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$usrCurs[] = $row;
		}
		return $usrCurs;
	}

	function iniMatriz () {
		$users  = getUserC ();
		$cursos = getCursos ();

		$matriz = array ();
		foreach ($cursos as $curso) {
			foreach ($users as $user) {
				$matriz [ $curso ['formacion_de_cursosid'] ][ $user ['id'] ] = 0;
			}
		}
		return $matriz;
	}

	function getMatrizUsuariosActCurs () {
		global $adb;

		$matriz = iniMatriz ();
		$result = $adb->pquery (
			'SELECT
				vfc.formacion_de_cursosid,
				crmrel.relcrmid
			FROM
				vtiger_formacion_de_cursos vfc
		  		INNER JOIN vtiger_crmentity crm ON crm.crmid=vfc.formacion_de_cursosid AND crm.deleted=0
		  		INNER JOIN vtiger_crmentityrel crmrel ON crmrel.relmodule=? AND crm.crmid=crmrel.crmid',
			array ('Users')
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return $matriz;
		}

		$anotherMatrix = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$anotherMatrix [] = $row;
		}

		foreach ($anotherMatrix as $element) {
			$i                   = $element ['formacion_de_cursosid'];
			$j                   = $element ['relcrmid'];
			$v['Asignado']       = 1;
			$v['Aprobado']       = 0;
			$v['user']           = $element ['relcrmid'];
			$matriz [ $i ][ $j ] = $v;
		}
		return $matriz;
	}

	function generaterMatrizCursosUsuarios () {
		$matriz  = array ();
		$users   = getUserC ();
		$cursos  = getCursos ();
		$usrCurs = getUsuariosActCurs ();
		foreach ($cursos as $curso) {
			foreach ($users as $user) {
				foreach ($usrCurs as $uc) {
					$en = array_search ($user ['id'], $uc ['relcrmid']);
					if ($en) {
						$matriz[ $curso ['formacion_de_cursosid'] ][ $user ['id'] ] = 1;
					} else {
						$matriz[ $curso ['formacion_de_cursosid'] ][ $user ['id'] ] = 0;
					}
				}
			}
		}
		return $matriz;
	}

	function isInArray ($array, $key, $key_value) {
		$withinArray = 'no';
		foreach ($array as $k => $v) {
			if (is_array ($v)) {
				$withinArray = isInArray ($v, $key, $key_value);
				if ($withinArray == 'yes') {
					break;
				}
			} else {
				if ($v == $key_value && $k == $key) {
					$withinArray = 'yes';
					break;
				}
			}
		}
		return $withinArray;
	}

	function multiDiff ($arr1, $arr2) {
		$result = array ();
		foreach ($arr1 as $k => $v) {
			if (!isset($arr2[ $k ])) {
				$result[ $k ] = $v;
			} else {
				if (is_array ($v) && is_array ($arr2[ $k ])) {
					$diff = multiDiff ($v, $arr2[ $k ]);
					if (!empty($diff)) {
						$result[ $k ] = $diff;
					}
				}
			}
		}
		return $result;
	}

	function generarMatrizCusrsosUsuariosEdit ($matriz) {
		$matrizbase = getMatrizUsuariosCursos ();
		$matrizmod  = $matriz;
		multiDiff ($matrizbase, $matrizmod);
		foreach ($matrizbase as $item) {
			if (in_array ($item, $matrizmod)) {
				echo 'Existe';
			} else {
				echo 'no enco';
			}
		}
		return $matriz;
	}

	function getMatrizUsuariosCursos () {
		global $adb;

		$matriz = iniMatriz ();
		$result = $adb->pquery (
			'SELECT
				vfc.formacion_de_cursosid,
				crmrel.relcrmid
			FROM
				vtiger_formacion_de_cursos vfc
		  		INNER JOIN vtiger_crmentity crm ON crm.crmid=vfc.formacion_de_cursosid AND crm.deleted=0
		  		INNER JOIN vtiger_crmentityrel crmrel ON crmrel.relmodule=? AND crm.crmid=crmrel.crmid',
			array ('Users')
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return $matriz;
		}

		$anotherMatrix = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$anotherMatrix [] = $row;
		}

		foreach ($anotherMatrix as $element) {
			$i                  = $element ['formacion_de_cursosid'];
			$j                  = $element ['relcrmid'];
			$v                  = '1';
			$matriz[ $i ][ $j ] = $v;
		}
		return $matriz;
	}

	function updateMatrizCursosUsuarios ($matriz) {
		global $adb;
		foreach ($matriz as $k => $cu) {
			foreach ($cu as $k1 => $c) {
				$sql = 'SELECT * FROM vtiger_crmentityrel WHERE crmid=' . $k . ' AND relcrmid=' . $k1;
				$q   = $adb->pquery ($sql, '');
				if ($adb->num_rows ($q) == 0) {
					if ($matriz[ $k ][ $k1 ] == 1) {
						$adb->pquery (
							'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)',
							array ($k, 'formacion_de_cursos', $k1, 'Users')
						);
					}
				}
				if ($adb->num_rows ($q) != 0) {
					if ($matriz[ $k ][ $k1 ] == 1) {
						$adb->pquery (
							'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)',
							array ($k, 'formacion_de_cursos', $k1, 'Users')
						);
					} else if ($matriz[ $k ][ $k1 ] == 0) {
						$sql = "DELETE FROM vtiger_crmentityrel WHERE crmid=? AND relcrmid=?";
						$adb->pquery ($sql, array ($k, $k1));
					}
				}
			}
		}
	}

	function generaterMatrizGuardar ($matriz) {
		$user       = getUserC ();
		$cursos     = getCursos ();
		$matrizbase = getMatrizUsuariosCursos ();
		$matrizmod  = $matriz;

		$matriz1 = array ();
		foreach ($cursos as $cu) {
			foreach ($user as $usr) {
				if ($matrizbase[ $cu['formacion_de_cursosid'] ][ $usr['id'] ] = $matrizmod[ $cu['formacion_de_cursosid'] ][ $usr['id'] ]) {
					$matriz1[ $cu['formacion_de_cursosid'] ][ $usr['id'] ] = 1;
				} else {
					$matriz1[ $cu['formacion_de_cursosid'] ][ $usr['id'] ] = 0;
				}
			}
		}
		updateMatrizCursosUsuarios ($matriz1);
		return $matriz1;
	}

	/**
	 * Función que obtiene las pruebas de los cursos por usuarios
	 *
	 * @param integer $curso el id del curso
	 * @param integer $usario el id del usuario
	 *
	 * @return array $pruebas las pruebas realizadas por los usuarios
	 */
	function getPruebasCursosUsuario ($curso, $usario) {
		global $adb;

		$result = $adb->pquery (
			'SELECT vfe.*, vfc.titulo, vfp.titulo AS tituloprueba 
                    FROM vtiger_formacion_evaluacion vfe INNER JOIN vtiger_users vu ON vfe.userid=vu.id 
                    INNER JOIN vtiger_formacion_de_cursos vfc ON vfe.id_formacion_curso=vfc.formacion_de_cursosid 
                    INNER JOIN vtiger_formacion_pruebas vfp ON vfe.formacion_pruebasid=vfp.formacion_pruebasid 
                    WHERE vfc.formacion_de_cursosid=? AND vu.id=? 
                    ORDER BY vfe.formacion_pruebasid',
			array ($curso, $usario)
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$pruebas = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$pruebas [] = $row;
		}
		return $pruebas;
	}

	/**
	 * Función que genera la matriz de las pruebas realizadas
	 *
	 * @return mixed las pruebas realizadas
	 */
	function getMatrizUsuariosActCursPru1 () {
		$matriz = iniMatriz ();
		foreach ($matriz as $curso => $ma) {
			foreach ($ma as $key => $value) {
				$prueba                   = getPruebasCursosUsuario ($curso, $key);
				$matriz[ $curso ][ $key ] = $prueba;
			}
		}
		return $matriz;
	}
