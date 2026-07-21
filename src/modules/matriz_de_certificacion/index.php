<?php
	require_once ('Smarty_setup.php');
	require_once ('matriz_de_certificacion.php');

	$matriz  = getMatrizUsuariosActCurs ();
	$usuario = getUserNombres ();
	$titulos = getCursosTitulos ();

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('TIT', $titulos);
	$smarty->assign ('MATRIZ', $matriz);
	$smarty->assign ('USR', $usuario);
	$smarty->display ('modules/matriz_de_certificacion/MatrizCursoUsuario.tpl');
