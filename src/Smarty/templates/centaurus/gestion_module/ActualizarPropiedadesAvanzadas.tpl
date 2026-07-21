
<!-- 
	Template: ModuloDefinicion.tpl
	Objetivo: Presentar el paso inicial de definición de datos para construir un nuevo Módulo
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
	{rdelim} else {ldelim}
		document.getElementById(id+'tamanoCampo'+row).style.display = '';
	{rdelim}
{rdelim}

jQuery(document).ready(function() {ldelim}
	jQuery('#{$ID_DLG_RELATED_EVENT}').hide();
	jQuery('#{$ID_DLG_RELATED_EVENT}').removeClass('md-modal md-effect-1');
{rdelim});
</script>

<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso3">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<div class="md-content">
	<div class="modal-header">
		<h4 class="modal-title" id="labelDiv">{$MOD.LBL_ACTUALIZAR_PROPIEDADES_AVANZADAS}</h4>
	</div>
	<div class="modal-body">
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine" >
			<tr>
				<td width="45%">
					{$MOD.LBL_PERMITIR_FILTROS_EN_LISTAS}
				</td>
				<td width="45%" align="right">
					<input type="checkbox" name="permitirFiltrosListas" id="permitirFiltrosListas" maxlength='100' value="" {$HABILITA_FILTRO_LISTAS}></input>				
				</td>
			</tr>
			<tr>
				<td width="45%">
					{$MOD.LBL_REGISTRAR_EVENTO_AL_INSERTAR_REGISTRO}
				</td>
				<td width="45%" align="right">
					<input type="checkbox" name="registrarEventoAlInsertarRegistro" id="registrarEventoAlInsertarRegistro" maxlength='100' value="" {$REGISTRA_EVENTO_AL_INSERTAR_REGISTRO} ></input>
					<button class="btn btn-warning" 
						onclick="jQuery('#registrarEventoAlInsertarRegistro').attr('checked', true);jQuery.ajax({ldelim}type: 'POST',url: 'index.php',data: {ldelim} fld_module: '{$_FLD_MODULE}', module: '{$MODULE}', action: '{$MODULE}Ajax', file: 'defineRelatedEvent' {rdelim}{rdelim}).done(function( html ) {ldelim} jQuery( '#texto{$ID_DLG_RELATED_EVENT}' ).html( html );jQuery( '#{$ID_DLG_RELATED_EVENT}' ).show() {rdelim});">
						{$MOD.LBL_CFG_REGISTRO_EVENTO}
					</button>
				</td>
			</tr>
		</table>
	</div>
	<div class="modal-footer">
		<input type="button" name="save" value=" {$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-primary"  onclick="irPaso(document.wizardPaso3,'actualizarPropiedadesAvanzadas');" />&nbsp;
		<!-- Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
		jQuery('#propiedadesAvanzadas') : cerrar el modal correspondiente a este ID
		-->
		<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#propiedadesAvanzadas').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
	</div>	
</div>
</form>
{$DLG_RELATED_EVENT}
