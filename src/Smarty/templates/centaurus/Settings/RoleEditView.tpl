{strip}
{if (isset ($ROLE))}
	{assign var='roleId' value=$ROLE->getId ()}
	{assign var='roleName' value=$ROLE->getName ()}
	{assign var='roleProfiles' value=$ROLE->getProfiles ()}
{else}
	{assign var='roleId' value=null}
	{assign var='roleName' value=null}
	{assign var='roleProfiles' value=null}
{/if}
{if (isset ($PARENT_ROLE))}
	{assign var='parentRoleId' value=$PARENT_ROLE->getId ()}
	{assign var='parentRoleName' value=$PARENT_ROLE->getName ()}
{else}
	{assign var='parentRoleId' value=null}
	{assign var='parentRoleName' value=null}
{/if}
<style type="text/css">
	label {
		font-size:   1em;
		font-weight: 300;
		margin:      0;
	}
	.required {
		color: #FF0000;
	}
</style>
<form action="index.php" method="post" onsubmit="return RoleUtils.validate (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveRole" />
	<input type="hidden" name="roleid" value="{$roleId}" />
	<input type="hidden" name="Ajax" value="true" />
	<input type="hidden" name="parenttab" value="Settings" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=Settings&action=listroles&parenttab=Settings">Rol</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info" style="margin-right: 5px;">{$APP.LBL_SAVE_LABEL}</button>
				<a href="index.php?module=Settings&action=listroles&parenttab=Settings" class="btn btn-warning">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
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
	<div class="main-box clearfix col-xs-12">
		<header class="main-box-header clearfix">
			<h2>Información general</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="role-name">Nombre <span class="required">*</span></label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<input type="text" id="role-name" name="rolename"  value="{$roleName}" class="form-control" maxlength="200" placeholder="{$MOD.LBL_ROLE_NAME}" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="parent-role-name">Informa a</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<input type="hidden" id="parent-role-id" name="parentroleid" value="{$parentRoleId}" />
					<input type="text" id="parent-role-name" value="{$parentRoleName}" class="form-control" disabled="disabled" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="profile-ids">Aplicaciones</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<select id="profile-ids" name="profileids[]" class="form-control" multiple="multiple" size="10">
{foreach $AVAILABLE_PROFILES as $availableProfile}
	{assign var='isSelected' value=false}
	{if (!empty ($roleProfiles))}
		{foreach $roleProfiles as $roleProfile}
			{if ($roleProfile->getName () == $availableProfile->getName ())}
				{assign var='isSelected' value=true}
				{break}
			{/if}
		{/foreach}
	{/if}
						<option value="{$availableProfile->getId ()}"{if ($isSelected)} selected="selected"{/if}>{if ($availableProfile->getId () != 1)}{$availableProfile->getName ()}{else}Administrador de todas las aplicaciones{/if}</option>
{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
(function (jQuery) {
	var validate = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#role-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nombre');
			field.focus ();
			return false;
		}

		field = form.find ('#profile-ids > option:selected');
		if (field.length === 0) {
			alert ('Selecciona las aplicaciones');
			field.focus ();
			return false;
		}

		return true;
	};

	window.RoleUtils = {
		validate: validate
	};
} (jQuery));
</script>
{/strip}
