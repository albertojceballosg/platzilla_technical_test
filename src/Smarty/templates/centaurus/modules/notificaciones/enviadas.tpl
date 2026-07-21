<div class="main-box-body clearfix">
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th><a href="#"><span>{$MOD.LBL_SUBJECT}</span></a></th>
					<th><a href="#"><span>{$MOD.LBL_ACCOUNT_NAME}</span></a></th>
					<th><a href="#"><span>{$MOD.LBL_CONTACTS}</span></a></th>
					<th><a href="#"><span>{$MOD.LBL_DATE}</span></a></th>
					<th><span>{$MOD.LBL_SEND_BY}</span></th>
					<th><span>{$MOD.LBL_ASSOCIATED_RECORD}</span></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			{foreach item=notificacion from=$NOTIFICACIONES}
				<tr>
					<td style="font-size: 1em;">
						{$notificacion.subject}
					</td>
					<td>
						{$notificacion.accountname}
					</td>
					<td>
						{$notificacion.contactlist}
					</td>
					<td>
						{$notificacion.date}
					</td>
					<td>
						{$notificacion.recibidos}
					</td>
					<td>
						{$notificacion.enlaceticket}
					</td>
					<td>
						{$notificacion.botonforma}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	{$PAGINACION}
</div>