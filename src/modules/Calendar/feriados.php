<?php
$anio=date('Y');
if(isset($_REQUEST['anio']) && $_REQUEST['anio']!=''){
	$anio=$_REQUEST['anio'];
}
$página_inicio = file_get_contents('http://www.mininterior.gov.ar/asuntos_politicos_y_alectorales/dinap/feriados/feriados'.$anio.'.php');
echo str_replace('../../','',$página_inicio);

?>