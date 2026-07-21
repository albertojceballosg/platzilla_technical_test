{strip}
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<script type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
{if (isset ($ERROR))}
<div class="alert alert-danger">
	<strong>Error:</strong> {$ERROR}
</div>
{/if}
<form action="index.php?module=Settings&action=SaveEditApps" id="SaveEditApps" method="post" name="index" enctype="multipart/form-data">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-9 pull-left">
				<h1>
					<a href="index.php?module=Settings&action=ConfigApps&parenttab=Settings">{$MOD.LBL_TITLE_MODAL_EDIT_APP}</a>
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
					<div class="form-group">
						<label for="app_code" id="label_app_code">{$MOD.LBL_CONFIG_APPS_CODE}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" id="app_code" name="code" value="{$APPLICATION.code}" maxlength="14" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_name" id="label_app_name">{$MOD.LBL_CONFIG_APPS_NAME}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" id="app_name" name="name" value="{$APPLICATION.name}" maxlength="100" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_price" id="label_app_name">{$MOD.LBL_CONFIG_APPS_PRICE}</label>
						<input type="text" id="app_price" name="price" value="{$APPLICATION.price}" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_url" id="label_app_url">{$MOD.LBL_CONFIG_APPS_URL}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" id="app_url" name="url" value="{$APPLICATION.url}" maxlength="255" class="form-control" />
					</div>
					<div class="form-group">
						<label for="app_descripcion" id="label_app_descripcion">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</label>&nbsp;<span style="color: red;">*</span>
						<textarea rows="3" id="app_descripcion" name="descripcion" class="form-control">{$APPLICATION.description}</textarea>
					</div>
					<div class="form-group">
						<label for="app_status">{$MOD.LBL_CONFIG_APPS_STATUS}</label>&nbsp;<span style="color: red;">*</span>
						<select id="app_status" name="status" class="form-control">
							<option value="Activa"{if ($APPLICATION.status == 'Activa')} selected="selected"{/if}>{$MOD.LBL_ACTIVE}</option>
							<option value="Inactiva"{if ($APPLICATION.status == 'Inactiva')} selected="selected"{/if}>{$MOD.LBL_INACTIVE}</option>
						</select>
					</div>
					<div class="form-group">
						<label for="app_category">{$MOD.LBL_CATEGORYAPPS_LABEL}</label>&nbsp;<span style="color: red;">*</span>
						<select id="app_category" name="category[]" multiple="multiple" class="form-control">
{foreach $CATEGORIES as $category}
							<option value="{$category.catappid}"{if (in_array ($category.catappid, $APPLICATION.category))} selected="selected"{/if}>{$category.name}</option>
{/foreach}
						</select>
					</div>
					<div class="form-group">
						<label for="app_status">{$MOD.LBL_IMAGE_APPS}</label>&nbsp;<span style="color: red;">*</span>
						<br />
						<input type="hidden" name="MAX_FILE_SIZE" value="800000" />
						<input type="hidden" name="PREV_FILE" value="" />
						<input type="button" name="binFileButton" id="binFileButton" class="btn btn-primary btn-sm" value="{if ($APPLICATION.image == 1)}{$APPLICATION.code}.png{else}Seleccionar Archivo{/if}" onclick="jQuery ('#binFile').trigger ('click');" />
						<label id="displaySize"></label>
						<input type="file" accept="image/png" name="binFile" id="binFile" style="display: none" value="" onchange="validatePngFilenameImage (this, 0.8); if (this.value != '') {ldelim} jQuery ('#binFileButton').val (this.value); {rdelim}" />[{if ($APPLICATION.image == 1)}{$APPLICATION.code}.png{/if}]
						<input type="hidden" name="binFile_hidden" value="{if ($APPLICATION.image == 1)}{$APPLICATION.code}.png{/if}" />
					</div>
					<div class="form-group">
						<textarea id="nestable-output" name="nestable-output" style="display: none" placeholder=""></textarea>
						<input type="hidden" id="record" name="record" value="{$APPLICATION.id}" />
						<input type="hidden" id="mode" name="mode" value="edit" />
						<label for="">{$MOD.LBL_ASIG_MODULES}</label>&nbsp;<span style="color: red;">*</span>
						<div class="row cf nestable-lists">
							<div class="table-responsive">
								<table class="table" width="100%" cellpadding="5" cellspacing="0">
									<tr>
										<th width="50%" class="text-center">{$MOD.LBL_FREE}</th>
										<th width="50%" class="text-center">{$MOD.LBL_ASIG}</th>
									</tr>
									<tr>
										<td>
											<div id="nestable2" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
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
											<div id="nestable" class="dd" style="vertical-align: top; width: 100%; height: 250px; overflow: auto;">
{if (!isset ($APPLICATION)) || (count ($APPLICATION.modules) == 0)}
												<div class="dd-empty"></div>
{else}
												<ol class="dd-list">
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
												</ol>
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
		var nestable = jQuery ('#nestable');
		var updateOutput = function (e) {
			var list   = e.length ? e : jQuery (e.target),
				output = list.data ('output');
			var modules = [];
			var elementos = list[ 0 ][ 'children' ][ 0 ][ 'children' ];
			for (var i = elementos.length - 1; i >= 0; i--) {
				var obj = {};
				obj[ 'id' ] = elementos[ i ][ 'dataset' ][ 'id' ];
				modules.push (obj);
			}

			if (modules != "") {
				jQuery ('#nestable-output').html (window.JSON.stringify (modules))
			} else {
				jQuery ('#nestable-output').html (window.JSON.stringify (list.nestable ('serialize')))
			}

			var applications = JSON.parse (output.val ());
		};

		nestable.nestable ({
			group: 1
		}).on ('change', updateOutput);

		jQuery ('#nestable2').nestable ({
			group: 1
		});

		jQuery ('#app_code').keyup (function () {
			validField ('app_code');
		});

		updateOutput (nestable.data ('output', jQuery ('#nestable-output')));
	});

	function validateRepeatData () {
{/literal}
		var param = 'validation=norepeatnameappEdit&app_code=' + jQuery ('#app_code').val () + '&app_name=' + jQuery ('#app_name').val () + '&appid={$APPLICATION.id}';
{literal}
		new Ajax.Request (
			'index.php',
			{
				queue:      {
					position: 'end',
					scope: 'command'
				},
				method:     'post',
				postBody:   'action=SettingsAjax&module=Settings&file=validateSaveEditApps&' + param,
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
			alert ('Introduzca la url de la aplicación');
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

	function validateForm () {
		if (!jQuery ('#app_code').val ()) {
			alert ('Introduzca el código de la aplicación');
			return false;
		}
		if (!jQuery ('#app_name').val ()) {
			alert ('Introduzca el nombre de la aplicación');
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
{/literal}
</script>
{/strip}