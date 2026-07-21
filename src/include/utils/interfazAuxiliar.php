<?php
	function escribeSelect($name,$id,$datos,$parametros = '',$bSeleccione = true,$value = '') {
		global $mod_strings;
		$bufferSalida = '<select name="'.$name.'" id="'.$id.'" class="form-control" '.$parametros.'>';
		
		if ($bSeleccione)
			$bufferSalida.= '<option value="">'.$mod_strings['Seleccione'].'</option>';
								
		for($i = 0;$i < count($datos);$i++) {
			$selected = '';
			if ($value == $datos[$i][0])
				$selected = " selected";
			$bufferSalida.= '<option value="'.$datos[$i][0].'"'.$selected.'>'.$datos[$i][1].'</option>';
		}
		$bufferSalida.= '</select>';
		
		return $bufferSalida;
	}
	
	function escribeEntradaTexto($name,$id,$valor,$parametros = '') {
		$bufferSalida = '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$valor.'" class="detailedViewTextBox" '.$parametros.'/>';
		
		return $bufferSalida;
	}
	
	function escribeEntradaCheck($name,$id,$valor,$parametros = '') {
		$bufferSalida = '<input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$valor.'" '.$parametros.'/>';
		
		return $bufferSalida;
	}
	
	function escribeEntradaOculta($name,$id,$valor,$parametros = '') {
		$bufferSalida = '<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$valor.'" '.$parametros.'/>';
		
		return $bufferSalida;
	}
	
	function escribeBotonForma($name,$tipo,$style,$etiqueta,$parametros = '') {
		$bufferSalida = '<button type="'.$tipo.'" name="'.$name.'" class="'.$style.'" '.$parametros.'>'.$etiqueta.'</button>';
		
		return $bufferSalida;
	}
?>