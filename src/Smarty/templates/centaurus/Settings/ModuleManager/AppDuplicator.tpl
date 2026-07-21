{strip}
<style type="text/css">
	label {
		font-size:   1em;
		font-weight: 300;
	}
	.required {
		color: #FF0000;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-cubes green-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a></li>
					<li><a href="index.php?module=Settings&action=ConfigApps&parenttab=Settings">{$MOD.CONFIG_APPS|upper} </a></li>
					<li class="active">DUPLICADOR DE APLICACIONES</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Permite duplicar una aplicación del sistema</td>
		</tr>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<form method="post" action="index.php" onsubmit="return AppDuplicatorUtils.validateDuplicatorForm (this);">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="action" value="DuplicateApp" />
		<input type="hidden" name="Ajax" value="true" />
		<div class="row">
			<div class="col-xs-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix"><h2>Información de la aplicación</h2></header>
					<div class="main-box-body clearfix">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="old-application-code">Aplicación a duplicar <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="old-application-code" name="oldapplicationcode" class="form-control" onchange="AppDuplicatorUtils.showApplicationModules (this);">
										<option value=""{if (empty ($SELECTED_OLD_APPLICATION_CODE))} selected="selected"{/if}></option>
{foreach $AVAILABLE_APPLICATIONS as $application}
										<option value="{$application.app_code}"{if (!empty ($SELECTED_OLD_APPLICATION_CODE)) && ($application.app_code == $SELECTED_OLD_APPLICATION_CODE)} selected="selected"{/if}>{$application.app_name} ({$application.app_code})</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="new-application-code">Nuevo nombre <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<input type="text" id="new-application-code" name="newapplicationcode" value="{$SELECTED_NEW_APPLICATION_CODE}" class="form-control code" maxlength="25" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="new-application-name">Nuevo título <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<input type="text" id="new-application-name" name="newapplicationname" value="{$SELECTED_NEW_APPLICATION_NAME}" class="form-control" maxlength="64" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="new-application-description">Descripción <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<textarea id="new-application-description" name="newapplicationdescription" class="form-control">{$SELECTED_NEW_APPLICATION_DESCRIPTION}</textarea>
							</div>
						</div>
						<div id="application-modules" class="col-md-12"{if (empty ($SELECTED_OLD_APPLICATION_CODE))} style="display: none;"{/if}>
							<div class="table-responsive">
								<table class="table">
									<thead>
									<tr>
										<th>Módulo actual</th>
										<th>Nuevo código</th>
										<th>Nuevo título</th>
										<th>Menú donde aparecerá</th>
									</tr>
									</thead>
									<tbody>
{if (!empty ($AVAILABLE_APPLICATIONS)) && (!empty ($SELECTED_OLD_APPLICATION_CODE)) && (isset ($AVAILABLE_APPLICATIONS[$SELECTED_OLD_APPLICATION_CODE]))}
	{foreach $AVAILABLE_APPLICATIONS[$SELECTED_OLD_APPLICATION_CODE].modules as $availableModule}
									<tr>
										<td>
											<input type="text" value="{$availableModule.tablabel} ({$availableModule.name})" class="old-module-name form-control" readonly="readonly" placeholder="Módulo actual" />
										</td>
										<td>
											<input type="text" name="modules[{$availableModule.name}][newmodulename]" value="{if (!empty ($SELECTED_NEW_MODULE_DATA[$availableModule.name]))}{$SELECTED_NEW_MODULE_DATA[$availableModule.name].newmodulename}{/if}" class="new-module-name form-control code" maxlength="20" placeholder="Nuevo código" />
										</td>
										<td>
											<input type="text" name="modules[{$availableModule.name}][newmoduletitle]" value="{if (!empty ($SELECTED_NEW_MODULE_DATA[$availableModule.name]))}{$SELECTED_NEW_MODULE_DATA[$availableModule.name].newmoduletitle}{/if}" class="new-module-title form-control" maxlength="64" placeholder="Nuevo título" />
										</td>
										<td>
											<select name="modules[{$availableModule.name}][newmenulabel]" class="new-menu-label form-control" title="Menú donde aparecerá">
												<option value="" selected="selected"></option>
		{foreach $AVAILABLE_MENU_LABELS as $menuLabel}
												<option value="{$menuLabel}"{if (!empty ($SELECTED_NEW_MODULE_DATA[$availableModule.name])) && ($SELECTED_NEW_MODULE_DATA[$availableModule.name].newmenulabel == $menuLabel)} selected="selected"{/if}>{$menuLabel}</option>
		{/foreach}
											</select>
										</td>
									</tr>
	{/foreach}
{/if}
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-md-12 text-center">
							<button type="submit" class="btn btn-primary" style="margin-right: 0.5em;">Duplicar</button>
							<a href="index.php?module=Settings&action=ConfigApps&parenttab=Settings" class="btn btn-warning">Cancelar</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/html" id="template-application-module">
<tr>
	<td>
		<input type="text" class="old-module-name form-control" readonly="readonly" placeholder="Módulo actual" />
	</td>
	<td>
		<input type="text" name="modules[__OLD_MODULE_NAME__][newmodulename]" class="new-module-name form-control code" maxlength="20" placeholder="Nuevo código" />
	</td>
	<td>
		<input type="text" name="modules[__OLD_MODULE_NAME__][newmoduletitle]" class="new-module-title form-control" maxlength="64" placeholder="Nuevo título" />
	</td>
	<td>
		<select name="modules[__OLD_MODULE_NAME__][newmenulabel]" class="new-menu-label form-control" title="Menú donde aparecerá">
			<option value="" selected="selected"></option>
{foreach $AVAILABLE_MENU_LABELS as $menuLabel}
			<option value="{$menuLabel}">{$menuLabel}</option>
{/foreach}
		</select>
	</td>
</tr>
</script>
<script type="text/javascript">
(function (jQuery, availableApplications) {
	var getNormalizedText = function (value) {
		var from = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
			to   = 'aaaaeeeeiiiioooouuuunc______',
			i, l;

		value = value.toLowerCase ().replace (' ', '_');

		for (i = 0, l = from.length; i < l; i++) {
			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
		}

		value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
		return value;
	};

	var showApplicationModules = function (selectElement) {
		var selectedApplication       = jQuery (selectElement).val (),
			applicationModulesSection = jQuery ('#application-modules'),
			applicationModuleTemplate = jQuery ('#template-application-module'),
			applicationModules, applicationModule, i, n;

		applicationModulesSection.find ('tbody').empty ();
		if ((selectedApplication === undefined) || (selectedApplication === null) || (selectedApplication.trim () === '') || (availableApplications === null) || (!availableApplications.hasOwnProperty (selectedApplication))) {
			applicationModulesSection.hide ();
		} else {
			applicationModules = availableApplications [ selectedApplication ].modules;
			n = applicationModules.length;
			for (i = 0; i < n; i += 1) {
				applicationModule = jQuery (applicationModuleTemplate.html ().replace (/__OLD_MODULE_NAME__/g, applicationModules [ i ].name));
				applicationModule.find ('.old-module-name').val (applicationModules [ i ].tablabel + ' (' + applicationModules [ i ].name + ')');
				applicationModulesSection.find ('tbody').append (applicationModule);
			}
			applicationModulesSection.show ();
		}
	};

	var validateDuplicatorForm = function (formElement) {
		var form = jQuery (formElement),
			applicationModulesSection = form.find ('#application-modules'),
			applicationModules, applicationModule, field, value, i, n;

		field = form.find ('#old-application-code');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona la aplicación a duplicar');
			field.focus ();
			return false;
		}

		field = form.find ('#new-application-code');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nuevo nombre');
			field.focus ();
			return false;
		} else {
			field.val (getNormalizedText (value));
		}

		field = form.find ('#new-application-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nuevo título');
			field.focus ();
			return false;
		}

		field = form.find ('#new-application-description');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce la descripción');
			field.focus ();
			return false;
		}

		applicationModules = applicationModulesSection.find ('tbody > tr');
		n = applicationModules.length;
		if (n === 0) {
			alert ('La aplicación seleccionada no tiene módulos asociados');
			return false;
		}

		for (i = 0; i < n; i += 1) {
			applicationModule = jQuery (applicationModules [i]);

			field = applicationModule.find ('.new-module-name');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el nuevo código del módulo');
				field.focus ();
				return false;
			}

			field = applicationModule.find ('.new-module-title');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el nuevo título del módulo');
				field.focus ();
				return false;
			}

			field = applicationModule.find ('.new-menu-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Selecciona el menú donde aparecerá el nuevo módulo');
				field.focus ();
				return false;
			}
		}

		return true;
	};

	window.AppDuplicatorUtils = {
		showApplicationModules: showApplicationModules,
		validateDuplicatorForm: validateDuplicatorForm
	};

	jQuery (document).on ('keyup', '.code', function (evt) {
		var field = jQuery (evt.currentTarget);
		field.val (getNormalizedText (field.val ()));
	});
} (jQuery, {$AVAILABLE_APPLICATIONS|json_encode}));
</script>
{/strip}