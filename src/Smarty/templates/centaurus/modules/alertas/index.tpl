<script>

</script>




<div class="row">
	<div class="col-lg-9 pull-left">
		<h1><a href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}">{$MOD.Alertas}</a></h1>
	</div>
	<div class="col-lg-3 pull-right text-right">
		<button class="md-trigger btn btn-primary mrg-b-lg" data-modal="{$ID_DLG_NUEVA_ALERTA_LSSI}">{$MOD.LBL_CREATE_ALERT} LSSI</button>
	</div>




<div class="col-lg-12 col-md-12 col-sm-12">
	<div class="main-box clearfix">
		<div class="tabs-wrapper profile-tabs">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-lssi" data-toggle="tab">LSSI</a></li>
				<li><a href="#tab-general" data-toggle="tab">{$MOD.Detalles_de_Alertas}</a></li>
			</ul>
			<div class="tab-content">
				
				<div class="tab-pane fade in active" id="tab-lssi">
					<div id="lssi">
						<div class="table-responsive">

							<table class="table table-striped table-hover">

								<thead>
									<th>{$MOD.LBL_TITLE}</th>
									<th>{$MOD.LBL_BOXSCORE}</th>
									<th>{$MOD.LBL_INDICADOR}</th>
									<th>{$MOD.PARAMETERS}</th>
									<th>{$MOD.LBL_VALOR_COMP}</th>
									<th>{$MOD.LBL_PERIODICIDAD}</th>
									<th>{$MOD.LBL_ULTIMO_VALOR}</th>
									<th></th>
								</thead>
								<tbody>
									{foreach key=keyRLSSI item=alertaLSSI from=$REGISTROSLSSI}
									<tr>
										<td>{$alertaLSSI.titulo}</td>
										<td>{$alertaLSSI.tituloboxscore}</td>
										<td>{$alertaLSSI.tituloindicadorboxscore}</td>
										<td>{$alertaLSSI.parametro_default}</td>
										<td>{$alertaLSSI.comparacion_default}</td>
										<td>{$alertaLSSI.periodicidad}</td>
										<td>
											{if $alertaLSSI.cantidadRegistrosAlerta > 0}
											<a class="btn btn-danger" data-toggle="dropdown">
												<span class="count">{$alertaLSSI.cantidadRegistrosAlerta}</span>
												<i class="fa fa-warning"></i>
											{/if}
											</a>
										</td>
										<td style="width: 10%;">
											<a href="index.php?module=alertas&action=DetailAlerta&record={$alertaLSSI.alertasid}&return_module=alertas&return_action=index&parenttab=" class="table-link" style="margin:0 0px;">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-search fa-stack-1x fa-inverse"></i>
												</span>
											</a>

											<a href="index.php?module=alertas&action=EditView&record={$alertaLSSI.alertasid}&return_module=alertas&return_action=index&parenttab=" class="table-link" style="margin:0 0px;">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
												</span>
											</a>


											<a href="javascript:confirmdelete('index.php?module=alertas&action=Delete&record={$alertaLSSI.alertasid}&return_module=alertas&return_action=index&parenttab=')" class="table-link danger" style="margin:0 0px;">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
												</span>
											</a>
										</td>

									</tr>
									{/foreach}
								</tbody>


								
							</table>
						</div>
					</div>
				</div>

				


				<div class="tab-pane fade" id="tab-general">
					<div id="general">

						{foreach  key=keyDALSSI item=dataAlertaLSSI  from=$DATAALERTASLSSI}

						<div class="table-responsive">
							<h1 class="text-center">{$dataAlertaLSSI.titulo} </h1>
							<h2 class="text-center">{$dataAlertaLSSI.descripcion} {$dataAlertaLSSI.comparacion_default} {$dataAlertaLSSI.parametro_default}</h2>

							<table class="table table-striped table-hover">

								<thead>
									<tr>
										<th>{$MOD.LBL_DATE}</th>
										<th>{$MOD.LBL_VALUE}</th>
									</tr>
								</thead>
								<tbody>
									{foreach key=keyAlertaLSSI item=alertaLSSI from=$dataAlertaLSSI.data}
									<tr>
										
										<td>{$alertaLSSI.fecha}</td>
										<td>{$alertaLSSI.valor}</td>
									</tr>
									{/foreach}
								</tbody>


								
							</table>
						</div>
						{/foreach}


					</div>
				</div>


			</div>

		</div>
	</div>
</div>




</div>







{*   {$DLG_NUEVA_ALERTA}  *}

{$DLG_NUEVA_ALERTA_LSSI}