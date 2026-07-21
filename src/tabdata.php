<?php


$tabData = false;
if (isset($_SESSION['plat'])) {
	if(is_dir($_SESSION['plat'])) {
		require($_SESSION['plat'].'/tabdata.php');
		$tabData = true;
	}
}

if (!$tabData)
	require('tabdata_madre.php');
	
?>