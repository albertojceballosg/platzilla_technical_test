{strip}
<link rel="stylesheet" href="themes/centaurus/css/libs/select2.css" type="text/css" />
{if ($MENSAJE != '')}
<div class="alert alert-danger fade in">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	<i class="fa fa-times-circle fa-fw fa-lg"></i>
	<strong>Error!</strong> {$MENSAJE.descripcion}
</div>
{/if}
<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index">Configuración</a></li>
			<li class="active"><span>Duplicador de Aplicaciones</span></li>
		</ol>
		<h1>Duplicador de Aplicaciones</h1>
	</div>
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2>Datos de la Aplicación</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="row col-lg-12">
					<div class="col-lg-3">Código:</div>
					<div class="col-lg-9">{$INFOAPPDUPLICAR.app_code}</div>
					<div class="col-lg-3">Precio:</div>
					<div class="col-lg-9">{$INFOAPPDUPLICAR.app_price}</div>
					<div class="col-lg-3">Nombre:</div>
					<div class="col-lg-9">{$INFOAPPDUPLICAR.app_name}</div>
					<div class="col-lg-3">Descripción:</div>
					<div class="col-lg-9">{$INFOAPPDUPLICAR.app_descripcion}</div>
				</div>
			</div>
		</div>
	</div>
	<form action="index.php" method="post" id="ModuleDuplicator" name="ModuleDuplicator">
		<input type="hidden" name="module" id="module" value="Settings" class="form-control" />
		<input type="hidden" name="action" id="action" value="AppDuplicator3" class="form-control" />
		<input type="hidden" name="appidaduplicar" id="appidaduplicar" value="{$APPIDADUPLICAR}" class="form-control" />
		<input type="hidden" name="appnueva" id="appnueva" value="{$APPNUEVA}" class="form-control" />
		<input type="hidden" name="titulonuevo" id="titulonuevo" value="{$NUEVOTITULO}" class="form-control" />
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>Módulos Asociados</h2>
				</header>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
							<tr>
								<th colspan="2">Módulo actual</th>
								<th colspan="2">Nuevo módulo</th>
							</tr>
							<tr>
								<th width="25%">Nombre código</th>
								<th width="25%">Nombre público</th>
								<th width="25%">Nombre código</th>
								<th width="25%">Nombre público</th>
							</tr>
							</thead>
							<tbody>
{foreach $MODULOSASOCIADOS as $key => $module}
							<tr>
								<td>
									{$module.name}
									<input type="hidden" name="tabidoriginal[{$key}]" id="tabidoriginal_{$key}" value="{$module.tabid}" class="form-control" />
								</td>
								<td>{$module.tablabel}</td>
								<td>
									<input type="text" name="codigonuevomodulo[{$key}]" id="codigonuevomodulo_{$key}" value="{if (isset ($NEW_MODULE_NAMES.$key))}{$NEW_MODULE_NAMES.$key}{/if}" maxlength="25" class="form-control module-name" placeholder="" />
								</td>
								<td>
									<input type="text" name="nombrenuevomodulo[{$key}]" id="nombrenuevomodulo_{$key}" value="{if (isset ($NEW_MODULE_LABELS.$key))}{$NEW_MODULE_LABELS.$key}{/if}" maxlength="100" class="form-control module-label" placeholder="" />
								</td>
							</tr>
{/foreach}
							</tbody>
						</table>
					</div>
					<input type="submit" class="btn btn-primary btn-md" name="btnduplicar" value="Duplicar" id="btnDuplicar">
					&nbsp;
					<a class="btn btn-primary" href="index.php?module=Settings&action=AppDuplicator">Atrás</a>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<script type="text/javascript" src="themes/centaurus/js/select2.min.js"></script>
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

		var normalizeFieldContents = function (selector) {
			var field = jQuery (selector);
			field.val (getNormalizedText (field.val ()));
		};

		var validateForm = function (evt) {
			var form = jQuery (evt.currentTarget),
				moduleNames, moduleLabels, field, value, i, n;

			moduleNames = form.find ('.module-name');
			moduleLabels = form.find ('.module-label');

			if ((moduleNames.length === 0) || (moduleLabels.length === 0)) {
				alert ('No hay módulos a duplicar');
				evt.preventDefault ();
				return;
			}

			if (moduleNames.length !== moduleLabels.length) {
				alert ('La cantidad de campos de nombre y título no coincidem!!!');
				evt.preventDefault ();
				return;
			}

			if (moduleNames.length > 0) {
				n = moduleNames.length;
				for (i = 0; i < n; i += 1) {
					field = jQuery (moduleNames [i]);
					value = field.val ();
					if ((value === undefined) || (value === null) || (value.trim () === '')) {
						alert ('Introduce el nombre del módulo');
						field.focus ();
						evt.preventDefault ();
						return;
					}

					field = jQuery (moduleLabels [ i ]);
					value = field.val ();
					if ((value === undefined) || (value === null) || (value.trim () === '')) {
						alert ('Introduce el título del módulo');
						field.focus ();
						evt.preventDefault ();
						return;
					}
				}
			}
		};

		jQuery (document).ready (function () {
			jQuery ('.module-name').keyup (function (evt) {
				normalizeFieldContents (evt.currentTarget);
			});
			jQuery ('form[name="ModuleDuplicator"]').submit (validateForm);
		});
	} (jQuery));
</script>
{/strip}