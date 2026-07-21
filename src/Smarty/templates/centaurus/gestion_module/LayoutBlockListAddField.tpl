{*
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ *}
<!-- for adding customfield -->
<style>
{literal}
a.customMnu {
    padding-left: 30px;
    padding-top: 5px;
    padding-bottom: 5px;
    display: block;
    background-repeat: no-repeat;
    background-position: left;
    width: 185px;
    color: #000000;
    text-decoration: none;
}
a.customMnuSelected {
    padding-left: 30px;
    padding-top: 5px;
    padding-bottom: 5px;
    display: block;
    background-repeat: no-repeat;
    background-position: left;
    width: 185px;
    background-color: #0099FF;
    color: #FFFFFF !important;
    text-decoration: none;
}
{/literal}
</style>
	<div class="md-modal md-effect-1" id="addfield_{$entries.blockid}">
		<div class="md-content">
			<div class="modal-header">
				<h4 class="modal-title" id="labelDiv">{$MOD.LBL_ADD_FIELD}</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="mode" id="cfedit_mode" value="add">
				<table>
					<tr>
						<td>
							<table>
								<tr>
									<td>{$APP.LBL_SELECT_FIELD_TYPE}
									</td>
								</tr>
								<tr>
									<td>
										<div name="cfcombo" id="cfcombo" style="width:205px; height:150px; overflow-y:auto ;overflow-x:hidden ;overflow:auto; border:1px  solid #CCCCCC ;">
											<table>
												<tr><td align="left"><a id="field0_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'text.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,0,{$entries.blockid});">  {$MOD.Text} </a></td></tr>
												<tr><td align="left"><a id="field1_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'number.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,1,{$entries.blockid})" >  {$MOD.Number} </a></td></tr>
												<tr><td align="left"><a id="field2_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'percent.gif'|@vtiger_imageurl:$THEME});" 	onclick = "makeFieldSelected(this,2,{$entries.blockid});">  {$MOD.Percent} </a></td></tr>
												<tr><td align="left"><a id="field3_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'cfcurrency.gif'|@vtiger_imageurl:$THEME});" 	onclick = "makeFieldSelected(this,3,{$entries.blockid});">  {$MOD.Currency} </a></td></tr>
												<tr><td align="left"><a id="field4_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'date.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,4,{$entries.blockid});">  {$MOD.Date} </a></td></tr>
												<tr><td align="left"><a id="field5_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'email.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,5,{$entries.blockid});">  {$MOD.Email} </a></td></tr>
												<tr><td align="left"><a id="field6_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'phone.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,6,{$entries.blockid});">  {$MOD.Phone} </a>	</td></tr>
												<tr><td align="left"><a id="field7_{$entries.blockid}" 	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'cfpicklist.gif'|@vtiger_imageurl:$THEME});" 	onclick = "makeFieldSelected(this,7,{$entries.blockid});">  {$MOD.PickList} </a></td></tr>
												<tr><td align="left"><a id="field8_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'url.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,8,{$entries.blockid});">  {$MOD.LBL_URL} </a></td></tr>
												<tr><td align="left"><a id="field9_{$entries.blockid}" 	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'checkbox.gif'|@vtiger_imageurl:$THEME});" 	onclick = "makeFieldSelected(this,9,{$entries.blockid});">  {$MOD.LBL_CHECK_BOX} </a></td></tr>
												<tr><td align="left"><a id="field10_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'text.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,10,{$entries.blockid});"> {$MOD.LBL_TEXT_AREA} </a></td></tr>
												<tr><td align="left"><a id="field11_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'cfpicklist.gif'|@vtiger_imageurl:$THEME});" 	onclick = "makeFieldSelected(this,11,{$entries.blockid});"> {$MOD.LBL_MULTISELECT_COMBO} </a></td></tr>
												<tr><td align="left"><a id="field12_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'skype.gif'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,12,{$entries.blockid});"> {$MOD.Skype} </a></td></tr>
												<tr><td align="left"><a id="field13_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'time.PNG'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,13,{$entries.blockid});"> {$MOD.Time} </a></td></tr>
												<tr><td align="left"><a id="field14_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'related.PNG'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,14,{$entries.blockid});"> {$MOD.LBL_REFERENCIA_MODULO} </a></td></tr>
												<tr><td align="left"><a id="field15_{$entries.blockid}"	href="javascript:void(0);" class="customMnu" style="text-decoration:none; background-image:url({'bl_bar.jpg'|@vtiger_imageurl:$THEME});" 		onclick = "makeFieldSelected(this,15,{$entries.blockid});"> {$MOD.LBL_PROGRESS_BAR_CONFIG} </a></td></tr>
											</table>
										</div>
									</td>
								</tr>
							</table>
						</td>
						<td width="50%">
							<table width="100%" border="0" cellpadding="5" cellspacing="0">
								<tr id="fieldname_{$entries.blockid}">
									<td class="dataLabel" nowrap="nowrap" align="right" width="30%" style="padding-right:10px"><b>{$MOD.LBL_NAME_CAMPO} </b>
									</td>
									<td align="left" width="70%">
									<input id="fldName_{$entries.blockid}"  value="" type="text" maxlength=\'16\' readonly="readonly" class="form-control">
									</td>
								</tr>
								<tr>
									<td class="dataLabel" nowrap="nowrap" align="right" width="30%" style="padding-right:10px"><b>{$MOD.LBL_LABEL_CAMPO} </b>
									</td>
									<td align="left" width="70%">
									<input id="fldLabel_{$entries.blockid}"  value="" type="text" OnKeyUp="labelCopyFieldValue('fldName_{$entries.blockid}',this.id);" class="form-control">
									</td>
								</tr>
								<tr id="lengthdetails_{$entries.blockid}">
									<td class="dataLabel" nowrap="nowrap" align="right" style="padding-right:10px"><b>{$MOD.LBL_LENGTH}</b>
									</td>
									<td align="left">
									<input type="text" id="fldLength_{$entries.blockid}" value="" class="form-control">
									</td>
								</tr>
								<tr id="uniquevalue_{$entries.blockid}">
									<td class="dataLabel" nowrap="nowrap" align="right" width="30%" style="padding-right:10px"><b>{$MOD.LBL_UNIQUE_VALUE} </b>
									</td>
									<td align="left" width="70%">
									<input id="flduniquevalue_{$entries.blockid}" type="checkbox" class="form-control">
									</td>
								</tr>
								<tr id="decimaldetails_{$entries.blockid}" style="display:none;">
									<td class="dataLabel_{$entries.blockid}" nowrap="nowrap" align="right" style="padding-right:10px"><b>{$MOD.LBL_DECIMAL_PLACES}</b>
									</td>
									<td align="left">
									<input type="text" id="fldDecimal_{$entries.blockid}" value=""  class="form-control">
									</td>
								</tr>
								<tr id="picklistdetails_{$entries.blockid}" style="display:none;">
									<td class="dataLabel" nowrap="nowrap" align="right" valign="top" style="padding-right:10px"><b>{$MOD.LBL_PICK_LIST_VALUES}</b>
									</td>
									<td align="left" valign="top">
									<textarea id="fldPickList_{$entries.blockid}" rows="10" class="form-control" ></textarea>
									</td>
								</tr>
								<tr id="relatedmodule_{$entries.blockid}" style="display:none;">
									<td class="dataLabel" nowrap="nowrap" align="right" valign="top"><b>{$MOD.LBL_REFERENCIA_MODULO}</b>
									</td>
									<td align="left" valign="top">
									<select id="fldRelatedModule_{$entries.blockid}" class="form-control">
									{$LISTMODULES}
									</select>
									</td>
								</tr>
								<tr id="progressbar_min_{$entries.blockid}" style="display:none;">
									<td class="dataLabel_{$entries.blockid}" nowrap="nowrap" align="right" style="padding-right:10px"><b>{$MOD.LBL_PROGRESS_BAR_MIN}</b>
									</td>
									<td align="left">
									<input type="text" id="fldProgressMin_{$entries.blockid}" value=""  class="form-control">
									</td>
								</tr>
								<tr id="progressbar_max_{$entries.blockid}" style="display:none;">
									<td class="dataLabel_{$entries.blockid}" nowrap="nowrap" align="right" style="padding-right:10px"><b>{$MOD.LBL_PROGRESS_BAR_MAX}</b>
									</td>
									<td align="left">
									<input type="text" id="fldProgressMax_{$entries.blockid}" value=""  class="form-control">
									</td>
								</tr>
								<tr id="progressbar_ini_{$entries.blockid}" style="display:none;">
									<td class="dataLabel_{$entries.blockid}" nowrap="nowrap" align="right" style="padding-right:10px"><b>{$MOD.LBL_PROGRESS_BAR_INI}</b>
									</td>
									<td align="left">
									<input type="text" id="fldProgressIni_{$entries.blockid}" value=""  class="form-control">
									</td>
								</tr>
								<tr id="progressbar_ord_{$entries.blockid}" style="display:none;">
									<td class="dataLabel_{$entries.blockid}" nowrap="nowrap" align="right" style="padding-right:10px"><b>{$MOD.LBL_PROGRESS_BAR_ORD}</b>
									</td>
									<td align="left">
									<select id="fldProgressOrd_{$entries.blockid}" class="form-control">
										<option value="asc">ASC</option><option value="desc">DESC</option>
									</select>
									</td>
								</tr>

							</table>
						</td>
					</tr>
				</table>

			</div>
			<div class="modal-footer">
			<input type="button" name="save" value=" {$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-primary"  onclick = "getCreateCustomFieldForm('{$MODULE}','{$entries.blockid}','add');"/>&nbsp;
			<button class="btn btn-danger md-close" onclick="return false;" id="btnclose">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
			<input type="hidden" name="fieldType_{$entries.blockid}" id="fieldType_{$entries.blockid}" value="">
			<input type="hidden" name="selectedfieldtype_{$entries.blockid}" id="selectedfieldtype_{$entries.blockid}" value="">
			</div>
		</div>
	</div>


<!-- end custom field -->