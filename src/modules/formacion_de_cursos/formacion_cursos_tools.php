<?php
/**
 * Función que guarda la evaluación de la prueba de una lección
 *
 * @param array $data Arreglo de respuestas de la prueba realizada
 *
 * @return mixed
 */
function guardarEvaluacion ($data) {
    global $adb;
    $puntaje = 0;

    if (is_array ($data['preg_resp'])) {
        foreach ($data['preg_resp'] as $pregunta => $respuesta) {
            if (is_array ($respuesta)) {
                foreach ($respuesta as $id => $resp) {
                    $ppp   = 0;
                    $datos = array ('formacion_preguntasid' => $pregunta, 'id' => $resp);
                    $prep  = getPreguntaRespuesta ($datos);
                    if ($prep['correcta'] == '1') {
                        $ppp = ($prep['porciento_valor'] * $prep['ponderacion'] / 100);
                        $puntaje += ($prep['porciento_valor'] * $prep['ponderacion'] / 100);
                    }
                    $det        = 'text';
                    $toinsert[] = array ('formacion_preguntasid' => $pregunta, 'respuestaid' => $resp, 'puntaje' => $ppp, 'detalle' => $det);
                }
            } else if (!is_array ($respuesta)) {
                $datos = array ('formacion_preguntasid' => $pregunta, 'id' => $respuesta);
                $prep  = getPreguntaRespuesta ($datos);
                $ppp   = '0';
                if ($prep['correcta'] == '1') {
                    $ppp = $prep['ponderacion'];
                    $puntaje += $ppp;
                }
                $det        = '';
                $toinsert[] = array ('formacion_preguntasid' => $pregunta, 'respuestaid' => $respuesta, 'puntaje' => $ppp, 'detalle' => $det);
            }
        }
        $examen = getEvaluacionid ($data['formacion_pruebasid']);
        $estado = 'Aplazado';
        if ($examen['puntaje_minimo']<=$puntaje) {
            $estado = 'Aprobado';
        }
        $sql    = "INSERT INTO `vtiger_formacion_evaluacion` SET
				userid='" . $data['userid'] . "',
				formacion_pruebasid='" . $data['formacion_pruebasid'] . "',
				fecha=now(),
				puntaje_total='" . $puntaje . "',
				tiempo='00:" . $data['tiempo'] . "',
				id_formacion_curso='" . $data['idformacion_de_curso'] . "',
				estado='" . $estado . "'";
        $q      = $adb->pquery ($sql, '');
        //print_r($sql);
        $evalid = $adb->getLastInsertID ();
        $sql2   = "INSERT INTO `vtiger_formacion_evaluacion_respuestas` (
				 `evaluacionid`, `formacion_pruebasid`,`formacion_preguntasid`,`respuestaid`,`puntaje`,`detalle`) VALUES ";
        foreach ($toinsert as $val) {
            $sql2 .= " ('" . $evalid . "','" . $data['formacion_pruebasid'] . "'," . $val['formacion_preguntasid'] . ",'" . $val['respuestaid'] . "','" . (float) $val['puntaje'] . "','" . $val['detalle'] . "'),";
        }
        $sql2 = substr ($sql2, 0, -1);
        //print_r($sql2);
        $er   = $adb->pquery ($sql2, array());
        if (!er){
            die($sql2);
        }
        //echo 'El estado' .$estado;
        return $estado;
    }
}

/**
 * Función que obtiene la pregunta y respuestas de la prueba
 *
 * Recupera de la base de datos vtiger_formacion_preguntas y vtier_formacion_pregunas_respuestas las preguntas y las respuestas de la prueba
 *
 * @param array $data
 *
 * @return array|mixed
 */
function getPreguntaRespuesta ($data) {
    global $adb;
    $sql = 'SELECT * FROM `vtiger_formacion_preguntas` fp
				INNER JOIN `vtiger_formacion_preguntas_respuestas` fpr
				ON fpr.formacion_preguntasid=fp.formacion_preguntasid
				WHERE fp.formacion_preguntasid='
        . $data['formacion_preguntasid'] . " AND fpr.id='" . $data['id'] . "'";
    $q   = $adb->pquery ($sql, '');
    return $adb->fetchByAssoc ($q);
}

/**
 * Función que obtiene la información de formación de evaluación por el id del usuario y el id de la prueba
 *
 * @param integer $userid
 * @param integer $id
 *
 * @return array si encontró la información || 0 si no encontró la información
 */
function checkExamenporUsuario ($userid, $id) {
    global $adb;
    $sql = 'SELECT * FROM `vtiger_formacion_evaluacion` WHERE userid=' . $userid . ' AND formacion_pruebasid=' . $id;
    $q   = $adb->pquery ($sql);
    if ($adb->num_rows ($q) == 0) {
        return '0';
    } else {
        while ($r = $adb->fetchByAssoc ($q)) {
            $ret[] = $r;
        }
        return $ret;
    }
}

/**
 * Función que obtiene la extensión de la cadena
 *
 * @param $str
 *
 * @return '' en el caso que no encuetre el punto en la cadena |@return string en el caso que encuentre la extensión
 */
function getExtension ($str) {
    $pospunto = strrpos ($str, '.');
    if (!$pospunto) {
        return '';
    }
    $largo    = (strlen ($str) - $pospunto);
    $comienzo = ($pospunto + 1);
    $ext      = substr ($str, $comienzo, $largo);
    return $ext;
}

/**
 * Función que obtiene la prueba por el id de prueba
 *
 * @param $crmid Id de la prueba
 *
 * @return array con la información recuperada
 */
function getEvaluacionid ($crmid) {
    global $adb;
    $sql = "SELECT * FROM `vtiger_formacion_pruebas` WHERE `formacion_pruebasid` = '" . $crmid . "'";
    $q   = $adb->pquery ($sql,'');
    if(!$q){
        die($sql);
    }
    return $adb->fetchByAssoc ($q);
}

/**
 * Función que retorna las evaluación por el id de la leccion
 *
 * @param integer $fpid Id de la leccion
 *
 * @return array $ret retorna las evaluación
 */
function getEvaluacion ($fpid) {
    global $adb;
    $ret = array ();
    $sql = 'SELECT vfp.* FROM `vtiger_formacion_pruebas` vfp
					INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_pruebasid` AND crm.`deleted`=0
					INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.`crmid`=' . $fpid;
    $q   = $adb->pquery ($sql, array ());
    while ($r = $adb->fetchByAssoc ($q)) {
        $ret[] = $r;
    }
    return $ret;
}

/**
 * Función que retorna las preguntas y respuestas de la prueba por id de prueba
 *
 * @param integer $fpid Id de la prueba
 *
 * @return array $ret retorna las preguntas y respuestas
 */
function getPreguntas ($fpid) {
    global $adb;
    $ret  = array ();
    $resp = array ();
    $sql  = 'SELECT vfp.* FROM `vtiger_formacion_preguntas` vfp
						INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_preguntasid` AND crm.`deleted`=0
						INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.`crmid`=' . $fpid;
    $q    = $adb->pquery ($sql, array ());
    while ($r = $adb->fetchByAssoc ($q)) {
        $r['respuestas'] = getRespuestas ($r['formacion_preguntasid']);
        array_push ($ret, $r);
    }
    return $ret;
}

/**
 * Función que obtiene las respuestas de la pregunta
 *
 * @param integer $id Id de pregunta
 *
 * @return array $ret, las respuestas de la pregunta
 */
function getRespuestas ($id) {
    global $adb;
    $ret = array ();
    $sql = 'SELECT * FROM  `vtiger_formacion_preguntas_respuestas` WHERE  `formacion_preguntasid` =' . $id;
    $q   = $adb->pquery ($sql, array ());
    while ($r = $adb->fetchByAssoc ($q)) {
        array_push ($ret, $r);
    }
    return $ret;
}

/**
 * Función que obtiene las respuestas de la pregunta aleatoriamente
 *
 * @param integer $fpid
 * @param integer $lim
 */
function getPreguntasRand ($fpid, $lim) {
    global $adb;
    $ret  = array ();
    $resp = array ();
    $sql  = 'SELECT vfp.* FROM `vtiger_formacion_preguntas` vfp
					   INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_preguntasid` AND crm.`deleted`=0
					   INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.crmid=' . $fpid . '
					   ORDER BY rand() LIMIT ' . $lim;
    $q    = $adb->pquery ($sql, array ());
    while ($r = $adb->fetchByAssoc ($q)) {
        $r['respuestas'] = getRespuestas ($r['formacion_preguntasid']);
        array_push ($ret, $r);
    }
    return $ret;
}

function calcularLimite ($fpid) {
    global $adb;
    $lim = 0;
    //obtener la puntacion de la prueba
    $sql = 'SELECT ponderacion FROM vtiger_formacion_pruebas WHERE formacion_pruebasid=' . $fpid;
    $q   = $adb->pquery ($sql, array ());
    while ($r = $adb->fetchByAssoc ($q)) {
        $pun = $r['ponderacion'];
    }
    //Obtener si se tiene diferentes puntacion o igual
    $sql = 'SELECT COUNT(DISTINCT vfp.ponderacion) AS total, vfp.ponderacion FROM `vtiger_formacion_preguntas` vfp
					   INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_preguntasid` AND crm.`deleted`=0
					   INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.crmid=' . $fpid;
    $q   = $adb->pquery ($sql, array ());
    while ($r = $adb->fetchByAssoc ($q)) {
        $tot = $r['total'];
        $pon = $r['ponderacion'];
    }
    //calcular el limite
    //verifica si el total de ponderacion de la preguntas es igual o mayor que total de la prueba
    $sql    = 'SELECT sum(vfp.ponderacion) AS total FROM `vtiger_formacion_preguntas` vfp
					   INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_preguntasid` AND crm.`deleted`=0
					   INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.crmid=' . $fpid;
    $q      = $adb->pquery ($sql, array ());
    $pontot = 0;
    while ($r = $adb->fetchByAssoc ($q)) {
        $pontot = $r['total'];
    }
    if ($pontot >= $pun) {
        if ($tot == 1) {
            $lim = ((int) $pun / $pon);
        } else {
            $lim = 100;
        }
    } else {
        $lim = 0;
    }
    return $lim;
}

/**
 * Calcular el Progreso del Curso
 *
 * @param array $lecciones
 *
 * @return int
 */
function calcularProgresodelCurso ($lecciones) {
    $prog = 0;
    foreach ($lecciones as $leccion) {
        if ($leccion['test'] != 0) {
            $prog = ($prog + 1);
        }
    }
    return $prog;
}

/**
 * Función que encuentra en el arreglo si el valor de la clave esta
 *
 * @param $array donde se va buscar
 * @param $key la clave del arreglo
 * @param $key_value el valor de la clave
 *
 * @return string si encontro o no encontro el valor en el arreglo
 */
function is_Assoc_in_array ($array, $key, $key_value) {
    $encontro_in_array = 'no';
    foreach ($array as $k => $v) {
        if (is_array ($v)) {
            $encontro_in_array = is_Assoc_in_array ($v, $key, $key_value);
            if ($encontro_in_array == 'yes') {
                break;
            }
        } else {
            if ($v == $key_value && $k == $key) {
                $encontro_in_array = 'yes';
                break;
            }
        }
    }
    return $encontro_in_array;
}

function obtenerCursosUser($id) {
    global $adb;
    $result = $adb->pquery (
        'SELECT CONCAT(vtiger_attachments.path,vtiger_attachments.attachmentsid,\'_\',vtiger_attachments.name) AS image, fc.* 
                FROM vtiger_formacion_de_cursos fc 
                INNER JOIN vtiger_crmentityrel cr ON fc.formacion_de_cursosid=cr.crmid 
                LEFT JOIN `vtiger_attachments` ON `vtiger_attachments`.`attachmentsid`=fc.imagen 
                WHERE cr.relcrmid=? GROUP BY formacion_de_cursosid',
        array ($id)
    );
    $cursos = array();
    while ($r = $adb->fetchByAssoc ($result)) {
        array_push ($cursos, $r);
    }
    return $cursos;
}
