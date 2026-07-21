{strip}
<script src="themes/{$THEME}/js/jquery-ui.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/summernote.min.css" />
<form action="index.php" method="post" name="EditView" id="notifyForm">
	<input type="hidden" class="form-control" name="module" value="{$MODULE}" />
	<input type="hidden" class="form-control" name="action" value="Save" />
	<input type="hidden" class="form-control" name="mode" value="Edicion" />
	<input type="hidden" class="form-control" name="record" value="{$NOTIFICACION.notifyid}" />
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<div class="col-lg-12">
					<h1 class="col-lg-8 pull-left">
						<a href="index.php?module={$MODULE}&action=index">{$MOD.notifymanager}</a></h1>
					<div class="col-lg-4 pull-right text-right">
						<input type="button" class="btn btn-primary" name="save" onclick="guardarNotificacion()" value="Guardar" style="margin-right: 5px;" />
						<a class="btn btn-warning" href="index.php?module={$MODULE}&action=index">Cancelar</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix" style="min-height: 820px;">
				<div class="main-box-body no-header clearfix" style="padding-top: 15px;">
					<div class="row">
						<div class="form-group">
							<label for="title">Título</label>
							<input class="form-control" name="title" id="title" value="{$NOTIFICACION.title}" placeholder="Escriba un titulo para esta Notificación" type="text">
						</div>
						<div class="form-group">
							<label for="description">Descripción</label>
							<input class="form-control" name="description" id="description" value="{$NOTIFICACION.description}" placeholder="Describa la Notificación" type="text">
						</div>
						<div class="form-group">
							<label for="modulo">Módulo</label>
							<select class="form-control" name="modulo[]" id="modulo" multiple="multiple">
								<option value="Home"{if 'Home'|in_array:$NOTIFICACION.modules} selected="selected"{/if}>Home page</option>
{foreach key=keyModulo item=modulo from=$MODULOSDECAMPOS}
								<option value="{$modulo.name}"{if $modulo.name|in_array:$NOTIFICACION.modules} selected="selected"{/if}>{$modulo.tablabel} ({$modulo.name})</option>
{/foreach}
							</select>
						</div>
						<div class="form-group">
							<label for="view">Vista</label>
							<select class="form-control" name="view" id="view">
{foreach key=keyModulo item=vista from=$VISTASDISPONIBLES}
								<option value="{$vista.name}" {if $NOTIFICACION.action eq $vista.name} selected="selected" {/if}>{$vista.label}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group">
							<label for="active">Estado</label>
							<select class="form-control" name="active" id="active">
								<option value="1">Activa</option>
								<option value="0">Inactiva</option>
							</select>
						</div>
						<div class="form-group">
							<label for="textoNotificacionTextarea" style="margin-right: 5px;">Diseñe su notificación</label>
							<input type="button" class="btn btn-info" onclick="previsualizar()" value="Previsualizar" />
							<textarea name="textoNotificacionTextarea" id="textoNotificacionTextarea" style="display:none;"></textarea>
						</div>
					</div>
					<div class="summernote" id="textoNotificacion">
{$NOTIFICACION.design|unescape:"htmlall"}
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix" style="">
				<header class="main-box-header clearfix">
					<h2>Previsualización</h2>
				</header>
				<div class="main-box-body no-header clearfix">
					<div class="row">
						<div class="" id="preView">
{$NOTIFICACION.design|unescape:"htmlall"}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<div class="col-lg-12">
					<h1 class="col-lg-8 pull-left"></h1>
					<div class="col-lg-4 pull-right text-right">
						<input type="button" class="btn btn-primary" name="save" onclick="guardarNotificacion()" value="Guardar" style="margin-right: 5px;" />
						<a class="btn btn-warning" href="index.php?module={$MODULE}&action=index">Cancelar</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="themes/{$THEME}/js/summernote.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/summernote_es-ES.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/pace.min.js"></script>
<script type="text/javascript">
	jQuery (function () {
		jQuery ('.summernote').summernote ({
			height:    400,
			minHeight: 200,
			lang:      'es-ES'
		});
	});

	function guardarNotificacion () {
		var textoNotificacion = jQuery ('#textoNotificacion').code ();
		jQuery ('#textoNotificacionTextarea').val (textoNotificacion);
		var esValido = 1;
		if (jQuery.trim (jQuery ('#title').val ()) == '') {
			alert ('Escriba el título de la Notificación');
			return false;
		}

		if (jQuery.trim (jQuery ('#description').val ()) == '') {
			alert ('Escriba una Descripción para esta Notificación');
			return false;
		}

		if (jQuery ('#modulo').val () == null) {
			alert ('Seleccione el(los) módulo(s) en los que desea mostrar esta Notificación');
			return false;
		}

		if (jQuery.trim (textoNotificacion) == '') {
			alert ('Diseñe la Notificación');
			return false;
		}

		if (esValido == 1) {
			jQuery ('#notifyForm').submit ();
		}
	}

	function previsualizar () {
		var textoNotificacion = jQuery ('#textoNotificacion').code ();
		jQuery ('#preView').html (textoNotificacion);
	}
</script>
{/strip}