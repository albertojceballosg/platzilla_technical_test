<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/matriz_de_certificacion/matriz_de_certificacion.php');

	$guardar = vtlib_purify ($_REQUEST['save']);
	if ($guardar == true) {
		parse_str (vtlib_purify ($_REQUEST['datapost']), $my);
		foreach ($my as $my1) {
			$m = generaterMatrizGuardar ($my1);
		}
	} else {
		$matriz  = getMatrizUsuariosActCurs ();
		$usuario = getUserNombres ();
		$titulos = getCursosTitulos ();
		$smarty = new vtigerCRM_Smarty();
		$smarty->assign ('TIT', $titulos);
		$smarty->assign ('MATRIZ', $matriz);
		$smarty->assign ('USR', $usuario);
		$smarty->display ('modules/matriz_de_certificacion/EditMatriz.tpl');
	}
