<?php


$parentTabData = false;
if (isset($_SESSION['plat'])) {
	if(is_dir($_SESSION['plat'])) {
		require($_SESSION['plat'].'/parent_tabdata.php');
		$parentTabData = true;
	}
}

if (!$parentTabData)
	require('parent_tabdata_madre.php');


?>