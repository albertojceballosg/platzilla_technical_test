
<!--
	Template: ModuloDefinicion.tpl
	Objetivo: Presentar el paso inicial de definici�n de datos para construir un nuevo M�dulo
	Fecha: 2013-04-02
	Desarrollador: Leonardo Castillo Lacruz (LCL)

-->
<script type="text/javascript">
function irPaso(form,action)
{ldelim}
	form.action.value = action;
		new Ajax.Request('index.php', {ldelim}
			method: form.method,
			postBody: Form.serialize(form),
			onComplete: function(response) {ldelim}
								window.location.reload();
						{rdelim}
		{rdelim});
{rdelim}

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
		x1.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'etiquetaCampo1').innerHTML;
		x2.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'tipoCampo1').innerHTML;
		str=str.replace(',1',','+lstNumRows[tableid]);
		x3.innerHTML=str.replace('Campo1','Campo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'tamanoCampo1').innerHTML;
		x4.innerHTML=str.replace(/Campo1/g,'Campo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'precisionCampo1').innerHTML;
		x5.innerHTML=str.replace(/Campo1/g,'Campo'+lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'actionCampo1').innerHTML;
		x6.innerHTML=str.replace('(1)','('+lstNumRows[tableid]+')');
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
	document.getElementById(id+'valoresCampo'+row).style.display = 'none';
	document.getElementById(id+'tamanoCampo'+row).style.display = 'none';
	document.getElementById(id+'moduloCampo'+row).style.display = 'none';
	document.getElementById(id+'precisionCampo'+row).style.display = 'none';
	if (value == 10) {ldelim}
		document.getElementById(id+'moduloCampo'+row).style.display = '';
	{rdelim} else
	if (value == 15 || value == 33) {ldelim}
		document.getElementById(id+'valoresCampo'+row).style.display = '';
	{rdelim} else
	if (value == 7) {ldelim}
		document.getElementById(id+'precisionCampo'+row).style.display = '';
		document.getElementById(id+'tamanoCampo'+row).style.display = '';
		if (document.getElementById(id+'tamanoCampo'+row).value == '')
			document.getElementById(id+'tamanoCampo'+row).value = 18;
		if (document.getElementById(id+'precisionCampo'+row).value == '')
			document.getElementById(id+'precisionCampo'+row).value = 0;
	{rdelim} else {ldelim}
		document.getElementById(id+'tamanoCampo'+row).style.display = '';
	{rdelim}
{rdelim}
</script>

{literal}
<script>
function editValuesGridField(fldmodule,fieldid){
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=editGridValues&parenttab=gestion_module&fieldid='+fieldid+'&ajax=true'+'&fldmodule='+fldmodule,
			onComplete: function(response) {
				$("camposGridValues").innerHTML=response.responseText;
				fnvshNrm('camposGridValues');
				var scriptTags = $('camposGridValues').getElementsByTagName("script");
				for(var i = 0; i< scriptTags.length; i++){
					var scriptTag = scriptTags[i];
					var script = document.createElement("script");
					script.type = "text/javascript";
					var head = document.getElementsByTagName("head")[0];
					if (scriptTag.src == '') {
						script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
						head.appendChild(script);
					}
				}
				fnvshNrm('camposGridValues');
			}
		}

	);
}
</script>
{/literal}

<div id="camposGridValues" style="display:block; position:relative; width:600px;" class="layerPopup">
</div>
<br>

{if $CAMPOS_GRID_DEFINIDOS|@count gt 0}

	<div id="gridCfg">
		<div class="md-content" style="font-size:90%">
			<div class="modal-header">
				<h4 class="modal-title" id="labelDiv">{$MOD.LBL_CAMPOS_GRID_DEFINIDOS}</h4>
			</div>
			<div class="modal-body">
				<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine" >
					{foreach item=related from=$CAMPOS_GRID_DEFINIDOS name=relinfo}
					<tr>
						<td>{$related.fieldlabel}
						</td>
						{if $smarty.foreach.relinfo.first}
						<td align="right" >
							<input type="button" class="crmbutton small edit" value='{$MOD.LBL_EDITAR_VALORES_DEFECTO}' title='{$MOD.LBL_EDITAR_VALORES_DEFECTO}'  onclick="editValuesGridField('{$_FLD_MODULE}','{$related.fieldid}');fninvsh('gridCfg');fnvshNrm('camposGridValues');posLay(this,'camposGridValues');"  />
						</td>
						<td>
						</td>
					{/if}
					</tr>
					{/foreach}
				</table>
			</div>
			<div class="modal-footer">
			</div>
		</div>
	</div>
{/if}

<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso3">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<div class="md-content">
	<div class="modal-header">
		<h4 class="modal-title" id="labelDiv">{$MOD.LBL_DEFINICION_CAMPO_GRID}</h4>
	</div>
	<div class="modal-body">
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine" >
			<tr>
				<td width="45%">
					{$MOD.LBL_NOMBRE_CAMPO}
				</td>
				<td width="45%" align="right">
					<input type="text" name="nombreGrid" id="nombreGrid" maxlength='16' value="" OnKeyUp="validField(this.id)"></input>
				</td>
			</tr>
			<tr>
				<td width="45%">
					{$MOD.LBL_ETIQUETA_CAMPO}
				</td>
				<td width="45%" align="right">
					<input type="text" name="etiquetaGrid" id="etiquetaGrid" maxlength='100' value=""></input>
				</td>
			</tr>
			<tr>
				<td width="95%" colspan="2">
				<table>
					{$_LISTA_SUB_CAMPOS_GRID}
				</table>
				</td>
			</tr>
		</table>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" onclick="irPaso(document.wizardPaso3,'AddGridFields');">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
		<!-- Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposici�n - Platzilla
		jQuery('#camposGrid') : cerrar el modal correspondiente a este ID-->
		<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#camposGrid').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
	</div>
</div>
</form>
