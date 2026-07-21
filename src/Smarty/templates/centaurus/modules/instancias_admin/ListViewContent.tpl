{strip}
<div id="instanciascontent">
	<div class="col-lg-12">
{include file='modules/instancias_admin/ListViewContentPaginator.tpl'}
{include file='modules/instancias_admin/search.tpl'}
	</div>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th class="text-center">Empresa</th>
			<th class="text-center">Código</th>
			<th class="text-center">Email de admin</th>
			<th class="text-center">Fecha de creación</th>
			<th class="text-center">Contacto</th>
			<th class="text-center">Estatus</th>
			<th class="text-center">Número de usuarios</th>
			<th class="text-center">Número de Apps</th>
			<th class="text-center">Acciones</th>
		</tr>
		</thead>
		<tbody>
{foreach name=instanciaN item=instancia from=$INSTANCIAS.data}
		<tr>
			<td>{$instancia.name}</td>
			<td>{$instancia.code}</td>
			<td class="text-center">{$instancia.usuario}</td>
			<td>{$instancia.inidatedemo}</td>
			<td>{$instancia.firstname} {$instancia.lastname}</td>
			<td class="text-center"><span class="{$instancia.colorLabel}">{$instancia.statusLabel}</span></td>
			<td class="text-center">
				<span class="{$instancia.colorLabel}">{$instancia.numusuariosactivos} / {$instancia.numusuarios} </span>
			</td>
			<td class="text-center">
				<span class="{$instancia.colorLabel}">{$instancia.activeappcount} / {$instancia.appcount} </span>
			</td>
			<td style="width: 10em;">
				<a class="btn btn-info btn-icon" title="{$MOD.LBL_VER}" href="index.php?module={$MODULE}&action=DetailViewInstancia&record={$instancia.instanciasid}&parenttab=Settings">
					<i class="fa fa-search"></i>
				</a>
				<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la instancia {$instancia.code}?');" style="display: inline;">
					<input type="hidden" name="module" value="instancias_admin" />
					<input type="hidden" name="action" value="instanciasDelete" />
					<input type="hidden" name="code" value="{$instancia.code}" />
					<input type="hidden" name="Ajax" value="true" />
					<button type="submit" class="btn btn-danger btn-icon" title="{$MOD.LBL_ELIMINAR}"><i class="fa fa-trash-o"></i></button>
				</form>
				<button type="button" class="btn btn-primary btn-icon" title="Cambiar total de usuarios" data-instance-code="{$instancia.code}" data-instance-total-users="{$instancia.numusuarios}" onclick="InstancesUtils.openModal (this);"><i class="fa fa-users"></i></button>
			</td>
		</tr>
{/foreach}
		</tbody>
	</table>
{include file='modules/instancias_admin/ListViewContentPaginator.tpl'}
</div>
{/strip}