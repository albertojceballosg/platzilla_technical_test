<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">

				<div class="col-md-8">
					<h1 style="display:none;" id="addDirectriz">
					<a  href="index.php?module=directricesproyectos&action=EditView&directrizid={$directriz.directricesproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="  class="btn btn-success btn-sm"><i class="fa fa-plus-circle"></i></a>
					Directrices
				</h1>
				</div>
				<div class="col-md-4 pull-right text-right">
					<a href="#"  class="btn btn-success btn-sm" id="btnActivarEdicion" onClick="javascript:activarEdicion(event);">Editar</a>
					<a href="#"  class="btn btn-warning btn-sm" id="btnCancelarEdicion" style="display:none;" onClick="javascript:cancelarEdicion(event);">Cancelar</a>
				</div>
			</header>

			<div class="main-box-body clearfix">
				<div class="table-responsive" style="min-height:300px;">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>DIRECTRICES</th>
								<th>Objetivos Estratégicos</th>
								<th width="20%">ESTRATEGIAS</th>
								<th width="20%">Indicadores clave (KPI's)</th>
								<th width="20%">Proyectos</th>
							</tr>
						</thead>
						<tbody>
							{foreach key=key_one item=directriz from=$DIRECTRICES}
							<tr>
								<td>
									{$directriz.titulo} <br>
									<!-- botones -->
									<div class="btn-group pull-left" style="display:none;">
										<button type="button" style="padding:3px" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
											<i class="fa fa-gear"></i>
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu">

											<li><a href="index.php?module=directricesproyectos&action=EditView&record={$directriz.directricesproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-pencil"></i> Editar</a></li>


											<li><a href="javascript:confirmdelete('index.php?module=hoshinkanri&action=DeleteRegistroAsociado&delmodule=directricesproyectos&record={$directriz.directricesproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab=')"><i class="fa fa-trash-o"></i> Eliminar</a></li>

											<li><a href="index.php?module=directricesproyectos&action=DetailView&record={$directriz.directricesproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-search"></i>Ver</a></li>

											<li><a href="index.php?module=estrategiasproyectos&action=EditView&directrizid={$directriz.directricesproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="  class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i>Crear Estrategia</a></li>
										</ul>
									</div>

								</td>
								<td>
									{foreach key=key_two item=objetivo from=$directriz.objetivos}
										{$objetivo}<br>
									{/foreach}
								</td>
								<td colspan="3">

										<!-- tabla de estrategias-->
										<table width="100%" style="font-size:80%" class="table table-bordered">
											<tbody>
												{foreach key=key_three item=estrategia from=$directriz.estrategias}
												<tr style="border:1px solid transparent;">
													<td style="width:33%;border-width: 0px 2px 0px 0px;">

														{$estrategia.titulo} <br>

														<!-- botones -->
														<div class="btn-group pull-left" style="display:none;">
															<button type="button" style="padding:3px" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
																<i class="fa fa-gear"></i>
																<span class="caret"></span>
															</button>
															<ul class="dropdown-menu" role="menu">

																<li><a href="index.php?module=estrategiasproyectos&action=EditView&record={$estrategia.estrategiasproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-pencil"></i>Editar</a></li>

																<li><a href="javascript:confirmdelete('index.php?module=hoshinkanri&action=DeleteRegistroAsociado&delmodule=estrategiasproyectos&record={$estrategia.estrategiasproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab=')"><i class="fa fa-trash-o"></i>Eliminar</a></li>

																<li><a href="index.php?module=estrategiasproyectos&action=DetailView&record={$estrategia.estrategiasproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-search"></i>Ver</a></li>

																<li><a href="index.php?module=proyectos&action=EditView&estrategiasproyectosid={$estrategia.estrategiasproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab=" class="btn btn-info btn-sm"><i class="fa fa-plus-square"></i>Crear Proyecto</a></li>

																<li><a onClick="jQuery('#estrategiaid').val({$estrategia.estrategiasproyectosid});return window.open('index.php?module=proyectos&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=proyectoid&srcmodule=hoshinkanri&forrecord=21069','test','width=640,height=602,resizable=0,scrollbars=0,top=150,left=200');" href="javascript:;" ><i class="fa fa-plus-square"></i>Seleccionar Proyecto</a></li>


															</ul>
														</div>


													</td>
													<td style="width:33%;border-width: 0px 2px 0px 0px;">
														{foreach key=key_three item=kpi from=$estrategia.indicadores}
															{$kpi}<br>
														{/foreach}
													</td>
													<td style="width:33%;border:1px solid transparent;">
														<!-- editar proyecto -->
														{foreach key=key_three item=proyecto from=$estrategia.proyectos}

														<br>
														<!-- botones -->
														<div class="btn-group pull-left" style="display:none;">
															<button type="button" style="padding:3px" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
																<i class="fa fa-gear"></i>
																<span class="caret"></span>
															</button>
															<ul class="dropdown-menu" role="menu">

																<li><a href="index.php?module=proyectos&action=EditView&record={$proyecto.proyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-pencil"></i>Editar</a></li>

																<li><a href="index.php?action=DetailView&module=proyectos&record={$proyecto.proyectosid}&parenttab="><i class="fa fa-search"></i>Ver</a></li>

															</ul>
														</div>
														{$proyecto.name}<br>



														{/foreach}

													</td>
												</tr>
												{/foreach}
											</tbody>
										</table>
										<!-- fin de tabla de estrategias-->



								</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
					<br>

					<div id="leyenda" style="display:none;">
						<div class="btn btn-success btn-sm"><i class="fa fa-plus-circle"></i></div> Crear Directriz &nbsp; &nbsp;
						<div class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i></div> Crear Estrategia &nbsp; &nbsp;
						<div class="btn btn-info btn-sm"><i class="fa fa-plus-square"></i></div> Crear Proyecto
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<form  action="index.php" method="post" name="personalizado" id="personalizado" >

	<input id="module" name="module" value="hoshinkanri" type="hidden">
	<input id="action" name="action" value="asociarProyectoPopup" type="hidden">
	<input id="estrategiaid" name="estrategiaid" value="" type="hidden">
	<input id="proyectoid" name="proyectoid" value="" type="hidden">
	<input id="return_id" name="return_id" value="{$ID}" type="hidden">
	<input id="proyectoid_display" name="proyectoid_display" readonly="" class="form-control" value="" type="hidden">
</form>


<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script>

function activarEdicion(e){ldelim}
	e.preventDefault();
	jQuery(".btn-group.pull-left").css("display","block");
	jQuery("#addDirectriz").css("display","");
	jQuery("#btnActivarEdicion").css("display","none");
	jQuery("#btnCancelarEdicion").css("display","");
	jQuery("#leyenda").css("display","");

{rdelim}

function cancelarEdicion(e){ldelim}
	e.preventDefault();
	jQuery(".btn-group.pull-left").css("display","none");
	jQuery("#addDirectriz").css("display","none");
	jQuery("#btnActivarEdicion").css("display","");
	jQuery("#btnCancelarEdicion").css("display","none");
	jQuery("#leyenda").css("display","none");
{rdelim}



</script>
