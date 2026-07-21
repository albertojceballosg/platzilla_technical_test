<?php
	global $action, $currentModule, $smarty;

	if (!empty ($_SESSION ['platInstancia'])) {
		$botonesPersonalizadosClientes = $_SESSION['botonesPersonalizadosClientes'];
	} else {
		$botonesPersonalizadosClientes = obtenerBotonesPersonalizadosClientes ();
	}

	$botonesPersonalizadosClientesModulo = $botonesPersonalizadosClientes[ $currentModule ][ $action ];

	$smarty->assign ('CUSTOM_BUTTONS', $botonesPersonalizadosClientesModulo);
