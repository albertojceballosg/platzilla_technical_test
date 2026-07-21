{strip}
<style type="text/css">
	label {
		font-size: 1em;
		font-weight: 300;
		margin:      0;
	}
	.panel-title > a {
		line-height: 1.8em;
	}
	.panel-title > a:hover,
	.panel-title > a:active {
		color: #ffffff;
	}
	.required {
		color: #FF0000;
	}
</style>
<form action="index.php" method="post" name="form" onsubmit="return ProfileUtils.validateProfile (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="parenttab" value="Settings" />
	<input type="hidden" name="action" value="SaveProfile" />
	<input type="hidden" name="profileid" value="{if (isset ($PROFILE))}{$PROFILE->getId ()}{/if}" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=Settings&amp;action=ProfileListView&amp;parenttab=Settings">Perfil</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info" style="margin-right: 5px;">{$APP.LBL_SAVE_LABEL}</button>
				<a href="index.php?module=Settings&amp;action=ProfileListView&amp;parenttab=Settings" class="btn btn-warning">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="col-lg-12">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="main-box clearfix col-lg-12">
		<header class="main-box-header clearfix">
			<h2>Información general</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="profile-name">Nombre <span class="required">*</span></label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<input type="text" id="profile-name" name="profilename" class="form-control" value="{if (isset ($PROFILE))}{$PROFILE->getName ()}{/if}"{if ((isset ($PROFILE)) && ($PROFILE->getId () == 1)) || ($IS_APPLICATION_PROFILE)} readonly="readonly"{/if} />
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="profile-description">Descripción</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<input type="text" id="profile-description" name="description" class="form-control" value="{if (isset ($PROFILE))}{$PROFILE->getDescription ()}{/if}" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="profile-application-codes">{if ($IS_APPLICATION_PROFILE)}Aplicación{else}Aplicaciones{/if}</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
{if (($IS_APPLICATION_PROFILE) || ((isset ($PROFILE)) && ($PROFILE->getId () == 1)))}
					<input type="hidden" name="applicationcodes[]" value="{if (isset ($APPLICATION_CODE))}{$APPLICATION_CODE}{/if}" />
					<input type="text" id="profile-application-codes" class="form-control" value="{if (isset ($APPLICATION_NAME))}{$APPLICATION_NAME}{/if}" readonly="readonly" />
{else}
					<select id="profile-application-codes" name="applicationcodes[]" class="form-control" onchange="ProfileUtils.showApplicationModules (this);">
						<option value=""{if (!isset ($APPLICATION_CODE))} selected="selected"{/if}>Todas</option>
	{foreach $APPLICATIONS as $application}
						<option value="{$application->getCode ()}" data-module-names="{if (isset ($APPLICATION_MODULE_NAMES[$application->getCode ()]))}{join(',', $APPLICATION_MODULE_NAMES[$application->getCode ()])}{/if}"{if ($application->getCode () == $APPLICATION_CODE)} selected="selected"{/if}>{$application->getName ()}</option>
	{/foreach}
					</select>
{/if}
				</div>
			</div>
		</div>
	</div>
	<div class="main-box clearfix col-lg-12">
		<header class="main-box-header clearfix">
			<h2>Permisos sobre la plataforma</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="col-md-6">
				<div class="col-md-6">
					<div class="label-input">
						<label for="global-view-permission">Ver todo</label>
					</div>
				</div>
				<div class="form-group col-md-3 field-container">
					<input type="checkbox" id="global-view-permission" name="globalviewpermission" value="Y"{if (!isset ($PROFILE)) || ($PROFILE->getViewPermission () == 1)} checked="checked"{/if} />
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-6">
					<div class="label-input">
						<label for="global-edit-permission">Editar todo</label>
					</div>
				</div>
				<div class="form-group col-md-3 field-container">
					<input type="checkbox" id="global-edit-permission" name="globaleditpermission" value="Y"{if (!isset ($PROFILE)) || ($PROFILE->getEditPermission () == 1)} checked="checked"{/if} />
				</div>
			</div>
		</div>
	</div>
	<div class="main-box clearfix col-lg-12">
		<header class="main-box-header clearfix">
			<h2>Permisos en los módulos</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="panel-group" id="profile">
{foreach $MODULES as $module}
	{assign var='moduleName' value=$module->getName ()}
	{assign var='moduleLabel' value=$module->getLabel ()}
	{assign var='blocks' value=$module->getBlocks ()}
	{assign var='views' value=$module->getViews ()}
	{if (isset ($PROFILE))}
		{assign var='moduleProfiles' value=$PROFILE->getModuleProfiles ()}
		{assign var='fieldProfiles' value=$PROFILE->getFieldProfiles ()}
		{assign var='viewProfiles' value=$PROFILE->getViewProfiles ()}
	{else}
		{assign var='moduleProfiles' value=null}
		{assign var='fieldProfiles' value=null}
		{assign var='viewProfiles' value=null}
	{/if}
	{assign var='isApplicationModule' value=(!empty ($APPLICATION_CODE)) && (!empty ($APPLICATION_MODULE_NAMES[$APPLICATION_CODE])) && (in_array ($moduleName, $APPLICATION_MODULE_NAMES[$APPLICATION_CODE]))}
	{assign var='moduleProfile' value=null}
	{if (!empty ($moduleProfiles))}
		{foreach $moduleProfiles as $profile}
			{if ($profile->getModuleName () == $moduleName)}
				{assign var='moduleProfile' value=$profile}
				{break}
			{/if}
		{/foreach}
	{/if}
				<div id="module-permissions-{$moduleName}" class="panel panel-default module-permissions"{if (!empty ($APPLICATION_CODE)) && (!$isApplicationModule)} style="display: none;"{/if}>
					<div class="panel-heading">
						<h4 class="row panel-title">
							<a class="col-xs-12 col-md-9" data-toggle="collapse" data-parent="#profile" href="#module-{$moduleName}">{$moduleLabel} ({$moduleName})</a>
							<span class="col-xs-12 col-md-3 text-right">
								<button type="button" class="btn btn-primary btn-sm" onclick="ProfileUtils.toggleVisibility ('module-{$moduleName}');"><i class="fa fa-check"></i> Marcar / Desmarcar todo</button>
							</span>
						</h4>
					</div>
					<div id="module-{$moduleName}" class="panel-collapse collapse module-panel" data-module-name="{$moduleName}">
						<div class="panel-body">
							<div class="row">
								<div class="col-xs-12">
									<div class="main-box">
										<header class="title-section main-box-header clearfix">
											<h2>Permisos sobre el módulo</h2>
										</header>
										<div class="main-box-body clearfix">
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="access-permission-{$moduleName}">Acceder</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="access-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][accesspermission]" value="Y" class="standard-permission"{if (empty ($APPLICATION_CODE)) || (!isset ($moduleProfile)) || (($isApplicationModule) && ($moduleProfile->getAccessPermission () === 0))} checked="checked"{/if} />
												</div>
											</div>
	{if ($module->getIsEntityType ())}
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="delete-permission-{$moduleName}">Eliminar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="delete-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][deletepermission]" value="Y" class="standard-permission"{if (empty ($APPLICATION_CODE)) || (!isset ($moduleProfile)) || (($isApplicationModule) && ($moduleProfile->getDeletePermission () === 0))} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="edit-permission-{$moduleName}">Editar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="edit-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][editpermission]" value="Y" class="standard-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getEditPermission () === 0)} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="list-permission-{$moduleName}">Listar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="list-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][listpermission]" value="Y" class="standard-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getListPermission () === 0)} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="read-permission-{$moduleName}">Leer</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="read-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][readpermission]" value="Y" class="standard-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getReadPermission () === 0)} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="save-permission-{$moduleName}">Guardar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="save-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][savepermission]" value="Y" class="standard-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getSavePermission () === 0)} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="export-permission-{$moduleName}">Exportar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="export-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][exportpermission]" value="Y" class="utility-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getExportPermission () === 0)} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="import-permission-{$moduleName}">Importar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="import-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][importpermission]" value="Y" class="utility-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getImportPermission () == 0)} checked="checked"{/if} />
												</div>
											</div>
											<div class="col-md-2">
												<div class="col-md-9">
													<div class="label-input">
														<label for="merge-permission-{$moduleName}">Combinar</label>
													</div>
												</div>
												<div class="form-group col-md-3 field-container">
													<input type="checkbox" id="merge-permission-{$moduleName}" name="moduleprofiles[{$moduleName}][mergepermission]" value="Y" class="utility-permission"{if (!isset ($moduleProfile)) || ($moduleProfile->getMergePermission () == 0)} checked="checked"{/if} />
												</div>
											</div>
	{/if}
										</div>
									</div>
								</div>
							</div>
	{foreach $blocks as $block}
		{assign var='fields' value=$block->getFields ()}
							<div class="row">
								<div class="col-xs-12">
									<div class="main-box">
										<header class="title-section main-box-header clearfix">
											<h2>Campos del bloque {$block->getLabel ()|@getTranslatedString:$moduleName}</h2>
										</header>
										<div class="main-box-body clearfix">
		{foreach $fields as $field}
			{assign var='fieldName' value=$field->getName ()}
			{assign var='isMandatory' value=$field->isMandatory ()}
			{assign var='fieldProfile' value=null}
			{if (!empty ($fieldProfiles))}
				{foreach $fieldProfiles as $profile}
					{if ($profile->getModuleName () == $moduleName) && ($profile->getFieldName () == $fieldName)}
						{assign var='fieldProfile' value=$profile}
						{break}
					{/if}
				{/foreach}
			{/if}
											<div class="col-xs-6">
												<div class="col-md-7">
													<div class="label-input">
														<label for="field-{$moduleName}-{$fieldName}">{$field->getLabel ()|@getTranslatedString:$moduleName}{if ($isMandatory)} <span class="required">*</span>{/if}</label>
													</div>
												</div>
												<div class="form-group col-md-2 field-container">
			{if ($isMandatory) || (in_array ($field->getUiType (), array (4, 53, 70)))}
													<input type="hidden" name="moduleprofiles[{$moduleName}][fieldpermissions][{$fieldName}]" value="Y" class="field-permission" />
													<input type="checkbox" id="field-{$moduleName}-{$fieldName}" disabled="disabled" checked="checked" class="field-permission" />
			{else}
													<input type="checkbox" id="field-{$moduleName}-{$fieldName}" name="moduleprofiles[{$moduleName}][fieldpermissions][{$fieldName}]" value="Y"{if (!isset ($fieldProfile)) || ($fieldProfile->getVisibility () === 0)} checked="checked"{/if} class="field-permission" />
			{/if}
												</div>
											</div>
		{/foreach}
										</div>
									</div>
								</div>
							</div>
	{/foreach}
	{if (!empty ($views))}
							<div class="row">
								<div class="col-xs-12">
									<div class="main-box">
										<header class="title-section main-box-header clearfix">
											<h2>Vistas</h2>
										</header>
										<div class="main-box-body clearfix">
											<div class="row">
												<div class="col-xs-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="default-view-{$moduleName}">Por defecto <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<select id="default-view-{$moduleName}" name="moduleprofiles[{$moduleName}][defaultview]" class="form-control default-view-permission">
															<option value="">Vista por defecto del módulo</option>
		{foreach $views as $view}
			{assign var='viewName' value=$view->getName ()}
			{assign var='viewProfile' value=null}
			{if (!empty ($viewProfiles))}
				{foreach $viewProfiles as $profile}
					{if ($profile->getModuleName () == $moduleName) && ($profile->getViewName () == $viewName)}
						{assign var='viewProfile' value=$profile}
						{break}
					{/if}
				{/foreach}
			{/if}
															<option value="{$viewName}"{if (isset ($viewProfile)) && ($viewProfile->getDefault () === 1)} selected="selected"{/if}>{if ($viewName == 'All')}{$APP.COMBO_ALL}{else}{$viewName}{/if}</option>
		{/foreach}
														</select>
													</div>
												</div>
											</div>
		{foreach $views as $view}
			{assign var='viewName' value=$view->getName ()}
			{assign var='viewProfile' value=null}
			{if (!empty ($viewProfiles))}
				{foreach $viewProfiles as $profile}
					{if ($profile->getModuleName () == $moduleName) && ($profile->getViewName () == $viewName)}
						{assign var='viewProfile' value=$profile}
						{break}
					{/if}
				{/foreach}
			{/if}
											<div class="col-xs-6">
												<div class="col-md-7">
													<div class="label-input">
														<label for="view-{$moduleName}-{$viewName}">{if ($viewName == 'All')}{$APP.COMBO_ALL}{else}{$viewName}{/if}</label>
													</div>
												</div>
												<div class="form-group col-md-2 field-container">
													<input type="checkbox" id="view-{$moduleName}-{$viewName}" name="moduleprofiles[{$moduleName}][viewpermissions][{$viewName}]" value="Y"{if (!isset ($viewProfile)) || ($viewProfile->getAccessPermission () === 0)} checked="checked"{/if} class="view-permission" />
												</div>
											</div>
		{/foreach}
										</div>
									</div>
								</div>
							</div>
	{/if}
						</div>
					</div>
				</div>
{/foreach}
			</div>
		</div>
	</div>
	<div class="row" style="padding-top: 20px;">
		<div class="col-xs-12">
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info" style="margin-right: 5px;">{$APP.LBL_SAVE_LABEL}</button>
				<a href="index.php?module=Settings&amp;action=ProfileListView&amp;parenttab=Settings" class="btn btn-warning">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
{literal}
(function (jQuery) {
	var checked = {};

	var init = function () {
		var modulePanels = jQuery ('.module-panel'),
			n            = modulePanels.length,
			i, modulePanel;

		for (i = 0; i < n; i += 1) {
			modulePanel = jQuery (modulePanels [ i ]);
			checked [ modulePanel.attr ('data-module-name') ] = modulePanel.closest ('.module-permissions').is (':visible');
		}
	};

	var showApplicationModules = function (selectElement) {
		var applications = jQuery (selectElement),
			selectedOption = applications.find ('option:selected'),
			moduleNames, moduleName, i, n;

		if ((selectedOption.length === 0) || (selectedOption.val ().trim () === '')) {
			moduleNames = null;
		} else {
			moduleNames = selectedOption.attr ('data-module-names').split (',');
		}

		if ((!jQuery.isArray (moduleNames)) || (moduleNames.length === 0)) {
			for (moduleName in checked) {
				if (!checked.hasOwnProperty (moduleName)) {
					continue;
				}

				checked [ moduleName ] = false;
				toggleVisibility ('module-' + moduleName);
			}
			jQuery ('.module-permissions').show ();
		} else {
			for (moduleName in checked) {
				if (!checked.hasOwnProperty (moduleName)) {
					continue;
				}

				if (jQuery.inArray (moduleName, moduleNames) !== -1) {
					checked [ moduleName ] = false;
					jQuery ('#module-permissions-' + moduleName).show ();
				} else {
					checked [ moduleName ] = true;
					jQuery ('#module-permissions-' + moduleName).hide ();
				}
				toggleVisibility ('module-' + moduleName);
			}
		}
	};

	var toggleVisibility = function (panelId) {
		var panel                 = jQuery ('#' + panelId),
			moduleName            = panel.attr ('data-module-name'),
			defaultViewPermission = panel.find ('.default-view-permission'),
			fieldPermissions      = panel.find ('.field-permission'),
			standardPermissions   = panel.find ('.standard-permission'),
			utilityPermissions    = panel.find ('.utility-permission'),
			viewPermissions       = panel.find ('.view-permission');

		standardPermissions.prop ('checked', !checked [ moduleName ]);
		utilityPermissions.prop ('checked', !checked [ moduleName ]);
		fieldPermissions.not (':disabled').prop ('checked', !checked [ moduleName ]);
		defaultViewPermission.val ('');
		viewPermissions.prop ('checked', !checked [ moduleName ]);
		checked [ moduleName ] = !checked[ moduleName ];
	};

	var validateProfile = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#profile-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre del perfil');
			field.focus ();
			return false;
		}

		return true;
	};

	window.ProfileUtils = {
		showApplicationModules: showApplicationModules,
		toggleVisibility: toggleVisibility,
		validateProfile: validateProfile
	};

	jQuery (document).ready (init);
} (jQuery));
{/literal}
</script>
{/strip}