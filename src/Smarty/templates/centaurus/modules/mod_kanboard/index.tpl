<div class="col-lg-12">

	<div class="row">



			<div class="col-lg-6 pull-left">
				<h1>Kanban</h1>
			</div>

	<div class="filter-block pull-right" style="margin-right:10px; margin-top:auto">

		<!--a href="javascript:window.open('index.php?module=HelpDesk&action=creaRegistro&record=&Popup=true&registro=Crear+Tarea+de+desarrollo&tipo=Incidencia','popup','width=500px,height=420,top=200,left=400');" id="" class="btn btn-primary  pull-right">
			<i class="fa fa-plus-circle fa-lg" title="Crear Tarea"></i> Tarea</a-->

		<div class="btn-group pull-right">
		<a href="index.php?module=mod_kanboard&action=analitica" id="" class="btn btn-default pull-right" >
			<i class="fa fa-bar-chart-o" title="View Analytics"></i> Analytics</a>
	</div>
	</div>

</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2 class="pull-left"></h2>

				<div class="filter-block pull-right">

					<form method="post" action="index.php?module={$MODULE}&action=index&parenttab={$PARENTTAB}">
						<div class="filter-block pull-right">
							<div class="form-group pull-left col-lg-2 col-xs-4">
								{$ACCOUNTS}
							</div>
							<div class="form-group pull-left col-lg-2 col-xs-4">
								<select class="form-control" name="proyectosid">
								<option value="" selected> Seleccione Proyecto </option>
								{$PROJECTS}
								</select>
							</div>
							<div class="form-group pull-left col-lg-2 col-xs-4">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="text" class="form-control" name="fecha_desde" id="fecha_desde" value="{$DATASELECCIONADA.fecha_desde}" placeholder="Desde">
								</div>
							</div>
							<div class="form-group pull-left col-lg-2 col-xs-4">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="text" class="form-control" name="fecha_hasta" id="fecha_hasta"  value="{$DATASELECCIONADA.fecha_hasta}"  placeholder="Hasta">

								</div>

							</div>
							<div class="form-group pull-left col-lg-2 col-xs-4">
								<div class="input-group" style="padding-right: 10px;">
									<input type="submit" value="Filter" name="filter" class="btn  btn-primary">


								</div>
							</div>








							<div class="btn-group pull-left" >
							<div class="form-group pull-left col-lg-2 col-xs-4" style="margin-right:20px;">
								<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
															<i class="fa fa-bar-chart-o"></i><span class="caret"></span>
														</button>
														<ul class="dropdown-menu" role="menu">
															<li><a href="index.php?module=mod_kanboard&action=adm_columnas">Gestionar Columnas</a></li>
														</ul>





							</div>

						</div>
</div>



					</form>



				</div>
			</header>

			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table" style="width:100%" >
						<tr>
						{foreach item=arr from=$TIPOS_GENERICOS}
							<th style="width:{math equation="x/y" x=100 y=$TIPOS_GENERICOS|@count}%">

							{$arr|@getTranslatedString:$MODULE}
							</th>
						{/foreach}
						</tr>
						<tbody>
							<tr id="elsortable">
							{foreach item=arrX key=key from=$TIPOS_GENERICOS}
								{if $key eq 0}
									{assign var="class" value="alert-info"}
								{elseif $key eq 1}
									{assign var="class" value="alert-success"}
								{elseif $key eq 2 || $key eq 3 || $key eq 4}
									{assign var="class" value="alert-warning"}
								{elseif $key eq 5}
									{assign var="class" value="alert-success"}
								{/if}
								<td style="font-size: 0.875em;font-weight: normal;vertical-align: top;" id="elsortable_{$key}" data-tipo="{$arrX}" data-class="alert {$class}">
								{foreach item=arr2 key=key2 from=$REGISTROS_GENERICOS[$key]}
									<div class="alert {$class}" data-ticketid="{$arr2.todotasksid}" id="ticketid_{$arr2.ticketid}">

										<a href="#" data-toggle="modal" data-target="#myModal{$arr2.todotasksid}">{$arr2.title|truncate:45:"...":true}</a>

									</div>

								{/foreach}
								</td>
							{/foreach}
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

{foreach item=arrX key=key from=$TIPOS_GENERICOS}

{foreach item=arr2 key=key2 from=$REGISTROS_GENERICOS[$key]}
<div class="modal fade" id="myModal{$arr2.todotasksid}" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">{$arr2.title}</h4>
				</div>
				<div class="modal-body">
					<form role="form">
						<div class="form-group">
							<small><i class="fa fa-suitcase"></i> {$arr2.accountname}</small><br/>
							<small><i class="fa fa-user"></i> {$arr2.user_name}</small><br/><br/>

						</div>
					</form>
					<p class="text-right">
						<a href="index.php?action=EditView&module=todotasks&record={$arr2.todotasksid}&return_module={$MODULE}&return_action=index" style="padding-right: 20px;"><i class="fa fa-edit"></i></a>
						<a href="javascript:confirmdelete('index.php?module=todotasks&action=Delete&record={$arr2.todotasksid}&return_module={$MODULE}&return_action=index');">
						<i class="fa fa-trash-o" style="padding-right: 5px;"></i></a>
					</p>



				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
</div>
{/foreach}
{/foreach}

<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script>
jQuery('#fecha_desde').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
jQuery('#fecha_hasta').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
var evento;
{foreach item=arrX key=key from=$TIPOS_GENERICOS}
	var elsortable_{$key}=jQuery('#elsortable_{$key}').sortable({ldelim}
			connectWith: "td",
			cursor: "move",
			receive: function( event, ui ){ldelim}
				evento=event;
				jQuery(ui.item).removeClass();
				jQuery(ui.item).addClass(event.target.dataset.class);
				saveRegistro(event,ui);
			{rdelim},
		{rdelim});
	jQuery( "#selsortable_{$key}" ).disableSelection();
{/foreach}
{literal}
function saveRegistro(event,ui){
	var tipo=event.target.dataset.tipo;
	var ticketid=ui.item.attr("data-ticketid");
	jQuery('#status').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "mod_kanboard", action: "mod_kanboardAjax",file: "Save",save:'estadotarea',tipo:tipo,ticketid:ticketid }
	}).done(function( response ) {
		jQuery('#status').hide();
		console.log(response);
	});
	return false;
}
{/literal}

</script>
