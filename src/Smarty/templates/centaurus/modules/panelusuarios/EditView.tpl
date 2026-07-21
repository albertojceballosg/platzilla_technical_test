{strip}
<style type="text/css">
	label {
		font-size:   1.11em;
		font-weight: 300;
	}
	.btn {
		margin-left: 5px;
	}
	.main-box .fa {
		color: #3498DB;
	}
	.required {
		color: #FF0000;
	}
	.radio-inline {
		font-size:   1em;
		font-weight: 300;
	}
	.image-container {
		border:     1px dashed;
		padding:    5px;
		position:   relative;
		text-align: center;
	}
	.image-container > .btn {
		background-color: transparent;
		border:           0;
		bottom:           5px;
		line-height:      1;
		right:            0;
		padding:          0 5px 2px 5px;
		position:         absolute;
		text-transform:   uppercase;
		z-index:          1;
	}
	.image-container > .image {
		display: inline-block;
	}
	.image-container > .image > .image-data {
		background-color: #3498DB;
		margin:           0 auto;
	}
	.image-container > input[type="file"] {
		bottom:   0;
		cursor:   pointer;
		left:     0;
		opacity:  0;
		position: absolute;
		top:      0;
		width:    100%;
	}
	.info {
		display:  inline-block;
		margin-right: 5px;
		position: relative;
		z-index:  1;
	}
	.info .infotext {
		background-color: #555;
		border-radius:    6px;
		color:            #fff;
		left:             480%;
		margin-left:      -60px;
		opacity:          0;
		padding:          5px 0;
		position:         absolute;
		text-align:       center;
		top:              -5px;
		transition:       opacity 1s;
		visibility:       hidden;
		width:            300px;
		z-index:          1;
	}
	.info:hover .infotext {
		opacity:    1;
		visibility: visible;
		z-index:    1;
	}
	.form-group {
		z-index: 0;
	}
	{* Large desktops and laptops. *}
	@media (min-width: 1200px) {
		.info .infotext {
			left:  480%;
			width: 300px;
		}
	}
	{* Landscape tablets and medium desktops. *}
	@media (min-width: 992px) and (max-width: 1199px) {
		.info .infotext {
			left:  480%;
			width: 300px;
		}
	}
	{* Portrait tablets and small desktops. *}
	@media (min-width: 768px) and (max-width: 991px) {
		.info .infotext {
			left:  480%;
			width: 300px;
		}
	}
	{* Landscape phones and portrait tablets. *}
	@media (min-width: 481px) and (max-width: 767px) {
		.info .infotext {
			left:  560%;
			width: 250px;
		}
	}
	{* Portrait phones and smaller. *}
	@media (max-width: 480px) {
		.info .infotext {
			left:  560%;
			width: 250px;
		}
	}
</style>
<div class="row">
	<div class="col-xs-12">
		<h1><a href="index.php?module=panelusuarios&action=index&parenttab=Settings">Usuario</a></h1>
	</div>
</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
<div class="row">
	<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
<div class="row">
	<div class="col-xs-12">
		<div class="main-box">
			<form method="post" action="index.php" onsubmit="return UsersUtils.validateUser (this);">
				<input type="hidden" name="module" value="panelusuarios" />
				<input type="hidden" name="action" value="SaveUser" />
{if (isset ($RECORD))}
				<input type="hidden" name="record" value="{$RECORD}" class="record" />
{/if}
				<header class="title-section main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
					<div class="action-bar pull-right">
						<button type="submit" class="btn btn-info">Guardar</button>
						<a href="index.php?module=panelusuarios&action=index&parenttab=Settings" class="btn btn-warning">Cancelar</a>
					</div>
				</header>
				<div class="main-box-body clearfix">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="user_name">Correo electrónico{if (empty ($RECORD))} <span class="required">*</span>{/if}</label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="email" id="user_name" name="user_name" value="{$USER.user_name}" class="form-control username"{if (!empty ($RECORD))} readonly="readonly"{/if} />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="first_name">Nombres</label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input id="first_name" name="first_name" value="{$USER.first_name}" class="form-control firstname" type="text" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="last_name">Apellidos <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input id="last_name" name="last_name" value="{$USER.last_name}" class="form-control lastname" type="text" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<div class="info"><i class="fa fa-info-circle"></i><span class="infotext">Es el papel que desarrolla un determinado usuario dentro de la plataforma.</span></div>
									<label for="role">Rol <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="role" name="roleid" class="form-control role">
										<option value=""></option>
{foreach $AVAILABLE_ROLES as $role}
										<option value="{$role.roleid}"{if ($role.roleid == $USER.roleid)} selected="selected"{/if}>{$role.rolename}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<div class="info"><i class="fa fa-info-circle"></i><span class="infotext">Cuando creas un usuario puedes elegir si este está activo o inactivo</span></div>
									<label for="status">Status <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="status" name="status" class="form-control status">
										<option value="Active"{if ($USER.status == 'Active')} selected="selected"{/if}>Activo</option>
										<option value="Inactive"{if ($USER.status == 'Inactive')} selected="selected"{/if}>Inactivo</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="user_password">Contraseña{if (empty ($RECORD))} <span class="required">*</span>{/if}</label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input id="user_password" name="user_password" value="" class="form-control password" type="password" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="user_password_repeated">Repite la contraseña{if (empty ($RECORD))} <span class="required">*</span>{/if}</label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input id="user_password_repeated" name="user_password_repeated" value="" class="form-control repeated-password" type="password" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<div class="info"><i class="fa fa-info-circle"></i><span class="infotext">Puedes adjuntar una imagen propia o seleccionar un avatar de nuestra galería</span></div>
									<label for="status">Adjuntar <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<label class="radio-inline">
									<input type="radio" name="userimage[type]" onchange="return UsersUtils.showImageSelection (this);" value="IMAGE"{if (!empty ($USER_IMAGE_URI))} checked="checked"{/if} /> Imagen
								</label>
								<label class="radio-inline">
									<input type="radio" name="userimage[type]" onchange="return UsersUtils.showAvatarSelection (this);" value="AVATAR"{if (!empty ($USER_AVATAR_FILE_NAME))} checked="checked"{/if} /> Avatar
								</label>
							</div>
						</div>
					</div>
					<div id="image-selection" class="row" style="display: {if (!empty ($USER_IMAGE_URI))}block{else}none{/if};">
						<div class="col-xs-12">
							<div class="image-container">
								<button type="button" class="btn btn-close" onclick="UsersUtils.restoreImage (this);">X</button>
								<figure class="image">
									<img src="{$USER_IMAGE_URI}" class="img-responsive image-data" data-original-src="" />
									<figcaption class="text-center image-name" data-original-name="Seleccione una imagen...">Seleccione una imagen...</figcaption>
								</figure>
								<input id="user-image" type="file" onchange="UsersUtils.changeImage (event || window.event);"{if (!empty ($USER_AVATAR_FILE_NAME))} disabled="disabled"{/if} />
								<input type="hidden" name="userimage[uri]" value="{$USER_IMAGE_URI}"{if (!empty ($USER_AVATAR_FILE_NAME))} disabled="disabled"{/if} />
								<input type="hidden" name="userimage[data]"{if (!empty ($USER_AVATAR_FILE_NAME))} disabled="disabled"{/if} />
							</div>
						</div>
					</div>
					<div id="avatar-selection" class="row" style="display: {if (!empty ($USER_AVATAR_FILE_NAME))}block{else}none{/if};">
						<div class="col-xs-12">
							<div class="main-box-body clearfix">
								<ul class="clearfix gallery-photos">
									<input type="hidden" name="userimage[uri]" value="{$USER_AVATAR_FILE_NAME}"{if (empty ($USER_AVATAR_FILE_NAME))} disabled="disabled"{/if}>
									<input type="hidden" name="userimage[data]"{if (empty ($USER_AVATAR_FILE_NAME))} disabled="disabled"{/if} />
{foreach $AVAILABLE_AVATAR_FILE_NAMES as $index => $avatarFileName}
									<li class="col-xs-6 col-sm-3 col-md-3">
										<label for="avatar-{$index}" class="photo-box image-link" style="background-image: url('modules/{$MODULE_NAME}/avatars/{$avatarFileName}'); background-size: 60% 80%;"></label>
										<span class="thumb-meta-time">
											<input id="avatar-{$index}" type="radio" name="avatar" value="{$avatarFileName}" placeholder="" onchange="UsersUtils.setAvatarData (this);"{if ($USER_AVATAR_FILE_NAME == $avatarFileName)} checked="checked"{/if}{if (empty ($USER_AVATAR_FILE_NAME))} disabled="disabled"{/if}>
										</span>
									</li>
{/foreach}
								</ul>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/panelusuarios/panelusuarios.js"></script>
{/strip}
