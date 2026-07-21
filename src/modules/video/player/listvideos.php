<html>
<head>
<style type="text/css">@import url("../../../themes/softed/style.css");</style>
</head>
<body>
<?php

	//$title=$_GET['desc'];
	//$file=$_GET['file'];
	include('../../../include/conexion_vtiger.php');	
	$module=$_GET['module'];
	$back="&module=".$module;
	$addquery='';
	if(isset($_GET['submod'])){
		$addquery=" and (b.description like '%".$_GET['submod']."%' or b.file like '%".$_GET['submod']."%')";
		$back.="&submod=".$_GET['submod'];
	}
	if($module!="suporte"){
		$sql="select a.*,b.* from vtiger_tab a,vtiger_videos b where a.presence=0 and ( a.tablabel='".$module."' or a.name='".$module."') 
				and b.tabid=a.tabid".$addquery;
	}elseif($module=="suporte"){
		$sql="select b.* from vtiger_videos b where description like '%Support%'";
		//exit();
	}
 
	$q=mysql_query($sql) or die (mysql_error());
	//exit;
	$nVideos=mysql_num_rows($q);
	if($nVideos==0 && $module!="suporte"){
	?>
		<table border=0 cellspacing=0 cellpadding=0 width=100% class="hdrNameBg" bgcolor="#F68121" >
		<tr><td valign=top><img src="../../../themes/softed/images/vtiger-crm.gif" alt="Ormita CRM" title="Ormita CRM" border=0></td></tr>
		</table>

	<?php
		echo "<br /><br /><br /><br /><br /><br />";
		echo "<center>THERE IS NOT VIDEOS FOR THIS SECCTION</center>";
	}elseif($nVideos==1){
		$r=mysql_fetch_array($q);
		header('Location: index.php?idv='.$r['idvideo']);
	}elseif($nVideos>1){
		$back.="&back";
	
?>
<table border=0 cellspacing=0 cellpadding=0 width=100% class="hdrNameBg" bgcolor="#F68121" >
<tr><td valign=top><img src="../../../themes/softed/images/vtiger-crm.gif" alt="Ormita CRM" title="Ormita CRM" border=0></td></tr>
</table>

<br />
<table border="0" cellspacing="1" cellpadding="3" width="100%" class="lvt small">
		<!-- Table Headers -->
		<tbody>
				<tr>
						<td class="lvtCol"><b>Video</b></td>
						<td class="lvtCol"><b>Description</b></td>
						<td class="lvtCol" align="center"><b>Play</b></td>
				</tr>
<?php
		while($r=mysql_fetch_array($q)){
?>
				<tr bgcolor="white" class="lvtColData">
                	<td><?=$r['file']?></td>
                    <td><?=$r['description']?></td>
                    <td align="center"><a href="index.php?idv=<?=$r['idvideo']?><?=$back?>"><img src="../../../themes/images/next.gif" title="Play <?=$r['description']?> video" /></a></td>
                </tr>
<?php			
			
		}
?>
         </tbody>
</table>

</body>
</html>
<?php
	}
	
	//echo $r['file']." *";

	exit;

?>