{strip}
{assign var='userId' value=$USER->getId ()}
{assign var='defaultModuleName' value=$USER->getDefaultModuleName ()}
{assign var='firstName' value=$USER->getFirstName ()}
{assign var='fullName' value=trim("{$USER->getFirstName ()} {$USER->getLastName ()}")}
{assign var='lastName' value=$USER->getLastName ()}
{assign var='userName' value=$USER->getUserName ()}
{assign var='roles' value=$USER->getRoles ()}
{if (!empty ($USER->getImageUri ()))}
	{assign var='imageUri' value=$USER->getImageUri ()}
{else}
	{assign var='imageUri' value='themes/centaurus/img/photo.png'}
{/if}
{if (!empty ($USER->getRoles ()))}
	{assign var='roleName' value=$roles[0]->getName ()}
{else}
	{assign var='roleName' value=null}
{/if}
<div class="main-box">
	<div class="main-box-body clearfix">
		<div class="row">
			<div class="col-md-2 text-center">
				<figure style="display: inline-block;">
					<img src="{$imageUri}" class="img-responsive img-circle" style="background-color: #E7E7E7" />
					<figcaption class="text-center">{$fullName}</figcaption>
				</figure>
			</div>
			<div class="col-md-10">
				<div class="row">
					<div class="col-md-6">
						<div class="col-md-4">
							<div class="label-input">
								<label for="user_name">Correo electrónico</label>
							</div>
						</div>
						<div class="form-group col-md-8 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="email" id="user_name" name="user_name" value="{$userName}" class="form-control username" disabled="disabled" />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="col-md-4">
							<div class="label-input">
								<label for="password">Contraseña</label>
							</div>
						</div>
						<div class="form-group col-md-8 field-container">
							<button type="button" data-modal="modal-1" class="md-trigger mrg-b-lg btn btn-success">Cambiar contraseña</button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="col-md-4">
							<div class="label-input">
								<label for="role">Rol</label>
							</div>
						</div>
						<div class="form-group col-md-8 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="role" name="role" value="{$roleName}" class="form-control rolename" disabled="disabled" />
							</div>
						</div>
					</div>
{if (!empty ($defaultModuleName))}
	{assign var='moduleLabel' value=$defaultModuleName|getTranslatedString: $defaultModuleName}
					<div class="col-md-6">
						<div class="col-md-4">
							<div class="label-input">
								<label for="default-module">Iniciar en</label>
							</div>
						</div>
						<div class="form-group col-md-8 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="default-module" value="{$moduleLabel}" class="form-control" disabled="disabled" />
							</div>
						</div>
					</div>
{/if}
				</div>
				<div class="row">
					<div class="col-md-12 text-center">
						<a href="index.php?module=panelusuarios&action=EditUserProfile" class="btn btn-primary">Editar</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}