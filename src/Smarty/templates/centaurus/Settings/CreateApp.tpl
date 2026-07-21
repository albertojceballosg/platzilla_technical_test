{strip}
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<script type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
{if (isset ($ERROR))}
<div class="alert alert-danger">
	<strong>Error:</strong> {$ERROR}
</div>
{/if}
<form action="index.php?module=Settings&action=SaveEditApps" method="post" id="SaveEditApps" name="index" enctype="multipart/form-data">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-9 pull-left">
				<h1>
					<a href="index.php?module=Settings&action=ConfigApps&parenttab=Settings">{$MOD.LBL_TITLE_MODAL_CREATE_APP}</a>
				</h1>
			</div>
			<div class="col-lg-3 pull-right text-right">
				<button class="btn btn-primary" type="button" id="btnsave" onclick="if (validateForm ()) {ldelim} validateRepeatData(); {rdelim}">{$MOD.LBL_SAVE}</button>&nbsp;
				<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=ConfigApps">{$MOD.LBL_CANCEL_BUTTON}</a>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box no-header">
				<div class="main-box-body clearfix">
					<input type="hidden" id="nestable-output" name="nestable-output" />
					<div class="form-group">
						<label for="app_code" id="label_app_code">{$MOD.LBL_CONFIG_APPS_CODE}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" id="app_code" name="code" value="{if (isset ($APPLICATION))}{$APPLICATION.code}{/if}" maxlength="14" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_name" id="label_app_name">{$MOD.LBL_CONFIG_APPS_NAME}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" id="app_name" name="name" value="{if (isset ($APPLICATION))}{$APPLICATION.name}{/if}" maxlength="100" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_price" id="label_app_price">{$MOD.LBL_CONFIG_APPS_PRICE}</label>
						<input type="text" id="app_price" name="price" value="{if (isset ($APPLICATION))}{$APPLICATION.price}{/if}" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_url" id="label_app_url">{$MOD.LBL_CONFIG_APPS_URL}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" id="app_url" name="url" value="{if (isset ($APPLICATION))}{$APPLICATION.url}{/if}" maxlength="255" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_descripcion" id="label_app_descripcion">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</label>&nbsp;<span style="color: red;">*</span>
						<textarea rows="3" id="app_descripcion" name="descripcion" class="form-control">{if (isset ($APPLICATION))}{$APPLICATION.description}{/if}</textarea>
					</div>
					<div class="form-group">
						<label for="app_status">{$MOD.LBL_CONFIG_APPS_STATUS}</label>&nbsp;<span style="color: red;">*</span>
						<select id="app_status" name="status" class="form-control">
							<option value="Activa"{if (isset ($APPLICATION)) && ($APPLICATION.status == 'Activa')} selected="selected"{/if}>{$MOD.LBL_ACTIVE}</option>
							<option value="Inactiva"{if (isset ($APPLICATION)) && ($APPLICATION.status == 'Inactiva')} selected="selected"{/if}>{$MOD.LBL_INACTIVE}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="app_category">{$MOD.LBL_CATEGORYAPPS_LABEL} </label>&nbsp;<span style="color: red;">*</span>
						<select id="app_category" name="category[]" multiple="multiple" class="form-control">
{foreach $CATEGORIES as $category}
							<option value="{$category.catappid}"{if (isset ($APPLICATION)) && (in_array ($category.catappid, $APPLICATION.category))} selected="selected"{/if}>{$category.name}</option>
{/foreach}
						</select>
					</div>
					<div class="form-group">
						<label for="app_status">{$MOD.LBL_IMAGE_APPS}</label>&nbsp;<span style="color: red;">*</span>
						<br />
						<input type="hidden" name="MAX_FILE_SIZE" value="800000" />
						<input type="button" name="binFileButton" id="binFileButton" class="btn btn-primary btn-sm" value="Seleccionar archivo" onclick="jQuery ('#binFile').trigger ('click');" />
						<label id="displaySize"></label>
						<input type="file" name="binFile" id="binFile" style="display: none" value="" accept="image/png" onchange="validatePngFilenameImage (this, 0.8); if (this.value != '') {ldelim} jQuery ('#binFileButton').val (this.value); {rdelim}" />[]
						<input type="hidden" name="binFile_hidden" />
					</div>
					<div class="form-group">
						<label for="app_descripcion">{$MOD.LBL_ASIG_MODULES}</label>&nbsp;<span style="color: red;">*</span>
						<div class="row cf nestable-lists">
							<div class="table-responsive">
								<table class="table" width="100%" cellpadding="5" cellspacing="0">
									<tr>
										<th width="50%" class="text-center">{$MOD.LBL_FREE}</th>
										<th width="50%" class="text-center">{$MOD.LBL_ASIG}</th>
									</tr>
									<tr>
										<td>
											<div id="nestable2" class="dd" style="vertical-align: top; width: 100%; height: 250px; overflow: auto;">
												<ul class="dd-list">
{foreach $MODULES as $module}
	{if (!isset ($APPLICATION)) || (!in_array ($module.tabid, $APPLICATION.modules))}
													<li class="dd-item" data-id="{$module.tabid}">
														<div class="dd-handle">{$module.tablabel} ({$module.name})</div>
													</li>
	{/if}
{/foreach}
												</ul>
											</div>
										</td>
										<td>
											{$MOD.LBL_CONFIG_APPS_SELECT_APPLICATIONS}
											<div id="nestable" class="dd" style="vertical-align: top; width: 100%; height: 250px; overflow: auto;">
{if (!isset ($APPLICATION)) || (count ($APPLICATION.modules) == 0)}
												<div class="dd-empty"></div>
{else}
												<ul class="dd-list">
	{foreach $APPLICATION.modules as $applicationModuleId}
		{foreach $MODULES as $module}
			{if ($module.tabid == $applicationModuleId)}
													<li class="dd-item" data-id="{$module.tabid}">
														<div class="dd-handle">{$module.tablabel} ({$module.name})</div>
													</li>
				{break}
			{/if}
		{/foreach}
	{/foreach}
												</ul>
{/if}
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-9 pull-left">
			</div>
			<div class="col-lg-3 pull-right text-right">
				<button class="btn btn-primary" type="button" id="btnsave" onclick="if (validateForm ()) {ldelim} validateRepeatData (); {rdelim}">{$MOD.LBL_SAVE}</button>
				&nbsp;
				<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=ConfigApps">{$MOD.LBL_CANCEL_BUTTON}</a>
			</div>
		</div>
	</div>
</form>
<div id="editdiv" style="display: none; position: absolute; width: 400px;"></div>
<div class="md-overlay"></div>
<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
		var nestables = jQuery ('#nestable');
		var updateOutput = function (e) {
			var list   = e.length ? e : jQuery (e.target),
				output,
				modules = [],
				elementos = list[ 0 ][ 'children' ][ 0 ][ 'children' ],
				i,
				applications;
			for (i = elementos.length - 1; i >= 0; i--) {
				if (!elementos[ i ][ 'dataset' ].hasOwnProperty ('id')) {
					continue;
				}
				var obj = {};
				obj[ 'id' ] = elementos[ i ][ 'dataset' ][ 'id' ];
				modules.push (obj);
			}
			if (modules.length > 0) {
				jQuery ('#nestable-output').val (window.JSON.stringify (modules));
			} else {
				output = list.data ('output');
				if (!output) {
					return;
				}
				if (window.JSON) {
					output.val (window.JSON.stringify (list.nestable ('serialize')));
				} else {
					output.val ('Contacte al administrador');
				}
				applications = JSON.parse (output.val ());
			}
		};

		nestables.nestable ({
			group:    1,
			maxDepth: 0
		}).on ('change', updateOutput);

		jQuery ('#nestable2').nestable ({
			group:    1,
			maxDepth: 0
		});

		jQuery ('#app_code').keyup (function () {
			validField ('app_code');
		});

		updateOutput (nestables.data ('output', jQuery ('#nestable-output')));
	});

	function validateForm () {
		if (!jQuery ('#app_code').val ()) {
			alert ('Especifique el código de la aplicación');
			return false;
		}
		if (!jQuery ('#app_name').val ()) {
			alert ('Especifique el nombre de la aplicación');
			return false;
		}
		if (!jQuery ('#app_descripcion').val ()) {
			alert ('Introduzca la descripción de la aplicación');
			return false;
		}
		if (!jQuery ('#app_category').val ()) {
			alert ('Seleccione al menos una categoría para la aplicación');
			return false;
		}
		return true;
	}

	function validateRepeatData () {
		var param = 'validation=norepeatnameapp&app_code=' + jQuery ('#app_code').val () + '&app_name=' + jQuery ('#app_name').val ();
		new Ajax.Request (
			'index.php',
			{
				queue:      {
					position: 'end',
					scope: 'command'
				},
				method:     'post',
				postBody:   'module=Settings&action=SettingsAjax&file=validateSaveEditApps&' + param,
				onComplete: function (response) {
					if (response.responseText == 'repeated') {
						alert ('La aplicación ya existe');
						return false
					} else {
						return validateInfo ();
					}
				}
			}
		);
	}

	function validateInfo () {
		if (jQuery ('#app_url').val () == '') {
			alert ('Especifique la url de la aplicación');
			return false;
		}
		if (jQuery ('#binFileButton').val () == 'Seleccionar Archivo') {
			alert ('Elija una imagen para la aplicación');
			return false;
		}
		if (jQuery ('#nestable-output').val () == '[]') {
			alert ('Agregue al menos un módulo a esta aplicación');
			return false;
		}
		jQuery ('#SaveEditApps').submit ();
	}
{/literal}
</script>
{/strip}