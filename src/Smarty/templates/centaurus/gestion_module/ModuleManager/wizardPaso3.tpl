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
 
function addRowOtherOperationsFields(tableid) {ldelim}
	ctrlTable = document.getElementById(tableid);
	if (ctrlTable) {ldelim}
		if (lstNumRows[tableid]) 
			lstNumRows[tableid]++
		else
			lstNumRows[tableid] = (ctrlTable.rows.length);
		
		var row=ctrlTable.insertRow(ctrlTable.rows.length);
		var x1=row.insertCell(0);
		var x2=row.insertCell(1);
		var x3=row.insertCell(2);
		var x4=row.insertCell(3);
		var x5=row.insertCell(4);
		var x6=row.insertCell(5);
		row.id = 'row_'+tableid+'_'+lstNumRows[tableid];
		
		str = document.getElementById('td_'+tableid+'nombreCampo1').innerHTML;
		str = str.replace(/numBloque1/g,'numBloque'+lstNumRows[tableid]);
		x1.innerHTML=str.replace(/nombreCampo1/g,'nombreCampo'+lstNumRows[tableid]);
		
		str = document.getElementById('td_'+tableid+'etiquetaCampo1').innerHTML;
		str = str.replace(/nombreCampo1/g,'nombreCampo'+lstNumRows[tableid]);
		x2.innerHTML=str.replace(/etiquetaCampo1/g,'etiquetaCampo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'tipoCampo1').innerHTML;
		str=str.replace(',1',','+lstNumRows[tableid]);
		x3.innerHTML=str.replace('Campo1','Campo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'tamanoCampo1').innerHTML;
		x4.innerHTML=str.replace(/Campo1/g,'Campo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'precisionCampo1').innerHTML;
		x5.innerHTML=str.replace(/Campo1/g,'Campo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'actionCampo1').innerHTML;
		str = str.replace('(1)','('+lstNumRows[tableid]+')');
		x6.innerHTML=str.replace(',1',','+lstNumRows[tableid]);
		x1.id= 'td_'+tableid+'nombreCampo'+lstNumRows[tableid];
		x2.id= 'td_'+tableid+'etiquetaCampo'+lstNumRows[tableid];
		x3.id= 'td_'+tableid+'tipoCampo'+lstNumRows[tableid];
		x4.id= 'td_'+tableid+'tamanoCampo'+lstNumRows[tableid];
		x5.id= 'td_'+tableid+'precisionCampo'+lstNumRows[tableid];
		x6.id= 'td_'+tableid+'actionCampo'+lstNumRows[tableid];
		x1.className = 'crmTableRow small lineOnTop';
		x2.className = 'crmTableRow small lineOnTop';
		x3.className = 'crmTableRow small lineOnTop';
		x4.className = 'crmTableRow small lineOnTop';
		x5.className = 'crmTableRow small lineOnTop';
		x6.className = 'crmTableRow small lineOnTop';
	{rdelim}
	J("#"+tableid).tableDnD();
	
{rdelim}




function deleteOtherOperationFields(tableid,iNumRow) {ldelim}
	ctrlTable = document.getElementById(tableid);
	
	if (ctrlTable) {ldelim}
		var x = document.getElementById ('row_'+tableid+'_'+iNumRow);
		var tablepadre = x.parentNode;
		tablepadre.removeChild(x);
	{rdelim}
{rdelim}

function changeInterfaz(value,id,row) {ldelim}

	if (document.getElementById(id+'valoresCampo'+row))
		document.getElementById(id+'valoresCampo'+row).style.display = 'none';
	if (document.getElementById(id+'tamanoCampo'+row))
		document.getElementById(id+'tamanoCampo'+row).style.display = 'none';
	if (document.getElementById(id+'moduloCampo'+row))
		document.getElementById(id+'moduloCampo'+row).style.display = 'none';
	if (document.getElementById(id+'precisionCampo'+row))
		document.getElementById(id+'precisionCampo'+row).style.display = 'none';
	if (document.getElementById(id+'campoBarra'+row))
		document.getElementById(id+'campoBarra'+row).style.display = 'none';
	if (document.getElementById(id+'prefijoCampo'+row))
		document.getElementById(id+'prefijoCampo'+row).style.display = 'none';
	if (document.getElementById(id+'secuenciaCampo'+row))
		document.getElementById(id+'secuenciaCampo'+row).style.display = 'none';


	if (value == 10 || value == 404) {ldelim}
		document.getElementById(id+'moduloCampo'+row).style.display = '';
	{rdelim} else
	if (value == 15 || value == 33) {ldelim}
		document.getElementById(id+'valoresCampo'+row).style.display = '';
	{rdelim} else
	if (value == 7 || value == 71 || value == 9) {ldelim}
		document.getElementById(id+'precisionCampo'+row).style.display = '';
		document.getElementById(id+'tamanoCampo'+row).style.display = '';
		if (document.getElementById(id+'tamanoCampo'+row).value == '')
			document.getElementById(id+'tamanoCampo'+row).value = 18;
		if (document.getElementById(id+'precisionCampo'+row).value == '')
			document.getElementById(id+'precisionCampo'+row).value = 2;
	{rdelim} else
	if (value == 108 || value == 101) {ldelim}
		document.getElementById(id+'campoBarra'+row).style.display = 'block';
	{rdelim} else
	if (value == 4) {ldelim}
		document.getElementById(id+'prefijoCampo'+row).style.display = 'block';	
		document.getElementById(id+'secuenciaCampo'+row).style.display = 'block';	
	{rdelim} else {ldelim}
		document.getElementById(id+'tamanoCampo'+row).style.display = '';
		document.getElementById(id+'prefijoCampo'+row).style.display = 'none';
		document.getElementById(id+'secuenciaCampo'+row).style.display = 'none';
	{rdelim}
{rdelim}

J(document).ready(function() {ldelim}
	/*for(var n=1; n<100; n++){ldelim}
		for(var m=2; m<100;m++){ldelim}
			 if(J('#row_table'+n+'_'+m).length>0 && J('#row_table'+n+'_'+m).is(":visible")){ldelim}
				if(m==2){ldelim}
					J('#fld_table'+n+'nombreCampo'+m).val('');
					J('#fld_table'+n+'etiquetaCampo'+m).val('');
					J('#fld_table'+n+'tipoCampo'+m).val('1');
					J('#fld_table'+n+'tamanoCampo'+m).val('');
					changeInterfaz('1','fld_table'+n,m);
				{rdelim} else {ldelim}
					deleteOtherOperationFields('table'+n,m);
				{rdelim}
			{rdelim}	
		{rdelim}
	{rdelim}*/

		J("#proTabFields").tableDnD();

{rdelim});


</script>

<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso3">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<div class="wizard" id="myWizard">
<div class="wizard-inner">
<ul class="steps">
<li data-target="#step1"><span class="badge">1</span>Paso 1<span class="chevron"></span></li>
<li data-target="#step2"><span class="badge">2</span>Paso 2<span class="chevron"></span></li>
<li class="active" data-target="#step3"><span class="badge badge-primary">3</span>Paso 3<span class="chevron"></span></li>
<li data-target="#step4"><span class="badge">4</span>Paso 4<span class="chevron"></span></li>
</ul>
<div class="actions">
<button type="button" class="btn btn-default btn-mini btn-prev" onclick="irPaso(document.wizardPaso3,'wizardPaso2');"> <i class="icon-arrow-left"></i>{$MOD.LBL_ANTERIOR}</button>
<button data-last="Finish" id="button_next" class="btn btn-success btn-mini" type="button" onclick="ValidaPaso3();">{$MOD.LBL_SIGUIENTE}<i class="icon-arrow-right"></i></button>
</div>
</div>
<div class="step-content">
<div class="main-box clearfix">
	<header class="main-box-header clearfix">
		<h2 class="pull-left">{$MOD.LBL_CAMPOS_DEL_MODULO}</h2>
	</header>
	<div class="main-box-body clearfix">
	<div class="table-responsive">
		<table class="table" id="proTabFields">
			{$_LISTA_CAMPOS_POR_BLOQUES}
		</table>
	</div>
</div>
</div>
</div>
</form>