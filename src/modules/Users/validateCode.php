<?php
	require_once ('include/utils/utils.php');
	require_once ('config.inc.php');
	require_once ('modules/Users/Users.php');

	$var = obtenerValorVariable ('CORREO_VERIFICADO', 'Users');

	list($estado, $date, $code) = explode ('|', $var);

	if ($_REQUEST['code'] == $code) {
		updateValidateConfirmation ('', 'true');
		echo 'SUCCESS';
	} else {
		echo 'FAILURE';
	}
