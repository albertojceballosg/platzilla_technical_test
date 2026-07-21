<?php
require_once ('Smarty_setup.php');
checkFileAccessForInclusion("modules/$currentModule/$currentModule.php");
require_once("modules/$currentModule/$currentModule.php");
require_once("modules/$currentModule/formacion_cursos_tools.php");

global $currentModule;

$current_user=vtlib_purify($_SESSION['authenticated_user_id']);
$idCurso = vtlib_purify($_REQUEST['record']);
$evalu        = vtlib_purify ($_REQUEST['save']);
$dateva       = vtlib_purify (parse_str ($_REQUEST['datapost'], $my));
$focus        = CRMEntity::getInstance ($currentModule);
$smarty = new vtigerCRM_Smarty();

if ($idCurso != '') {
    $focus->id = $idCurso;
    $focus->retrieve_entity_info ($idCurso, $currentModule);
} else if ($idCurso == '' && $evalu == true) {
    $estado = guardarEvaluacion ($my);
    if ($estado ==='Aplazado') {
        echo 'Lo sentimos, usted no aprobo la prueba!!';
    } else {
        echo 'Felicidades!!!!!, usted aprobó la prueba';
    }

    //print_r ($estado);
    return;
}






$cursos=obtenerCursosUser($current_user);



$smarty->assign('CURSOS',$cursos);


if ($idCurso=='') {
    $smarty->display ('modules/formacion_de_cursos/ListViewCursosUser.tpl');
}else {
    // Module Sequence Numbering
    $mod_seq_field = getModuleSequenceField ($currentModule);
    if ($mod_seq_field != null) {
        $mod_seq_id = $focus->column_fields[ $mod_seq_field['name'] ];
    } else {
        $mod_seq_id = $focus->id;
    }
    $smarty->assign ('MOD_SEQ_ID', $mod_seq_id);
    // END
    $focus->column_fields['image'] = getFileFieldValue ($currentModule, 'imagen', $focus->id);
    $smarty->assign ('FIELDS', $focus->column_fields);
    $sql       = 'SELECT vfl.*,CONCAT(va.path,va.attachmentsid,\'_\',va.name) AS material,va.name AS archivo FROM vtiger_formacion_lecciones vfl
				INNER JOIN vtiger_crmentity crm ON crm.crmid=vfl.formacion_leccionesid AND crm.deleted=0
				INNER JOIN vtiger_crmentityrel crmrel ON crmrel.relcrmid=crm.crmid AND crmrel.crmid=?
				LEFT JOIN vtiger_attachments va ON va.attachmentsid=vfl.materiales
				ORDER by vfl.formacion_leccionesid ASC';

    $q         = $adb->pquery ($sql, array ($idCurso));
    $lecciones = array ();
    $order     = array ("\r\n", "\n", "\r");
    $replace   = '<br />';
    while ($r = $adb->fetchByAssoc ($q)) {
        $r             = str_replace ($order, $replace, $r);
        $r['ext']      = getExtension ($r['file']);
        $r['ext_arch'] = strtolower (getExtension ($r['archivo']));
        $eval          = getEvaluacion ($r['formacion_leccionesid']);
        if ($eval) {
            $r['eval'] = $eval;
            $lim       = calcularLimite ($eval[0]['formacion_pruebasid']);
            $preg      = getPreguntasRand ($eval[0]['formacion_pruebasid'], $lim);
            if ($lim != 100) {
                $r['preg'] = $preg;
            }
            $test = checkExamenporUsuario ($current_user, $eval[0]['formacion_pruebasid']);
            if (is_array ($test)) {
                $total = count ($test);
                if (is_Assoc_in_array ($test, 'estado', 'Aprobado') == 'yes') {
                    $r['test'] = 'Aprobado';
                } else {
                    if ($total < 3) {
                        $r['test'] = '1';
                    } else {
                        $r['test'] = 'Aplazado';
                    }
                }
            } else {
                $r['test'] = $test;
            }
        }

        $lecciones[] = $r;
    }

    $prog = calcularProgresodelCurso ($lecciones);
    if ($prog > 0) {
        $prog = ($prog * 100 / count ($lecciones));
    }
    $smarty->assign ('PROG', $prog);
    $smarty->assign ('LECCIONES', $lecciones);
    $smarty->assign ('LECCIONES_OBJ', json_encode ($lecciones));
    $smarty->assign ('RECORD', $idCurso);
    $smarty->assign ('UV', true);
    $smarty->assign ('usr_id', $current_user);

    $smarty->display ('modules/formacion_de_cursos/DetailViewCursos.tpl');
}
