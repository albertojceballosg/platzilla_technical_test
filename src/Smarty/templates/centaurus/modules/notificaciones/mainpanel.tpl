<script type="text/javascript" src="modules/notificaciones/notificaciones.js"></script>
{*
<div class="md-modal md-effect-1" id="modal-noti" style="max-width:1000px;width:1000px;max-height:550px;height:550px;">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close" onclick="jQuery('#modal-2').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">x</button>
			<table width="100%">
			<tr>
			<td>
			<h4>{$MOD.LBL_DETALLE_NOTIFICACION}</h4>
			</td>
			<td>
			<div style="float:right">
			<button type="button" name="guardar" class="md-trigger btn btn-primary mrg-b-lg" data-modal="modal-notiedit" onclick="cargarFormaNotificacion();">{$MOD.LBL_ANSWER_NOTIFICATION}</button>
			</div>
			</td>
			</tr>
			</table>
		</div>
		<div class="modal-body" id="detalleNotificacion" style="max-height:450px;height:450px;overflow:auto;">
		</div>
	</div>
</div>	

<div class="md-modal md-effect-1" id="modal-notiedit" style="max-width:1000px;width:1000px;max-height:550px;height:550px;">
	<div class="md-content">
	<div class="modal-header">
		<button class="md-close close" onclick="jQuery('#modal-2').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">x</button>
		<table width="100%">
			<tr>
				<td>
					<h4>{$MOD.LBL_NEW_NOTIFICATION}</h4>		
				</td>
				<td>
					<div style="float:right">
						<button type="button" class="btn btn-primary" onclick="jQuery('#formanotificacion').submit();">{$MOD.LBL_ANSWER_NOTIFICATION}</button>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class="modal-body" id="formaNotificacion" style="max-height:450px;height:450px;overflow:auto;">
	</div>
	</div>
</div>	

<div class="md-overlay"></div><!-- the overlay element -->

<div class="col-lg-12">
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">{$MOD.LBL_NOTIFICACIONES_RECIBIDAS}</h2>
			
			<div class="filter-block pull-right">
				<div class="form-group pull-left">
					<input type="text" class="form-control" placeholder="{$MOD.LBL_SELECCIONE_REGISTRO}">
					<i class="fa fa-search search-icon"></i>
				</div>
				<div class="form-group pull-left">
					<select class="form-control" name="FiltroNotificaciones" onchange="actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');">
						<option value="">{$MOD.LBL_ALL}</option>
						<option value="Unread">{$MOD.LBL_UNREAD}</option>
						<option value="Read">{$MOD.LBL_READ}</option>
					</select>
				</div>
			</div>
		</header>
		<div id="notificacionesRecibidas">
		{$NOTIFICACIONES_RECIBIDAS}
		</div>
	</div>
</div>

<div class="col-lg-12">
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">{$MOD.LBL_NOTIFICACIONES_ENVIADAS}</h2>
			
			<div class="filter-block pull-right">
				<div class="form-group pull-left">
					<select class="form-control" name="filtroModulos" onchange="actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');">
							<option value="">{$MOD.LBL_ANY_MODULE}</option>
						{foreach item=modulename from=$MODULESNAME}
							<option value="{$modulename.name}">{$modulename.label}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group pull-left">
					<select class="form-control" style="max-width:300px" name="filtroModulos" onchange="actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');">
						<option value="">{$MOD.LBL_ALL_RECORDS}</option>
						{foreach item=recordassoc from=$RECORDS}
							<option value="{$recordassoc.id}">{$recordassoc.value}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group pull-left">
					<button type="button" name="guardar" class="md-trigger btn btn-primary mrg-b-lg" data-modal="modal-notiedit" onclick="cargarFormaNotificacion();">{$MOD.LBL_NEW_NOTIFICATION}</button>
				</div>
			</div>
		</header>
		<div id="notificacionesEnviadas">
		{$NOTIFICACIONES_ENVIADAS}
		</div>
	</div>
</div>
*}

<!-- [ TT11375 ] Notificaciones para “Mi Cuenta en Platzilla” - Pedidos Información Johana Romero 11/10/2016 -->
<div class="col-lg-12">
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">{$MOD.LBL_OPCIONES_CORREO}</h2>			
			<div class="filter-block pull-right">					
				<div class="form-group pull-left">
					<button type="button" name="guardar" class="md-trigger btn btn-primary mrg-b-lg" onclick="saveOptionsMail()">{$MOD.LBL_SAVE_OPC_MAIL}</button>
				</div>
			</div>			
		</header>
		<div id="opcionesCorreo">
			{$OPCIONES_CORREO}
		</div>
	</div>
</div>
