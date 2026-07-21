<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	// TODO: Quitar este archivo, cambiar en el index.php para poder acceder directamente a los archivos de instancedatasharing

	$method = $_SERVER ['REQUEST_METHOD'];
	if ($method === 'GET') {
		require_once ('modules/instancesdatasharing/AskMissingRequestData.php');
	} else if ($method === 'POST') {
		require_once ('modules/instancesdatasharing/ProcessRequest.php');
	}
