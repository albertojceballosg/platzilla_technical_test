{strip}
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-key emerald-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">{$MOD.LBL_PROFILES|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_PROFILE_DESCRIPTION}</td>
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
				<a href="index.php?module=Settings&amp;action=ProfileEditView&amp;parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> {$CMOD.LBL_NEW_PROFILE}</a>
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
{if (!empty ($PROFILES))}
	{foreach $PROFILES as $profile}
		{assign var='mainApplicationCode' value=$profile->getMainApplicationCode ()}
		{if (!empty ($mainApplicationCode))}
			{assign var='isApplicationProfile' value=true}
			{assign var='linkAction' value='EditApplicationProfile'}
			{assign var='parameterName' value='applicationcode'}
			{assign var='parameterValue' value=$profile->getMainApplicationCode ()}
		{else}
			{assign var='linkAction' value='ProfileEditView'}
			{assign var='parameterName' value='profilename'}
			{assign var='parameterValue' value=$profile->getName ()}
			{assign var='isApplicationProfile' value=false}
		{/if}
					<tr>
						<td class="col-name">{$profile->getName ()}</td>
						<td class="col-description">{$profile->getDescription ()}</td>
						<td class="col-actions">
							<a href="index.php?module=Settings&action={$linkAction}&parenttab=Settings&{$parameterName}={$parameterValue}&returnmodule=Settings&returnaction=ProfileListView" class="btn btn-link" title="{$APP.LNK_EDIT}"><i class="fa fa-pencil"></i></a>
							<a href="index.php?module=Settings&action=ProfileEditView&parenttab=Settings&profilename={$profile->getName ()|escape: 'url'}&duplicate=true" class="btn btn-link" title="Duplicar"><i class="fa fa-files-o"></i></a>
		{if (($profile->getId () != 1) && (!$isApplicationProfile))}
							<button onclick="ProfileUtils.deleteProfile ('{$profile->getName ()}');" class="btn btn-link" title="{$APP.LNK_DELETE}"><i class="fa fa-trash-o"></i></button>
		{/if}
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="4" class="text-center">No hay perfiles registrados</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div id="profile-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="index.php" onsubmit="return ProfileUtils.validateProfileTransfer (this);">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="DeleteProfile" />
				<input type="hidden" name="profilename" value="" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Eliminar perfil <span id="profile-name"></span></h4>
				</div>
				<div class="modal-body">
					<div class="form-profile">
						<label for="transferto">Transferir todos los roles asignados a:</label>
						<select id="transferto" name="transferto" class="form-control">
							<option value=""></option>
{if (!empty ($PROFILES))}
	{foreach $PROFILES as $profile}
							<option value="{$profile->getName ()}">{$profile->getName ()}</option>
	{/foreach}
{/if}
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Aceptar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
(function (jQuery) {
	var deleteProfile = function (profileName) {
		var modal = jQuery ('#profile-modal');
		modal.find ('input[name="profilename"]').val (encodeURIComponent (profileName));
		modal.find ('#profile-name').text (profileName);
		modal.modal ({ backdrop: 'static' });
	};

	var validateProfileTransfer = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#transferto');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el nuevo perfil de los roles');
			field.focus ();
			return false;
		}

		return true;
	};

	window.ProfileUtils = {
		deleteProfile:           deleteProfile,
		validateProfileTransfer: validateProfileTransfer
	};
} (jQuery));
</script>
{/strip}