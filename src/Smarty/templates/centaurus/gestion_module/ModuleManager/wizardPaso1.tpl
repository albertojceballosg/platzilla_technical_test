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
<script type="text/javascript" src="modules/gestion_module/wizardPasos.js"></script>

<script type="text/javascript">
function irPaso2(form,root)
{ldelim}


        var sNombreCodigo = $('txtbox_nombreCodigo').value;
		var sNombrePublico = $('txtbox_nombrePublico').value;
		var sTipoModulo = $('tipoModulo').value;
		if(sNombreCodigo!='' && sNombrePublico!='')
        {ldelim}
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
{rdelim}

jQuery( document ).ready(function () {ldelim}
	jQuery('#txtbox_nombreCodigo').keyup(function(){ldelim}
		validField('txtbox_nombreCodigo');
	{rdelim});
	//[ TT11416 ] Ajustes generador de módulos y aplicaciones - Jesus A* - Se ocultan los campos dependiendo del la selección
	jQuery('#isAdmin').on('click', function(){ldelim}
		if (jQuery('#isAdmin').prop('checked')){ldelim}
			jQuery('#isAdmin').val('Si');
			jQuery('#appMadre').show();
			jQuery('#modPadre').hide();
		{rdelim}else{ldelim}
			jQuery('#isAdmin').val('No');
			jQuery('#appMadre').hide();
			jQuery('#modPadre').show();
		{rdelim}
   {rdelim});	

{rdelim})
</script>
<div class="wizard" id="myWizard">
<div class="wizard-inner">
<ul class="steps">
<li class="active" data-target="#step1"><span class="badge badge-primary">1</span>Paso 1<span class="chevron"></span></li>
<li data-target="#step2"><span class="badge">2</span>Paso 2<span class="chevron"></span></li>
<li data-target="#step3"><span class="badge">3</span>Paso 3<span class="chevron"></span></li>
<li data-target="#step4"><span class="badge">4</span>Paso 4<span class="chevron"></span></li>
</ul>
<div class="actions">
<button data-last="Finish" id="button_next" class="btn btn-success btn-mini" type="button" onclick="ValidaPaso1();">{$MOD.LBL_SIGUIENTE}<i class="icon-arrow-right"></i></button>
</div>
</div>
<div class="step-content">
<header class="main-box-header clearfix">
	<h2>{$MOD.LBL_INFORMACION_BASICA_DEL_MODULO}</h2>
</header>
<div class="main-box-body clearfix">

<form method="post" action='"index.php" return false;' name="wizardPaso1">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" value="wizardPaso2" />
<input type="hidden" name="Ajax" value="true" />
	<div class="form-group">
		<label for="exampleInputCodigo">{$MOD.LBL_NOMBRE_CODIGO}</label>
		<input type="text" class="form-control" id="txtbox_nombreCodigo" placeholder="" name="nombreCodigo" data-toggle="tooltip" data-placement="top" title="{$MOD.LBL_AYUDA_NOMBRE_CODIGO}" value="{$NOMBRE_CODIGO}">
	</div>
	<div class="form-group">
		<label for="exampleInputPublico">{$MOD.LBL_NOMBRE_PUBLICO}</label>
		<input type="text" class="form-control" id="txtbox_nombrePublico" placeholder="" name="nombrePublico" value="{$NOMBRE_PUBLICO}">
	</div>

	<div class="form-group">
		<label>{$MOD.LBL_TIPO_MODULO}</label>
		<select class="form-control" name="tipoModulo" id="tipoModulo">
			<option value="Completo" {$MODULO_COMPLETO}>
			{$MOD.LBL_MODULO_CON_CAMPOS}
			</option>
			<option value="Simple" {$MODULO_SIMPLE}>
			{$MOD.LBL_MODULO_SIMPLE}
			</option>
			<option value="Panel" {$MODULO_PANEL}>
			{$MOD.LBL_MODULO_PANEL}
			</option>
		</select>
	</div>
	{*[ TT11416 ] Ajustes generador de módulos y aplicaciones - Jesus Arias - Checkbox de si el modulo es de administración*}
	<div class="checkbox-nice">
		<input type="checkbox" name="isAdmin" id="isAdmin">
		<label for="isAdmin">{$MOD.LBL_MOD_ADMINISTRACION}</label>
	</div>
	{*[ TT11416 ] Ajustes generador de módulos y aplicaciones - Jesus Arias - Lista de aplicaciones*}
	<div class="form-group" style="display:none" id="appMadre">
		<label>{$MOD.LBL_APLICACION_MADRE}</label>
		{$APP_MADRE}
	</div>
	<div class="form-group" id="modPadre">
		<label>{$MOD.LBL_MODULO_PADRE}</label>
		{$MODULO_PADRE}
	</div>
</form>
</div>
</div>
</div>