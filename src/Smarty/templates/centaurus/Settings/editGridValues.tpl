	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
	<input type="hidden" name="fieldid" value="{$FIELDID}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="md-content" style="font-size:90%">
		<div class="modal-header">
			<h4 class="modal-title" id="labelDiv">{$MOD.LBL_DEFINICION_VALORES_DEFECTO}</h4>
		</div>
		<div class="modal-body" style="max-height:320px;overflow: auto;">
			<div class="row">
				<div class="col-md-12" id="results"></div>
				<div class="col-md-12">
					<div class="main-box">
						<header class="title-section main-box-header clearfix">
							<h2 class="pull-left">{$fieldlabel}</h2>
						</header>
						<div class="main-box-body clearfix">
							<div class="table-responsive">
								<table id="{$fieldname}" class="table table-bordered tablegridvalidate">
									<thead>
									<tr valign="top" >
                                        {foreach from=$lstSubCampos key=k item=v}
                                            {if $v.uitype neq 99 }
												<td class="">{$v.label}
												</td>
                                            {else}
												<td width="6%" class="">{if ! $swDetailView }Eliminar{else}&nbsp;{/if}</td>
                                            {/if}
                                        {/foreach}
									</tr>
									</thead>
									<tbody rowtotal="0">
                                    {assign var="keyValue" value=0}
									<tr numrowtr="{$keyValue}" id="row_{$fieldname}_{$keyValue}"  class="gridvalidationtr">
                                            {foreach from=$lstSubCampos key=k item=v}
                                                {assign var="fieldValue" value={$v.defaultvalue}}
                                                {include file='Settings/GridContenet.tpl'}
                                            {/foreach}
									</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary"  type="button"  id="saveValues">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
			<button class="btn btn-danger md-close" type="button"  id="btnclose" onclick="AddGridFieldsUtils.closeModal ();" >{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
		</div>
	</div>
<script language="JavaScript">
        jQuery('#saveValues').click(function (e) {
            principalAction = jQuery('#action').val();
            jQuery('#action').val('SettingsAjax');
            jQuery.ajax({
                url: 'index.php?module=Settings&action=SettingsAjax&file=guardarValoresCamposGrid&parenttab=Settings&ajax=true',
                data: jQuery('#editGridValues').serialize(),
                method: "POST",
                success: function (data) {
                    jQuery('#action').val(principalAction);
					jQuery('#results').html(data);

                }
            });
            e.preventDefault();
        })
</script>