<link rel="stylesheet" href="themes/centaurus/css/libs/select2.css" type="text/css" />
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<script src="themes/centaurus/js/select2.min.js"></script>
<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index">Configuración</a></li>
			<li class="active"><span>Eliminar Módulos</span></li>
		</ol>
		<h1>Eliminar Módulos</h1>
	</div>
</div>
<div class="col-lg-12">
	<div class="main-box clearfix">
		<header class="main-box-header clearfix"></header>
		<div class="main-box-body clearfix">
{if $MENSAJE neq ''}
				<div class="alert alert-danger fade in">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<i class="fa fa-times-circle fa-fw fa-lg"></i>
					{$MENSAJE.descripcion}
				</div>
{/if}
			<form id="ModuleDuplicator" name="ModuleDuplicator" method="post" action="index.php">
				<input type="hidden" name="module" id="module" value="Settings" class="form-control" />
				<input type="hidden" name="action" id="action" value="eliminarModulo2" class="form-control" />
				<input type="hidden" name="formodule" id="formodule" value="{$MODULOAELIMINAR}" class="form-control" />
				<input type="hidden" name="tabideliminar" id="tabideliminar" value="{$TABIDAELIMINAR}" class="form-control" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="row col-lg-12"></div>
				<input type="submit" class="btn btn-primary btn-md" name="eliminar" value="Eliminar" id="btnEliminar" />
			</form>
		</div>
	</div>
</div>