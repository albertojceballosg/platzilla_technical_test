
	<form method="post" action="index.php" name="editGridValues" onsubmit="return false;">
	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
	<input type="hidden" name="fieldid" value="{$FIELDID}" />
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="md-content" style="font-size:90%">
		<div class="modal-header">
			<h4 class="modal-title" id="labelDiv">{$MOD.LBL_DEFINICION_VALORES_DEFECTO}</h4>
		</div>
		<div class="modal-body" style="max-height:320px;overflow: auto;">
			{$VALUES_GRID_FIELD}
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary" onclick="irPaso(document.editGridValues,'guardarValoresCamposGrid');">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
			<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#modal').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
		</div>	
	</div>
	</form>