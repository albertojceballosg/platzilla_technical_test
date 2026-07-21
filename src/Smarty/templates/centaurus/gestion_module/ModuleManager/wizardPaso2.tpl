<!--
	Template: ModuloDefinicion.tpl
	Objetivo: Presentar el paso inicial de definición de datos para construir un nuevo Módulo
	Fecha: 2013-04-02
	Desarrollador: Leonardo Castillo Lacruz (LCL)

-->
<!-- this page specific styles -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css">


<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/wizard.js"></script>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>

<script type="text/javascript">
var iNumRows = -1;
var back = 0;

function irPaso(form,action)
{ldelim}
	form.action.value = action;
		new Ajax.Request('index.php', {ldelim}
			method: form.method,
			postBody: Form.serialize(form),
			onComplete: function(response) {ldelim}
								$('texto{$ID_DLG_CREACION_MODULOS}').innerHTML = response.responseText;
								// Evaluate all the script tags in the response text.
								var scriptTags = $('texto{$ID_DLG_CREACION_MODULOS}').getElementsByTagName("script");
								for(var i = 0; i< scriptTags.length; i++){ldelim}
									var scriptTag = scriptTags[i];
									var script = document.createElement("script");
									script.type = "text/javascript";
									var head = document.getElementsByTagName("head")[0];
									if (scriptTag.src == '') {ldelim}
										script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
										head.appendChild(script);
									{rdelim}
								{rdelim}
						{rdelim}
		{rdelim});
{rdelim}


function addRowOtherOperations() {ldelim}
	ctrlTable = document.getElementById('proTab');

	if (ctrlTable) {ldelim}
		if (iNumRows == -1)
			iNumRows = (ctrlTable.rows.length);
		else
			iNumRows++;
		var row=ctrlTable.insertRow(ctrlTable.rows.length);
		var x1=row.insertCell(0);
		var x2=row.insertCell(1);
		var x3=row.insertCell(2);
		row.id = 'row'+iNumRows;
		str = document.getElementById('td_nombreBloque1').innerHTML;
		x1.innerHTML=str.replace(/1/g,iNumRows);
		str = document.getElementById('td_visibilidadBloque1').innerHTML;
		x2.innerHTML=str.replace(/1/g,iNumRows);
		str = document.getElementById('td_actionBloque1').innerHTML;
		x3.innerHTML=str.replace('(1)','('+iNumRows+')');
		x1.id= 'td_nombreBloque'+iNumRows;
		x2.id= 'td_visibilidadBloque'+iNumRows;
		x3.id= 'td_actionBloque'+iNumRows;
		x1.className = 'crmTableRow small lineOnTop';
		x2.className = 'crmTableRow small lineOnTop';
		x3.className = 'crmTableRow small lineOnTop';
		document.getElementById('nombreBloque'+(iNumRows)).value = '';
		document.getElementById('visibilidadBloque'+(iNumRows)).value = '2';
	{rdelim}
	J("#proTab").tableDnD();

{rdelim}

function deleteOtherOperation(iNumRow) {ldelim}
	ctrlTable = document.getElementById('proTab');

	if (ctrlTable) {ldelim}
		var x = document.getElementById ('row'+iNumRow);
		var tablepadre = x.parentNode;
		tablepadre.removeChild(x);
	{rdelim}
{rdelim}


function cleanform(){ldelim}

	if (jQuery('#proTab tbody tr').length > 1){ldelim}
		    for(var i=2; i<100; i++){ldelim}
				if(jQuery('#row'+i).length>0 && jQuery('#row'+i).is(":visible") ){ldelim}
			    	if(i==2){ldelim}
			    		jQuery('#nombreBloque'+i).val('');
			    		jQuery('#visibilidadBloque'+i).val('1');
			    	{rdelim}else{ldelim}
						deleteOtherOperation(i);
					{rdelim}
				{rdelim}
		    {rdelim}
		{rdelim}

		if(jQuery('#proTab tbody tr').length == 1){ldelim}
			addRowOtherOperations();
		{rdelim}


{rdelim}


</script>

<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso2" id="wizardPaso2">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<div class="wizard" id="myWizard">
<div class="wizard-inner">
<ul class="steps">
<li data-target="#step1"><span class="badge">1</span>Paso 1<span class="chevron"></span></li>
<li class="active" data-target="#step2"><span class="badge badge-primary">2</span>Paso 2<span class="chevron"></span></li>
<li data-target="#step3"><span class="badge">3</span>Paso 3<span class="chevron"></span></li>
<li data-target="#step4"><span class="badge">4</span>Paso 4<span class="chevron"></span></li>
</ul>
<div class="actions">
<button type="button" class="btn btn-default btn-mini btn-prev" onclick="irPaso(document.wizardPaso2,'wizardPaso1');"> <i class="icon-arrow-left"></i>{$MOD.LBL_ANTERIOR}</button>
<!--button data-last="Finish" id="button_next" class="btn btn-success btn-mini" type="button" onclick="ValidaPaso2();">{$MOD.LBL_SIGUIENTE}<i class="icon-arrow-right"></i></button-->
<button data-last="Finish" class="btn btn-success btn-mini btn-next" id="button_next" type="button" onclick="ValidaPaso2();">{$MOD.LBL_SIGUIENTE}<i class="icon-arrow-right"></i></button>
</div>
</div>
<div class="step-content">
<div class="main-box clearfix">
	<header class="main-box-header clearfix">
		<h2 class="pull-left">{$MOD.LBL_BLOQUES_DE_CAMPOS}</h2>

		<div class="filter-block pull-right">
			<a href="#" class="btn btn-primary pull-right" onclick="addRowOtherOperations()">
				<i class="fa fa-plus-circle fa-lg"></i>{$MOD.LBL_ADD_BLOQUES}
			</a>
		</div>
	</header>
	<div class="main-box-body clearfix">
	<div class="table-responsive">
		<table class="table" id="proTab">
		<thead>
			<tr>
				<th class="detailedViewHeader" width="60%">
				<b>{$MOD.LBL_NOMBRE_DEL_BLOQUE}</b>
				</th>
				<th class="detailedViewHeader" width="30%">
				<b>{$MOD.LBL_VISIBILIDAD}</b>
				</th>
				<th class="detailedViewHeader" width="10%">
				</th>
			</tr>
		</thead>
		{$_LISTA_BLOQUES}
		</table>
	</div>
	</div>
</div>
</form>