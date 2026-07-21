<div class="row">
	<div class="col-lg-6">
		<h1>{$MOD.LBL_Step} | {$MOD.LBL_Task}</h1>
	</div>
	<div class="col-lg-6 filter-block pull-right text-right">
		<button title="Añadir Hito" class="btn btn-primary sm" onclick="this.form.action.value='EditView';this.form.module.value='hito';this.form.return_module.value='{$MODULE}';this.form.return_id.value='{$ID}';this.form.return_action.value='DetailView';this.form.record.value=''" name="button" value="" type="submit"><i class='fa fa-plus-circle'></i> {$MOD.LBL_Step}</button>


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
					<table class="table">
						<thead>
							<tr>
								<th></th>
								<th>{"LBL_Step"|@getTranslatedString} </th>
								<th>{"LBL_Progress"|@getTranslatedString} </th>
								<th>{"LBL_Task"|@getTranslatedString} </th>
							</tr>
						</thead>
						<tbody>
							{foreach key=key_one item=hito from=$HITOSTAREAS}
							<tr>
								<td>
									<a href="index.php?module=hito&action=EditView&record={$hito.id}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-pencil"></i></a>

									<a href="javascript:confirmdelete('index.php?module={$MODULE}&delmodule=hito&action=DeleteRegistroAsociado&record={$hito.id}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab=')"><i class="fa fa-trash-o"></i></a>

									<button type="button" id="open-wizard-{$hito.id}" idProyecto="{$ID}" idHito="{$hito.id}" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> {"LBL_Task"|@getTranslatedString}</button>

								</td>
								<td>{$hito.name}</td>
								<td>
									{* arra individual *}
									<div class="progress">
										<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{$hito.progreso}" aria-valuemin="0" aria-valuemax="100" style="width: {$hito.progreso}%">
											<span class="sr-only">{$hito.progreso}% Completado</span>
										</div>
									</div>

								</td>
								<td>

									<!-- tabla de estrategias-->
									<table class="table">
										<tbody>
											{foreach key=key_three item=ticket from=$hito.tickets}
											<tr>
												<td>
													<a href="index.php?module=todotasks&action=EditView&record={$ticket.ticketid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab="><i class="fa fa-pencil"></i></a>

													<a href="javascript:confirmdelete('index.php?module={$MODULE}&delmodule=todotasks&action=DeleteRegistroAsociado&record={$ticket.ticketid}&return_module={$MODULE}&return_action=DetailView&return_id={$ID}&parenttab=')"><i class="fa fa-trash-o"></i></a>
												</td>
												<td><a href="index.php?action=DetailView&module=todotasks&record={$ticket.ticketid}&parenttab=">{$ticket.ticket_no}</a></td>
												<td>
													{$ticket.title}
												</td>
												<td>
													{assign var=status value=$ticket.status}
													{"LBL_$status"|@getTranslatedString}
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
