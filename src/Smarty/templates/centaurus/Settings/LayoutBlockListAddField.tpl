{strip}
<style type="text/css">
{literal}
	a.customMnu {
		padding-left:     0;
		padding-top:      5px;
		padding-bottom:   5px;
		display:          block;
		background:       no-repeat left;
		width:            185px;
		color:            #000000;
		text-decoration:  none;
	}
	a.customMnuSelected {
		padding-left:     0;
		padding-top:      5px;
		padding-bottom:   5px;
		display:          block;
		width:            185px;
		background:       #0099FF no-repeat left;
		color:            #FFFFFF !important;
		text-decoration:  none;
	}
	.modal-size {
		width: 60% !important;
	}
	.box-inline {
		-moz-border-radius:    5px;
		-webkit-border-radius: 5px;
		background:    none repeat scroll 0 0 #f6f6f6;
		border-radius: 5px;
		border:        1px solid #CCCCCC;
		height:        150px;
		overflow-x:    hidden;
		overflow-y:    auto;
		padding:       10px;
		width:         275px;
	}
	#table-spaced th, #table-spaced td {
		padding: 4px !important;
	}
	.fa-ed {
		padding-right: 10px;
	}
	.md-overlay {
		background: rgba(0,0,0,0) !important;
	}
	.md-content {
		box-shadow: 0 5px 15px rgba(0,0,0,0.5); !important;
	}
	.calculated-list {
		max-height: 150px;
		overflow-y: auto;
		font-size: .8em
	}
	.calculated-list a {
		padding: 4px 6px !important;
		margin: 1px;
	}

{/literal}
</style>
<div class="md-modal md-effect-1 modal-size" id="addfield_{$entries.blockid}">
	<div class="md-content">
		<div class="modal-header">
			<h4 class="modal-title" id="labelDiv">{$MOD.LBL_ADD_FIELD}</h4>
		</div>
		<div class="modal-body">
			<input type="hidden" name="mode" id="cfedit_mode" value="add">
			<div class="table-responsive" style="overflow-y: visible;">
				<table style="width: 100%;">
				<tr>
					<td width="50%">
						<table>
							<tr>
								<td>{$APP.LBL_SELECT_FIELD_TYPE}</td>
							</tr>
							<tr>
								<td>
									<div name="cfcombo" id="cfcombo" class="box-inline">
										<table style="width: 100%;">
{foreach $FIELD_TYPE_OPTIONS as $index => $fieldType}
	{if (in_array ($fieldType.value, array (FieldInterface::UI_TYPE_CODE)))}{continue}{/if}
											<tr>
												<td align="left">
													<a id="field{$index}_{$entries.blockid}" href="javascript: void(0);" class="customMnu" style="width: 100%; text-decoration: none;" onclick="makeFieldSelected (this, {$fieldType.value}, {$entries.blockid});"><i class="fa {$fieldType.icon} fa-fw fa-ed" aria-hidden="true"></i>&nbsp;{$fieldType.text}</a>
												</td>
											</tr>
{/foreach}
										</table>
									</div>
								</td>
							</tr>
						</table>
					</td>
					<td width="50%">
						<table width="100%" border="0" id="table-spaced">
							<tr class="hidden" id="fieldname_{$entries.blockid}">
								<td class="dataLabel" nowrap="nowrap" align="right" width="30%" style="padding-right: 10px;">
									<b>{$MOD.LBL_NAME_CAMPO}</b>
								</td>
								<td align="left" width="70%">
									<input id="fldName_{$entries.blockid}" value="" type="text" maxlength="30" readonly="readonly" class="form-control" placeholder="" />
								</td>
							</tr>
							<tr style="margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" width="30%" style="padding-right: 10px;">
									<b>{$MOD.LBL_LABEL_CAMPO} </b>
								</td>
								<td align="left" width="70%">
									<input id="fldLabel_{$entries.blockid}" value="" type="text" OnKeyUp="labelCopyFieldValue('fldName_{$entries.blockid}',this.id);" class="form-control" maxlength="30" placeholder="" />
								</td>
							</tr>
							<tr id="lengthdetails_{$entries.blockid}" style="margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" style="padding-right: 10px;">
									<b>{$MOD.LBL_LENGTH}</b>
								</td>
								<td align="left">
									<input type="text" id="fldLength_{$entries.blockid}" value="" class="form-control" placeholder="" />
								</td>
							</tr>
							<tr id="uniquevalue_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" width="30%" style="padding-right: 10px;">
									<b>{$MOD.LBL_UNIQUE_VALUE} </b>
								</td>
								<td align="left" width="70%">
									<input id="flduniquevalue_{$entries.blockid}" type="checkbox" class="form-control" placeholder="" />
								</td>
							</tr>
							<tr id="decimaldetails_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td class="dataLabel_{$entries.blockid}" nowrap="nowrap" align="right" style="padding-right: 10px;">
									<b>{$MOD.LBL_DECIMAL_PLACES}</b>
								</td>
								<td align="left">
									<input type="text" id="fldDecimal_{$entries.blockid}" value="" class="form-control" placeholder="" />
								</td>
							</tr>
							<tr id="picklistdetails_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" valign="top" style="padding-right: 10px;">
									<b>{$MOD.LBL_PICK_LIST_VALUES}</b>
								</td>
								<td align="left" valign="top">
									<textarea id="fldPickList_{$entries.blockid}" rows="10" class="form-control" placeholder=""></textarea>
								</td>
							</tr>
							<tr id="relatedmodule_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" valign="top" style="padding-right: 10px;">
									<b>{$MOD.LBL_REFERENCIA_MODULO}</b>
								</td>
								<td align="left" valign="top">
									<select id="fldRelatedModule_{$entries.blockid}" class="form-control" title="">
{foreach $LISTMODULES as $module}
										<option value="{$module.value}">{$module.text}</option>
{/foreach}
									</select>
								</td>
							</tr>
							<tr id="relatedrecords_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" valign="top" style="padding-right: 10px;">
									<b>{$MOD.LBL_LISTADO_REGISTROS_MODULO}</b>
								</td>
								<td align="left" valign="top">
									<select id="fldRelatedRecords_{$entries.blockid}" class="form-control" title="">
{foreach $LISTMODULES as $module}
										<option value="{$module.value}">{$module.text}</option>
{/foreach}
									</select>
								</td>
							</tr>
							<tr id="global_picklists_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td class="dataLabel" nowrap="nowrap" align="right" valign="top" style="padding-right: 10px;">
									<b>Campo</b>
								</td>
								<td align="left" valign="top">
{if (!empty ($AVAILABLE_GLOBAL_PICKLISTS))}
									<select id="global_picklist_{$entries.blockid}" class="form-control" title="">
	{foreach $AVAILABLE_GLOBAL_PICKLISTS as $picklist}
										<option value="{$picklist->getName ()}">{$picklist->getLabel ()}</option>
	{/foreach}
									</select>
{/if}
								</td>
							</tr>
							<tr id="linkCalculated_{$entries.blockid}" style="display: none; margin-bottom: 5px;">
								<td colspan="2">
									<table class="table table-condensed">
										<thead>
										<tr>
											<th><input class="form-control input-sm search_Calculated" type="text" placeholder="Buscar cálculo" oninput="searchCalculated(this)"></th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<td>
												<input type="hidden" id="calculatedSystemId_{$entries.blockid}" name="calculatedSystemId_{$entries.blockid}" value="">
												<div class="list-group calculated-list">
{foreach $CALCULATED_SYSTEM as $cf}
													<a href="javascript: void(0);" rel="{$cf->getCalculationName ()}@{$entries.blockid}" title="{$cf->getDescription ()}" class="list-group-item" onclick="setCalculatedSystem(this)">{$cf->getName ()}</a>
{/foreach}
												</div>
												<p style="text-align: left">¿No encuentras el cálculo que necesitas? <a href="?module=calculated_fields&action=index&tab=system" title="Crear cálculo" target="_blank">Crea tu propio cálculo</a></p>
											</td>
										</tr>
										</tbody>
									</table>

								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary" name="save" onclick="getCreateCustomFieldForm ('{$MODULE}','{$entries.blockid}','add');">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
			<button class="btn btn-default md-close" id="btnclose" onclick="return false;">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
			<input type="hidden" name="fieldType_{$entries.blockid}" id="fieldType_{$entries.blockid}" value="" />
		</div>
	</div>
</div>
{/strip}