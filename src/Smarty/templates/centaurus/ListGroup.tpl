{strip}
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-users purple-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">{$MOD.USERGROUPLIST|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_GROUP_DESCRIPTION}</td>
		</tr>
		</tbody>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<div class="pull-right">
				<a href="index.php?module=Settings&amp;action=GroupEditView&amp;parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Crear grupo</a>
			</div>
		</header>
		<div class="main-box-body clearfix">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name"><b>Nombre</b></th>
						<th class="col-description"><b>Descripción</b></th>
						<th class="col-actions"><b>Acciones</b></th>
					</tr>
					</thead>
					<tbody>
{if (!empty ($GROUPS))}
	{foreach $GROUPS as $groupData}
					<tr>
						<td class="col-name">{$groupData.groupname}</td>
						<td class="col-description">{$groupData.description}</td>
						<td class="col-actions">
							<a href="index.php?module=Settings&action=GroupEditView&parenttab=Settings&groupId={$groupData.groupid}" class="btn btn-link" title="{$APP.LNK_EDIT}"><i class="fa fa-pencil"></i></a>
							<button onclick="GroupUtils.deleteGroup ({$groupData.groupid}, '{$groupData.groupname}');" class="btn btn-link" title="{$APP.LNK_DELETE}"><i class="fa fa-trash-o"></i></button>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="4" class="text-center">No hay grupos registrados</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div id="group-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="index.php" onsubmit="return GroupUtils.validateGroupTransfer (this);">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="DeleteGroup" />
				<input type="hidden" name="groupid" value="" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Eliminar grupo <span id="group-name"></span> </h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="transferto">Transferir todos los registros asignados a:</label>
						<select id="transferto" name="transferto" class="form-control">
							<option value=""></option>
{if (!empty ($GROUPS))}
							<optgroup label="Grupos">
	{foreach $GROUPS as $group}
								<option value="{$group.groupid}" class="group">{$group.groupname}</option>
	{/foreach}
							</optgroup>
{/if}
{if (!empty ($USERS))}
							<optgroup label="Usuarios">
		{foreach $USERS as $userId => $userName}
								<option value="{$userId}">{$userName}</option>
		{/foreach}
							</optgroup>
{/if}
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Aceptar</button>
					<button type="button" class="btn btn-cancel" data-dismiss="modal">Cancelar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
(function (jQuery) {
	var deleteGroup = function (groupId, groupName) {
		var modal = jQuery ('#group-modal');
		modal.find ('input[name="groupid"]').val (groupId);
		modal.find ('#group-name').text (groupName);
		modal.find ('option.group').show ();
		modal.find ('option.group[value="group::' + groupId + '"]').hide ();
		modal.modal ('show');
	};

	var validateGroupTransfer = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#transferto');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el nuevo dueño de los registros del grupo');
			field.focus ();
			return false;
		}

		return true;
	};

	window.GroupUtils = {
		deleteGroup: deleteGroup,
		validateGroupTransfer: validateGroupTransfer
	};
} (jQuery));
</script>
{/strip}