<div class="main-box-body clearfix">
	<div class="table-responsive">		
		<table class="table" id="emails_conf">
			<thead>
				<tr>
					<th><span>{$MOD.LBL_NAME_MAIL}</span></th>
					<th><span>{$MOD.LBL_OPTION_MAIL}</span></th>
				</tr>
			</thead>
			<tbody>
			{foreach item=notificacion from=$NOTIFICACIONES}
				<tr>
					<td style="font-size: 1em;">
						{$notificacion.name}
					</td>
					<td>
						{$notificacion.checkstatus}
					</td>
				</tr>
			{/foreach}			
			</tbody>
		</table>
	</div>
</div>

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery.dataTables.js"></script>
<script src="themes/{$THEME}/js/dataTables.tableTools.js"></script>
<script src="themes/{$THEME}/js/jquery.dataTables.bootstrap.js"></script>

<script type="text/javascript">
	
</script>