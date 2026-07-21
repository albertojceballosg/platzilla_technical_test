{strip}
<div class="col-lg-12">
	<div class="pull-left">
		<h1><a href="index.php?action=index&module=instancias_admin&parenttab=&paginadorInstancia=1">Instancias</a>
		</h1>
	</div>
	<div class="pull-right text-right">
		<a href="index.php?action=index&module=instancias_admin&parenttab=" class="btn btn-warning">Volver</a>
		<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la instancia {$INSTANCIA.code}?');" style="display: inline; margin-left: 5px;">
			<input type="hidden" name="module" value="instancias_admin" />
			<input type="hidden" name="action" value="instanciasDelete" />
			<input type="hidden" name="code" value="{$INSTANCIA.code}" />
			<input type="hidden" name="Ajax" value="true" />
			<button type="submit" class="btn btn-danger" title="{$MOD.LBL_ELIMINAR}">Borrar</button>
		</form>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box">
			<header class="title-section main-box-header clearfix">
				<h2>Datos básicos</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Nombre/Código</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
						<span class="form-control" readonly="" id="" data-toggle="tooltip">
							{$INSTANCIA.code}
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Nombre Público</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
						<span class="form-control" readonly="" id="" data-toggle="tooltip">
							{$INSTANCIA.name}
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Usuario Primario</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
						<span class="form-control" readonly="" id="" data-toggle="tooltip">
							{$INSTANCIA.usuario}
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Es instancia demo?</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa {if $INSTANCIA.isdemo eq 1}fa-check-square{else}fa-square-o{/if}"></i></span>
							<span class="form-control label-readonly b-left" readonly="" id="" data-toggle="tooltip">{if $INSTANCIA.isdemo eq 1}yes{else}no{/if}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Cuenta</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
						<span class="form-control label-readonly b-left" readonly="" id="" data-toggle="tooltip">
							<a href="index.php?module=Accounts&action=DetailView&record={$INSTANCIA.accountid}" title="">{$INSTANCIA.accountname}</a>
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Contacto</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
						<span class="form-control label-readonly b-left" readonly="" id="" data-toggle="tooltip">
							<a href="index.php?module=Contacts&action=DetailView&record={$INSTANCIA.contactid}" title="">{$INSTANCIA.firstname} {$INSTANCIA.lastname}</a>
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Fecha de inicio del Demo</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-calendar"></i></span>
							<span class="form-control label-readonly b-left" readonly="" data-toggle="tooltip">{$INSTANCIA.inidatedemo}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Fecha de inicio del Servicio</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-calendar"></i></span>
							<span class="form-control label-readonly b-left" readonly="" data-toggle="tooltip">{$INSTANCIA.inidateservices}</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Usuarios Activos / Contratados </h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
						<span class="form-control label-readonly b-left" readonly="" id="" data-toggle="tooltip">
							{$INSTANCIA.numusuariosactivos} / {$INSTANCIA.numusuarios}
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Aplicaciones Pagadas / Contratadas </h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>
						<span class="form-control label-readonly b-left" readonly="" id="" data-toggle="tooltip">
							{$INSTANCIA.activeappcount} / {$INSTANCIA.appcount}
						</span>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>Estado </h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<label class="{$INSTANCIA.colorLabel}">{$INSTANCIA.statusLabel}</label>
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
				<h2>Aplicaciones Contratadas</h2>
			</header>
			<div class="main-box-body clearfix" id="">
				<div class="table-responsive">
					<table class="table table-stripped table-hover" width="100%" cellpadding="5">
						<thead>
						<tr>
							<th>Aplicación</th>
							<th>Status</th>
							<th>Fecha de inicio de demo</th>
							<th>Fecha de inicio del Servicio</th>
						</tr>
						</thead>
						<tbody>
						{foreach name=instanceApp item=app from=$APPS}
							<tr>
								<td>
									<a href="index.php?module=instancias_admin&action=DetailApps&record={$app.applicationid}&return_module={$MODULE}&return_action=DetailViewInstancia&return_record={$INSTANCIA.instanciasid}">{$app.app_name}</a>
								</td>
								<td><span class="{$app.colorLabelStatus}">{$app.status}</span></td>
								<td>{$app.datedemo|date_format: 'd/m/Y'}</td>
								<td>{if (!empty ($app.dateiniservice))}{$app.dateiniservice|date_format: 'd/m/Y'}{/if}</td>
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