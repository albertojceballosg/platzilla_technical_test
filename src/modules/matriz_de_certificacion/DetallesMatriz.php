<?php
	require_once ('Smarty_setup.php');
	require_once ('matriz_de_certificacion.php');

	$user   = getUserNombres ();
	$matriz = getMatrizUsuariosActCursPru1 ();
	$titulo = getCursosTitulos ();
	foreach ($titulo as $ti) {
		foreach ($matriz as $key => $va) {
			if ($key == $ti['formacion_de_cursosid']) {
				$new                     = $matriz[ $key ];
				$matriz[ $ti['titulo'] ] = $new;
				unset($matriz[ $key ]);
				unset($new);
			}
		}
	}

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('MATRIZ', $matriz);
	$smarty->assign ('USER', $user);
	$smarty->display ('modules/matriz_de_certificacion/DetallesMatriz.tpl');
