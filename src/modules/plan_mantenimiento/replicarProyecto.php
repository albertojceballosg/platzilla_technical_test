<?php

	include_once('include/utils/jQueryUtils.php');
	
	global $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;
	
	$url = 'index.php?module=proyectos&action=definirProyecto&Ajax=true&accountid='.$_REQUEST['record'];
	$label = 'Definir par&aacute;metros proyecto';
	
	$idDlgAcciones = "dlgAcciones";
	
	echo '
	<div style="width:100%;margin-left:auto;margin-right:auto;text-align:center;">
	<input class="crmbutton small edit" onclick="jQuery.ajax({url: \''.$url.'\'}).done(function( html ) {jQuery(\'#texto'.$idDlgAcciones.'\').html(html);});jQuery(\'#'.$idDlgAcciones.'\').slideDown(function(){OpenClosecortina();});" type="button" name="Edit" value="'.$label.'">
	</div>
	';
	echo escribeDlgModal($idDlgAcciones,'');
?>