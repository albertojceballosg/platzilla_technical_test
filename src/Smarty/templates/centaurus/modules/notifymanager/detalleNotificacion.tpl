
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery-ui.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/summernote.min.css" />

<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-12">
				<h1 class="col-lg-8 pull-left"><a href="index.php?module={$MODULE}&action=index">{$MOD.notifymanager}</a></h1>
				<div class="col-lg-4 pull-right text-right">
					<a href="index.php?module={$MODULE}&action=EditView&record={$NOTIFICACION.notifyid}" class="btn btn-primary">Editar</a>
					<a href="index.php?module={$MODULE}&action=index" class="btn btn-warning">Cancelar</a>
				</div>
			</div>
		</div>
	</div>
</div>




<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix" style="">
			<header class="main-box-header clearfix">
				<h2>Información Básica</h2>
			</header>
			
			<div class="main-box-body no-header clearfix">
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">Título</label>
						<p><b>{$NOTIFICACION.title}</b></p>
					</div>
					<div class="form-group col-md-6">
						<label for="description">Descripción</label>
						<p><b>{$NOTIFICACION.description}</b></p>
					</div>
					<div class="form-group col-md-6">
						<label for="modulo">Módulo</label>
						<p><b>{$NOTIFICACION.tablabel}</b></p>
					</div>
					<div class="form-group col-md-6">
						<label for="view">Vista</label>
						<p><b>{$NOTIFICACION.action}</b></p>
					</div>
					<div class="form-group col-md-6">
						<label for="active">Estado</label>
						<p><b><span class="label label-{if $NOTIFICACION.active eq 1 }success{else}warning{/if}"> 
									{if $NOTIFICACION.active eq 1 } Activa {else} Inactiva {/if} </span></b></p>
					</div>
					
				</div>

			</div>
					
		</div>
	</div>
</div>


<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix" style="">
			<header class="main-box-header clearfix">
				<h2>Diseño de la Notificación</h2>
			</header>
			
			<div class="main-box-body no-header clearfix">
				<div class="row">
					
				</div>

				<div class="summernote" id="textoNotificacion">
					{$NOTIFICACION.design|unescape:"htmlall"}
				</div>
			</div>
					
		</div>
	</div>
</div>



