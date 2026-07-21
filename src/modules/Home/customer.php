<?php
	global $adb,$current_user,$gInstanciaEmpresaFacil;
	
	include_once 'include/security.php';
	//Obtengo los datos de conexion desde la bd
	
	//$sql = "SELECT user_customer, pass_customer FROM vtiger_organizationdetails";
	$sql = "SELECT user_name,customerpass FROM vtiger_users WHERE id = ?";
	
	
	$result = $adb->pquery($sql,array($current_user->id));
	$row = $adb->fetchByAssoc($result);
	
	//$user_name = decrypt($row['user_customer'],"estaeslaclave01EncryptadaDeTimeManagement");
	//$user_password = decrypt($row['pass_customer'],"estaeslaclave01EncryptadaDeTimeManagement");
	
	//$securityString = serialize(serialize($user_name)."|".serialize($user_password)."|".$_SESSION['vtiger_authenticated_user_theme']);
	$securityString = serialize(serialize($row['user_name'].'@'.$_SESSION['plat'].'.crm-facil.com')."|".serialize($row['customerpass'])."|".$_SESSION['vtiger_authenticated_user_theme']);
	
	if (strstr($row['user_name'], '@'))
		$securityString = serialize(serialize($row['user_name'])."|".serialize($row['customerpass'])."|".$_SESSION['vtiger_authenticated_user_theme']);
	$cadenaEncryptada = encrypt($securityString,"estaeslaclave01EncryptadaDeTimeManagement");
	
	//Para efecto de pruebas
	$plat = 'cliente-crm-'.$_SESSION['plat'];
	/*Hay que definir una mejor forma para esto.
	if (isset($_SESSION['plat']) && strstr($_SESSION['plat'],'test'))
		$plat = 'cliente-testtime';
	*/
	
	if ($gInstanciaEmpresaFacil) {
		$plat = 'cliente-crm-'.$_SESSION['plat'];
	}
	$addurl="&parentplat=".$_SESSION['plat'];
	if($_REQUEST['buy']){
		$addurl="&buy=".urlencode($_REQUEST['buy'])."&goto_module=Invoice&goto_action=index&parentplat=".$_SESSION['plat'];
		if($_REQUEST['serviceid']){
			$addurl.="&serviceid=".$_REQUEST['serviceid'];
		}
	}elseif(isset($_REQUEST['token']) && $_REQUEST['return']=='PayPal'){
		$addurl="&goto_module=Invoice&goto_action=index&parentplat=".$_SESSION['plat']."&return=PayPal&token=".$_REQUEST['token']."&PayerID=".$_REQUEST['PayerID']."&record=".$_REQUEST['record'];
	}
?>
<iframe id="iframeDot" src="http://<?php echo $plat;?>.crm-facil.com/index.php?VLK=<?php echo urlencode($cadenaEncryptada);?><?php echo $addurl?>" width="100%" height="600px" frameborder="0">

</iframe>

<script>

function determinaAlturaCustomer(ctrl) {
	if (document.all) {
		alturaCustomer = ctrl.clientHeight;
	}
	else {
		alturaCustomer = ctrl.innerHeight
	}
	return alturaCustomer;
}

function onLoadSystemCustomer() {

	if (document.all) {
		alturaCustomer = determinaAlturaCustomer(document.body);
	} else {
		alturaCustomer = determinaAlturaCustomer(window);
	}

	
	ctrlFrame = document.getElementById('iframeDot');

	alturaCustomer-= 65;

	if (ctrlFrame)
		ctrlFrame.style.height = alturaCustomer+'px';	
}

if (window.addEventListener)
	window.addEventListener("load",onLoadSystemCustomer,false);
else
	window.attachEvent("onload",onLoadSystemCustomer,false);


</script>