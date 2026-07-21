<div class="row">
	<div class="col-lg-6">
		<h1>Directrices	</h1>
	</div>

</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2 class="pull-left"></h2>

				<div class="filter-block pull-right">

				</div>
			</header>

			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th style="width:25%">DIRECTRICES</th>
								<th style="width:15%">Objetivos Estratégicos</th>
								<th style="width:20%">ESTRATEGIAS</th>
								<th style="width:20%">Indicadores clave (KPI's)</th>
								<th style="width:20%">Proyectos</th>
							</tr>
						</thead>
						<tbody>
							{foreach key=key_one item=directriz from=$DIRECTRICES}
							<tr>
								<td>
									<!-- ver directriz -->
									<a href="index.php?module=directricesproyectos&action=DetailView&record={$directriz.directricesproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-search"></i></a>

									{$directriz.titulo}
								</td>
								<td>
									{foreach key=key_two item=objetivo from=$directriz.objetivos}
										{$objetivo}<br>
									{/foreach}
								</td>
								<td colspan="3">


										<!-- tabla de estrategias-->
										<table width="100%" class="">
											<tbody>
												{foreach key=key_three item=estrategia from=$directriz.estrategias}
												<tr style="border:1px solid transparent;">
													<td style="width:33%;border:1px solid transparent;">

														<!-- ver estrategia -->
														<a href="index.php?module=estrategiasproyectos&action=DetailView&record={$estrategia.estrategiasproyectosid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-search"></i></a>

														{$estrategia.titulo}
													</td>
													<td style="width:33%;border:1px solid transparent;">
														{foreach key=key_three item=kpi from=$estrategia.indicadores}
															{$kpi}<br>
														{/foreach}
													</td>
													<td style="width:33%;border:1px solid transparent;">
														{foreach key=key_three item=proyecto from=$estrategia.proyectos}

															<a href="index.php?action=DetailView&module=proyectos&record={$proyecto.proyectosid}&parenttab="><i class="fa fa-search"></i></a>
															{$proyecto.name}

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

				</div>
			</div>
		</div>
	</div>
</div>
<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script>

</script>
