{strip}
<div id="email-box" class="clearfix" style="padding-bottom: 20px;">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-bell emerald-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">GESTOR DE NOTIFICACIONES</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Notificaciones del sistema basadas en eventos</td>
		</tr>
		</tbody>
	</table>
{if (!empty ($MESSAGE))}
	<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
		<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<div class="col-xs-12 col-md-6">
				<form name="filters" action="index.php" method="GET" class="form-inline">
					<input type="hidden" name="module" value="notifications" />
					<input type="hidden" name="action" value="ListView" />
					<input type="hidden" name="parenttab" value="Settings" />
					<div class="form-group">
						<input type="text" name="keyword" value="{$SEARCH_KEYWORD}" class="form-control" placeholder="Palabras clave">
					</div>
					<input type="submit" value="Buscar" class="btn btn-primary">
				</form>
			</div>
			<div class="col-xs-12 col-md-6 text-right">
				<button class="btn btn-primary" onclick="NotificationUtils.openModalWizard ()" ><i class="fa fa-plus-circle"></i> Crear notificación</button>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name">Nombre</th>
						<th class="col-lpm">Módulos</th>
						<th class="col-event">Evento</th>
						<th class="col-type">Tipo</th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $notification}
		{assign var='event' value=$notification->getEvent ()}
		{assign var='status' value=$notification->getStatus ()}
					<tr>
						<td>
							<p style="margin-bottom: 0;">
                                {$notification->getName ()}
							</p>
							<p style="font-size: 0.85em; font-style: italic; margin-bottom: 0;">{$notification->getDescription ()}</p>
						</td>
						<td>
		{foreach $notification->getModuleNames () as $moduleName}
							<p style="margin-bottom: 0;">{if $moduleName eq 'Users'}Todos los módulos{else}{$moduleName} {/if}</p>
		{/foreach}
						</td>
						<td>{$MOD[$event]}</td>
						<td>{$MOD[$notification->getStyle ()]}</td>
						<td>
							<ul class="actions">
								<li class="action">
									<form method="post" action="index.php">
										<input type="hidden" name="module" value="notifications" />
										<input type="hidden" name="action" value="ChangeStatusNotification" />
										<input type="hidden" name="record" value="{$notification->getId ()}" />
										<input type="hidden" name="Ajax" value="true" />
		{if $notification->getStatus () == Notification::STATUS_ACTIVE}
										<button type="submit" class="btn btn-success" title="Deshabilitar"><i class="fa fa-check"></i></button>
		{else}
										<button type="submit" class="btn btn-warning" title="Habilitar"><i class="fa fa-ban"></i></button>
		{/if}
										</form>
								</li>
								<li class="action">
									<a href="index.php?module=notifications&action=LogView&record={$notification->getId ()}&parenttab=Settings" class="btn btn-default" title="Registro de eventos"><i class="fa fa-search"></i></a>
								</li>
								<li class="action">
                                    <button class="btn btn-primary" onclick="NotificationUtils.openModalWizard ('{$notification->getId ()}')" ><i class="fa fa-pencil"></i></button>
								</li>
								<li class="action">
									<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la notificación seleccionada?');" style="display: inline;">
										<input type="hidden" name="module" value="notifications" />
										<input type="hidden" name="action" value="Delete" />
										<input type="hidden" name="record" value="{$notification->getId ()}" />
										<input type="hidden" name="Ajax" value="true" />
										<button type="submit" class="btn btn-danger btn-icon" title="Eliminar"><i class="fa fa-trash-o"></i></button>
									</form>
								</li>
							</ul>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="5" class="text-center">No hay notificaciones registradas</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
	{if (!empty ($SEARCH_KEYWORD))}
		{assign var='keywordUrlPart' value="&keyword=$SEARCH_KEYWORD"}
	{else}
		{assign var='keywordUrlPart' value=''}
	{/if}

			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=notifications&action=ListView&parenttab=Settings{$keywordUrlPart}&page=1{/if}"><i class="fa fa-step-backward"></i></a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=notifications&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$DATA.page - 1}{/if}"><i class="fa fa-chevron-left"></i></a>
				</li>
				{for $i=1 to $DATA.totalPages}
					<li{if ($i == $DATA.page)} class="active"{/if}>
						<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=notifications&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$i}{/if}">{$i}</a>
					</li>
				{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=notifications&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$DATA.page + 1}{/if}"><i class="fa fa-chevron-right"></i></a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=notifications&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$DATA.totalPages}{/if}"><i class="fa fa-step-forward"></i></a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
    {include file='modules/notifications/NotificationsWizard.tpl'}
	<script type="text/javascript" src="modules/notifications/notifications-wizard.js?v=2.2"></script>
{/strip}