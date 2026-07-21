<?php
	set_time_limit (0);
	session_start ();

	require_once ('include/utils/InstancesCreator.class.php');
	echo 'Iniciando actualización de instancias en stock' . PHP_EOL;
	InstancesCreator::run ();
	echo 'Finalizando actualización de instancias en stock' . PHP_EOL;