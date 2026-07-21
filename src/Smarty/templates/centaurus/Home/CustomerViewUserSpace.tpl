{strip}
<div class="tab-pane fade in{if ($SELECTED_TAB == 'user-space')} active{/if}" id="user-space">
	<div class="main-box">
		<header class="main-box-header clearfix">
			<h2>Espacio ocupado</h2>
		</header>
		<div class="main-box-body clearfix">
			<div class="row">
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="instance_size">Instancia (MB)</label>
						</div>
					</div>
					<div class="form-group col-md-8 field-container">
						<div class="input-group" style="width: 100%;">
							<input type="text" id="instance_size" value="{$INSTANCESIZE}" class="form-control" disabled="disabled" />
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="bd_size">BD (MB)</label>
						</div>
					</div>
					<div class="form-group col-md-8 field-container">
						<div class="input-group" style="width: 100%;">
							<input type="text" id="bd_size" value="{$BDSIZE}" class="form-control" disabled="disabled" />
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="num_files">Número de archivos</label>
						</div>
					</div>
					<div class="form-group col-md-8 field-container">
						<div class="input-group" style="width: 100%;">
							<input type="text" id="num_files" value="{$NUMFILES}" class="form-control" disabled="disabled" />
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="num_folders">Número de directorios</label>
						</div>
					</div>
					<div class="form-group col-md-8 field-container">
						<div class="input-group" style="width: 100%;">
							<input type="text" id="num_folders" value="{$NUMFOLDERS}" class="form-control" disabled="disabled" />
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for="space_total">Total (MB)</label>
						</div>
					</div>
					<div class="form-group col-md-4 field-container">
						<div class="input-group" style="width: 100%;">
							<input type="text" id="space_total" value="{$SPACETOTAL}" class="form-control" disabled="disabled" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}