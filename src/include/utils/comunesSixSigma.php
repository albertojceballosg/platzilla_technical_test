<?php
/* comunesSixSigma.php:
	Archivo que implementa las funciones comunes de calculo de factores para la tecnica de SixSigma

	Fecha: 2014/08/06
	Leonardo Castillo
	© TimeManagement
*/
	define('D2',1.128);
	define('D3',0);
	define('D4',3.267);
	define('SIGMA1',1);
	define('SIGMA2',2);
	define('SIGMA3',3);

	function calculaParametrosVariable($variablesid,$inidate,$enddate = '',$lstParameters = '') {

		$datavariable = obtieneVariableSixSigma($variablesid);

		$variable = $datavariable['name'];
		$variableLabel = $datavariable['label'];

		$adb = conectaPlataformaHija($_SESSION['plat'],DB_DT_SS);

		if (empty($enddate))
			$enddate = date('Y-m-d');
		if (is_array($lstParameters)) {
			foreach ($lstParameters as $parameter)
			$aditionalCondition.= " AND ".$parameter['name']." = '".$parameter['value']."'";
		}
		$sql = "SELECT date,value FROM ss_$variable WHERE date >= '$inidate' AND date <= '$enddate' ".$aditionalCondition." ORDER BY date";

		$result = $adb->query($sql);

		$previousValue = '';
		$i = 0;
		$j = 0;
		$sumMRi = 0;
		$sumX = 0;
		while($row = $adb->fetchByAssoc($result)) {
			if ($previousValue != '') {
				$sumMRi+=abs($previousValue-$row['value']);
				$data['valuesMR'][] = abs($previousValue-$row['value']);
				$i++;
			}
			$previousValue = $row['value'];
			$sumX+=$row['value'];
			$j++;
			$data['values'][] = $row['value'];
			$data['date'][] = $row['date'];
		}
		$XProm = $sumX / $j;
		$XMRiProm = $sumMRi / $i;
		$desviacion = $XMRiProm/D2;

		$LCS = $XProm+SIGMA3*$desviacion;
		$LCI = $XProm-SIGMA3*$desviacion;

		$ZonaBMas = $XProm+SIGMA2*$desviacion;
		$ZonaBMenos = $XProm-SIGMA2*$desviacion;

		$ZonaCMas = $XProm+SIGMA1*$desviacion;
		$ZonaCMenos = $XProm-SIGMA1*$desviacion;

		$LCSMR = $XMRiProm*D4;
		$LCIMR = $XMRiProm*D3;

		$data['XProm'] = $XProm;
		$data['MRProm'] = $XMRiProm;
		$data['LCS'] = $LCS;
		$data['LCI'] = $LCI;
		$data['ZonaBMas'] = $ZonaBMas;
		$data['ZonaBMenos'] = $ZonaBMenos;
		$data['ZonaCMas'] = $ZonaCMas;
		$data['ZonaCMenos'] = $ZonaCMenos;
		$data['LCSMR'] = $LCSMR;
		$data['LCIMR'] = $LCIMR;


		$data['variableName'] = $variable;
		$data['variableLabel'] = $variableLabel;

		$adb->disconnect();

		$adb = conectaPlataformaHija($_SESSION['plat'],DB_MAIN);

		return $data;
	}

	function obtenerTextoParametros($parameters) {
		$bufferSalida = '';
		foreach($parameters as $parameter) {
			if (!empty($bufferSalida))
				$bufferSalida.=';';
			$bufferSalida.= $parameter['name'].':'.$_REQUEST[$parameter['name']];
		}
		return $bufferSalida;
	}


	/*
	function registrarNoConformidad($data,$name,$incidente,$descripcion) {
		$cfocus = CRMEntity::getInstance('conformidades');
		$enlace = '';
		foreach($_REQUEST as $key => $value) {
			if (!empty($enlace))
				$enlace.= "&";
			$enlace.= $key.'='.$value;
		}
		$cfocus->column_fields['name'] = $name;
		$cfocus->column_fields['incidente'] = $incidente;
		$cfocus->column_fields['descripcion'] = $descripcion;
		$cfocus->column_fields['clasificacion'] = 'No conformidad';
		$cfocus->column_fields['origen'] = 'Pruebas de Control SixSigma';
		$cfocus->column_fields['assigned_user_id'] = 1;//admin
		$cfocus->column_fields['enlace_sixsigma'] = $enlace;



		$cfocus->save('conformidades');
	}
	*/

	/*
	Realiza la prueba No. 1 de SixSigma.
	Un dato fuera del límite de control
	*/
	function realizaPrueba1($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		for($i = 0;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $data['LCS'] || $data['values'][$i] < $data['LCI']) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$lstFallas[] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
			}
		}
		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_1'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 2 de SixSigma.
	Ocho puntos en forma consecutiva por arriba o por debajo de la línea central (línea base).
	*/
	function realizaPrueba2($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		$puntosContinuosArriba = 0;
		$puntosContinuosAbajo = 0;
		$bUltimoValor = 0;
		$j = 0;

		for($i = 0;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $data['XProm']) {
				if($bUltimoValor < 0) {
					$puntosContinuosArriba = 0;
					$puntosContinuosAbajo = 0;
					unset($lstFallas[$j]);
				}
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
				$puntosContinuosArriba++;
				$bUltimoValor = 1;
			} elseif ($data['values'][$i] < $data['XProm']) {
				if($bUltimoValor > 0) {
					$puntosContinuosArriba = 0;
					$puntosContinuosAbajo = 0;
					unset($lstFallas[$j]);
				}
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
				$puntosContinuosAbajo++;
				$bUltimoValor = -1;
			} else {
				$bUltimoValor = 0;
			}

			if ($puntosContinuosArriba > 8 || $puntosContinuosAbajo > 8) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$j++;
				$puntosContinuosArriba = 0;
				$puntosContinuosAbajo = 0;
			}
		}
		if ($puntosContinuosArriba < 8 || $puntosContinuosAbajo < 8) {
			unset($lstFallas[$j]);
		}
		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_2'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 3 de SixSigma.
	Cinco puntos consecutivos en forma ascendente o descendente.
	*/
	function realizaPrueba3($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		$puntosConsecutivosArriba = 0;
		$puntosConsecutivosAbajo = 0;
		$previusValue = $data['values'][0];
		$bUltimoValor = 0;
		$j = 0;

		for($i = 1;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $previusValue) {
				if($bUltimoValor < 0) {
					$puntosConsecutivosArriba = 0;
					$puntosConsecutivosAbajo = 0;
					unset($lstFallas[$j]);
					$lstFallas[$j][] = array();
				}
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
				$puntosConsecutivosArriba++;
				$bUltimoValor = 1;
			} elseif ($data['values'][$i] < $previusValue) {
				if($bUltimoValor > 0) {
					$puntosConsecutivosArriba = 0;
					$puntosConsecutivosAbajo = 0;
					unset($lstFallas[$j]);
					$lstFallas[$j][] = array();
				}
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
				$puntosConsecutivosAbajo++;
				$bUltimoValor = -1;
			} else {
				$bUltimoValor = 0;
			}
			$previusValue = $data['values'][$i];
			if ($puntosConsecutivosArriba > 5 || $puntosConsecutivosAbajo > 5) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$j++;
			}
		}
		if ($puntosConsecutivosArriba < 5 || $puntosConsecutivosAbajo < 5)
			unset($lstFallas[$j]);

		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_3'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 4 de SixSigma.
	Catorce puntos alternándose en forma consecutiva arriba y abajo.
	*/
	function realizaPrueba4($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		$puntosConsecutivosAlternados = 0;
		$previusValue = $data['values'][0];
		$bUltimoValor = 0;
		$j = 0;

		for($i = 1;$i < count($data['values']);$i++) {
			if (($data['values'][$i] > $previusValue && $bUltimoValor <= 0) || ($data['values'][$i] < $previusValue && $bUltimoValor >= 0)) {
				$puntosConsecutivosAlternados++;
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
			} else {
				$puntosConsecutivosAlternados = 0;
				unset($lstFallas[$j]);
				$lstFallas[$j][] = array();
			}
			if ($data['values'][$i] > $previusValue)
				$bUltimoValor = 1;
			elseif ($data['values'][$i] < $previusValue)
				$bUltimoValor = -1;
			else
				$bUltimoValor = 0;

			$previusValue = $data['values'][$i];
			if ($puntosConsecutivosAlternados > 14) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$j++;
			}
		}
		if ($puntosConsecutivosAlternados < 14)
			unset($lstFallas[$j]);

		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_4'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 5 de SixSigma.
	Tres puntos en la Zona A o más allá
	*/
	function realizaPrueba5($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		$puntosEnLaZonaA = 0;

		for($i = 0;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $data['ZonaBMas'] || $data['values'][$i] < $data['ZonaBMenos']) {
				$puntosEnLaZonaA++;
				$lstFallas[] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
			}

			if ($puntosEnLaZonaA >= 3) {
				if (!$bMostrarTodasLasFallas)
					return false;
			}
		}
		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_5'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 6 de SixSigma.
	Cuatro de cinco puntos consecutivos en la zona B o más allá.
	*/
	function realizaPrueba6($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		$puntosEnZonaB = 0;
		$puntosDentroZonaC = 0;
		$j = 0;

		for($i = 0;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $data['ZonaCMas'] || $data['values'][$i] < $data['ZonaCMenos'])
				$puntosEnZonaB++;
			else
				$puntosDentroZonaC++;
			$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);

			if ($puntosDentroZonaC > 1) {
				$puntosEnZonaB = 0;
				$puntosDentroZonaC = 0;
				unset($lstFallas[$j]);
				$lstFallas[$j][] = array();
			}

			if ($puntosDentroZonaB >= 4 && $puntosDentroZonaC <= 1) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$j++;
			}
		}
		if ($puntosDentroZonaB < 4 || $puntosDentroZonaC > 1) {
			unset($lstFallas[$j]);
		}
		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_6'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 7 de SixSigma.
	Quince puntos consecutivos en la zona C.
	*/
	function realizaPrueba7($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas     = array();
		$puntosEnZonaB = 0;
		$j             = 0;
		$puntosConsecutivosEnZonaC = 0;

		for($i = 0;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $data['ZonaCMenos'] && $data['values'][$i] < $data['ZonaCMas']) {
				$puntosConsecutivosEnZonaC++;
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
			}
			else {
				$puntosConsecutivosEnZonaC = 0;
				unset($lstFallas[$j]);
				$lstFallas[$j][] = array();
			}

			if ($puntosConsecutivosEnZonaC >= 15) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$j++;
			}
		}
		if ($puntosConsecutivosEnZonaC < 15)
			unset($lstFallas[$j]);

		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_7'));
			}
			return false;
		}
		return true;
	}

	/*
	Realiza la prueba No. 8 de SixSigma.
	Ocho puntos consecutivos que no caigan en la zona C.
	*/
	function realizaPrueba8($data,$bMostrarTodasLasFallas = false,$registrarNoConformidad = false) {
		global $lstFallas;

		$lstFallas = array();
		$puntosConsecutivosFueraZonaC = 0;

		for($i = 0;$i < count($data['values']);$i++) {
			if ($data['values'][$i] > $data['ZonaCMas'] || $data['values'][$i] < $data['ZonaCMenos']) {
				$puntosConsecutivosFueraZonaC++;
				$lstFallas[$j][] = array('date'=>$data['date'][$i],'value'=>$data['values'][$i]);
			}
			else {
				$puntosConsecutivosFueraZonaC = 0;
				unset($lstFallas[$j]);
				$lstFallas[$j][] = array();
			}

			if ($puntosConsecutivosFueraZonaC >= 8) {
				if (!$bMostrarTodasLasFallas)
					return false;
				$j++;
			}
		}
		if ($puntosConsecutivosFueraZonaC < 8)
			unset($lstFallas[$j]);
		if (count($lstFallas)>0) {
			if ($registrarNoConformidad) {
				$textoResultado = obtenerTextoResultadoFallas($lstFallas,false);
				registrarNoConformidad($data,$data['variableLabel'].' - '.obtenerTextoParametros($data['parameters']),$textoResultado,getTranslatedString('LBL_PRUEBA_8'));
			}
			return false;
		}
		return true;
	}

	function obtenerTextoResultadoFallas($lstFallas,$bHTML = true) {

		if (count($lstFallas) == 0)
			return;

		if ($bHTML) {
			$bufferSalida = '<table class=&quot;tableHeading&quot;><tr><th>Periodo/Fecha</th><th>Valor de falla</th></tr>';
			foreach($lstFallas as $var => $value) {
				if (!isset($value['date'])) {
					foreach($value as $var2 => $value2)
						$bufferSalida.= '<tr><td class=&quot;small cellLabel&quot;>'.$value2['date'].'</td><td class=&quot;small cellLabel&quot;>'.$value2['value'].'</td></tr>';
				} else
					$bufferSalida.= '<tr><td class=&quot;small cellLabel&quot;>'.$value['date'].'</td><td class=&quot;small cellLabel&quot;>'.$value['value'].'</td></tr>';
			}
			$bufferSalida.= '</table>';
		} else {
			$bufferSalida = "Valores con falla:\n";
			foreach($lstFallas as $var => $value) {
				if (!isset($value['date'])) {
					foreach($value as $var2 => $value2)
						$bufferSalida.= $value2['date'].':'.$value2['value']."\n";
				} else
					$bufferSalida.= $value['date'].':'.$value['value']."\n";
			}
		}

		return $bufferSalida;
	}

	function escribeResultadoPruebasSixSigma($data) {
		global $lstFallas;
		$theme = $_SESSION['vtiger_authenticated_user_theme'];
		$bufferSalida = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<style type="text/css">@import url("themes/'.$theme.'/style.css?v=5.4.0");</style>
		<script language="JavaScript" type="text/javascript" src="include/js/general.js?v=5.4.0"></script>

		</head>
		<body>
		<table width="100%" cellspacing="0" cellpadding="5" border="0" class="tableHeading">
			<tbody><tr>
				<td class="big"><strong>Resultado de las pruebas de control </strong></td>
			</tr>
			</tbody>
		</table>
		</body>
		</html>
		';

		$registrarConformidad = false;

		if (realizaPrueba1($data,true,$registrarConformidad)) {
			$resultado1 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado1 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado1 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado1.'\');"';
			$resultado1 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba2($data,true,$registrarConformidad)) {
			$resultado2 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado2 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado2 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado2.'\');"';
			$resultado2 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba3($data,true,$registrarConformidad)) {
			$resultado3 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado3 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado3 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado3.'\');"';
			$resultado3 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba4($data,true,$registrarConformidad)) {
			$resultado4 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado4 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado4 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado4.'\');"';
			$resultado4 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba5($data,true,$registrarConformidad)) {
			$resultado5 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado5 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado5 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado5.'\');"';
			$resultado5 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba6($data,true,$registrarConformidad)) {
			$resultado6 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado6 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado6 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado6.'\');"';
			$resultado6 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba7($data,true,$registrarConformidad)) {
			$resultado7 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado7 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado7 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado7.'\');"';
			$resultado7 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		if (realizaPrueba8($data,true,$registrarConformidad)) {
			$resultado8 = '<img src="'.vtiger_imageurl('ok.png',$theme).'">';
		} else {
			$textoResultado8 = obtenerTextoResultadoFallas($lstFallas);
			$textoResultado8 = 'onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, \''.$textoResultado8.'\');"';
			$resultado8 = '<img src="'.vtiger_imageurl('cancel.png',$theme).'">';
		}

		$bufferSalida.= '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="listRow" >
			<tbody><tr>
				<td valign="top" class="small"><table width="100%" cellspacing="0" cellpadding="5" border="0">
				  <tbody><tr>
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_1').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado1.'>'.$resultado1.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_2').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado2.'>'.$resultado2.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_3').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado3.'>'.$resultado3.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_4').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado4.'>'.$resultado4.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_5').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado5.'>'.$resultado5.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_6').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado6.'>'.$resultado6.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_7').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado7.'>'.$resultado7.'</td>
				  </tr>
				  <tr valign="top">
					<td width="60%" class="small cellLabel"><strong>'.getTranslatedString('LBL_PRUEBA_8').'</strong></td>
					<td width="40%" class="small cellText" '.$textoResultado8.'>'.$resultado8.'</td>
				  </tr>
				</tbody></table>

				</td>
			  </tr>
			</tbody>
		</table>';

		return $bufferSalida;
	}

	function escribeGraficoControlX($data) {

		$seriesLCS = '';
		$seriesLCI = '';
		$ejeX = '';
		$seriesX = '';
		$seriesXProm = '';
		$seriesMR = '';
		$seriesMRProm = '';
		$seriesZonaBMas = '';
		$seriesZonaBMenos = '';
		$seriesZonaCMas = '';
		$seriesZonaCMenos = '';

		for($i = 0;$i < count($data['values']);$i++) {
			if (!empty($ejeX))
				$ejeX.= ',';
			$ejeX.= "'".($i+1)."'";

			if (!empty($seriesLCS))
				$seriesLCS.= ',';
			$seriesLCS.= $data['LCS'];

			if (!empty($seriesLCI))
				$seriesLCI.= ',';
			$seriesLCI.= $data['LCI'];

			if (!empty($seriesZonaBMas))
				$seriesZonaBMas.= ',';
			$seriesZonaBMas.= $data['ZonaBMas'];

			if (!empty($seriesZonaBMenos))
				$seriesZonaBMenos.= ',';
			$seriesZonaBMenos.= $data['ZonaBMenos'];

			if (!empty($seriesZonaCMas))
				$seriesZonaCMas.= ',';
			$seriesZonaCMas.= $data['ZonaCMas'];

			if (!empty($seriesZonaCMenos))
				$seriesZonaCMenos.= ',';
			$seriesZonaCMenos.= $data['ZonaCMenos'];

			if (!empty($seriesX))
				$seriesX.= ',';
			$seriesX.= $data['values'][$i];

			if (!empty($seriesXProm))
				$seriesXProm.= ',';
			$seriesXProm.= $data['XProm'];

			if (!empty($seriesMR))
				$seriesMR.= ',';
			$seriesMR.= $data['valuesMR'][$i];

			if (!empty($seriesMRProm))
				$seriesMRProm.= ',';
			$seriesMRProm.= $data['MRProm'];

			if (!empty($seriesLCSMR))
				$seriesLCSMR.= ',';
			$seriesLCSMR.= $data['LCSMR'];

			if (!empty($seriesLCIMR))
				$seriesLCIMR.= ',';
			$seriesLCIMR.= $data['LCIMR'];
		}

		$bufferSalida = "
		<script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js\"></script>
		<script type=\"text/javascript\">
		$(function () {
			$('#containerControlX".$data['variableName']."').highcharts({
            title: {
                text: 'Control X',
                x: -20 //center
            },
            xAxis: {
				title: {
                    text: 'Período'
                },
                categories: [".$ejeX."]
            },
            yAxis: {
                title: {
                    text: '".$data['variableLabel']."'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: '%'
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 1
            },
            series: [
			{
                name: 'LCS',
				dashStyle: 'Dash',
				color: 'green',
				data: [".$seriesLCS."],
				marker: {
                    enabled: false
                }
            }, {
                name: 'Zona B+',
                data: [".$seriesZonaBMas."],
				dashStyle: 'LongDashDot',
				color: 'gray',
				marker: {
                    enabled: false
                }
            }, {
                name: 'Zona C+',
				dashStyle: 'LongDashDot',
                data: [".$seriesZonaCMas."],
				color: '#D8D8D8',
				marker: {
                    enabled: false
                }

            }, {
				name: 'Línea Base',
				color: '#FFBF00',
				data: [".$seriesXProm."],
				marker: {
                    enabled: false
                }
            }, {
                name: 'Valor alcanzado X',
                data: [".$seriesX."]
            }, {
                name: 'Zona C-',
				dashStyle: 'LongDashDot',
                data: [".$seriesZonaCMenos."],
				color: '#D8D8D8',
				marker: {
                    enabled: false
                }
            }, {
                name: 'Zona B-',
                data: [".$seriesZonaBMenos."],
				dashStyle: 'LongDashDot',
				color: 'gray',
				marker: {
                    enabled: false
                }
            }, {
                name: 'LCI',
				dashStyle: 'Dash',
				color: 'brown',
				data: [".$seriesLCI."],
				marker: {
                    enabled: false
                }
            } ]
        });
    });

	$(function () {
			$('#containerControlMR".$data['variableName']."').highcharts({
            title: {
                text: 'Control MR',
                x: -20 //center
            },
            xAxis: {
				title: {
                    text: 'Período'
                },
                categories: [".$ejeX."]
            },
            yAxis: {
                title: {
                    text: '".$data['variableLabel']."'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: '%'
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 1
            },
            series: [
			{
                name: 'LCS',
				dashStyle: 'Dash',
				color: 'green',
				data: [".$seriesLCSMR."],
				marker: {
                    enabled: false
                }
            }, {
				name: 'Línea Base',
				color: '#FFBF00',
				data: [".$seriesMRProm."],
				marker: {
                    enabled: false
                }
            }, {
                name: 'Valor alcanzado MR',
                data: [".$seriesMR."]
            }, {
                name: 'LCI',
				dashStyle: 'Dash',
				color: 'brown',
				data: [".$seriesLCIMR."],
				marker: {
                    enabled: false
                }
            } ]
        });
    });


		</script>

		<script src=\"include/js/highcharts/js/highcharts.js\"></script>
		<script src=\"include/js/highcharts/js/modules/exporting.js\"></script>

		<div id=\"containerControlX".$data['variableName']."\" style=\"min-width: 310px; height: 400px; margin: 0 auto\"></div>
		<div id=\"tablaPruebasControl".$data['variableName']."\" style=\"width: 100%; margin: 0 auto\">".
		escribeResultadoPruebasSixSigma($data)."
		</div>
		<div id=\"containerControlMR".$data['variableName']."\" style=\"min-width: 310px; height: 400px; margin: 0 auto\"></div>";

		return $bufferSalida;
	}



	function obtieneListaModulosGraficos(){
		global $adb;

		$sql = "SELECT DISTINCT vtiger_tab.tabid, vtiger_tab.tablabel FROM vtiger_tab INNER JOIN vtiger_graficos ON (vtiger_tab.name = vtiger_graficos.fld_module)
			ORDER BY vtiger_tab.tablabel";

		$result = $adb->query($sql);

		while($row = $adb->fetch_array($result)) {
			$lst[] = $row;
		}
		return $lst;
	}

	function obtieneListaModulosVarSixSigma() {
		global $adb;

		$sql = "SELECT vtiger_tab.tabid, vtiger_tab.tablabel FROM vtiger_tab INNER JOIN vtiger_ss_variables ON (vtiger_tab.tabid = vtiger_ss_variables.tabid)
			ORDER BY vtiger_tab.tablabel";

		$result = $adb->query($sql);

		while($row = $adb->fetch_array($result)) {
			$lst[] = $row;
		}
		return $lst;
	}


	function getListaBoxScore(){

		global $adb;

		$sql = "SELECT boxscoreid,titulo FROM vtiger_boxscore bs join vtiger_crmentity crm on (crm.crmid = bs.boxscoreid) WHERE deleted = 0 ";
		$result = $adb->pquery($sql,array());

		$lst = array();
		while($row = $adb->fetchByAssoc($result)) {
			$lst[$row['boxscoreid']] = $row['titulo'];
		}
		return $lst;

	}



	function getIndicadoresBS($boxscoreid){
		global $adb;

		$sql = "SELECT box_score_dataid,box_score FROM vtiger_box_score_data WHERE boxscoreid = ? ";
		$result = $adb->pquery($sql,array($boxscoreid));

		$lst = array();
		while($row = $adb->fetchByAssoc($result)) {
			$lst[$row['box_score_dataid']] = html_entity_decode($row['box_score']);
		}
		return $lst;
	}

	function getDescripctionIndicadorBS($boxscoredataid){
		global $adb;

		$sql = "SELECT box_score FROM vtiger_box_score_data WHERE box_score_dataid = ? ";
		$result = $adb->pquery($sql, array($boxscoredataid));
		return html_entity_decode($adb->query_result($result, 0, "box_score"));
	}


	function getDescripctionBS($boxscoreid){
		global $adb;

		$sql = "SELECT titulo FROM vtiger_boxscore WHERE boxscoreid = ? ";
		$result = $adb->pquery($sql, array($boxscoreid));
		return html_entity_decode($adb->query_result($result, 0, "titulo"));
	}


	/*
	getAlertaLSSI: get alerts' array data for index (ListView)
	*/
	function getAlertasLSSI(){

		global $adb;

		$sql = "SELECT * FROM vtiger_alertas WHERE origendatos = 'boxscore' ";
		$result = $adb->pquery($sql,array());

		$lst = array();
		while($row = $adb->fetchByAssoc($result)) {
			$row['titulo'] = html_entity_decode($row['titulo']);
			$row['tituloindicadorboxscore'] = getDescripctionIndicadorBS($row['indicadorboxscore']);
			$row['tituloboxscore'] = getDescripctionBS($row['boxscoreid']);
			$lst[] = $row;
		}
		return $lst;

	}

	/*
	getAlertaLSSI: get alert's data for DetailView
	*/
	function getAlertaLSSI($alertaid){

		global $adb;

		$sql = "SELECT * FROM vtiger_alertas WHERE alertasid = ?";
		$result = $adb->pquery($sql,array($alertaid));
		$data = array();
		while($row = $adb->fetchByAssoc($result)) {
			$row['titulo'] = html_entity_decode($row['titulo']);
			$row['tituloindicadorboxscore'] = getDescripctionIndicadorBS($row['indicadorboxscore']);
			$row['tituloboxscore'] = getDescripctionBS($row['boxscoreid']);
			//$row['emailsid'] = explode('#', $row['emailsid']);
			$data = $row;
		}

		return $data;
	}


	function obtieneListaUsuariosConEmail() {
	global $adb;

	$sql = "SELECT id, CONCAT(last_name,', ',first_name), email1 FROM vtiger_users  WHERE status = 'Active' and email1 <> '' ORDER BY 2";

	$result = $adb->query($sql);

	while ($result && $row = $adb->fetch_row($result)) {
		$registros[] = $row;
	}
	return $registros;
}

/*
getAlertDataForNC: get alerts' data to create noconformity. It's used by BoxScoreAlertsCronJob
*/
function getAlertDataForNC(){

	global $adb;

	$query = " SELECT * ,
			(
			    CASE periodicidad
			    WHEN 'Anual' THEN (CURRENT_DATE - INTERVAL 12 MONTH)
			    WHEN 'Semestral' THEN (CURRENT_DATE - INTERVAL 6 MONTH)
			    WHEN 'Trimestral' THEN (CURRENT_DATE - INTERVAL 3 MONTH)
			    WHEN 'Mensual' THEN (CURRENT_DATE - INTERVAL 1 MONTH)
			    WHEN 'Quincenal' THEN (CURRENT_DATE - INTERVAL 15 DAY)
			    WHEN 'Semanal' THEN (CURRENT_DATE - INTERVAL 1 WEEK)
			    WHEN 'Diario' THEN (CURRENT_DATE - INTERVAL 1 DAY)
			    END
			) AS fecha_revision
			FROM `vtiger_alertas` al join vtiger_boxscore bx on (bx.boxscoreid = al.boxscoreid)
            join vtiger_crmentity crm on (crm.crmid = bx.boxscoreid)
			WHERE (
			    ultimaactualizacion is null
			    or (ultimaactualizacion >=
			    	(
					    CASE periodicidad
					    WHEN 'Anual' THEN (CURRENT_DATE - INTERVAL 12 MONTH)
					    WHEN 'Semestral' THEN (CURRENT_DATE - INTERVAL 6 MONTH)
					    WHEN 'Trimestral' THEN (CURRENT_DATE - INTERVAL 3 MONTH)
					    WHEN 'Mensual' THEN (CURRENT_DATE - INTERVAL 1 MONTH)
					    WHEN 'Quincenal' THEN (CURRENT_DATE - INTERVAL 15 DAY)
					    WHEN 'Semanal' THEN (CURRENT_DATE - INTERVAL 1 WEEK)
					    WHEN 'Diario' THEN (CURRENT_DATE - INTERVAL 1 DAY)
					    END
						)
					)
				)
				AND origendatos = 'boxscore' AND crearnc = 1 AND enviaremail = 0
				AND crm.deleted = 0";

	$result = $adb->pquery($query,array());

	while($row = $adb->fetchByAssoc($result)) {


		switch ($row['periodicidad']) {
			case 'Diario':
					$intervalo = ' 1 DAY';
				break;
			case 'Semanal':
					$intervalo = ' 1 WEEK';
				break;
			case 'Quincenal':
					$intervalo = ' 15 DAY';
				break;
			case 'Mensual':
					$intervalo = ' 1 MONTH';
				break;
			case 'Trimestral':
					$intervalo = ' 3 MONTH';
				break;
			case 'Semestral':
					$intervalo = ' 6 MONTH';
				break;
			case 'Anual':
					$intervalo = ' 12 MONTH';
				break;

			default:
				# code...
				$intervalo = ' 1 WEEK';
				break;
		}



		$query = "SELECT * FROM vtiger_box_score_data_semanal where boxscoreid = ".$row['boxscoreid']."
			and box_score_dataid = ".$row['indicadorboxscore']."
			AND valor  ".html_entity_decode($row['comparacion_default'])." ".$row['parametro_default']."
			AND fecha > '".$row['ultimaactualizacion']."'" ;

		$result2 = $adb->pquery($query,array());

		$num_rows = $adb->num_rows($result2);
		while($row2 = $adb->fetchByAssoc($result2)) {

			// Crear NC
			$data['titulo'] 		= $row['titulo'].' '.html_entity_decode($row['comparacion_default']).' '.$row['parametro_default'];
			$data['descripcion'] 	= $row['descripcion'].' '.html_entity_decode($row['comparacion_default']).' '.$row['parametro_default'].". Se ha detectado el valor ".$row2['valor'];
			$data['fecha'] 			= $row2['fecha'];
			$data['responsable'] 	= $row['emailsid'];
			$data['origen'] 		= 'Alerta';
			$data['estado'] 		= 'Definida';

			registrarNoConformidad($data);
		}

		updateLastUpdateAlert($row['alertasid']);
	}

}



/*
getAlertDataForNC: get alerts' data to create noconformity and send email to assigned user. It's used by BoxScoreAlertsCronJob
*/
function getAlertDataForNCandSendEmail(){

	global $adb;

	$query = " SELECT * ,
			(
			    CASE periodicidad
			    WHEN 'Anual' THEN (CURRENT_DATE - INTERVAL 12 MONTH)
			    WHEN 'Semestral' THEN (CURRENT_DATE - INTERVAL 6 MONTH)
			    WHEN 'Trimestral' THEN (CURRENT_DATE - INTERVAL 3 MONTH)
			    WHEN 'Mensual' THEN (CURRENT_DATE - INTERVAL 1 MONTH)
			    WHEN 'Quincenal' THEN (CURRENT_DATE - INTERVAL 15 DAY)
			    WHEN 'Semanal' THEN (CURRENT_DATE - INTERVAL 1 WEEK)
			    WHEN 'Diario' THEN (CURRENT_DATE - INTERVAL 1 DAY)
			    END
			) AS fecha_revision ,
			(SELECT CONCAT(first_name,' ',last_name) FROM vtiger_users u WHERE u.id = emailsid) as nombreresponsable,
			(SELECT email1 FROM vtiger_users u WHERE u.id = emailsid) as emailresponsable,
            (SELECT box_score FROM vtiger_box_score_data bxdata WHERE bxdata.box_score_dataid = al.indicadorboxscore) as tituloindicador,
			bx.titulo as tituloboxscore
			FROM `vtiger_alertas` al join vtiger_boxscore bx on (bx.boxscoreid = al.boxscoreid)
            join vtiger_crmentity crm on (crm.crmid = bx.boxscoreid)
			WHERE (
			    ultimaactualizacion is null
			    or (ultimaactualizacion >=
			    	(
					    CASE periodicidad
					    WHEN 'Anual' THEN (CURRENT_DATE - INTERVAL 12 MONTH)
					    WHEN 'Semestral' THEN (CURRENT_DATE - INTERVAL 6 MONTH)
					    WHEN 'Trimestral' THEN (CURRENT_DATE - INTERVAL 3 MONTH)
					    WHEN 'Mensual' THEN (CURRENT_DATE - INTERVAL 1 MONTH)
					    WHEN 'Quincenal' THEN (CURRENT_DATE - INTERVAL 15 DAY)
					    WHEN 'Semanal' THEN (CURRENT_DATE - INTERVAL 1 WEEK)
					    WHEN 'Diario' THEN (CURRENT_DATE - INTERVAL 1 DAY)
					    END
						)
					)
				)
				AND origendatos = 'boxscore' AND crearnc = 1 AND enviaremail = 1
                AND crm.deleted = 0";

	//echo "<pre>PRIMARIO NC EMAIL ".$query."</pre>";

	$result = $adb->pquery($query,array());

	while($row = $adb->fetchByAssoc($result)) {


		switch ($row['periodicidad']) {
			case 'Diario':
					$intervalo = ' 1 DAY';
				break;
			case 'Semanal':
					$intervalo = ' 1 WEEK';
				break;
			case 'Quincenal':
					$intervalo = ' 15 DAY';
				break;
			case 'Mensual':
					$intervalo = ' 1 MONTH';
				break;
			case 'Trimestral':
					$intervalo = ' 3 MONTH';
				break;
			case 'Semestral':
					$intervalo = ' 6 MONTH';
				break;
			case 'Anual':
					$intervalo = ' 12 MONTH';
				break;

			default:
				# code...
				$intervalo = ' 1 WEEK';
				break;
		}

		$fechaBusqueda = ($row['ultimaactualizacion']) ? $row['ultimaactualizacion'] : $row['fecha_revision'];



		$query = "SELECT * FROM vtiger_box_score_data_semanal where boxscoreid = ".$row['boxscoreid']."
			and box_score_dataid = ".$row['indicadorboxscore']."
			AND valor  ".html_entity_decode($row['comparacion_default'])." ".$row['parametro_default']."
			AND fecha > '".$fechaBusqueda."'" ;


		$result2 = $adb->pquery($query,array());

		$num_rows = $adb->num_rows($result2);

		//echo "<pre>".$query." y cantidadRegistrosAlerta es $num_rows</pre>";
		while($row2 = $adb->fetchByAssoc($result2)) {

			// Crear NC
			$data['titulo'] 		= $row['titulo'].' '.html_entity_decode($row['comparacion_default']).' '.$row['parametro_default'];
			$data['descripcion'] 	= $row['descripcion'].' '.html_entity_decode($row['comparacion_default']).' '.$row['parametro_default'].". Se ha detectado el valor ".$row2['valor'];
			$data['fecha'] 			= $row2['fecha'];
			$data['responsable'] 	= $row['emailsid'];
			$data['origen'] 		= 'Alerta';
			$data['estado'] 		= 'Definida';

			$noconformidadID = registrarNoConformidad($data);

			// data for send email
			/*
			CUSTOM1 titulo alerta
			CUSTOM2 responsable
			CUSTOM3 indicador
			CUSTOM4 boxscore
			CUSTOM5 comparador
			CUSTOM6 parametro
			CUSTOM7 valor detectado
			CUSTOM8 fecha
			*/
			$customVars['CUSTOM_CUSTOM1']	= $row['titulo'].' '.html_entity_decode($row['comparacion_default']).' '.$row['parametro_default'];
			$customVars['CUSTOM_CUSTOM2']	= $row['nombreresponsable'];
			$customVars['CUSTOM_CUSTOM3']	= $row['tituloindicador'];
			$customVars['CUSTOM_CUSTOM4']	= '<a href="http://'.$_SERVER['HTTP_HOST'].'/index.php?module=boxscore&action=DetailView&record='.$row['boxscoreid'].'">'.$row['tituloboxscore'].'</a>';
			$customVars['CUSTOM_CUSTOM5']	= html_entity_decode($row['comparacion_default']);
			$customVars['CUSTOM_CUSTOM6']	= $row['parametro_default'];
			$customVars['CUSTOM_CUSTOM7']	= $row2['valor'];
			$customVars['CUSTOM_CUSTOM8']	= $row2['fecha'];
			$customVars['CUSTOM_CUSTOM9']	= '<a href="http://'.$_SERVER['HTTP_HOST'].'/index.php?module=noconformidad&action=DetailView&record='.$noconformidadID.'">'.$data['titulo'].'</a>';
			$customVars['toName'] 			= $row['nombreresponsable'];
			$customVars['email'] 			= $row['emailresponsable'];


			sendAlertEmail($eventcode=103, $language='Español',$customVars, $attachment = null);

		}

		updateLastUpdateAlert($row['alertasid']);

	}


}





/*
getAlertDataForNC: get alerts' data to send email to assigned user. It's used by BoxScoreAlertsCronJob
*/
function getAlertDataForSendEmail(){

	global $adb;

	$query = " SELECT * ,
			(
			    CASE periodicidad
			    WHEN 'Anual' THEN (CURRENT_DATE - INTERVAL 12 MONTH)
			    WHEN 'Semestral' THEN (CURRENT_DATE - INTERVAL 6 MONTH)
			    WHEN 'Trimestral' THEN (CURRENT_DATE - INTERVAL 3 MONTH)
			    WHEN 'Mensual' THEN (CURRENT_DATE - INTERVAL 1 MONTH)
			    WHEN 'Quincenal' THEN (CURRENT_DATE - INTERVAL 15 DAY)
			    WHEN 'Semanal' THEN (CURRENT_DATE - INTERVAL 1 WEEK)
			    WHEN 'Diario' THEN (CURRENT_DATE - INTERVAL 1 DAY)
			    END
			) AS fecha_revision ,
			(SELECT CONCAT(first_name,' ',last_name) FROM vtiger_users u WHERE u.id = emailsid) as nombreresponsable,
			(SELECT email1 FROM vtiger_users u WHERE u.id = emailsid) as emailresponsable,
            (SELECT box_score FROM vtiger_box_score_data bxdata WHERE bxdata.box_score_dataid = al.indicadorboxscore) as tituloindicador,
			bx.titulo as tituloboxscore
			FROM `vtiger_alertas` al join vtiger_boxscore bx on (bx.boxscoreid = al.boxscoreid)
            join vtiger_crmentity crm on (crm.crmid = bx.boxscoreid)
			WHERE (
			    ultimaactualizacion is null
			    or (ultimaactualizacion >=
			    	(
					    CASE periodicidad
					    WHEN 'Anual' THEN (CURRENT_DATE - INTERVAL 12 MONTH)
					    WHEN 'Semestral' THEN (CURRENT_DATE - INTERVAL 6 MONTH)
					    WHEN 'Trimestral' THEN (CURRENT_DATE - INTERVAL 3 MONTH)
					    WHEN 'Mensual' THEN (CURRENT_DATE - INTERVAL 1 MONTH)
					    WHEN 'Quincenal' THEN (CURRENT_DATE - INTERVAL 15 DAY)
					    WHEN 'Semanal' THEN (CURRENT_DATE - INTERVAL 1 WEEK)
					    WHEN 'Diario' THEN (CURRENT_DATE - INTERVAL 1 DAY)
					    END
						)
					)
				)
				AND origendatos = 'boxscore' AND crearnc = 0 AND enviaremail = 1
                AND crm.deleted = 0";


	$result = $adb->pquery($query,array());

	while($row = $adb->fetchByAssoc($result)) {


		switch ($row['periodicidad']) {
			case 'Diario':
					$intervalo = ' 1 DAY';
				break;
			case 'Semanal':
					$intervalo = ' 1 WEEK';
				break;
			case 'Quincenal':
					$intervalo = ' 15 DAY';
				break;
			case 'Mensual':
					$intervalo = ' 1 MONTH';
				break;
			case 'Trimestral':
					$intervalo = ' 3 MONTH';
				break;
			case 'Semestral':
					$intervalo = ' 6 MONTH';
				break;
			case 'Anual':
					$intervalo = ' 12 MONTH';
				break;

			default:
				# code...
				$intervalo = ' 1 WEEK';
				break;
		}



		$query = "SELECT * FROM vtiger_box_score_data_semanal where boxscoreid = ".$row['boxscoreid']."
			and box_score_dataid = ".$row['indicadorboxscore']."
			AND valor  ".html_entity_decode($row['comparacion_default'])." ".$row['parametro_default']."
			AND fecha > '".$row['ultimaactualizacion']."'" ;

		$result2 = $adb->pquery($query,array());

		$num_rows = $adb->num_rows($result2);
		while($row2 = $adb->fetchByAssoc($result2)) {

			// data for send email
			/*
			CUSTOM1 titulo alerta
			CUSTOM2 responsable
			CUSTOM3 indicador
			CUSTOM4 boxscore
			CUSTOM5 comparador
			CUSTOM6 parametro
			CUSTOM7 valor detectado
			CUSTOM8 fecha
			*/
			$customVars['CUSTOM_CUSTOM1']	= $row['titulo'].' '.html_entity_decode($row['comparacion_default']).' '.$row['parametro_default'];
			$customVars['CUSTOM_CUSTOM2']	= $row['nombreresponsable'];
			$customVars['CUSTOM_CUSTOM3']	= $row['tituloindicador'];
			$customVars['CUSTOM_CUSTOM4']	= '<a href="http://'.$_SERVER['HTTP_HOST'].'/index.php?module=boxscore&action=DetailView&record='.$row['boxscoreid'].'">'.$row['tituloboxscore'].'</a>';
			$customVars['CUSTOM_CUSTOM5']	= html_entity_decode($row['comparacion_default']);
			$customVars['CUSTOM_CUSTOM6']	= $row['parametro_default'];
			$customVars['CUSTOM_CUSTOM7']	= $row2['valor'];
			$customVars['CUSTOM_CUSTOM8']	= $row2['fecha'];
			$customVars['toName'] 			= $row['nombreresponsable'];
			$customVars['email'] 			= $row['emailresponsable'];


			sendAlertEmail($eventcode=102, $language='Español',$customVars, $attachment = null);

		}

		updateLastUpdateAlert($row['alertasid']);
	}

}



/*
sendAlertEmail:  send email about Alerts to assigned user. It's used by BoxScoreAlertsCronJob
*/
	function sendAlertEmail ($eventcode, $language, $customVars, $attachment = null) {
		return true;
	}


/*
updateLastUpdateAlert:  Register the date has run the last alert's update by cronjob. It's used by BoxScoreAlertsCronJob
*/
function updateLastUpdateAlert($alertasid){

	global $adb;


	$query = "UPDATE vtiger_alertas SET ultimaactualizacion = CURRENT_DATE WHERE alertasid = ? ";
	$result = $adb->pquery($query,array($alertasid));

}


/*
registrarNoConformidad:  Register a noconformity based on alerts. It's used by BoxScoreAlertsCronJob
*/
function registrarNoConformidad($data) {

	include('modules/noconformidad/noconformidad.php');

	$cfocus = CRMEntity::getInstance('noconformidad');

	$cfocus->column_fields['titulo'] 			= $data['titulo'];
	$cfocus->column_fields['descripcion'] 		= $data['descripcion'];
	$cfocus->column_fields['fecha'] 			= $data['fecha'];
	$cfocus->column_fields['responsable'] 		= $data['responsable'];
	$cfocus->column_fields['origen'] 			= $data['origen'];
	$cfocus->column_fields['estado'] 			= $data['estado'];
	$cfocus->column_fields['assigned_user_id'] 	= $data['responsable'];    //1; // admin

	$cfocus->save('noconformidad');

	echo "<pre>SE REGISTRO LA NO CONFORMIDAD ".$cfocus->id."</pre>";

	return $cfocus->id;
}


/*
getDataAlertasLSSI: alerts' array data for module listView. It's used by module alertas.
*/
function getDataAlertasLSSI(){

	global $adb;

	$sql = "SELECT * FROM vtiger_alertas WHERE origendatos = 'boxscore' ";
	$result = $adb->pquery($sql,array());


	$lst = array();

	while($row = $adb->fetchByAssoc($result)) {

		switch ($row['periodicidad']) {
			case 'Diario':
					$intervalo = ' 1 DAY';
				break;
			case 'Semanal':
					$intervalo = ' 1 WEEK';
				break;
			case 'Quincenal':
					$intervalo = ' 15 DAY';
				break;
			case 'Mensual':
					$intervalo = ' 1 MONTH';
				break;
			case 'Trimestral':
					$intervalo = ' 3 MONTH';
				break;
			case 'Semestral':
					$intervalo = ' 6 MONTH';
				break;
			case 'Anual':
					$intervalo = ' 12 MONTH';
				break;

			default:
				# code...
				$intervalo = ' 1 WEEK';
				break;
		}


		$dataSemanal = array();
		$query = "SELECT * FROM vtiger_box_score_data_semanal where boxscoreid = ".$row['boxscoreid']."
			and box_score_dataid = ".$row['indicadorboxscore']."
			AND valor  ".html_entity_decode($row['comparacion_default'])." ".$row['parametro_default']."
			AND fecha > (CURRENT_DATE - INTERVAL $intervalo)" ;

		$result2 = $adb->pquery($query,array());
		$num_rows = $adb->num_rows($result2);
		while($row2 = $adb->fetchByAssoc($result2)) {
			$dataSemanal[] = $row2;
		}


		$row['cantidadRegistrosAlerta'] = $num_rows;
		$row['data'] = $dataSemanal;
		$row['descripcion'] = html_entity_decode($row['descripcion'], ENT_COMPAT, 'UTF-8');
		$row['titulo'] = html_entity_decode($row['titulo']);
		$row['tituloindicadorboxscore'] = getDescripctionIndicadorBS($row['indicadorboxscore']);
		$row['tituloboxscore'] = getDescripctionBS($row['boxscoreid']);


		$lst[] = $row;


	}
	return $lst;

}



	/*


	function getDataAlertasLSSI(){

		global $adb;

		$sql = "SELECT * FROM vtiger_alertas WHERE origendatos = 'boxscore' ";
		$result = $adb->pquery($sql,array());


		$lst = array();

		while($row = $adb->fetchByAssoc($result)) {

			switch ($row['periodicidad']) {
				case 'Diario':
						$intervalo = ' 1 DAY';
					break;
				case 'Semanal':
						$intervalo = ' 1 WEEK';
					break;
				case 'Quincenal':
						$intervalo = ' 15 DAY';
					break;
				case 'Mensual':
						$intervalo = ' 1 MONTH';
					break;
				case 'Trimestral':
						$intervalo = ' 3 MONTH';
					break;
				case 'Semestral':
						$intervalo = ' 6 MONTH';
					break;
				case 'Anual':
						$intervalo = ' 12 MONTH';
					break;

				default:
					# code...
					$intervalo = ' 1 WEEK';
					break;
			}


			$dataSemanal = array();
			$query = "SELECT * FROM vtiger_box_score_data_semanal where boxscoreid = ".$row['boxscoreid']."
				and box_score_dataid = ".$row['indicadorboxscore']."
				AND valor  ".html_entity_decode($row['comparacion_default'])." ".$row['parametro_default']."
				AND fecha > (CURRENT_DATE - INTERVAL $intervalo)" ;

			$result2 = $adb->pquery($query,array());
			$num_rows = $adb->num_rows($result2);
			while($row2 = $adb->fetchByAssoc($result2)) {
				$dataSemanal[] = $row2;
			}


			$row['cantidadRegistrosAlerta'] = $num_rows;
			$row['data'] = $dataSemanal;
			$row['descripcion'] = html_entity_decode($row['descripcion'], ENT_COMPAT, 'UTF-8');
			$row['titulo'] = html_entity_decode($row['titulo']);
			$row['tituloindicadorboxscore'] = getDescripctionIndicadorBS($row['indicadorboxscore']);
			$row['tituloboxscore'] = getDescripctionBS($row['boxscoreid']);


			$lst[] = $row;


		}
		return $lst;

	}

	*/






/*
	function getTabID($moduleName){
		global $adb;

		$sql = "SELECT tabid FROM vtiger_tab WHERE name = ? ";
		$result = $adb->pquery($sql, array($moduleName));
		return $adb->query_result($result, 0, "tabid");
	}
*/








	function obtieneListaVariablesSixSigma($tabid) {
		global $adb;

		$sql = "SELECT variablesid, name FROM vtiger_ss_variables WHERE tabid = ?
			ORDER BY name";

		$result = $adb->pquery($sql,array($tabid));

		$lst = array();
		while($row = $adb->fetchByAssoc($result)) {
			$lst[$row['variablesid']] = $row['name'];
		}
		return $lst;
	}

	function obtieneVariableSixSigma($variablesid) {
		global $adb;

		$sql = "SELECT * FROM vtiger_ss_variables WHERE variablesid = ?
			";

		$result = $adb->pquery($sql,array($variablesid));

		return $adb->fetchByAssoc($result);
	}

	function obtieneParametrosVariablesSixSigma($variablesid,$bConCamposFecha = true) {
		global $adb;

		$sql = "SELECT name, parameters FROM vtiger_ss_variables WHERE variablesid = ?
			";

		$result = $adb->pquery($sql,array($variablesid));

		$row = $adb->fetchByAssoc($result);
		$parameters = unserialize(decode_html($row['parameters']));

		foreach($parameters as $var => $value) {
			if ($value['type'] == 'D' && $bConCamposFecha) {
				$interfaz = escribeEntradaPeriodo($var);
			} else
			if ($value['type'] == 'L') {
				$interfaz = escribeEntradaLista($variablesid,$var,$row['name']);
			}
			if (isset($interfaz))
				$lst[$value['label']] = $interfaz;
		}
		return $lst;
	}

	function escribeEntradaPeriodo($var) {
		$bufferSalida = '
		<script>
	jQuery(\'#ini'.$var.'\').datepicker({
        dateFormat: \'mm/yy\',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,

        onClose: function(dateText, inst) {
            var month = jQuery("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = jQuery("#ui-datepicker-div .ui-datepicker-year :selected").val();
            jQuery(this).val(jQuery.datepicker.formatDate(\'mm/yy\', new Date(year, month, 1)));
        }
    });

    jQuery(\'#ini'.$var.'\').focus(function () {
        jQuery(".ui-datepicker-calendar").hide();
        jQuery("#ui-datepicker-div").position({
            my: "center top",
            at: "center bottom",
            of: $(this)
        });
    });

	jQuery(\'#end'.$var.'\').datepicker({
        dateFormat: \'mm/yy\',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,

        onClose: function(dateText, inst) {
            var month = jQuery("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = jQuery("#ui-datepicker-div .ui-datepicker-year :selected").val();
            jQuery(this).val(jQuery.datepicker.formatDate(\'mm/yy\', new Date(year, month, 1)));
        }
    });

    jQuery(\'#end'.$var.'\').focus(function () {
        jQuery(".ui-datepicker-calendar").hide();
        jQuery("#ui-datepicker-div").position({
            my: "center top",
            at: "center bottom",
            of: $(this)
        });
    });
	</script>
	<input type="text" name="ini'.$var.'" id="ini'.$var.'" /> -
	<input type="text" name="end'.$var.'" id="end'.$var.'" />';

		return $bufferSalida;

	}

	function escribeEntradaLista($variableid,$var,$variable) {
		$adb = conectaPlataformaHija($_SESSION['plat'],DB_DT_SS);

		$sql = "SELECT distinct ".$var." as ".$var." FROM ss_$variable ORDER BY 1";

		$result = $adb->query($sql);

		$bufferSalida = '
		<select name="'.$var.'" id="'.$var.'" class="small">
		<option value="" >-</option>';

		while($row = $adb->fetchByAssoc($result)) {
			$bufferSalida.= '<option value="'.$row[$var].'" >'.$row[$var].'</option>';
		}

		$bufferSalida.= '</select>';

		return $bufferSalida;
	}

?>