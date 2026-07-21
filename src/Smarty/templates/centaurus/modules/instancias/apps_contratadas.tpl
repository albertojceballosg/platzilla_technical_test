

<div class="table-responsive">
	<table width="100%" class="table table-hover" cellpadding="5">
		<thead>
			<tr>
				<th>Aplicación</th>
				<th>Status</th>
				<th>Fecha de inicio de demo</th>
				<th>Fecha de inicio del Servicio</th>
				<th>Servicio Asociado</th>
			</tr>
		</thead>
		<tbody>
			{foreach key=label item=data from=$APPS_ASOCIADAS}
				<tr>
					<td>{$data.app_name}</td>
					<td>{$data.status}</td>
					<td>{$data.datedemo}</td>
					<td> {if $data.dateiniservice neq '0000-00-00'} {$data.dateiniservice}  {/if}</td>
					<td>{if $data.serviceid neq ''}  <a href="index.php?module=Services&action=DetailView&record={$data.serviceid}">{$data.service_no}  {$data.servicename}</a> {/if}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
