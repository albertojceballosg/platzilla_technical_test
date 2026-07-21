<?php

	if((!isset($_GET['idv']) || $_GET['idv']=='')){
		exit;
	}
	//$title=$_GET['desc'];
	//$file=$_GET['file'];
	$idv=$_GET['idv'];
	include('../../../include/conexion_vtiger.php');	
	//$module=$_GET['module'];
	$sql="select * from vtiger_videos b where idvideo=".$idv;
	$q=mysql_query($sql);
	$r=mysql_fetch_array($q);
	
	//echo $r['file']."*".$r['description'];
	$title=$r['description'];
	$file=$r['file'];
	//exit;

?>

<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<script type="text/javascript" src="flowplayer-3.2.9.min.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css">
	<title><?=$title?></title>
</head>
<body>

	<div align="center" >
    	<br>
		<a  
			 href="../../../video_uploads/<?=$file?>"
			 style="display:block;width:600px;height:330px"  
			 id="player"> 
		</a> 
	
		<!-- this will install flowplayer inside previous A- tag. -->
		<script>
			flowplayer("player", "flowplayer-3.2.10.swf");
		</script>
	
		
		
		<!-- 
			after this line is purely informational stuff. 
			does not affect on Flowplayer functionality 
		-->

	</div>
	
	<?php
$styleButt='background-color: orange;
			color: white;
			font-weight: bold;
			background-image: url(../../../themes/softed/images/buttonorange.png);
			margin-top: 5px;';
		if(isset($_GET['back'])){
			/*$back="module=".$_GET['module'];
			if(isset($_GET['submod']))
			$back.="&submod=".$_GET['submod'];
			echo "<br><a href=\"listvideos.php?$back\" style=\"".$styleButt."\">Back</a>";*/
			echo '<input title="Back" type="button" class="crmbutton small cancel" style="'.$styleButt.'" value="Back" language="javascript" onclick="history.back();">';
		}
	?>
	
</body></html>