{strip}
<style type="text/css">
	.btn.btn-icon {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
	.col-number {
		text-align: right;
		width: 10em;
	}
	.col-default {
		text-align: center;
		width: 8em;
	}
	.col-actions {
		width: 8em;
	}
</style>
<div id="email-box" class="clearfix" style="padding-bottom: 20px;">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-book purple-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">TARIFAS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Tarifas aplicables a los planes de suscripción a Platzilla</td>
		</tr>
		</tbody>
	</table>
{if (!empty ($MESSAGE))}
	<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
		<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix text-right">
			<div class="pull-right">
				<a href="index.php?module=Pricebooks&action=EditView&parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Crear tarifa</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name"><b>Tarifa</b></th>
						<th class="col-number"><b>Multiplicador</b></th>
						<th class="col-default"><b>Tarifa por defecto</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $pricebook}
					<tr>
						<td class="col-name">
							<p style="margin-bottom: 0;"><a href="index.php?module=Pricebooks&action=EditView&record={$pricebook->getId ()}&parenttab=Settings">{$pricebook->getName ()}</a></p>
							<p style="font-size: 0.85em; font-style: italic; margin-bottom: 0;">{$pricebook->getDescription ()}</p>
						</td>
						<td class="col-number">{$pricebook->getMultiplier ()|number_format: 2: ',': '.'}</td>
						<td class="col-default"><span class="label label-{if ($pricebook->isDefault ())}success{else}info{/if}">{if ($pricebook->isDefault ())}Sí{else}No{/if}</span></td>
						<td class="col-actions">
							<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la tarifa {$pricebook->getName ()}?');" style="display: inline;">
								<input type="hidden" name="module" value="Pricebooks" />
								<input type="hidden" name="action" value="Delete" />
								<input type="hidden" name="record" value="{$pricebook->getId ()}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-danger btn-icon" title="Eliminar"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
	{/foreach}
{else}
						<tr class="lvtColData">
							<td colspan="4" class="text-center">No hay tarifas registradas</td>
						</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=Pricebooks&action=ListView&parenttab=Settings&page=1{/if}"><i class="fa fa-step-backward"></i></a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=Pricebooks&action=ListView&parenttab=Settings&page={$DATA.page - 1}{/if}"><i class="fa fa-chevron-left"></i></a>
				</li>
				{for $i=1 to $DATA.totalPages}
					<li{if ($i == $DATA.page)} class="active"{/if}>
						<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=Pricebooks&action=ListView&parenttab=Settings&page={$i}{/if}">{$i}</a>
					</li>
				{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Pricebooks&action=ListView&parenttab=Settings&page={$DATA.page + 1}{/if}"><i class="fa fa-chevron-right"></i></a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Pricebooks&action=ListView&parenttab=Settings&page={$DATA.totalPages}{/if}"><i class="fa fa-step-forward"></i></a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
{/strip}