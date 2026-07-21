<?php
	if ($_REQUEST['return_module']=='formacion_cursos') {
		$where="NOT EXISTS (SELECT * from vtiger_crmentityrel e WHERE e.relcrmid = vtiger_formacion_lecciones.formacion_leccionesid AND e.relmodule='formacion_lecciones') ";
	}
	require_once('Popup.php');
?>
