<script type="text/javascript" src="modules/panelusuarios/panelusuarios.js"></script>
		
		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">Configuración</a></li>
					<li class="active"><span><a href="index.php?module=Settings&action=listroles&parenttab=Settings">Panel de Usuarios</span></a></li>
				</ol>
			</div>
		</div>


		<div class="row">
		<div class="col-lg-12">	
			<div class="main-box no-header">
				<div class="main-box-body clearfix" id="">
					{$BUTTONS}
				</div>
			</div>
		</div>
	</div>

	<!-- Johana Romero - [ TT11338 ] Nueva estructura de Usuarios de instancias - PLATZILLA - Mensaje de error al llegar al limite de usuarios -->
	<div class="row">
		<div class="col-lg-12">	
			<div class="main-box no-header clearfix">
				<div class="main-box-body clearfix">
					{if $MAXUSERS eq 1}
						<div class="alert alert-danger">
							<i class="fa fa-times-circle fa-fw fa-lg"></i>
							<strong>Ha llegado al límite de usuarios contratados!</strong>
							Debe actualizar la suscripción para poder agregar más usuarios.
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>


		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header clearfix">
					<div class="main-box-body clearfix">
						<div class="table-responsive">
							<table class="table user-list table-hover">
								<thead>
									<tr>
										<th><span>User</span></th>
										<th><span>Created</span></th>
										<th class="text-center"><span>Status</span></th>
										<th><span>Email</span></th>
										<th>&nbsp;</th>
									</tr>
								</thead>
								<tbody>
									
									{foreach item=user key=count from=$USUARIOS}
									<tr>
										<td>
											
											{if $user.profileImage neq ''}
												<img src="{$user.profileImage}" style="border-radius:50%">
											{else}
												<i class="fa fa-user"></i>
											{/if}
											<a href="{$user.hrefLinkDetail}" class="user-link">{$user.first_name} {$user.last_name}</a>
											<span class="user-subhead">{$user.rolename}</span>
										</td>
										<td>
											{$user.date_entered}
										</td>
										<td class="text-center">
											<span class="label {$user.statusLabel}">{$user.status}</span>
										</td>
										<td>
											<a href="#">{$user.email1} </a>
										</td>
										<td style="width: 20%;">
											
											{$user.linkDetail}
											{$user.linkEdit} 
									    {if ($smarty.session.esInstancia) } 
									    	{$user.linkPermission}
									    {/if}
											{$user.linkDelete}											
											<!--a href="#" class="table-link">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
												</span>
											</a>

											<a href="#" class="table-link">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
												</span>
											</a>
											<a href="#" class="table-link danger">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
												</span>
											</a-->
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

		<!--div class="row"  style="border: 10px solid #ff00c3">
			<div class="col-lg-12">
				{$DATA}
			</div>
		</div-->
