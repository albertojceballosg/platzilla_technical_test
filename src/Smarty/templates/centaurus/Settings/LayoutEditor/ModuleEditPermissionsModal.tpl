{strip}
{if (isset ($MODULE))}
	{assign var='moduleName' value=$MODULE->getName ()}
{else}
	{assign var='moduleName' value=null}
{/if}
<script type="text/html" id="permissions-modal-template">
<div class="modal fade" id="permissions-modal" tabindex="-1" role="dialog" aria-hidden="false">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="index.php" method="post" onsubmit="return PermissionUtils.validateFilters (this);">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="action" value="ModuleEditPermissionSave" />
				<input type="hidden" name="Ajax" value="true" />
				<input type="hidden" name="formodule" value="{$moduleName}" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Permisologías</h4>
				</div>
				<div class="row modal-body filters-section">
					<div class="col-xs-12">
						<h4 class="pull-left">Los registros se bloquearan (no se podrán editar) sí se cumplen alguna de éstas condiciones:</h4>
						<div class="action-bar pull-right">
							<button type="button" class="btn btn-success btn-icon" onclick="PermissionUtils.addFilterGroup ();" title="Agregar grupo de filtros">
								<i class="fa fa-plus"></i></button>
						</div>
					</div>
					<div class="col-xs-12 filter-groups">
{if (!empty ($AVAILABLE_EDIT_PERMISSIONS))}
	{foreach $AVAILABLE_EDIT_PERMISSIONS as $group}
		{include file='Settings/LayoutEditor/ModuleEditPermissionConditionGroup.tpl' GROUP=$group}
	{/foreach}
{/if}
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
<script type="text/html" id="permissions-filter-template">
{include file='Settings/LayoutEditor/ModuleEditPermissionCondition.tpl'}
</script>
<script type="text/html" id="permissions-filter-group-template">
{include file='Settings/LayoutEditor/ModuleEditPermissionConditionGroup.tpl'}
</script>
{/strip}