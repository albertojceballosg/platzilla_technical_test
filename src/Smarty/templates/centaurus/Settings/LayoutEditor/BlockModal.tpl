{strip}
{if (isset ($MODULE))}
	{assign var='blocks' value=$MODULE->getBlocks ()}
{else}
	{assign var='blocks' value=null}
{/if}
<script type="text/html" id="block-modal-template">
<div class="modal fade" id="block-modal" tabindex="-1" role="dialog" aria-hidden="false">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="index.php" method="post" onsubmit="BlockUtils.saveBlock (this); return false;">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="SaveBlock" />
				<input type="hidden" name="modulename" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Crear bloque</h4>
				</div>
				<div class="modal-body">
					<div class="form-group" style="margin-bottom: 5px;">
						<label for="block-label">Nombre</label>
						<input type="text" id="block-label" name="label" class="form-control" />
					</div>
					<div class="form-group" style="margin-bottom: 5px;">
						<label for="block-sequence">Posición</label>
						<select id="block-sequence" name="sequence" class="form-control">
{foreach $blocks as $block}
							<option value="{$block->getSequence ()}">Antes de {$block->getLabel ()}</option>
{/foreach}
							<option value="-1">(Último)</option>
						</select>
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