{strip}
<style type="text/css">
	.col-date {
		width: 7em;
	}
	.col-status {
		width: 7em;
	}
	.col-actions {
		width: 8em;
	}
	.col-actions .btn {
		height:         27px;
		line-height:    27px;
		margin:         0 0 0 5px;
		padding:        0;
		width:          27px;
	}
	.col-actions > form {
		display: inline;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-user red-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li class="active">{$MOD.LBL_USERS}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_USER_DESCRIPTION}</td>
		</tr>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
{/if}
	<div class="col-xs-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<div class="col-xs-12 text-right">
					<a href="index.php?module=panelusuarios&action=EditView"
					   onclick="UsersUtils.addUsers(this, event)"
					   class="btn btn-primary">
						<i class="fa fa-plus-circle"></i> Crear usuario
					</a>
				</div>
			</header>
			<div class="main-box-body clearfix" id="ListViewContents">
				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<thead>
						<tr>
							<th class="col-fullname">Usuario</th>
							<th class="col-date">Creado el</th>
							<th class="col-status">Status</th>
							<th class="col-email">Email</th>
							<th class="col-actions"></th>
						</tr>
						</thead>
						<tbody>
{if (count ($USERS) > 0) }
	{foreach $USERS as $user}
							<tr class="lvtColData">
								<td class="col-fullname">
									<div style="display: inline-block; margin-right: 10px; vertical-align: middle;">
		{if (!empty ($user.profileimage))}
										<img src="{$user.profileimage}" style="border-radius: 50%; width: 40px;">
		{else}
										<img src="themes/centaurus/img/photo.png" style="background-color: #000000; border-radius: 50%; width: 40px;">
		{/if}
									</div>
									<div style="display: inline-block; vertical-align: middle;">
										<a href="index.php?module=panelusuarios&action=EditView&parenttab=Settings&record={$user.id}" class="link">{$user.first_name|cat: ' '|cat: $user.last_name|trim}</a>
										<p style="margin-bottom: 0;">{$user.rolename}</p>
									</div>
								</td>
								<td class="col-date">{$user.date_entered}</td>
								<td class="col-status">
									<span class="label {if ($user.status == 'Active')}label-success{elseif ($user.status == 'Inactive')}label-danger{else}label-default{/if}">{if ($user.status == 'Active')}Activo{elseif ($user.status == 'Inactive')}Inactivo{else}{$user.status}{/if}</span>
								</td>
								<td class="col-email">{$user.email1}</td>
								<td class="col-actions">
		{if ($IS_ADMIN)}
									<a href="index.php?module=panelusuarios&action=EditView&parenttab=Settings&record={$user.id}" class="btn btn-primary" title="{$MOD.LBL_EDIT}">
										<i class="fa fa-pencil"></i>
									</a>
			{if ($user.is_admin != 'on')}
							<a href="index.php?module=panelusuarios&action=ChangeEntityOwner&Ajax=true&record={$user.id}"
							   class="btn btn-danger delete-button"
							   title="{$MOD.LBL_DELETE}"
							   data-toggle="lightbox" data-max-width="400" data-title="<strong>Usuario a eliminar:</strong>  {$user.first_name|cat: ' '|cat: $user.last_name|trim}">
								<i class="fa fa-trash-o"></i>
							</a>

			{/if}
		{/if}
								</td>
							</tr>
	{/foreach}
{else}
							<tr class="lvtColData">
								<td colspan="5" class="text-center">No se encuentran usuarios registrados</td>
							</tr>
{/if}
					<tr class="lvtColData">
						<td colspan="5" class="text-left">
                            {if $ADVANCED_OPTION}
								<a class="btn btn-info" href="index.php?module=Settings&action=index&option=&option=SINGLE&parenttab=Settings">Deshabilitar opciones avanzadas de usuario</a>
                            {else}
							<a class="btn btn-info" href="index.php?module=Settings&action=index&option=ADVANCED&parenttab=Settings">Habilitar opciones avanzadas de usuario</a>
                            {/if}
						</td>
					</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/panelusuarios/panelusuarios.js"></script>
{include file='Smarty/templates/centaurus/modules/panelusuarios/GoUpdateUserModal.tpl'}
{/strip}