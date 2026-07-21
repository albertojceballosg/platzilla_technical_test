{strip}
<script type="text/html" id="instances-data-sharing-syncs-modal-template">
<div id="instances-data-sharing-syncs-modal" class="modal fade instance-data-sharing-element" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Histórico de compartir</h4>
			</div>
			<div class="modal-body">
				<div id="sent-syncs" style="display: none;">
					<h4>Compartidos por tí o alguien de tu compañía</h4>
					<div class="table">
						<table class="table-responsive" style="width: 100%;">
							<thead>
							<tr>
								<th class="record-identifier-name"></th>
								<th>Quién lo compartió</th>
								<th>A quién se le compartió</th>
								<th>Bajo qué regla</th>
								<th style="width: 7em;"></th>
							</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
				<div id="received-syncs" style="display: none;">
					<h4>Compartidos por otros usuarios de Platzilla</h4>
					<div class="table">
						<table class="table-responsive" style="width: 100%;">
							<thead>
							<tr>
								<th class="record-identifier-name"></th>
								<th>Quién lo compartió</th>
								<th>A quién se le compartió</th>
								<th>Bajo qué regla</th>
								<th style="width: 7em;"></th>
							</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</script>
<script type="text/html" id="instances-data-sharing-syncs-modal-sync-template">
<tr>
	<td class="record-identifier-value" style="font-size: 1em;"></td>
	<td class="source-email-address" style="font-size: 1em;"></td>
	<td class="target-email-address" style="font-size: 1em;"></td>
	<td class="rule-name" style="font-size: 1em;"></td>
	<td class="actions">
		<form action="index.php" method="post" onsubmit="return DataSharingUtils.deleteSync ();">
			<input type="hidden" name="module" value="instancesdatasharing" />
			<input type="hidden" name="action" value="DeleteSync" />
			<input type="hidden" name="returnmodule" value="{$MODULE}" />
			<input type="hidden" name="returnaction" value="{$ACTION}" />
{if (isset ($RECORD))}
			<input type="hidden" name="returnid" value="{$RECORD}" />
{/if}
			<input type="hidden" name="record" value="" />
			<input type="hidden" name="Ajax" value="true" />
			<button type="submit" class="btn btn-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
		</form>
	</td>
</tr>
</script>
{/strip}