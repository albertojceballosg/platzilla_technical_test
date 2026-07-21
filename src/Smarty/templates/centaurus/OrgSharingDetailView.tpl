{strip}
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-lock emerald-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">{$MOD.LBL_SHARING_ACCESS|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_SHARING_ACCESS_DESCRIPTION}</td>
		</tr>
		</tbody>
	</table>
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">{$CMOD.LBL_GLOBAL_ACCESS_PRIVILEGES}</h2>
			<form action="index.php" method="post" onsubmit="return confirm ('{$CMOD.LBL_RECALC_MSG}');" class="pull-right text-right">
				<input type="hidden" name="module" value="Users" />
				<input type="hidden" name="action" value="RecalculateSharingRules" />
				<input type="hidden" name="parenttab" value="Settings" />
				<input type="hidden" name="Ajax" value="true" />
				<button type="submit" class="btn btn-info" style="margin-right: 5px;">{$CMOD.LBL_RECALCULATE_BUTTON}</button>
				<a href="index.php?module=Settings&amp;action=OrgSharingEditView&amp;parenttab=Settings" class="btn btn-warning">{$CMOD.LBL_CHANGE} {$CMOD.LBL_PRIVILEGES}</a>
			</form>
		</header>
		<div class="main-box-body clearfix">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-modulename"><b>Módulo</b></th>
						<th class="col-access"><b>Acceso</b></th>
						<th class="col-description"><b>Descripción</b></th>
					</tr>
					</thead>
					<tbody>
{if (!empty ($DEFAULT_SHARING))}
	{foreach $DEFAULT_SHARING as $module}
					<tr>
						<td class="col-modulename">{$AVAILABLE_MODULES[$module[0]]['tablabel']} ({$module[0]})</td>
						<td class="col-access">
							<span class="label label-{if ($module[1] == 'Public: Read, Create/Edit, Delete')}success{elseif (in_array ($module[1], array ('Public: Read Only', 'Public: Read, Create/Edit', 'Show Details', 'Show Details and Add Events')))}warning{else}danger{/if}">
		{if ($module[1] == 'Public: Read, Create/Edit, Delete')}
								Público
		{elseif (in_array ($module[1], array ('Public: Read Only', 'Public: Read, Create/Edit', 'Show Details', 'Show Details and Add Events')))}
								Semi público
		{else}
								Privado
		{/if}
							</span>
						</td>
						<td class="col-description">{$module[2]}</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="3" class="text-center">No hay privilegios registrados</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{if (!empty ($MODSHARING))}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">{$CMOD.LBL_CUSTOM_ACCESS_PRIVILEGES}</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="panel-group">
	{foreach $MODSHARING as $moduleName => $sharingData}
				<div class="panel panel-info">
					<div class="panel-heading">
						<h2 class="panel-title">
							<a data-toggle="collapse" href="#panel-{$sharingData@iteration}">{$AVAILABLE_MODULES[$moduleName]['tablabel']} ({$moduleName})</a>
							<span class="badge badge-default" style="margin-left: 1em;">{count ($sharingData[$moduleName])}</span>
							<button class="btn btn-link pull-right" title="{$CMOD.LBL_ADD_PRIVILEGES_BUTTON}" onclick="OrgSharingUtils.addRule ('{$moduleName}', '{$AVAILABLE_MODULES[$moduleName]['tablabel']}');"><i class="fa fa-plus"></i></button>
						</h2>
					</div>
					<div id="panel-{$sharingData@iteration}" class="panel-collapse collapse">
		{if (!empty ($sharingData))}
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
								<tr>
									<th class="col-from"><b>Registros asignados a</b></th>
									<th class="col-to"><b>Se otrogaron permisos de</b></th>
									<th class="col-privileges"><b>A</b></th>
									<th class="col-actions"><b>Acciones</b></th>
								</tr>
								</thead>
								<tbody>
			{foreach $sharingData[$moduleName] as $rule}
								<tr>
									<td class="col-from">{$FROM_OPTIONS[$rule.from].text}</td>
									<td class="col-to">{if ($rule.permission == 0)}Solo lectura{elseif ($rule.permission == 1)}Lectura y escritura{else}Desconocido{/if}</td>
									<td class="col-privileges">{$TO_OPTIONS[$rule.to].text}</td>
									<td class="col-actions">
										<button type="button" class="btn btn-link" onclick="OrgSharingUtils.editRule ({json_encode($rule)|escape: 'htmlall'}, '{$ruleModuleName}', '{$AVAILABLE_MODULES[$ruleModuleName]['tablabel']}');"><i class="fa fa-pencil"></i></button>
										<form action="index.php" method="post" onsubmit="return OrgSharingUtils.deleteRule ();" style="display: inline;">
											<input type="hidden" name="module" value="Settings" />
											<input type="hidden" name="action" value="DeleteSharingRule" />
											<input type="hidden" name="shareid" value="{$rule.shareid}" />
											<input type="hidden" name="Ajax" value="true" />
											<button type="submit" class="btn btn-link"><i class="fa fa-trash-o"></i></button>
										</form>
									</td>
								</tr>
			{/foreach}
								</tbody>
							</table>
						</div>
		{else}
						<div class="panel-body text-center">No hay reglas de acceso personalizadas registradas</div>
		{/if}
					</div>
				</div>
	{/foreach}
			</div>
		</div>
	</div>
{/if}
</div>
<div id="rule-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="index.php" onsubmit="return OrgSharingUtils.validateRule (this);">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="SaveSharingRule" />
				<input type="hidden" name="shareid" value="" />
				<input type="hidden" name="sharemodule" value="" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><span id="module-label"></span> - <span id="operation"></span> regla</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="owner">A los registros asignados a:</label>
						<select id="owner" name="owner" class="form-control">
							<option value=""></option>
{foreach $FROM_OPTIONS as $option}
							<option value="{$option.value}">{$option.text}</option>
{/foreach}
						</select>
					</div>
					<div class="form-group">
						<label for="access">Otorgar permisos de:</label>
						<select id="access" name="access" class="form-control">
							<option value=""></option>
							<option value="0">Solo lectura</option>
							<option value="1">Lectura y escritura</option>
						</select>
					</div>
					<div class="form-group">
						<label for="to">A:</label>
						<select id="to" name="to" class="form-control">
							<option value=""></option>
{foreach $TO_OPTIONS as $option}
							<option value="{$option.value}">{$option.text}</option>
{/foreach}
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Aceptar</button>
					<button type="button" class="btn btn-cancel" data-dismiss="modal">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
(function (jQuery) {
	var addRule = function (moduleName, moduleLabel) {
		var modal = jQuery ('#rule-modal');

		modal.find ('input[name="shareid"]').val ('');
		modal.find ('input[name="sharemodule"]').val (moduleName);
		modal.find ('#module-label').text (moduleLabel);
		modal.find ('#operation').text ('Agregar');
		modal.find ('#owner').val ('');
		modal.find ('#access').val ('');
		modal.find ('#to').val ('');
		modal.modal ('show');
	};

	var deleteRule = function () {
		return confirm ('¿Estás seguro que quieres eliminar la regla seleccionada?');
	};

	var editRule = function (rule, moduleName, moduleLabel) {
		var modal = jQuery ('#rule-modal');

		modal.find ('input[name="shareid"]').val (rule.shareid);
		modal.find ('input[name="sharemodule"]').val (moduleName);
		modal.find ('#module-label').text (moduleLabel);
		modal.find ('#operation').text ('Modificar');
		modal.find ('#owner').val (rule.from);
		modal.find ('#access').val (rule.permission);
		modal.find ('#to').val (rule.to);
		modal.modal ('show');
	};

	var validateRule = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#owner');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el dueño de los registros');
			field.focus ();
			return false;
		}

		field = form.find ('#access');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el acceso a otorgar');
			field.focus ();
			return false;
		}

		field = form.find ('#to');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona a quién otorgarás los accesos');
			field.focus ();
			return false;
		}

		return true;
	};

	window.OrgSharingUtils = {
		addRule:      addRule,
		deleteRule:   deleteRule,
		editRule:     editRule,
		validateRule: validateRule
	};
} (jQuery));
</script>
{/strip}