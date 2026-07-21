{strip}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css" />
<script type="text/javascript" src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="modules/Settings/wizard-utils.js"></script>
<div class="wizard" id="myWizard">
	<div class="wizard-inner">
		<ul class="steps">
			<li class="active"><span class="badge badge-primary">1</span>Paso 1<span class="chevron"></span></li>
			<li><span class="badge">2</span>Paso 2<span class="chevron"></span></li>
			<li><span class="badge">3</span>Paso 3<span class="chevron"></span></li>
			<li><span class="badge">4</span>Paso 4<span class="chevron"></span></li>
		</ul>
		<div class="actions">
			<button data-last="Finish" id="button_next" class="btn btn-success btn-mini btn-next" type="button" onclick="WizardUtils.goForwardToStep2 ();">
				{$MOD.LBL_SIGUIENTE} <i class="icon-arrow-right"></i>
			</button>
		</div>
	</div>
	<div class="step-content">
		<header class="main-box-header clearfix">
			<h2>{$MOD.LBL_INFORMACION_BASICA_DEL_MODULO}</h2>
		</header>
		<div class="main-box-body clearfix">
			<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso1" data-dialog="#texto{$ID_DLG_CREACION_MODULOS}">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="wizardPaso2" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="form-group">
					<label for="txtbox_nombreCodigo">{$MOD.LBL_NOMBRE_CODIGO}</label>
					<input type="text" id="txtbox_nombreCodigo" name="nombreCodigo" value="{$NOMBRE_CODIGO}" class="form-control module-name" data-toggle="tooltip" data-placement="top" title="{$MOD.LBL_AYUDA_NOMBRE_CODIGO}" />
				</div>
				<div class="form-group">
					<label for="txtbox_nombrePublico">{$MOD.LBL_NOMBRE_PUBLICO}</label>
					<input type="text" id="txtbox_nombrePublico" name="nombrePublico" value="{$NOMBRE_PUBLICO}" class="form-control module-label" />
				</div>
				<div class="form-group">
					<label for="tipoModulo">{$MOD.LBL_TIPO_MODULO}</label>
					<select id="tipoModulo" name="tipoModulo" class="form-control">
						<option value="Completo"{if ($SELECTED_MODULE_TYPE == 'Completo')} selected="selected"{/if}>{$MOD.LBL_MODULO_CON_CAMPOS}</option>
						<option value="Simple"{if ($SELECTED_MODULE_TYPE == 'Simple')} selected="selected"{/if}>{$MOD.LBL_MODULO_SIMPLE}</option>
					</select>
				</div>
				<div class="checkbox-nice">
					<input type="checkbox" id="isAdmin" name="isAdmin"{if ($IN_ADMINISTRATION)} checked="checked"{/if} />
					<label for="isAdmin">{$MOD.LBL_MOD_ADMINISTRACION}</label>
				</div>
				<div id="appMadre" class="form-group"{if (!$IN_ADMINISTRATION)} style="display: none"{/if}>
{if (!empty ($APPLICATIONS))}
					<label for="appMadre">{$MOD.LBL_APLICACION_MADRE}</label>
					<select id="appMadre" name="appMadre" class="form-control">
						<option value="{'LBL_SELECCIONE_APLICACION'|@getTranslatedString}">{'LBL_SELECCIONE_APLICACION'|@getTranslatedString}</option>
	{foreach $APPLICATIONS as $application}
						<option value="{$application.app_name}"{if ($SELECTED_APPLICATION == $application.app_name)} selected="selected"{/if}>{$application.app_name|@getTranslatedString}</option>
	{/foreach}
					</select>
{/if}
				</div>
				<div id="modPadre" class="form-group"{if ($IN_ADMINISTRATION)} style="display: none"{/if}>
{if (!empty ($PARENT_MODULES))}
					<label for="moduloPadre">{$MOD.LBL_MODULO_PADRE}</label>
					<select id="moduloPadre" name="moduloPadre" class="form-control">
	{foreach $PARENT_MODULES as $parentModule}
						<option value="{$parentModule.parenttab_label}"{if ($SELECTED_PARENT_MODULE == $parentModule.parenttab_label)} selected="selected"{/if}>{$parentModule.parenttab_label|@getTranslatedString}</option>
	{/foreach}
					</select>
{/if}
				</div>
			</form>
		</div>
	</div>
</div>
{/strip}