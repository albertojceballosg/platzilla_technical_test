<script language="JavaScript" type="text/javascript" src="modules/Settings/Settings.js"></script>
<form action="index.php?module=Settings&action=SaveEditCustomButtons" method="post" id="SaveEditCustomButtons" name="index" onsubmit="">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-9 pull-left">
				<h1><a href="index.php?module=Settings&action=CustomButtons&parenttab=Settings">{$MOD.LBL_CUSTOM_BUTTONS} </a></h1>
			</div>
			<div class="col-lg-3 pull-right text-right">
				<a class="btn btn-primary" type="submit" href="index.php?module=Settings&action=EditCustomButtons&record={$CUSTOMBUTTON.custombuttonid}">{$MOD.Edit}</a>
				<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=CustomButtons">{$MOD.LBL_CUSTOM_BUTTONS_BACK}</a>
			</div>
		</div>
	</div>
</form>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box">
			<header class="title-section main-box-header clearfix">
				<h2>Detalles de {$MOD.LBL_CUSTOM_BUTTONS}</h2>
			</header>
			<div class="main-box-body clearfix" id="">
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_TITLE}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.label}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_DESCRIPCION}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.description}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_MODULE}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.module}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_VIEW}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.viewlabel}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_TYPEBUTTON}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{if ($CUSTOMBUTTON.type == 'js')}Javascript{else}Enlace{/if}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_ACTIVE}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;"> 
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.active}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_STYLEBUTTON}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">

							<!-- Inicio Cambio Estilo Botón - PEBG - 16/10/2025 -->
							<a class="btn btn-{$CUSTOMBUTTON.style} btn-circle btn-xs"
								href="#" title="{$CUSTOMBUTTON.description}"
								title="{$CUSTOMBUTTON.description|default:$CUSTOMBUTTON.label}"
								style="margin-left:.5em; margin-right:.5em; border-radius: 9999px;">
								<span class="fa {$CUSTOMBUTTON.faicon}"></span>
							</a>
							<!-- Fin Cambio Estilo Botón - PEBG - 16/10/2025 -->

						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_RUNINNEWWINDOW}</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="">{if ($CUSTOMBUTTON.runinnewwindow)}Sí{else}No{/if}</span>
						</div>
					</div>
				</div>
{if ($CUSTOMBUTTON.type == 'js')}
				<div class="col-md-12">
					<div class="col-md-2">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_CLICKACTION}</label>
						</div>
					</div>
					<div class="form-group col-md-10">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.onclick}</span>
						</div>
					</div>
				</div>
{else}
				<div class="col-md-12">
					<div class="col-md-2">
						<div class="label-input">
							<label for="">{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}</label>
						</div>
					</div>
					<div class="form-group col-md-10">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="readonly">{$CUSTOMBUTTON.link}</span>
						</div>
					</div>
				</div>
{/if}
			</div>
		</div>
	</div>
</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div><!-- the overlay element -->




<script>
	jQuery (document).ready (function () {ldelim}





		{rdelim});

	function validateForm () {ldelim}



		return true;

		if (!jQuery ('#app_code').val ()) {ldelim}
			alert ('Especifique el código de la Aplicación');
			return false;
			{rdelim}
		if (!jQuery ('#app_name').val ()) {ldelim}
			alert ('Especifique el nombre de la Aplicación');
			return false;
			{rdelim}

		return true


		{rdelim}

	function validateRepeatData () {ldelim}

		var param = 'validation=norepeatnameapp&app_code=' + jQuery ('#app_code').val () + '&app_name=' + jQuery ('#app_name').val ();

		new Ajax.Request (
				'index.php',

				{
					ldelim}queue: { ldelim}position: 'end', scope: 'command'{rdelim},
					method:       'post',
					postBody:     'action=SettingsAjax&module=Settings&file=validateSaveEditApps&' + param,
					onComplete:   function (response) {ldelim}
						console.log (response.responseText);
						if (response.responseText == 'repeated') {ldelim}
							alert ('La aplicaci\u00F3n Ya Existe');
							return false
							{rdelim} else {ldelim}
							console.log ("enviando a validateInfo")
							return validateInfo ()
							{rdelim}
						{rdelim}
					{rdelim}
		);

		{rdelim}

	function validateInfo () {ldelim}



		jQuery ('#SaveEditCustomButtons').submit ();
		return true;

		if (!jQuery ('#app_price').val ()) {ldelim}
			alert ('Especifique el precio de la Aplicación');
			return false;
			{rdelim}
		if (jQuery ('#app_price').val () <= 0) {ldelim}
			alert ('Especifique el precio de la Aplicación');
			return false;
			{rdelim}
		if (jQuery ('#app_url').val () == '') {ldelim}
			alert ('Especifique la url de la Aplicación');
			return false;
			{rdelim}
		if (jQuery ('#binFileButton').val () == 'Seleccionar Archivo') {ldelim}
			alert ('Elija una imagen para la Aplicación');
			return false;
			{rdelim}
		if (jQuery ('#nestable-output').val () == '[]') {ldelim}
			alert ('Agregue al menos un módulo a esta Aplicación');
			return false;
			{rdelim}

		jQuery ('#SaveEditApps').submit ();

		{rdelim}

	/*[ TT11276 ] Ajustar precio de las aplicaciones - Jesus A - 15/08/2016 */
	/* Función que consulta el precio de la aplicación via Ajax en la tabla vtiger_variables_instancias y
	 calcula el monto total para colocarlo en el campo precio de la forma*/

	function updatedPriceValueOfApps (quantity) {ldelim}

		var param = 'sub_mode=priceQueryApps';

		new Ajax.Request (
				'index.php',

				{
					ldelim}queue: { ldelim}position: 'end', scope: 'command'{rdelim},
					method:       'post',
					postBody:     'action=SettingsAjax&module=Settings&file=priceApps&' + param,
					onComplete:   function (response) {ldelim}
						//console.log(response.responseText);
						if (response.responseText != "") {ldelim}
							//alert(response.responseText);
							var priceApp = 0;
							priceApp = response.responseText;
							var totalPrice = priceApp * quantity;
							jQuery ('#app_price').val (totalPrice);
							{rdelim}
						{rdelim}
					{rdelim}
		);

		{rdelim}
</script>

