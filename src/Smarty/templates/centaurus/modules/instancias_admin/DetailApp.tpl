<div class="col-lg-12">
	<div class="pull-left">
		<h1><a href="index.php?action=DetailViewInstancia&module={$RETURNMODULE}&record={$RETURNRECORD}&parenttab=">Detalle de Aplicación</a></h1>
	</div>
	<div class="pull-right text-right">
		<a class="btn btn-warning" href="index.php?action=DetailViewInstancia&module={$RETURNMODULE}&record={$RETURNRECORD}&parenttab=">Volver</a>
	</div>
</div>


<div class="row">

	<div class="col-lg-12">

		<div class="main-box">
			<header class="title-section main-box-header clearfix">
				<h2>Datos básicos</h2>
			</header>
			<div class="main-box-body clearfix">

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CONFIG_APPS_CODE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" id="" data-toggle="tooltip">
								{$CONFIGAPPLICATION.app_code}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CONFIG_APPS_NAME}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" id="" data-toggle="tooltip">
								{$CONFIGAPPLICATION.app_name}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CONFIG_APPS_PRICE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" id="" data-toggle="tooltip">
								{$CONFIGAPPLICATION.app_price}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CONFIG_APPS_URL}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" id="" data-toggle="tooltip">
								{$CONFIGAPPLICATION.app_url}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" id="" data-toggle="tooltip">
								{$CONFIGAPPLICATION.app_description}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CONFIG_APPS_STATUS}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="label {if $CONFIGAPPLICATION.app_status eq 'Activa'}label-success{else}label-danger{/if}">{$CONFIGAPPLICATION.app_status}</span>
						</div>
						<br><br>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CATEGORYAPPS_LABEL}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">

							{foreach key=keyC item=category from=$CATEGORIES}
								{if in_array($category.catappid,$CONFIGAPPLICATION.app_category)}{$category.name}<br> {/if}
							{/foreach}
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_ASIG_MODULES}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							{foreach item=module from=$CONFIGAPPLICATION.modules}
								{$module.tablabel}<br>
							{/foreach}

						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>





<div class="row">

	<div class="col-lg-12">

		<div class="main-box">
			<header class="title-section main-box-header clearfix">
				<h2>{$MOD.LBL_IMAGE_APPS}</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="col-md-6">
					<div class="input-group" style="width: 100%;">
						{if $CONFIGAPPLICATION.app_image eq 1}<img src="{$APPSIMAGE_PATH}{$CONFIGAPPLICATION.app_code}.png"> {/if}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>





<script>

</script>

