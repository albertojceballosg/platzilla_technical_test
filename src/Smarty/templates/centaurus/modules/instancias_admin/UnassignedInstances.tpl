{strip}
<div id="unassignedinstances">
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th class="text-center">Código</th>
			<th class="text-center" style="width: 8em;">Acciones</th>
		</tr>
		</thead>
		<tbody>
{foreach $UNASSIGNED_INSTANCES as $instance}
		<tr>
			<td>{$instance.code}</td>
			<td>
				<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la instancia {$instance.code}?');">
					<input type="hidden" name="module" value="instancias_admin" />
					<input type="hidden" name="action" value="instanciasDelete" />
					<input type="hidden" name="record" value="{$instance.instanciasid}" />
					<input type="hidden" name="Ajax" value="true" />
					<button type="submit" class="btn btn-danger btn-icon" title="{$MOD.LBL_ELIMINAR}"><i class="fa fa-trash-o"></i></button>
				</form>
			</td>
		</tr>
{/foreach}
		</tbody>
	</table>
	{include file='modules/instancias_admin/ListViewContentPaginator.tpl'}
</div>
{/strip}