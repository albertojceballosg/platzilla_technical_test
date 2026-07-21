{strip}
<div id="email-box" class="clearfix">
	<div class="row">
		<div class="col-lg-12">
			<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<td rowspan="2" valign="top">
						<div class="infographic-box" style="width: 30px; padding: 0;">
							<i class="fa fa-cubes green-bg"></i>
						</div>
					</td>
					<td class="heading2" valign="bottom">
						<ol class="breadcrumb">
							<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
							<li>{$MOD.LBL_CUSTOMIZE_MODENT_NUMBER}</li>
						</ol>
					</td>
				</tr>
				<tr>
					<td class="small" colspan="3" valign="top">{$MOD.LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION}</td>
				</tr>
			</table>
		</div>
		<div class="col-lg-12">
			<div class="main-box no-header clearfix" style="">
				<form name="entity-number-form" action="index.php" method="post">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="CustomModEntityNo" />
					<input type="hidden" name="Ajax" value="true" />
					<div class="table-responsive">
						<table class="table" id="proTabList">
							<thead>
							<tr>
								<th class="lvtCol">{$MOD.LBL_MODULE}</th>
								<th class="lvtCol" width="15%">{$MOD.LBL_PREFIJO}</th>
								<th class="lvtCol" width="15%">{$MOD.LBL_SECUENCIA}</th>
								<th class="lvtCol" width="15%">{$MOD.LBL_SECUENCIA_ACTUAL}</th>
							</tr>
							</thead>
							<tbody>
{if (count ($MODULES) > 0)}
	{foreach $MODULES as $module}
							<tr>
								<td class="lvtCol">
									<input type="hidden" name="moduleids[]" value="{$module.tabid}" />
									{$module.name}
								</td>
								<td class="lvtCol" width="15%">
									<input type="text" name="prefixes[]" value="{$module.prefix}" class="form-control prefix" placeholder="" />
								</td>
								<td class="lvtCol" width="15%">
									<input type="text" name="startids[]" value="{$module.start_id}" class="form-control start-id" placeholder="" style="text-align: right;" />
								</td>
								<td class="lvtCol" width="15%">
									<input type="text" name="currentids[]" value="{$module.cur_id}" class="form-control current-id" placeholder="" style="text-align: right;" />
								</td>
							</tr>
	{/foreach}
{else}
							<tr>
								<th colspan="4">No se encuentran registrados módulos de tipo entidad</th>
							</tr>
{/if}
							</tbody>
						</table>
					</div>
					<div class="action-bar text-right" style="margin: 0 10px 10px 0;">
						<button type="submit" class="btn btn-primary">{$MOD.LBL_SAVE}</button>
						&nbsp;
						<a href="index.php?module=Settings&action=index&parenttab=Settings" class="btn btn-warning">{$MOD.LBL_CANCEL_BUTTON}</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
{literal}
	(function (jQuery) {
		var isFormValid = function (form) {
			var prefixes, startIds, currentIds, field, value, i, n;

			prefixes = form.find ('.prefix');
			startIds = form.find ('.start-id');
			currentIds = form.find ('.current-id');
			if ((prefixes.length !== startIds.length) || (prefixes.length !== currentIds.length)) {
				alert ('La cantidad de campos no es igual. Notifícanos de este error!');
				return false;
			}

			n = prefixes.length;
			for (i = 0; i < n; i += 1) {
				field = jQuery (prefixes [ i ]);
				value = field.val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					alert ('Introduce el prefijo del módulo');
					field.focus ();
					return false;
				}
				if (/^[a-zA-Z]+\-*$/.test (value) === false) {
					alert ('Introduce un prefijo compuesto por sólo letras, sin símbolos ni espacios');
					field.focus ();
					return false;
				}

				field = jQuery (startIds [ i ]);
				value = field.val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					alert ('Introduce la secuencia inicial del módulo');
					field.focus ();
					return false;
				}
				if (/^[0-9]+$/.test (value) === false) {
					alert ('Introduce una secuencia inicial compuesta por sólo números, sin símbolos ni espacios');
					field.focus ();
					return false;
				}

				field = jQuery (currentIds [ i ]);
				value = field.val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					alert ('Introduce la secuencia actual del módulo');
					field.focus ();
					return false;
				}
				if (/^[0-9]+$/.test (value) === false) {
					alert ('Introduce una secuencia actual compuesta por sólo números, sin símbolos ni espacios');
					field.focus ();
					return false;
				}
			}
			return true;
		};

		var onFormSubmitHandler = function (evt) {
			var form = jQuery ('form[name="entity-number-form"]');
			if (!isFormValid (form)) {
				evt.preventDefault ();
			}
		};

		var onPrefixKeyUpHandler = function (evt) {
			var field = jQuery (evt.currentTarget);
			field.val (field.val ().toUpperCase ());
		};

		var onDocumentReadyHandler = function () {
			jQuery ('form[name="entity-number-form"]').on ('submit', onFormSubmitHandler);
			jQuery ('.prefix').keyup (onPrefixKeyUpHandler);
		};

		jQuery (document).ready (onDocumentReadyHandler);
	}) (jQuery);
{/literal}
</script>
{/strip}