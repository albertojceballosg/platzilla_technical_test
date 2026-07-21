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
			<td rowspan="2" valign="top"><div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-list-alt emerald-bg"></i></div></td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a></li>
					<li><a href="index.php?module=Settings&action=ModuleManager&parenttab=Settings">{$MOD.VTLIB_LBL_MODULE_MANAGER|upper} </a></li>
					<li class="active">DUPLICADOR DE MÓDULOS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Permite duplicar un módulo de campos del sistema</td>
		</tr>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="title-section main-box-header clearfix"><h2>Información general</h2></header>
				<div class="main-box-body clearfix">
					<form method="post" action="index.php" onsubmit="return ModuleDuplicatorUtils.validateDuplicatorForm (this);">
						<input type="hidden" name="module" value="Settings" />
						<input type="hidden" name="action" value="DuplicateModule" />
						<input type="hidden" name="Ajax" value="true" />
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="old-module-name">Módulo a duplicar <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="old-module-name" name="oldmodulename" class="form-control">
										<option value=""{if (empty ($SELECTED_OLD_MODULE_NAME))} selected="selected"{/if}></option>
{foreach $AVAILABLE_MODULES as $module}
										<option value="{$module.name}"{if (!empty ($SELECTED_OLD_MODULE_NAME)) && ($module.name == $SELECTED_OLD_MODULE_NAME)} selected="selected"{/if}>{$module.tablabel} ({$module.name})</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="new-menu-label">Menú donde aparecerá <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<select id="new-menu-label" name="newmenulabel" class="form-control">
									<option value=""{if (empty ($SELECTED_NEW_MENU_LABEL))} selected="selected"{/if}></option>
{foreach $AVAILABLE_MENU_LABELS as $menuLabel}
									<option value="{$menuLabel}"{if (!empty ($SELECTED_NEW_MENU_LABEL)) && ($menuLabel == $SELECTED_NEW_MENU_LABEL)} selected="selected"{/if}>{$menuLabel}</option>
{/foreach}
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="new-module-name">Nuevo nombre <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<input type="text" id="new-module-name" name="newmodulename" value="{$SELECTED_NEW_MODULE_NAME}" class="form-control" maxlength="20" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="new-module-label">Nuevo título <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<input type="text" id="new-module-label" name="newmodulelabel" value="{$SELECTED_NEW_MODULE_LABEL}" class="form-control" maxlength="64" />
							</div>
						</div>
						<div class="col-md-12 text-center">
							<button type="submit" class="btn btn-primary" style="margin-right: 0.5em;">Duplicar</button>
							<a href="index.php?module=Settings&action=ModuleManager&parenttab=Settings" class="btn btn-warning">Cancelar</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
(function (jQuery) {
	var getNormalizedText = function (value) {
		var from = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
			to   = 'aaaaeeeeiiiioooouuuunc______',
			i, l;

		value = value.toLowerCase ().replace (' ', '_');

		// remove accents, swap ñ for n, etc
		for (i = 0, l = from.length; i < l; i++) {
			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
		}

		value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
		return value;
	};

	var validateDuplicatorForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#old-module-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el módulo a duplicar');
			field.focus ();
			return false;
		}

		field = form.find ('#new-menu-label');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el menú donde aparecerá');
			field.focus ();
			return false;
		}

		field = form.find ('#new-module-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el nuevo nombre');
			field.focus ();
			return false;
		} else {
			field.val (getNormalizedText (value));
		}

		field = form.find ('#new-module-label');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el nuevo título');
			field.focus ();
			return false;
		}

		return true;
	};

	window.ModuleDuplicatorUtils = {
		validateDuplicatorForm: validateDuplicatorForm
	};

	jQuery (document).ready (function () {
		jQuery ('#new-module-name').keyup (function (evt) {
			var field = jQuery (evt.currentTarget);
			field.val (getNormalizedText (field.val ()));
		});
	});
} (jQuery))
</script>
{/strip}