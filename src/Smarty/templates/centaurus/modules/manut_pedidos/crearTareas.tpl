<style>
{literal}


{/literal}
</style>

<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nifty-component.css"/>

<div class="md-modal md-effect-1" id="crearTareasPopup">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close">&times;</button>
			<h4 class="modal-title">Crear tarea</h4>
		</div>
		<div class="modal-body">
			<form role="form" name="formaTareas" action="index.php?file=DetailViewAjax&module=manut_pedidos&action=manut_pedidos&ajxaction=REGISTRATAREA" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="titulo">T&iacute;tulo</label>
					<input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ingrese un t&iacute;tulo">
				</div>
				<div class="form-group">
					<label for="descripcion">Descripci&oacute;n</label>
					<textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
				</div>
				
				<!--div class="form-inline form-inline-box">
					<div class="form-group">
						<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
					</div>
					<div class="form-group">
						<select class="form-control">
							<option>Active</option>
							<option>Inactive</option>
						</select>
					</div>
					<button type="submit" class="btn btn-link"><i class="fa fa-eye"></i> Preview</button>
				</div-->
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary">Save changes</button>
		</div>
	</div>
</div>
<div class="md-overlay"></div><!-- the overlay element -->
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>
