<?php
	define ('_WEBSERVICE_EF_USER', 'usrcustomer@empresa-facil.com');
	define ('_WEBSERVICE_EF_PASS', 'c3ez10v0');

	require_once ('Smarty_setup.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/instancias/instancias.php');
	require_once ('modules/instancias/lib/InstancesHelper.class.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings;

	$newIds = array (
		'formacion_cursos'    => array (),
		'formacion_lecciones' => array (),
		'formacion_preguntas' => array (),
	);

	function crearPreguntas ($preguntas, $oldid) {
		global $adb, $current_user, $newIds;

		foreach ($preguntas as $pregunta) {
			if ($pregunta['formacion_preguntasid'] == $oldid) {
				echo "<h3 style='margin-left:15px'>Instalando pregunta: {$pregunta ['titulo']}</h3><br />";

				/** @var formacion_preguntas|stdClass $p */
				$p             = new formacion_preguntas();
				InstancesHelper::setValuesFromArray ($p, $pregunta);
				$p->column_fields['assigned_user_id'] = $current_user->id;
				$p->save ('formacion_preguntas');

				$newIds ['formacion_preguntas'][ $pregunta['formacion_preguntasid'] ] = $p->id;
				foreach ($pregunta['respuestas'] as $resp) {
					$adb->pquery (
						'INSERT INTO vtiger_formacion_preguntas_respuestas (formacion_preguntasid, orden, respuesta, correcta, porciento_valor) VALUES (?, ?, ?, ?, ?)',
						array ($p->id, $resp ['orden'], $resp ['respuesta'], $resp ['correcta'], $resp['porciento_valor'])
					);
				}
				return $p->id;
			}
		}
		return null;
	}

	function restoreHtml (&$s) {
		$hc  = array ('&amp;');
		$hcr = array ('&');
		$s   = str_ireplace ($hc, $hcr, $s);
		$s   = html_entity_decode ($s, ENT_COMPAT, 'UTF-8');
	}

	function saveCursoImage ($curso) {
		global $Server_Path;
		unset($_FILES);
		if (!$curso['image']) {
			return null;
		}
		$ext   = substr ($curso['image'], -3);
		$image = file_get_contents ($Server_Path . $curso['image']);
		$img   = 'tmp.' . $ext;
		file_put_contents ($img, $image);
		$size                = getimagesize ($img);
		$_FILES['img_curso'] = array (
			'name'     => 'imagen.' . $ext,
			'type'     => $size['mime'],
			'tmp_name' => getcwd () . '/' . $img,
			'error'    => '0',
			'size'     => filesize ($img),
		);
		return 'imagen.' . $ext;
	}

	$courseIds = isset ($_REQUEST ['cursos']) ? vtlib_purify ($_REQUEST ['cursos']) : null;
	$instance  = isset ($_REQUEST ['instancia']) ? vtlib_purify ($_REQUEST ['instancia']) : null;
	$mode      = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$recordId  = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;

	if (($recordId) && (!$instance)) {
		$result       = $adb->pquery ('SELECT * FROM vtiger_instancias WHERE instanciasid=?', array ($recordId));
		$instanceData = $adb->fetchByAssoc ($q);
	} else {
		$instanceData = array (
			'code'   => 'ff',
			'titulo' => 'Formacion-facil',
		);
	}

	$host                         = "http://{$instanceData ['code']}.platzilla.com";
	$soapClient                   = new soapclient2 ("{$host}/vtigerservice.php?service=customerportal&plat_customer={$instanceData ['code']}", false, '', '', '', '');
	$soapClient->soap_defencoding = 'utf-8';
	$entity                       = new instancias ();

	if ($mode == 'action') {
		echo "<h3>Conectando a {$instanceData ['code']}</h3><br />";
		$cursos    = $entity->soapRequest ($soapClient, 'getCursos', array ('ids' => implode (',', $courseIds)));
		$lecciones = $entity->soapRequest ($soapClient, 'getLecciones');
		$pruebas   = $entity->soapRequest ($soapClient, 'getPruebas');
		$preguntas = $entity->soapRequest ($soapClient, 'getPreguntas');
		array_walk_recursive ($cursos, 'restoreHtml');
		array_walk_recursive ($lecciones, 'restoreHtml');
		array_walk_recursive ($pruebas, 'restoreHtml');
		array_walk_recursive ($preguntas, 'restoreHtml');

		foreach ($cursos as $curso) {
			echo "<h3>Instalando curso: <b>{$curso ['titulo']}</b></h3><br />";
			$curso ['img_curso_hidden'] = saveCursoImage ($curso);
			$curso ['img_curso_id']     = '';
			unset ($curso ['img_curso']);
			unset ($curso ['image']);

			$currentModule = 'formacion_cursos';
			/** @var formacion_cursos|stdClass $fc */
			$fc = new formacion_cursos ();
			InstancesHelper::setValuesFromArray ($fc, $curso);
			$fc->column_fields ['assigned_user_id'] = $current_user->id;
			$fc->save ('formacion_cursos');
			$newIds ['formacion_cursos'][ $curso ['formacion_cursosid'] ] = $fc->id;

			foreach ($curso ['related'] as $rel) {
				$leccion = $entity->searchForId ($rel ['relcrmid'], $lecciones, 'formacion_leccionesid');
				if ($leccion == null) {
					continue;
				}
				echo "<h3 style='margin-left:15px'>Instalando Lección: {$leccion ['titulo']}</h3><br />";

				$currentModule = 'formacion_lecciones';
				/** @var formacion_lecciones|stdClass $l */
				$l = new formacion_lecciones();
				InstancesHelper::setValuesFromArray ($l, $leccion);
				$l->save ('formacion_lecciones');
				$newIds ['formacion_lecciones'][ $leccion['formacion_leccionesid'] ] = $l->id;
				$adb->pquery (
					"INSERT INTO vtiger_crmentityrel SET crmid=?, module='formacion_cursos', relcrmid=?, relmodule='formacion_lecciones'",
					array ($fc->id, $l->id)
				);
				if (($entity->checkVideos ()) && ($leccion ['idvideo'])) {
					$adb->pquery (
						'INSERT INTO vtiger_videos SET idvideo=?, file=?, description=?',
						array ($leccion ['idvideo'], $leccion ['file'], $leccion['description'])
					);
				}
			}
		}

		echo '<br /><br /><h3>Instalando exámenes</h3><br />';
		foreach ($pruebas as $prueba) {
			$currentModule = 'formacion_pruebas';
			/** @var formacion_pruebas|stdClass $pr */
			$pr = new formacion_pruebas ();
			InstancesHelper::setValuesFromArray ($pr, $prueba);
			$pr->column_fields['assigned_user_id'] = $current_user->id;

			$shouldSave = false;
			foreach ($prueba ['related'] as $related) {
				echo "<h3 style='margin-left:15px'>Instalando examen: {$prueba ['titulo']}</h3><br />";
				if (in_array ($related ['relcrmid'], $courseIds)) {
					$shouldSave                                                      = true;
					$newIds ['formacion_pruebas'][ $prueba ['formacion_pruebasid'] ] = $pr->id;
				}
			}

			if ($shouldSave) {
				$pr->save ('formacion_pruebas');
				foreach ($prueba ['related'] as $related) {
					if ($related ['relmodule'] == 'formacion_preguntas') {
						$newIds [ $related ['relmodule'] ][ $related ['relcrmid'] ] = crearPreguntas ($preguntas, $related ['relcrmid']);
						$rel                                                        = '';
						$adb->pquery (
							"INSERT INTO vtiger_crmentityrel SET crmid=?, module='formacion_pruebas', relcrmid=?, relmodule=?",
							array ($pr->id, $newIds [ $related ['relmodule'] ][ $related ['relcrmid'] ], $related ['relmodule'])
						);
					}
				}
				foreach ($prueba ['related_lecciones'] as $related) {
					if (($related ['module'] == 'formacion_lecciones') && ($newIds ['formacion_lecciones'][ $related['crmid'] ])) {
						$adb->pquery (
							"INSERT INTO vtiger_crmentityrel SET crmid=?, module='formacion_lecciones', relcrmid=?, relmodule='formacion_pruebas'",
							array ($newIds['formacion_lecciones'][ $related ['crmid'] ], $pr->id)
						);
					}
				}
			}
		}

		echo '<br /><br /><h3>INSTALACIÓN EXITOSA!</h3><br />';
		echo "<script type=\"text/javascript\">window.location.href='index.php?module=formacion_cursos&action=index'</script>";
		exit ();
	} else {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('CURSOS', $entity->soapRequest ($soapClient, 'getCursos'));
		$smarty->assign ('INSTANCIA', $instance);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('RECORD', $recordId);
		$smarty->display ("modules/{$currentModule}/listadoCursos.tpl");
	}
