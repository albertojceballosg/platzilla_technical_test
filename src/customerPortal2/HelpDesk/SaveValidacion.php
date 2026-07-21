<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
	include('../include/conexion_auxiliar.php');
 
	global $conex;
	
	if ($_REQUEST['validaTarea'] == 'Si') {
		$sql2 = "UPDATE vtiger_troubletickets SET status = '13. Dev validado por cliente. Pdte de evaluacion interna' WHERE ticketid = ".$_REQUEST['ticketid'];
		$re2=mysql_query($sql2,$conex);
	} else {
		$sql2 = "UPDATE vtiger_troubletickets SET status = '12. Dev rechazado por cliente. En revision' WHERE ticketid = ".$_REQUEST['ticketid'];
		$re2=mysql_query($sql2,$conex);
		$sql2 = "UPDATE vtiger_ticketcf SET cf_666 = '".$_REQUEST['textoNoValidacion']."' WHERE ticketid = ".$_REQUEST['ticketid'];
		$re2=mysql_query($sql2,$conex);
	}
?>
<script>
	var ticketid = <?php echo $_REQUEST['ticketid']; ?>;
	window.location.href = "index.php?module=HelpDesk&action=index&fun=detail&ticketid="+ticketid
</script>