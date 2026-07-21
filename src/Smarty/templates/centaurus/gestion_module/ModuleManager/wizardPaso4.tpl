<!--
	Template: ModuloDefinicion.tpl
	Objetivo: Presentar el paso inicial de definición de datos para construir un nuevo Módulo
	Fecha: 2013-04-02
	Desarrollador: Leonardo Castillo Lacruz (LCL)

-->
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css">

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/wizard.js"></script>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>

<script type="text/javascript">
var lstNumRows = new Array();

function addRowOtherOperationsList(tableid) {ldelim}
	ctrlTable = document.getElementById(tableid);
	if (ctrlTable) {ldelim}
		if (lstNumRows[tableid])
			lstNumRows[tableid]++;
		else
			lstNumRows[tableid] = (ctrlTable.rows.length);

		var row=ctrlTable.insertRow(ctrlTable.rows.length);
		var x1=row.insertCell(0);
		var x2=row.insertCell(1);
		var x3=row.insertCell(2);
		var x4=row.insertCell(3);
		row.id = 'row_'+tableid+'_'+lstNumRows[tableid];
		row.className = 'lvtColData';

		str = document.getElementById('td_'+tableid+'labelModulos1').innerHTML;
		x1.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'listaModulos1').innerHTML;
		x2.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'listaAcciones1').innerHTML;
		x3.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'actionCampo1').innerHTML;
		x4.innerHTML=str.replace(',1',','+lstNumRows[tableid]);
		x1.id= 'td_'+tableid+'listaModulos'+lstNumRows[tableid];
		x2.id= 'td_'+tableid+'listaAcciones'+lstNumRows[tableid];
		x3.id= 'td_'+tableid+'actionCampo'+lstNumRows[tableid];
		x1.className = 'dvtCellInfo';
		x2.className = 'dvtCellInfo';
		x3.className = 'dvtCellInfo';
	{rdelim}
	J("#"+tableid).tableDnD();

{rdelim}

function deleteOtherOperationList(tableid,iNumRow) {ldelim}
	ctrlTable = document.getElementById(tableid);

	if (ctrlTable) {ldelim}
		var x = document.getElementById ('row_'+tableid+'_'+iNumRow);
		var tablepadre = x.parentNode;
		tablepadre.removeChild(x);
	{rdelim}
{rdelim}

J(document).ready(function() {ldelim}
	J("#proTabList").tableDnD();
{rdelim});

</script>
<div id="formid">
<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso4">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<div class="wizard" id="myWizard">
<div class="wizard-inner">
<ul class="steps">
<li data-target="#step1"><span class="badge">1</span>Paso 1<span class="chevron"></span></li>
<li data-target="#step2"><span class="badge">2</span>Paso 2<span class="chevron"></span></li>
<li data-target="#step3"><span class="badge">3</span>Paso 3<span class="chevron"></span></li>
<li class="active" data-target="#step4"><span class="badge badge-primary">4</span>Paso 4<span class="chevron"></span></li>
</ul>
<div class="actions">
<button type="button" class="btn btn-default btn-mini btn-prev" onclick="irPaso(document.wizardPaso4,'wizardPaso3');"> <i class="icon-arrow-left"></i>{$MOD.LBL_ANTERIOR}</button>
<button data-last="Finish" id="button_next" class="btn btn-success btn-mini" type="button" onclick="ValidaPaso4();">{$MOD.LBL_SIGUIENTE}<i class="icon-arrow-right"></i></button>
<!--button data-last="Finish" class="btn btn-success btn-mini btn-next" type="button" onclick="activaMensaje();irPaso(document.wizardPaso4,'wizardPaso5');">{$MOD.LBL_SIGUIENTE}<i class="icon-arrow-right"></i></button-->
</div>
</div>
<div class="step-content">
<div class="main-box clearfix">
	<header class="main-box-header clearfix">
		<h2 class="pull-left">{$MOD.LBL_PROPIEDADES_AVANZADAS_DEL_MODULO}</h2>
	</header>
	<div class="main-box-body clearfix">
	<div class="table-responsive">
		<table class="table" id="proTabList">
			<tr style="height:25px">
				<td width="20%" class="dvtCellLabel" align="right"><font color="red">*</font>{$MOD.LBL_CAMPO_IDENTIFICADOR_DEL_MODULO}</td>
				<td width="30%" align="left" class="dvtCellInfo">{$_LISTADO_CAMPOS}</td>
			</tr>
			<tr style="height:25px">
				<td width="20%" class="dvtCellLabel" align="right"><font color="red">*</font>{$MOD.LBL_IS_REPORT}</td>
				<td width="30%" align="left" class="dvtCellInfo"><input type="checkbox" id="check_reportAvailable" name="reportAvailable" ></td>
			</tr>
			<tr>
				<td class="detailedViewHeader" colspan="2">
				<div style="float:left">
				<b>{$MOD.LBL_COLUMNAS_FILTRO}</b>
				</div>
				</td>
			</tr>
			<tr>
				<td class="dvtCellInfo" colspan="2">
				{$_COLUMNAS_FILTRO}
				</td>
			</tr>
			<tr>
				<td class="detailedViewHeader" colspan="2">
				<div style="float:left">
				<b>{$MOD.LBL_LISTAS_RELACIONADAS}</b>
				</div>
				<div class="filter-block pull-right">
					<a href="#" class="btn btn-primary pull-right" onclick="addRowOtherOperationsList('table1')">
						<i class="fa fa-plus-circle fa-lg"></i>{$MOD.LBL_ADD_LISTA}
					</a>
				</div>
				</td>
			</tr>
			<tr>
				<td class="dvtCellInfo" colspan="2">
				{$_LISTADOS_RELACIONADOS}
				</td>
			</tr>
		</table>
	</div>
	</div>
</div>
</div>
</div>
</form>
<div id="mensaje" style="display:none;text-align:center;width:100%;">
<img src="themes/images/loading.gif" />
</div>