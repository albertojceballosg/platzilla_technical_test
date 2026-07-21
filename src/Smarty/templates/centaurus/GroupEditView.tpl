{strip}
<style type="text/css">
	label {
		font-size:   1.11em;
		font-weight: 300;
	}
	.main-box > .main-box-header {
		padding-bottom: 20px;
		padding-top:    20px;
	}
	.required {
		color: #FF0000;
	}
</style>
<form method="post" action="index.php" onsubmit="return GroupUtils.validateGroup (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveGroup" />
	<input type="hidden" name="groupid" value="{if (isset ($RECORD))}{$RECORD}{/if}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=Settings&action=listgroups&parenttab=Settings">Grupo</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info" style="margin-right: 5px;">Guardar</button>
				<a href="index.php?module=Settings&action=listgroups&parenttab=Settings" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="groupname">Nombre <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="groupname" name="groupname" value="{if (isset ($SELECTED_GROUP))}{$SELECTED_GROUP[0]}{/if}" maxlength="100" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="description">Descripción</label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="description" name="groupdescription" class="form-control" value="{if (isset ($SELECTED_GROUP))}{$SELECTED_GROUP[1]}{/if}" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Miembros</h2>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<label for="available">Disponibles</label>
							<button type="button" class="btn btn-primary" style="width: 100%;" onclick="GroupUtils.addMembers ()">&rsaquo;&rsaquo;</button>
							<select id="available" class="form-control" multiple="multiple" style="min-height: 20em;">
{if (!empty ($AVAILABLE_GROUPS))}
								<optgroup id="available-groups" label="Grupos">
	{foreach $AVAILABLE_GROUPS as $groupId => $groupName}
		{if (!isset ($SELECTED_GROUP_MEMBERS.groups['group::'|cat: $groupId]))}
									<option value="group::{$groupId}">{$groupName}</option>
		{/if}
	{/foreach}
								</optgroup>
{/if}
{if (!empty ($AVAILABLE_ROLES))}
								<optgroup id="available-roles" label="Roles">
	{foreach $AVAILABLE_ROLES as $roleId => $roleName}
		{if (!isset ($SELECTED_GROUP_MEMBERS.roles['role::'|cat: $roleId]))}
									<option value="role::{$roleId}">{$roleName}</option>
		{/if}
	{/foreach}
								</optgroup>
{/if}
{if (!empty ($AVAILABLE_ROLES))}
								<optgroup id="available-rs" label="Roles y subordinados">
	{foreach $AVAILABLE_ROLES as $roleId => $roleName}
		{if (!isset ($SELECTED_GROUP_MEMBERS.rs['rs::'|cat: $roleId]))}
									<option value="rs::{$roleId}">{$roleName}</option>
		{/if}
	{/foreach}
								</optgroup>
{/if}
{if (!empty ($AVAILABLE_USERS))}
								<optgroup id="available-users" label="Usuarios">
	{foreach $AVAILABLE_USERS as $userId => $userName}
		{if (!isset ($SELECTED_GROUP_MEMBERS.users['user::'|cat: $userId]))}
									<option value="user::{$userId}">{$userName}</option>
		{/if}
	{/foreach}
								</optgroup>
{/if}
							</select>
						</div>
						<div id="members-container" class="col-md-6">
							<label for="members">Miembros</label>
							<button type="button" class="btn btn-danger" style="width: 100%;" onclick="GroupUtils.removeMembers ();">&lsaquo;&lsaquo;</button>
							<select id="members" class="form-control" multiple="multiple" style="min-height: 20em;">
{if (!empty ($SELECTED_GROUP_MEMBERS.groups))}
								<optgroup id="members-groups" label="Grupos">
	{foreach $SELECTED_GROUP_MEMBERS.groups as $groupId => $groupName}
									<option value="{$groupId}">{$groupName}</option>
	{/foreach}
								</optgroup>
{/if}
{if (!empty ($SELECTED_GROUP_MEMBERS.roles))}
								<optgroup id="members-roles" label="Roles">
	{foreach $SELECTED_GROUP_MEMBERS.roles as $roleId => $roleName}
									<option value="{$roleId}">{$roleName}</option>
	{/foreach}
								</optgroup>
{/if}
{if (!empty ($SELECTED_GROUP_MEMBERS.rs))}
								<optgroup id="members-rss" label="Roles y subordinados">
	{foreach $SELECTED_GROUP_MEMBERS.rs as $roleId => $roleName}
									<option value="{$roleId}">{$roleName}</option>
	{/foreach}
								</optgroup>
{/if}
{if (!empty ($SELECTED_GROUP_MEMBERS.users))}
								<optgroup id="members-users" label="Usuarios">
	{foreach $SELECTED_GROUP_MEMBERS.users as $userId => $userName}
									<option value="{$userId}">{$userName}</option>
	{/foreach}
								</optgroup>
{/if}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
(function (jQuery) {
	var getOptGroup = function (select, optGroupId, optGroupLabel) {
		var optGroups = select.find ('#' + optGroupId),
			optGroup;
		if (optGroups.length === 0) {
			select.append ('<optgroup id="' + optGroupId + '" label="' + optGroupLabel + '"></optgroup>');
			optGroup = select.find ('#' + optGroupId);
		} else {
			optGroup = jQuery (optGroups [0]);
		}
		return optGroup;
	};

	var addMembers = function () {
		var available = jQuery ('#available'),
			members = jQuery ('#members'),
			options = available.find ('option:selected'),
			i, n, element, type, value, dummy, optGroup;

		if (options.length === 0) {
			alert ('Selecciona los miembros a agregar');
			return;
		}

		n = (options.length - 1);
		for (i = n; i >= 0; i -= 1) {
			element = jQuery (options [i]);
			dummy = element.val ().split ('::');
			type = dummy [0];
			value = dummy [1];
			if (type === 'group') {
				optGroup = getOptGroup (members, 'members-groups', 'Grupos');
			} else if (type === 'role') {
				optGroup = getOptGroup (members, 'members-roles', 'Roles');
			} else if (type === 'rs') {
				optGroup = getOptGroup (members, 'members-rs', 'Roles y subordinados');
			} else {
				optGroup = getOptGroup (members, 'members-users', 'Usuarios');
			}
			optGroup.append (element);
			element.removeAttr ('selected');
		}
	};

	var removeMembers = function () {
		var available = jQuery ('#available'),
			members   = jQuery ('#members'),
			options   = members.find ('option:selected'),
			i, n, element, type, value, dummy, optGroup;

		if (options.length === 0) {
			alert ('Selecciona los miembros a remover');
			return;
		}

		n = (options.length - 1);
		for (i = n; i >= 0; i -= 1) {
			element = jQuery (options [ i ]);
			dummy = element.val ().split ('::');
			type = dummy [ 0 ];
			value = dummy [ 1 ];
			if (type === 'group') {
				optGroup = getOptGroup (available, 'available-groups', 'Grupos');
			} else if (type === 'role') {
				optGroup = getOptGroup (available, 'available-roles', 'Roles');
			} else if (type === 'rs') {
				optGroup = getOptGroup (available, 'available-rs', 'Roles y subordinados');
			} else {
				optGroup = getOptGroup (available, 'available-users', 'Usuarios');
			}
			optGroup.append (element);
			element.removeAttr ('selected');
		}
	};

	var validateGroup = function (formElement) {
		var form = jQuery (formElement),
			members = form.find ('#members'),
			field, value, options, i, n, container;

		field = form.find ('#groupname');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nombre del grupo');
			field.focus ();
			return false;
		}

		container = jQuery ('#members-container');
		container.find ('input.group-member').remove ();

		options = members.find ('option');
		n = options.length;
		for (i = 0; i < n; i += 1) {
			container.append ('<input type="hidden" class="group-member" name="groupmembers[]" value="' + jQuery (options [ i ]).val () + '" />');
		}

		return true;
	};

	window.GroupUtils = {
		addMembers: addMembers,
		removeMembers: removeMembers,
		validateGroup: validateGroup
	}
} (jQuery));
</script>
{/strip}