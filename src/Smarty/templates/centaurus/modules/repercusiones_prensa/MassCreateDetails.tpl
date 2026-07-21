{strip}
<div class="panel">
	<div class="panel-heading" style="position: relative;">
		<a data-toggle="collapse" data-parent="#repercussions" href="#repercussion-{$ID}">
			<h4 class="panel-title">
				Repercusión: <span class="title"></span> - <span class="media"></span>
			</h4>
		</a>
{include file='modules/repercusiones_prensa/ActionsButton.tpl' CLASS='pull-right'}
	</div>
	<div id="repercussion-{$ID}" class="panel-collapse collapse in repercussion" data-id="{$ID}">
		<div class="row">
			<div class="col-md-1">
				<div class="label-input">
					<label for="title_{$ID}">Titular: <span class="required">*</span></label>
				</div>
			</div>
			<div class="form-group col-md-11 field-container">
				<div class="input-group" style="width: 100%;">
					<input type="text" id="title_{$ID}" name="titles[{$ID}]" class="form-control field-title" value="{$TITLE}" onchange="MassCreateUtils.updatePanelTitle (this);" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-1">
				<div class="label-input">
					<label for="related_{$ID}_display">Relacionado con: <span class="required">*</span></label>
				</div>
			</div>
			<div class="form-group col-md-11 field-container">
				<div class="input-group" style="width: 100%;">
					<input type="hidden" id="related_{$ID}" name="related[{$ID}]" value="{$RELATED}" class="for-filter field-related-id" />
					<input type="text" id="related_{$ID}_display" class="form-control placeholderStyle input-readonly b-right field-related" value="{$RELATED}" readonly="readonly" />
					<div class="input-group-addon" onclick="return window.open ('index.php?module=clientes_bdi&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=related_{$ID}&srcmodule=repercusiones_prensa', 'related', 'width=640,height=602,resizable=0,scrollbars=1,top=150,left=200');">
						<i class="fa fa-plus-circle"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-1">
				<div class="label-input">
					<label for="media_{$ID}_display">Medio: <span class="required">*</span></label>
				</div>
			</div>
			<div class="form-group col-md-5 field-container">
				<div class="input-group" style="width: 100%;">
					<input type="hidden" id="media_{$ID}" name="media[{$ID}]" value="{$MEDIA}" class="for-filter field-media-id" />
					<input type="text" id="media_{$ID}_display" class="form-control placeholderStyle input-readonly b-right field-media" value="{$MEDIA_NAME}" readonly="readonly" />
					<div class="input-group-addon" onclick="return window.open ('index.php?module=medios_bdi&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=media_{$ID}&srcmodule=repercusiones_prensa', 'media', 'width=640,height=602,resizable=0,scrollbars=1,top=150,left=200');">
						<i class="fa fa-plus-circle"></i>
					</div>
				</div>
			</div>
			<div class="col-md-1">
				<div class="label-input">
					<label for="date_{$ID}">Fecha: <span class="required">*</span></label>
				</div>
			</div>
			<div class="form-group col-md-5 field-container">
				<div class="input-group" style="width: 100%;">
					<div class="input-group-addon" style="border: 1px solid #ddd !important">
						<i class="fa fa-calendar"></i>
					</div>
					<input type="text" id="date_{$ID}" name="date[{$ID}]" class="form-control pull-right input-readonly b-left date-field" maxlength="18" value="{$DATE}" readonly="readonly" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-1">
				<div class="label-input">
					<label for="url_{$ID}">URL: <span class="required">*</span></label>
				</div>
			</div>
			<div class="form-group col-md-11 field-container">
				<div class="input-group" style="width: 100%;">
					<input type="text" id="url_{$ID}" name="urls[{$ID}]" class="form-control field-url" value="{$URL}" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="panel others">
				<div class="panel-heading clickable">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#others_{$ID}"> + Información adicional</a>
					</h4>
				</div>
				<div id="others_{$ID}" class="panel-collapse collapse">
					<div class="panel-body">
{foreach key=header item=data from=$BLOCKS name=block}
	{assign var="fromlink" value=""}
	{foreach key=label item=subdata from=$data}
		{foreach key=mainlabel item=maindata from=$subdata}
			{if (!in_array ($maindata[2][0], ['cod_repercusione', 'fecha', 'medio_donde_apar', 'relacionado_con', 'titular', 'url']))}
				{include file='modules/repercusiones_prensa/EditViewUI.tpl' ID=$ID}
			{/if}
		{/foreach}
	{/foreach}
{/foreach}
{if ($CAMPOS_PERSONALIZADOS) || ($CAMPOS_TIPO_GRID) || ($CAMPOS_TIPO_MATRIX)}
	{$CAMPOS_PERSONALIZADOS}
	<script type="text/javascript" src="include/js/gridFormValidate.js"></script>
	{$CAMPOS_TIPO_GRID}
	{$CAMPOS_TIPO_MATRIX}
{/if}
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 drop-zone">
				<input type="file" multiple="multiple" onchange="MassCreateUtils.addAttachments (event || window.event);" />
				<span class="title">Arrastra imágenes aquí o haz clic</span>
			</div>
			<ul class="col-md-12 attachments-container"></ul>
		</div>
		<div class="action-bar text-right">
{include file='modules/repercusiones_prensa/ActionsButton.tpl'}
		</div>
	</div>
</div>
{/strip}