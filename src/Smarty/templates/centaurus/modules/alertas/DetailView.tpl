



<div class="row">
	<div class="col-lg-9 pull-left">
		<h1><a href="index.php?action=index&module={$MODULE}&parenttab={$CATEGORY}"> {$MOD.LBL_ALERTA}</a></h1>
	</div>
	<div class="col-lg-3 pull-right text-right" >
		<a href="index.php?module={$MODULE}&action=EditView&record={$ID}&return_module={$MODULE}&return_action=DetailAlerta" class="btn btn-primary">{$APP.LBL_EDIT_BUTTON}</a>
		<a href="javascript:confirmdelete('index.php?module={$MODULE}&action=Delete&record={$ID}&return_module={$MODULE}&return_action=index&parenttab=')" class="btn btn-danger">{$APP.LBL_DELETE_BUTTON}</a>
	</div>

</div>

<div class="row">
  	<div class="col-lg-12"> 
    	<div class="main-box">

	    	<header class="title-section main-box-header clearfix">
				<h2>Detalles de {$MOD.LBL_ALERTA}</h2>
			</header>

		    <div class="main-box-body clearfix" id="">

		    	<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_TITLE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_TITLE}">
								{$DATAALERTA.titulo}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_BOXSCORE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_BOXSCORE}">
								{$DATAALERTA.tituloboxscore}
							</span>
						</div>
					</div>
				</div>

		    	<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_INDICADOR}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_INDICADOR}">
								{$DATAALERTA.tituloindicadorboxscore}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_PERIODICIDAD_ANALISIS}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_PERIODICIDAD_ANALISIS}">
								{$DATAALERTA.periodicidad}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_COMPARACION}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_COMPARACION}">
								{$DATAALERTA.comparacion_default}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_VALOR}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_VALOR}">
								{$DATAALERTA.parametro_default}
							</span>
						</div>
					</div>
				</div>


				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CREATE_NC}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa {if $DATAALERTA.crearnc eq 1}fa-check-square{else}fa-square-o{/if}"></i></span>
							<span class="form-control label-readonly b-left" readonly="" data-toggle="tooltip">{if $DATAALERTA.crearnc eq 1} sí {else} no {/if}</span>
						</div>
					</div>

					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_SEND_EMAIL}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="input-group-addon label-readonly"><i class="fa {if $DATAALERTA.enviaremail eq 1}fa-check-square{else}fa-square-o{/if}"></i></span>
							<span class="form-control label-readonly b-left" readonly="" data-toggle="tooltip">{if $DATAALERTA.enviaremail eq 1} sí {else} no {/if}</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_USUARIO_RESPONSABLE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_COMPARACION}">
								{foreach item=arr from=$LISTA_USUARIOS}

									{if $arr[0] eq $DATAALERTA.emailsid}
										{$arr[1]} ({$arr[2]}) <br>
									{/if}
									
								{/foreach}
							</span>
						</div>
					</div>
				</div>

		    </div>
    	</div>
  	</div>
</div>








