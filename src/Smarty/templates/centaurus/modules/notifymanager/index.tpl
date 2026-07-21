<script src="themes/{$THEME}/js/jquery-ui.custom.min.js"></script>
<style type="text/css">
	.table {
		margin-bottom: 0;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width: 30px; padding: 0;">
						<i class="fa fa-bell emerald-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li>
							<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
						</li>
						<li class="active">GESTOR DE NOTIFICACIONES EN PANTALLA</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" valign="top"></td>
			</tr>
		</tbody>
	</table>
{if $MSG_ERROR neq ''}
	<div class="col-lg-12">
		<div class="alert alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<i class="fa fa-times-circle fa-fw fa-lg"></i>
			<strong>ERROR!</strong> {$MSG_ERROR}.
		</div>
	</div>
{/if}
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2 class="pull-left"></h2>
				<div class="filter-block pull-right">
					<div class="form-group pull-left">
						<input class="form-control" placeholder="Buscar..." type="text">
						<i class="fa fa-search search-icon"></i>
					</div>
					<a href="index.php?module={$MODULE}&action=crearnotificacion&parenttab=Settings" class="btn btn-primary pull-right">
						<i class="fa fa-plus-circle fa-lg" title=""></i> Nueva notificación
					</a>
				</div>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th><a href="#"><span>Título</span></a></th>
								<th class="text-right"><a href="#" class="asc"><span>Módulo</span></a></th>
								<th class="text-center"><span>Vista</span></th>
								<th class="text-center"><span>Estado</span></th>
								<th class="text-center"><span>Descripción</span></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
{foreach key=keyNotificacion item=notificacion from=$NOTIFICACIONES}
	{assign var=vista value=$notificacion.action}
							<tr>
								<td>{$notificacion.title}</td>
								<td>{$notificacion.tablabel}</td>
								<td>{$MOD.$vista}</td>
								<td> <span class="label label-{if $notificacion.active eq 1 }success{else}warning{/if}">
									{if $notificacion.active eq 1 } Activa {else} Inactiva {/if} </span>
								</td>
								<td>{$notificacion.description}</td>
								<td style="width: 15%;">
									<a href="index.php?module=notifymanager&action=DetalleNotificacion&record={$notificacion.notifyid}" class="table-link">
										<span class="fa-stack">
											<i class="fa fa-square fa-stack-2x"></i>
											<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
										</span>
									</a>
									<a href="index.php?module=notifymanager&action=EditView&record={$notificacion.notifyid}" class="table-link">
										<span class="fa-stack">
											<i class="fa fa-square fa-stack-2x"></i>
											<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
										</span>
									</a>
									<a href="index.php?module=notifymanager&action=Delete&record={$notificacion.notifyid}" class="table-link danger">
										<span class="fa-stack">
											<i class="fa fa-square fa-stack-2x"></i>
											<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
										</span>
									</a>
								</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>