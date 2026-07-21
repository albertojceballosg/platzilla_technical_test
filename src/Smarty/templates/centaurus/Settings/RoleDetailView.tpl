{strip}
{if (isset ($ROLE))}
	{assign var='roleId' value=$ROLE->getId ()}
	{assign var='roleName' value=$ROLE->getName ()}
	{assign var='roleProfiles' value=$ROLE->getProfiles ()}
	{assign var='parentRole' value=$ROLE->getParent ()}
{else}
	{assign var='roleId' value=null}
	{assign var='roleName' value=null}
	{assign var='roleProfiles' value=null}
	{assign var='parentRole' value=null}
{/if}
{if (isset ($parentRole))}
	{assign var='parentRoleName' value=$parentRole->getName ()}
{else}
	{assign var='parentRoleName' value=null}
{/if}
<style type="text/css">
	label {
		font-size:   1em;
		font-weight: 300;
		margin:      0;
	}
	ul {
		list-style: none;
		padding: 0;
	}
</style>
<form action="index.php" method="get">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="RoleEditView" />
	<input type="hidden" name="roleid" value="{$roleId}" />
	<input type="hidden" name="parenttab" value="Settings" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=Settings&action=listroles&parenttab=Settings">Rol</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info" style="margin-right: 5px;">Editar</button>
			</div>
		</div>
	</div>
	<div class="main-box clearfix col-xs-12">
		<header class="main-box-header clearfix">
			<h2>Información general</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="role-name">Nombre</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<input type="text" id="role-name" value="{$roleName}" class="form-control" disabled="disabled" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="parent-role-name">Informa a</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
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
					<ul id="profile-ids">
{if (!empty ($roleProfiles))}
	{foreach $roleProfiles as $roleProfile}
		{if (!empty ($roleProfile->getMainApplicationCode ()))}
						<li>
							<a href="index.php?module=Settings&action=EditApplicationProfile&applicationcode={$roleProfile->getMainApplicationCode ()}&parenttab=Settings">{if ($roleProfile->getId () != 1)}{$roleProfile->getName ()}{else}Administrador de todas las aplicaciones{/if}</a>
						</li>
		{else}
						<li>
							<a href="index.php?module=Settings&action=ProfileEditView&profilename={$roleProfile->getName ()}&parenttab=Settings">{if ($roleProfile->getId () != 1)}{$roleProfile->getName ()}{else}Administrador de todas las aplicaciones{/if}</a>
						</li>
		{/if}
	{/foreach}
{/if}
					</ul>
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="users">Usuarios</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<ul id="users">
{if (!empty ($ROLE_USERS))}
	{foreach $ROLE_USERS as $user}
						<li>{trim ("{$user->getFirstName ()} {$user->getLastName ()}")}</li>
	{/foreach}
{/if}
					</ul>
				</div>
			</div>
		</div>
	</div>
</form>
{/strip}