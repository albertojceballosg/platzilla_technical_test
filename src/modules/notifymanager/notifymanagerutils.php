<?php

function getModulosDeCampos(){

	global $adb;

	// Seleccionando módulos de campos que estén disponibles
	$query = "SELECT tabid,name,tablabel from vtiger_tab where isentitytype = 1 and presence = 0 and avaliable = 1";

	$q=$adb->pquery($query);
	$modulosDeCampos = array();
	$noofrows = $adb->num_rows($q);
	if($noofrows > 0){
		
		while($row=$adb->fetchByAssoc($q)){

			if(esModuloDeCampos($row['tabid']) == 1){
				$modulosDeCampos[] = $row;
			}

		}
	}

	return $modulosDeCampos;

}


function getVistasDisponibles(){

	$acciones = array();
	// Por el momento solo estará disponible el ListView
	$acciones[] = array('name' => 'ListView' , 'label'=> 'Listado de registros');

	return $acciones;
}



?>