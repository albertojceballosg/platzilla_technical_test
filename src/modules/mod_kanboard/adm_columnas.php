<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('Smarty_setup.php');
require_once 'include/utils/comunesTareas.php';
require_once 'include/utils/comunesKanban.php';
$smarty = new vtigerCRM_Smarty;

global $currentModule;
global $mod_strings;
global $app_strings;


$smarty->assign('MODULE',$currentModule);
$smarty->assign('PARENTTAB',$_REQUEST['parenttab']);
$smarty->assign('MOD', $mod_strings);
$smarty->assign('APP', $app_strings);

$smarty->assign('ACCOUNTS',escribeListaCuentas(null));
$smarty->assign('TYPES',escribeListaTipos());
$smarty->assign('VENDORS',escribeListaDesarrolladores());
$smarty->assign('PROJECTS',escribeListaProyectos());

/*
    $module = 'HelpDesk';
    $tipos = obtieneTiposModulo($module);
    $smarty->assign("TIPOS_GENERICOS", $tipos);
    $registrosGenericos = obtieneRegistrosSegunTareas($tipos);
    $smarty->assign("REGISTROS_GENERICOS", $registrosGenericos);
*/

$module = 'todotasks';
$tipos = obtieneTiposModuloKanbanTT($module);
$smarty->assign('TIPOS_GENERICOS', $tipos);
$registrosGenericos = obtieneRegistrosSegunTareasTT($tipos,$filtro);
$smarty->assign('REGISTROS_GENERICOS', $registrosGenericos);



/**
 * Presentacion final de los valores de indicadores
 */
// $smarty->display("modules/$currentModule/index.tpl");
?>
<div class="row">
	<div class="col-lg-6">
		<h1>Administrar columnas kanban</h1>
	</div>
</div>
<form method="post" action="index.php?module=mod_kanboard&action=Save">
<input type="hidden" name="save" value="columnas"/>
<div class="col-lg-12">
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<h2 class="pull-left"></h2>
			<div class="pull-right">
				<input title="Guardar [Alt+S]" accesskey="S" class="btn btn-success btn-sm"  type="submit" name="button" value="  Guardar  " >
				<input title="Cancelar [Alt+X]" accesskey="X" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="Cancelar  ">
			</div>
		</header>
	</div>
</div>
<div class="col-lg-12">
	<div class="main-box clearfix">
		<div class="main-box-body clearfix">
			<header class="main-box-header clearfix"><h2 class="pull-left">&nbsp;</h2></header>
		</div>
		<div class="main-box-body clearfix" id="ordencols">
			<div class="row">
				<div class="form-group col-lg-5">
					<label>Label </label>
				</div>
				<div class="form-group col-lg-5">
					<label>Texto </label>
				</div>
				<div class="form-group col-lg-2">
					<label>&nbsp; </label>
				</div>
			</div>
			<?php
			foreach($tipos as $k => $t){
			?>
			<div class="row" style="cursor:move;" id="col_<?php echo ($k+1); ?>">
				<div class="form-group col-lg-5">
					<input type="text" tabindex="" name="label[]" value="<?php echo $t; ?>" class="form-control">
				</div>
				<div class="form-group col-lg-5">
					<input type="text" tabindex="" name="text[]" value="<?php echo getTranslatedString($t); ?>" class="form-control">
				</div>
				<div class="form-group col-lg-2">
					<a href="#" onclick="if(confirm('Esta seguro que desea quitar el registro?')){jQuery('#col_<?php echo ($k+1); ?>').remove();}" class="table-link danger">
						<span class="fa-stack">
							<i class="fa fa-square fa-stack-2x"></i>
							<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
						</span>
					</a>
				</div>
			</div>
			<?php
			}
			?>
		</div>
		<div class="form-group col-lg-12" align="center">
			<input class="btn btn-success btn-sm"  type="button" name="addcol" value="  Agregar  " onclick="addCol();return false;" >
		</div>
	</div>
</div>
</form>
<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script>
var ordencols=jQuery('#ordencols').sortable();
var col=<?php echo (count($tipos)+1); ?>;
function addCol(){
	var html='<div class="row" style="cursor:move;" id="col_'+col+'">'+
			 '	<div class="form-group col-lg-5">'+
			 '		<input type="text" tabindex="" name="label[]" value="" class="form-control">'+
			 '	</div>'+
			 '	<div class="form-group col-lg-5">'+
			 '		<input type="text" tabindex="" name="text[]" value="" class="form-control">'+
			 '	</div>'+
			 '	<div class="form-group col-lg-2">'+
			 '		<a href="#" onclick="if(confirm(\'Esta seguro que desea quitar el registro?\')){jQuery(\'#col_'+col+'\').remove();}" class="table-link danger">'+
			 '		<span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span>'+
			 '	</div>'+
			 '</div>';
		col++;
	jQuery('#ordencols').append(html);
}
</script>
