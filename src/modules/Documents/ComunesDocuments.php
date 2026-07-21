<?php



function getParentFolders(){
	
	global $adb;
	$carpetas = array();
	$sql ="SELECT af.*,
			(select count(*) from vtiger_attachmentsfolder af2 where af2.parentfolder = af.folderid) as childfolders 
			FROM `vtiger_attachmentsfolder` af 
			where parentfolder = 0 ";

	$result = $adb->pquery($sql,array());
	while($row=$adb->fetchByAssoc($result)){
		$carpeta = array();
		$carpeta['folderid'] 		= $row['folderid'];
		$carpeta['foldername'] 		= $row['foldername'];
		$carpeta['description'] 	= $row['description'];
		$carpeta['childfolders'] 	= $row['childfolders'];
		$carpeta['sequence'] 		= $row['sequence'];
		$carpeta['children']		= array();
		if ($row['childfolders'] > 0)
			$carpeta['children'] = getInfoFolder($row['folderid']);
		array_push($carpetas,$carpeta);
		unset($carpeta);
	}

	return $carpetas;

}


function getParentFolderForEdit($folderid){
	
	global $adb;
	$carpetas = array();
	$sql ="SELECT af.*,
			(select count(*) from vtiger_attachmentsfolder af2 where af2.parentfolder = af.folderid) as childfolders 
			FROM `vtiger_attachmentsfolder` af 
			where parentfolder = ? ";

	$result = $adb->pquery($sql,array($folderid));
	while($row=$adb->fetchByAssoc($result)){
		$carpeta = array();
		$carpeta['folderid'] 		= $row['folderid'];
		$carpeta['foldername'] 		= $row['foldername'];
		$carpeta['description'] 	= $row['description'];
		$carpeta['childfolders'] 	= $row['childfolders'];
		$carpeta['sequence'] 		= $row['sequence'];
		$carpeta['children']		= array();
		if ($row['childfolders'] > 0)
			$carpeta['children'] = getInfoFolder($row['folderid']);
		array_push($carpetas,$carpeta);
		unset($carpeta);
	}

	return $carpetas;

}


function getInfoFolder($folderid){
	
	global $adb;
	$carpetas = array();

	$sql ="SELECT af.*,
			(select count(*) from vtiger_attachmentsfolder af2 where af2.parentfolder = af.folderid) as childfolders 
			FROM `vtiger_attachmentsfolder` af 
			where parentfolder = $folderid ";

	$result = $adb->pquery($sql,array());
	while($row=$adb->fetchByAssoc($result)){
		$carpeta = array();
		$carpeta['folderid'] 		= $row['folderid'];
		$carpeta['foldername'] 		= $row['foldername'];
		$carpeta['description'] 	= $row['description'];
		$carpeta['childfolders'] 	= $row['childfolders'];
		$carpeta['sequence'] 		= $row['sequence'];
		$carpeta['children']		= array();
		if ($row['childfolders'] > 0)
			$carpeta['children'] = getInfoFolder($row['folderid']);
		array_push($carpetas,$carpeta);
		unset($carpeta);
	}

	return $carpetas;

}


function getProfilesforFolders(){

	global $adb;
	$profiles = array();

	$sql ="SELECT * FROM vtiger_profile";

	$result = $adb->pquery($sql,array());
	while($row=$adb->fetchByAssoc($result)){
		$profile = array();
		$profile['profileid'] 		= $row['profileid'];
		$profile['profilename'] 		= $row['profilename'];
		$profile['description'] 	= $row['description'];
		array_push($profiles,$profile);
		unset($profile);
	}

	return $profiles;
}


/*
LLenando la tabla de control de perfiles y carpetas (vtiger_profile2folders)
*/
function inicializarPermisologia(){

	global $adb;
	$profiles = array();

	$sql ="INSERT INTO vtiger_profile2folders (profileid,folderid) 
			SELECT profileid,folderid 
			FROM `vtiger_profile` p, vtiger_attachmentsfolder f 
			where not EXISTS (
				select 1 from vtiger_profile2folders p2f 
				where p2f.profileid = p.profileid and p2f.folderid = f.folderid
			) order by profileid,folderid";

	$result = $adb->pquery($sql,array());

}

function getPermissionFolderbyProfile($profileid,$folderid){

	global $adb;

	$sql ="SELECT * FROM vtiger_profile2folders where profileid = ? and folderid = ?";

	$result = $adb->pquery($sql,array($profileid,$folderid));
	while($row=$adb->fetchByAssoc($result)){
		$permiso = array();
		$permiso['profileid'] 			= $row['profileid'];
		$permiso['folderid'] 			= $row['folderid'];
		$permiso['read_act'] 			= $row['read_act'];
		$permiso['edit_act'] 			= $row['edit_act'];
		$permiso['delete_act'] 			= $row['delete_act'];
		$permiso['profilefolderspk'] 	= $row['profilefolderspk'];
	}

	return $permiso;

}



function getBasicInfoFolder($folderid){
	
	global $adb;

	$sql = "SELECT * FROM vtiger_attachmentsfolder where folderid = $folderid ";

	$result = $adb->pquery($sql,array());
	while($row=$adb->fetchByAssoc($result)){
		$carpeta = array();
		$carpeta['folderid'] 		= $row['folderid'];
		$carpeta['foldername'] 		= $row['foldername'];
		$carpeta['description'] 	= $row['description'];
	}

	return $carpeta;

}




function savePermissionFolderbyProfile($data){

	global $adb;

	$sql = "UPDATE vtiger_profile2folders SET read_act = ?,  edit_act = ?, delete_act = ?
		where folderid = ? and profileid = ? ";

	$adb->pquery($sql,array($data['read_act'],$data['edit_act'],$data['delete_act'],$data['folderid'],$data['profileid']));

	$querySubFolder = "SELECT * FROM vtiger_attachmentsfolder where parentfolder = ? ";
	$result = $adb->pquery($querySubFolder,array($data['folderid']));
	while($row=$adb->fetchByAssoc($result)){
		
		$subfolderEditQuery = "UPDATE vtiger_profile2folders SET read_act = ?,  edit_act = ?, delete_act = ?
			where folderid = ? and profileid = ? ";
		$adb->pquery($subfolderEditQuery,array($data['read_act'],$data['edit_act'],$data['delete_act'],$row['folderid'],$data['profileid']));
	}

	

}

function getCarpetaPadreID($folderid){

	global $adb;
	$parentfolder = 0;

	$sql = "SELECT parentfolder FROM vtiger_attachmentsfolder where folderid = $folderid ";

	$result = $adb->pquery($sql,array());
	while($row=$adb->fetchByAssoc($result)){
		$parentfolder = $row['parentfolder'];
	}

	return $parentfolder;


}

function getPermisologiaPorPerfil($profileid){

	global $adb;
	$permiso = array();

	$sql ="SELECT * FROM vtiger_profile2folders where profileid = ? ";

	$result = $adb->pquery($sql,array($profileid));
	while($row=$adb->fetchByAssoc($result)){
		$permiso[$row['folderid']]['read_act'] 	= $row['read_act'];
		$permiso[$row['folderid']]['edit_act'] 	= $row['edit_act'];
		$permiso[$row['folderid']]['delete_act'] = $row['delete_act'];

		/*
		$permiso[$row['folderid']][$row['profileid']]['read_act'] 	= $row['read_act'];
		$permiso[$row['folderid']][$row['profileid']]['edit_act'] 	= $row['edit_act'];
		$permiso[$row['folderid']][$row['profileid']]['delete_act'] = $row['delete_act'];
		*/
	}

	return $permiso;

}

function getPermisologiaDocumento(){

	global $adb,$current_user;

	$permiso = array();

	$sql = "SELECT usuariospermitidos,notesid,privado 
		FROM `vtiger_seattachmentsrel` searel 
		left join vtiger_notes n on (n.notesid = searel.crmid) 
		join vtiger_crmentity crm on (crm.crmid = searel.crmid)
		where 
		( 
			privado = 1 and 
			(usuariospermitidos not like '".$current_user->id." |%' and usuariospermitidos not like '%| ".$current_user->id." |%' and usuariospermitidos not like '%| ".$current_user->id."') and 
			 smownerid <> ".$current_user->id."
		) ";

	$result = $adb->pquery($sql,array());
	while($row=$adb->fetchByAssoc($result)){
		$permiso['privado'][$row['notesid']] 	= 1;

	}

	return $permiso;

}





?>