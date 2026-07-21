<script>
jQuery.noConflict();
</script>
<?php
@include("../PortalConfig.php");
setPortalCurrentLanguage();
$default_language = getPortalCurrentLanguage();
@include("../language/$default_language.lang.php");

if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '')
{
	@header("Location: $Authenticate_Path/login.php");
	exit;
}


	$customerid =  $_SESSION['customer_id'];
	$sessionid = $_SESSION['customer_sessionid'];
	$accountid =  $_SESSION['accountid'];

	if (isset($_REQUEST['year'])and ((int)$_REQUEST['year']>0) ) $year = (int)$_REQUEST['year'];
	else $year = 0;

	if ($year > 1900) $where =" and ((P.fechainicial like '".(int)$year."%')or(P.fechafinal like '".(int)$year."%'))";
	else  $where='';

	$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'accountid'=>"$accountid", 'where' => "$where"));

	$proyectos = $client->call('get_proyectos_list', $params, $Server_Path, $Server_Path);
	

?>

<table class="dvtContentSpace" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="left">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
		<form name="GANTT" method="POST" action="index.php">
		<input type="hidden" name="module" value="GANTT">
		<input type="hidden" name="action" value="index">		

			<tr>
				<td  class="detailedViewHeader" width="70%"><span class="lvtHeaderText">GANTT</span></td>
				<td  class="detailedViewHeader" width="30%" style='text-align:right;'><span class="lvtHeaderText">A&ntilde;o:</span><select name="year" onchange="this.form.submit();">
					<? for($i=date('Y');$i>=2010;$i--)  echo "<option value='$i'".($i==$year?" selected='selected'":'').">$i</option>"; ?>
				</select></td>
			</tr>
			<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">
					<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="center"><tr><td  width="100%" valign="top">


</td></tr>
<tr><td>
<?

$data1 = Array();

foreach($proyectos as $key => $reg) {


	// TITULO
	$titulo = "<a href='javascript:void();' onclick=\"alert('";
	$titulo .= 'Fecha Inicial: '.$reg['fechainicial'].'\n';
	$titulo .= 'Fecha Final: '.$reg['fechafinal'].'\n\n';
	$titulo .= 'Descripcion: '.$reg['descripcion'];
	$titulo .= "');\">".ucfirst($reg['titulo'])."</a>";
	$titulo = "<a href='#".$reg['proyectosid']."' class='clicktitle'>".ucfirst($reg['titulo'])."</a>";
	
	// FECHAS
	$fechainicial  = $reg['fechainicial'];
	$fechafinal  = $reg['fechafinal'];
	if ($year > 0) {
			if ($reg['fechainicial'] < $year.'-01-01') $fechainicial = $year.'-01-01';
			if ($reg['fechafinal'] > $year.'-12-31') $fechafinal = $year.'-12-31';
	}

	// ESTADO = CLASS (existen 3 class, sin nada, 'important' y 'urgent')
	$class = '';  // AUTO: no se pide validacion al cliente
	if ($reg['estado'] == 'SI: Confirmado por el cliente') $class = 'important';
	// if ($reg['estado'] == 'SI: Confirmado por el cliente') $class = 'urgent';

	$data1[] = array(
		  'label' => $titulo,
		  'start' => $fechainicial,
		  'end'   => $fechafinal,
		  'class' => $class,
	);
	$todivs[$reg['proyectosid']]=$reg;
	$params2=Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'proyectosid'=>$reg['proyectosid']));
	$hitos = $client->call('get_hitos_list', $params2, $Server_Path, $Server_Path);
	foreach($hitos as $r){
		$data1[]= array(
		  'label' => "<span style='margin-left:10px'>".ucfirst($r['titulo'])."</span>", 
		  'start' => $r['fecha_desde'], 
		  'end'   => $r['fecha_hasta'], 
		  'class' => 'important',
		);
		$data2= array(
		  'label' => "<span style='margin-left:10px'>".ucfirst($r['titulo'])."</span>", 
		  'start' => $r['fecha_desde'], 
		  'end'   => $r['fecha_hasta'], 
		  'class' => 'important',
		);
		$todivs[$reg['proyectosid']]['hitos'][]=$data2;
	}
	
}
// echo "<pre>".print_r($data,true)."</pre>";
require('GANTT/gantti/gantti.php');
date_default_timezone_set('UTC');
setlocale(LC_ALL, 'es_ES');

if (count($data1)<=0) {
	echo "<span class='lvtHeaderText'>No se encontraron datos!</span>";
} else {

		$gantti = new Gantti($data1, array(
		  'title'      => '',
		  'cellwidth'  => 5,
		  'cellheight' => 35,
		  'today'      => true
		));
		echo $gantti;
}

?>
					</td></tr></table>
			</td></tr>
			</table>
			</form>
</td></tr>
</table>
<style>
.proyectinfo{
	position:absolute;
	width:500px;
	height:250px;
	margin-top:-125px;
	margin-left:-250px;
	top:50%;
	left:50%;
	background:#fff;
	display:none;
	z-index: 1001;
	border: 1px solid #ccc;
	-moz-border: 1px solid #ccc;
	-ms-border: 1px solid #ccc;
	border-radius: 5px;
	-moz-border-radius: 5px;
	-ms-border-radius: 5px;
}
.addshadow{
	-moz-box-shadow:     -2px 4px 12px 0px #999;
	-webkit-box-shadow:  -2px 4px 12px 0px #999;
	box-shadow:          -2px 4px 12px 0px #999;
}
.img_close{
	position: absolute;
	right: -15px;
	top: -13px;
	cursor:pointer;
}
#proyectinfo{
	padding:10px
}
</style>
<script>
jQuery(".clicktitle").click(function() {
  jQuery("#proyectinfo").html(jQuery(jQuery(this).attr('href')).html());
  jQuery("#proyectinfocontent").fadeIn();
});
function openProyect(proyectid){
	jQuery("#proyectinfocontent").fadeIn();
}
</script>
<?
foreach($todivs as $poryid => $proydat){
?>
<div id="<?=$poryid?>" style="display:none">
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td width="1%">Titulo</td><td><?=$proydat['titulo']?></td>
		</tr>
		<tr>
			<td>Inicia</td><td><?=$proydat['fechainicial']?></td>
		</tr>
		<tr>
			<td>Termina</td><td><?=$proydat['fechafinal']?></td>
		</tr>
		<tr>
			<td>Descripcion</td><td><?=$proydat['descripcion']?></td>
		</tr>
		<tr>
			<td colspan="2">
				<ul>
				<?
				foreach($proydat['hitos'] as $hit){
				//"<pre>".print_r($hit['label'],true)."</pre>";
				?>
					<li><?=$hit['label']?></li>
				<?
				}
				?>
				</ul>
			</td>
		</tr>
	</table>
</div>
<?
}
?>
	<div id="proyectinfocontent" class="proyectinfo addshadow">
		<img class="img_close" src="images/ico-close.png" onclick="jQuery('#proyectinfocontent').fadeOut();"/>
		<div id="proyectinfo" style="width:100%"></div>
	</div>
