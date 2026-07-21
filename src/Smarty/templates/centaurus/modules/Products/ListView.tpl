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
	.col-type {
		width: 10em;
	}
	.col-number {
		text-align: right;
		width: 10em;
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
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-briefcase red-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">PRODUCTOS Y SERVICIOS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Administra los productos y servicios que ofrece Platzilla</td>
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
				<a href="index.php?module=Products&action=EditView&parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Crear producto o servicio</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name"><b>Nombre</b></th>
						<th class="col-type"><b>Tipo</b></th>
						<th class="col-number"><b>Precio base</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $product}
					<tr>
						<td class="col-name">
							<a href="index.php?module=Products&action=EditView&record={$product->getId ()}&parenttab=Settings">{$product->getName ()}</a>
						</td>
						<td class="col-type">{$MOD[$product->getType ()]}</td>
						<td class="col-number">{$product->getBasePrice ()|number_format: 2: ',': '.'}</td>
						<td class="col-actions">
							<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar el producto/servicio {$product->getName ()}?');" style="display: inline;">
								<input type="hidden" name="module" value="Products" />
								<input type="hidden" name="action" value="Delete" />
								<input type="hidden" name="record" value="{$product->getId ()}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-danger btn-icon" title="Eliminar"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
	{/foreach}
{else}
						<tr class="lvtColData">
							<td colspan="4" class="text-center">No hay productos o servicios registrados</td>
						</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=Products&action=ListView&parenttab=Settings&page=1{/if}"><i class="fa fa-step-backward"></i></a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=Products&action=ListView&parenttab=Settings&page={$DATA.page - 1}{/if}"><i class="fa fa-chevron-left"></i></a>
				</li>
				{for $i=1 to $DATA.totalPages}
					<li{if ($i == $DATA.page)} class="active"{/if}>
						<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=Products&action=ListView&parenttab=Settings&page={$i}{/if}">{$i}</a>
					</li>
				{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Products&action=ListView&parenttab=Settings&page={$DATA.page + 1}{/if}"><i class="fa fa-chevron-right"></i></a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Products&action=ListView&parenttab=Settings&page={$DATA.totalPages}{/if}"><i class="fa fa-step-forward"></i></a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
{/strip}