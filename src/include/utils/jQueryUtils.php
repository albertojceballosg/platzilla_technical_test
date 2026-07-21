<?php
/*********************************************************************************
 * jQueryUtils.php,v 1.00 2013/04/02 1
 * Desarrollador:  Leonardo Castillo Lacruz (LCL)
 * Fecha de Creaci�n: 02/04/2013
 * Requerimiento: Archivo de funciones comunes utilizando plataforma jQuery como API
 * Tipo :  Propiedad de Timemanagement_
 ********************************************************************************/

/** Funcion que retorna un buffer con el codigo HTML/JS necesario para escribir un dialogo modal retractil
  * @id -- nombre del control o dialogo:: Tipo cadena
  * @textoDlg -- Contenido a mostrar dentro del dialogo, codigo HTML:: Tipo cadena
  * @ancho -- Ancho del dialogo en pixels:: Tipo entero
  * @maximaAltura -- M�xima altura del dialogo en pixels:: Tipo entero
  *
*/

 function escribeDlgModal($id,$textoDlg = '', $ancho = 1000,$maximaAltura = 450, $left = 50, $top = 50, $funcionCerrar = '',$openNow = false,$bBtnCerrar = true,$title = '') {
	global $bDlgModales, $currentModule;
	/*
	* Porque el cambio...
	* Si el top y el left lo ponemos en 50%, para que el popup se centre en la pantalla perfectamente
	* se debe hacer un margin-left de (-) la mitad del ancho y un margin-top de (-) la mitad del alto del popup.
	* Ej: width: 1000px; height:450px; top:50%; left: 50%; margin-left: -500px; margin-top: -250px;
	* El margin-left se le suma 40 por el padding que tiene la clase ed-Al-Fo
	*/

	$bDlgModales = true;

	$clearmd = '';
	/*[ TT11223 ] Migrar funcionalidad “Administrador de Módulos” a un módulo simple
	 * DM 25/07/2016
	* Se agrega módulo gestion_module ya que el comportamiento debe ser el mismo que en el módulo Settings
	*/
	if($currentModule == 'Settings' || $currentModule == 'gestion_module'){
		$clearmd ="onclick=\"window.location.href='index.php?module={$currentModule}&action=ModuleManager&parenttab=Settings'\"";
	}

	if ($_SESSION['vtiger_authenticated_user_theme'] == 'centaurus') {
		$bufferSalida = '
			<div class="md-modal md-effect-1" id="'.$id.'" style="max-width:'.$ancho.'px;width:'.$ancho.'px;max-height:600px;overflow:auto;">
				<div class="md-content">
					<div class="modal-header">
						<button class="md-close close" '.$clearmd.'>&times;</button>
						<h4 class="modal-title">'.$title.'</h4>
					</div>
					<div class="modal-body">
						<div id="texto'.$id.'">
						'.$textoDlg.'
						</div>
					</div>
				</div>
			</div>';
	} else {

		if ($bBtnCerrar)
			$htmlCerrar = '<a href="javascript:void(0)" class="close-editor" onclick="jQuery(\'#'.$id.'\').slideUp(function(){OpenClosecortina();'.$funcionCerrar.'});"></a>';

		$bufferSalida = '
			<div id="'.$id.'" class="ed-Al-Fo shadow-this" style="width:'.$ancho.'px; position:fixed; left:'.$left.'%; top:'.$top.'%;margin-left:-'.(($ancho/2)+40).'px;margin-top:-'.($maximaAltura/2).'px; z-index:89990; max-height:'.$maximaAltura.'px; overflow:auto;">'.
				$htmlCerrar.'
				<div id="texto'.$id.'">
				'.$textoDlg.'
				</div>
			</div>
		';

		if ($openNow) {
			$bufferSalida.= '
			<script type="text/javascript">
				jQuery( document ).ready(function() {
				  jQuery(\'#'.$id.'\').slideDown(OpenClosecortina());
				});
			</script>';
		}
	}

	return $bufferSalida;
 }

 function escribeDlgError($id){
	$bufferSalida='
		<div id="'.$id.'" style="display:none; padding:10px; background-color:#FA5858;border:4px solid;border-color:red;width:500px; position:fixed; left:50%; top:50%;margin-left:-250px;margin-top:-100px; height:50px; z-index:89991; -moz-border-radius: 15px;
			border-radius: 0px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; ">
			<p id="in'.$id.'" style="color:#fff;text-align:center"></p>
		</div>
	';
	return $bufferSalida;
 }

 ?>