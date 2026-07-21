<style>
{literal}


{/literal}
</style>
	<script type="text/javascript" src="include/jquery/jquery.dimScreen.js"></script>
	<script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>


<div class="md-modal md-effect-2" id="atribuyePedidoUI">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close">&times;</button>
			<h4 class="modal-title">Atribuir Ticket</h4>
		</div>
		<div class="modal-body">
		
			<table border="0" cellspacing="0" cellpadding="5" width="95%" align="center" bgcolor="#FFFFFF">
				<tbody>
					<tr>
						<td class="small">
							<div style="text-align:left;">
								<b><span style="width:150px; display:inline-block">Código</span></b>
								<span style="width:150px; display:inline-block" id="hdcodigo"></span>
							</div>
							<!--br /-->
							<div style="text-align:left;display:none;">
								<b><span style="width:150px; display:inline-block">Descripción</span></b>
								<span style="width:150px; display:inline-block" id="hddescripcion"></span>
							</div>
							<!--br /-->
							<div style="text-align:left;display:none;">
								<b><span style="width:150px; display:inline-block">Solicitante</span></b>
								<span style="width:150px; display:inline-block" id="hdutilizador"></span>
							</div>
							<!--br /-->
							<div style="text-align:left;display:none;">
								<b><span style="width:150px; display:inline-block">Fecha de creación</span></b>
								<span style="width:150px; display:inline-block" id="hdfecha"></span>
							</div>
							
							<hr />
							<div style="text-align:left;">
								<b><span style="width:150px; display:inline-block">A Técnico</span></b>
								<select id="tecnico">
									{foreach item=tecnico key=tecnicoid from=$TECNICOS}
									<option value="{$tecnicoid}">
										{$tecnico}
									</option>
									{/foreach}
								</select>
								<br />
							</div>
							<div style="text-align:left;">
								<b><span style="width:150px; display:inline-block">Comentarios</span></b>
								<textarea id="comentarios"></textarea>
								<br />
							</div>
							<input type="hidden" id="recordid2" name="recordid" value="" />
							<br />
						</td>
						
					</tr>
				</tbody>
			</table>
			
		</div>
		<div class="modal-footer">
			{*<input onclick="atribuyePedido();" alt="" title="Enviar" accesskey="S" type="button" name="enviar" class="crm button small save" style="width:110px" value="Atribuir" id="btnEnviar2">*}
			<button class="btn btn-warning" onclick="atribuyePedido();" type="button" id="btnAtribuir">Atribuir</button>
			{*<input alt="" title="" type="button" class="crm button small cancel" style="width:110px" name="eventcancel" value="Descartar" onclick="cierraAtribuyePedidoUI(); return false;">*}
			<p id="enviando2" style="display:none"><img src="themes/images/ajax-loader.gif"> Atribuyendo ticket...</p>
		</div>
	</div>
</div>


<div class="md-overlay"></div>

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>

<script>
{literal}
jQuery(document).ready(function() {
	var theEventHandle = document.getElementById("moveEvent2");
	var theEventRoot   = document.getElementById("atribuyePedidoUI");
	//Drag.init(theEventHandle, theEventRoot);
});
{/literal}
</script>