{strip}
<script type="text/html" id="entity-number-modal-template">
<div class="modal fade" id="entity-number-modal" tabindex="-1" role="dialog" aria-hidden="false">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="index.php" method="post" onsubmit="EntityNumberUtils.saveEntityNumber (this); return false;">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="SaveRegistrationNumber" />
				<input type="hidden" name="modulename" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Número de Registro</h4>
				</div>
				<div class="modal-body">
					<div class="form-group" style="margin-bottom: 5px;">
						<label for="entity-number-prefix">Prefijo</label>
						<input type="text" id="entity-number-prefix" name="prefix" class="form-control" />
					</div>
					<div class="form-group" style="margin-bottom: 5px;">
						<label for="entity-number-initial-sequence">Secuencia inicial</label>
						<input type="text" id="entity-number-initial-sequence" name="initialsequence" class="form-control" />
					</div>
					<div class="form-group" style="margin-bottom: 5px;">
						<label for="entity-number-current-sequence">Secuencia actual</label>
						<input type="text" id="entity-number-current-sequence" name="currentsequence" class="form-control" />
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>
</script>
{/strip}