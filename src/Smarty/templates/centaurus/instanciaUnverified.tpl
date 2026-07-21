<div class="row">
	<div class="col-lg-12">		
		<div id="error-box">
			<i class="fa fa-warning fa-fw fa-lg"></i>
			<strong>Atención!</strong>
			Verifica tu cuenta
			<p>{$MENSAJE}</p>
			{if isset($estatus) && $estatus eq '0'}
				<p style="color: red; font-weight: bold">Código inválido</p>
			{/if}
			<p></p>
			<div class="row">				
				<div class="col-sm-4 col-sm-offset-4">
					<form class="form-inline" role="form" method="POST" action="index.php">					
						<input type="hidden" name="module" value="Settings">
						<input type="hidden" name="action" value="codeverification">						
						<input type="hidden" name="mode" value="codeverification">						
						<div class="form-group">					
							<input id="codigo" name="codigo" class="form-control" placeholder="Código" type="text">
						</div>
						<button class="btn btn-success" type="submit">Verificar</button>
					</form>
				</div>
			</div>				
			<br><br><br><br>
			<p>¿No te llegó el correo? Haz click <a href="index.php?action=codeverification&module=Settings&mode=resendemail">aquí</a> para enviarlo!</p>
		</div>		
	</div>
</div>

<script type="text/javascript">
	jQuery('#codigo').keyup(function(){ldelim}
	    this.value = this.value.toUpperCase();
	{rdelim});
</script>