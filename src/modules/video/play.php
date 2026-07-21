<?php

function getExtension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return strtolower($ext);
}	
	
	if((!isset($_GET['idv']) || $_GET['idv']=='')){
		die("Error... File id is missing");
	}
	
	$idv=$_GET['idv'];
	global $adb;
	//$module=$_GET['module'];
	$sql="select * from vtiger_videos b where idvideo=".$idv;
	$q=$adb->pquery($sql);
	$r=$adb->fetchByAssoc($q);
	// echo "<pre>".print_r($r,true)."</pre>";
	// echo "<pre>".print_r(getcwd(),true)."</pre>";
	// exit;
	//echo $r['file']."*".$r['description'];
	$title=$r['description'];
	$file=$r['file'];
	$video_extension = getExtension($r['file']);
	//exit;

?>

<!--script type="text/javascript" src="modules/video/player/flowplayer-3.2.9.min.js"></script>
<link rel="stylesheet" type="text/css" href="modules/video/player/style.css"-->


 <!-- Chang URLs to wherever Video.js files will be hosted -->
<link href="modules/video/html5player/video-js.css" rel="stylesheet" type="text/css">
<!-- video.js must be in the <head> for older IEs to work. -->
<script src="modules/video/html5player/video.js"></script>

	<div align="center" >
    	<br>
		<!--a  
			 href="storage/video_uploads/<?=$file?>"
			 style="display:block;width:600px;height:330px"  
			 id="player"> 
		</a--> 
		<?php
		if($video_extension=='mp3'){
		?>
		<audio controls>
		  <source src="storage/video_uploads/<?=$file?>" type="audio/<?=$video_extension?>">
		</audio>
		<?php
		}else{
		?>
		<video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" width="640" height="340" data-setup="{}">
			<source src="storage/video_uploads/<?=$file?>" type='video/<?=$video_extension?>' />
			<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
		</video>
		<script>
			videojs.options.flash.swf = "modules/video/html5player/video-js.swf";
		</script>
		<?php
		}
		?>
		
		
	
		<!-- this will install flowplayer inside previous A- tag. -->
		<!--script>
			flowplayer("player", "modules/video/player/flowplayer-3.2.10.swf");
		</script-->
	
		


	</div>
	
	<?php
$styleButt='background-color: orange;
			color: white;
			font-weight: bold;
			background-image: url(themes/softed/images/buttonorange.png);
			margin-top: 5px;';
		if(isset($_GET['back'])){
			/*$back="module=".$_GET['module'];
			if(isset($_GET['submod']))
			$back.="&submod=".$_GET['submod'];
			echo "<br><a href=\"listvideos.php?$back\" style=\"".$styleButt."\">Back</a>";*/
			echo '<input title="Back" type="button" class="crmbutton small cancel" style="'.$styleButt.'" value="Back" language="javascript" onclick="history.back();">';
		}
	?>
	