{strip}
<style type="text/css">
	label {
		font-size: 1em;
	}
</style>
<div class="col-lg-12">
	<div class="pull-left">
		<h1><a href="index.php?module=instances&action=index&parenttab=Settings">Instancia</a></h1>
	</div>
	<div class="pull-right text-right">
		<a href="index.php?module=instances&action=index&page={$PAGE}&parenttab=Settings" class="btn btn-warning">Volver</a>
		<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres actualizar la instancia {$INSTANCE->getCode ()}?');" style="display: inline; margin-left: 5px;">
			<input type="hidden" name="module" value="instances" />
			<input type="hidden" name="action" value="Update" />
			<input type="hidden" name="code" value="{$INSTANCE->getCode ()}" />
			<input type="hidden" name="Ajax" value="true" />
			<button type="submit" class="btn btn-info btn-icon" title="Actualizar">Actualizar</button>
		</form>
		<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la instancia {$INSTANCE->getCode ()}?');" style="display: inline; margin-left: 5px;">
			<input type="hidden" name="module" value="instances" />
			<input type="hidden" name="action" value="Delete" />
			<input type="hidden" name="code" value="{$INSTANCE->getCode ()}" />
			<input type="hidden" name="Ajax" value="true" />
			<button type="submit" class="btn btn-danger" title="Eliminar">Eliminar</button>
		</form>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box">
			<header class="title-section main-box-header clearfix">
				<h2>Información general</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="code">Código</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span id="code" class="form-control" readonly="readonly">{$INSTANCE->getCode ()}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="name">Empresa</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span id="name" class="form-control" readonly="readonly">{$INSTANCE->getName ()}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="administrator">Administrador</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span id="administrator" class="form-control" readonly="readonly">{$INSTANCE->getAdministrator ()->getUserName ()}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="billing-plan">Plan</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span id="billing-plan" class="form-control" readonly="readonly">{$INSTANCE->getBillingPlan ()->getName ()}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="account">Cuenta</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
							<span id="accountname" class="form-control label-readonly b-left" readonly="readonly">
								<a href="index.php?module=clientes&action=DetailView&record={$INSTANCE->getAccountId ()}">{$ACCOUNT_NAME}</a>
							</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="users">Usuarios</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
							<span id="users" class="form-control label-readonly b-left" readonly="readonly">Activos: {count($INSTANCE->getUsers ()) + 1} / Contratados: {if ($INSTANCE->getBillingPlan ()->getTotalUsers () != -1)}{$INSTANCE->getBillingPlan ()->getTotalUsers ()}{else}Ilimitados{/if}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="applications">Aplicaciones</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
							<span id="applications" class="form-control label-readonly b-left" readonly="readonly">Contratadas: {count($INSTANCE->getApplications ())} / En catálogo: {$TOTAL_APPLICATIONS}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="status">Status</label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span id="status" class="form-control" readonly="readonly">{$MOD[$INSTANCE->getStatus ()]}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box">
			<header class="title-section main-box-header clearfix">
				<h2>Aplicaciones</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table table-stripped table-hover" width="100%" cellpadding="5">
						<thead>
						<tr>
							<th>Aplicación</th>
							<th>Status</th>
							<th>Agregada</th>
							<th>Inicio del contrato</th>
							<th>Fin del contrato</th>
						</tr>
						</thead>
						<tbody>
{assign var='applications' value=$INSTANCE->getApplications ()}
{foreach $applications as $application}
	{assign var='applicationCode' value=$application->getCode ()}
	{assign var='instanceApplication' value=$INSTANCE_APPLICATIONS[$applicationCode]}
						<tr>
							<td>{$application->getName ()}</td>
							<td><span class="label label-{if ($instanceApplication.status == ApplicationSubscriptionInterface::STATUS_ACTIVE)}success{else}info{/if}">{$instanceApplication.status}</span></td>
							<td>{$instanceApplication.registrationdate|date_format: 'd/m/Y'}</td>
							<td>{if (!empty ($instanceApplication.servicestartdate))}{$instanceApplication.servicestartdate|date_format: 'd/m/Y'}{/if}</td>
							<td>{if (!empty ($instanceApplication.serviceenddate))}{$instanceApplication.serviceenddate|date_format: 'd/m/Y'}{/if}</td>
							<td>{if (!empty ($instanceApplication.disablingdate))}{$instanceApplication.disablingdate|date_format: 'd/m/Y'}{/if}</td>
						</tr>
{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}