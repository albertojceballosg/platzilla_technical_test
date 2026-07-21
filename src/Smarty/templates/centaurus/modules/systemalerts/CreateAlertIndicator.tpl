{strip}
{math equation= rand() assign= "idSystemAlert"}
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header" style="text-align:center">
            {* close button *}
			<button class="close" aria-hidden="true" data-dismiss="modal" type="button" onclick="jQuery ('#createAlert').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});jQuery ('#createAlert').html(''); return false;">×</button>
			<h4 class="modal-title">
				<span id="titleBlock" style="color: black;">{if $MODE == 'create'}{$MODSTRING.LBL_CREATE_ALERT}{else}{$MODSTRING.LBL_EDIT_ALERT}{/if}</span>
			</h4>
		</div>
		<div class="modal-body">
    {*$DETAIL_ALERT.source_alert|var_dump*}
			<label><span style="color:red">*</span>&nbsp; Todos los campos son obligatorios.</label>
			<form id="system-alert-{$idSystemAlert}" role="form">
				<input type="hidden" id="module" name="module" value="systemalerts">
				<input type="hidden" id="flmodule" name="elementName" value="{$DETAIL_ALERT.tab_name}">
				<input type="hidden" id="flmodulelabel" name="elementLabel" value="{$DETAIL_ALERT.tab_label}">
				<input type="hidden" id="action" name="action" value="SaveAlert">
				<input type="hidden" id="systemAlertId" name="systemAlertId" value="{$DETAIL_ALERT.systemalerts_id}">
				<input type="hidden" id="app" name="app" value="{$TAB_ACTIVE}">
				<input type="hidden" id="mode" name="mode" value="{$MODE}">
				<input type="hidden" id="systemAlertIdRel" name="systemAlertIdRel" value="{$DETAIL_ALERT.systemalerts_id_rel}">
				<input type="hidden" name="Ajax" value="true">
				{* app select content *}
				<div class="form-group" id="appAlertNew" {if $MODE == 'edit' || $TAB_ACTIVE != 'all'} style="display: none;"{/if}>
					<label for="colorbase">{$MODSTRING.APP}</label>
					<select class="form-control" id="codeApp" name="codeApp"
							onchange="SystemAlertUtils.selectApp(this)"
							title="{$MODSTRING.APP}">
						<option value="">{$MODSTRING.LBL_SELECTION_APP}</option>
						{foreach $APPLICATIONS as $keyApp => $itemApp}
							{if $keyApp == $DETAIL_ALERT.code_app}
								{assign var='selected' value='selected="selected"'}
							{else}
								{assign var='selected' value=''}
							{/if}
							{if $itemApp.app_name != $LABEL_ALL_APLICATIONS}
								<option value="{$keyApp}" {$selected}>{$itemApp}</option>
							{/if}
						{/foreach}
					</select>
				</div>
                {* Alert name or title. *}
				<div class="form-group">
					<label for="colorbase">{$MODSTRING.LBL_ALERT_TITLE}</label>
					<input type="text" name="titleAlert" id="titleAlert" class="form-control" value="{$DETAIL_ALERT.alert}">
				</div>
                {* app type source element content *}
				<div class="form-group" id="appAlertType">
					<label for="colorbase">{$MODSTRING.LBL_ALERT_TYPE}</label>
					<select class="form-control" id="codetype" name="codetype" title="{$MODSTRING.LBL_ALERT_TYPE}"
							onchange="SystemAlertUtils.selectAlertType('{$MODE}', '{$DETAIL_ALERT.indicator_id}', '{$DETAIL_ALERT.tab_id}', '{$DETAIL_ALERT.condition_alert}')">
						{if $MODE != 'edit'}
							<option value="" >{$MODSTRING.LBL_SELECTION}</option>
						{/if}
						<option value="Indicators" {if $DETAIL_ALERT.source_alert == 'Indicators'}selected="selected"{/if}>{$MODSTRING.LBL_OPTION_INDICATORS}</option>
						<option value="Task_object_no_cump" {if $DETAIL_ALERT.source_alert == 'Task_object_no_cump'}selected="selected"{/if}>{$MODSTRING.LBL_ALERT_MODULE}</option>
						<option value="Task_prog" {if $DETAIL_ALERT.source_alert == 'Task_prog'}selected="selected"{/if}>{$MODSTRING.LBL_OPTION_TASK_PROG}</option>
						{*<option value="Task_no_ejec" {if $DETAIL_ALERT.source_alert == 'Task_no_ejec'}selected="selected"{/if}>{$MODSTRING.LBL_OPTION_TASK_NO_EJEC}</option>*}
					</select>
				</div>
                {* period of alert evaluaton *}
				<div class="form-group" id="periodAlert" style="display:{if $DETAIL_ALERT.source_alert eq 'Task_object_no_cump'}block{else} none{/if};">
					<label for="colorbase">{$MODSTRING.LBL_ALERT_PERIODICITY}</label>
					<select class="form-control" id="scale" name="scale" title="{$MODSTRING.LBL_ALERT_PERIODICITY}">
						<option value="">{$MODSTRING.LBL_SELECTION_PERIODICITY}</option>
						<option value="Month" {if $DETAIL_ALERT.scale == 'Month'}selected="selected"{/if}>{$MODSTRING.LBL_VIEW_MONTH}</option>
						<option value="Week" {if $DETAIL_ALERT.scale == 'Week'}selected="selected"{/if}>{$MODSTRING.LBL_VIEW_WEEK}</option>
					</select>
				</div>
                {* Element type for alert *}
				<div class="form-group" id="appAlertElement">
					<label id="codeElement-title" for="colorbase">{if $DETAIL_ALERT.source_alert neq NULL} {$MODSTRING[$DETAIL_ALERT.source_alert]}{else} {$MODSTRING.LBL_ALERT_ENTITY}{/if}</label>
					<select class="form-control" id="codeElement" name="codeElement" title="{$MODSTRING.LBL_ALERT_ENTITY}"
							onchange="SystemAlertUtils.selectElement(this, '0', '{$MODE}', '{$DETAIL_ALERT.field_id}', '{$DETAIL_ALERT.condition_alert}', '{$DETAIL_ALERT.tab_name}')">
						<option value="">{$MODSTRING.LBL_SELECTION}</option>
						{if $DETAIL_ALERT.source_alert neq NULL}
                        	{if $DETAIL_ALERT.source_alert eq 'Task_object_no_cump'}
                                {foreach $DETAIL_ALERT.element as $module}
									<option value="{$module['tabid']}" tabname="{$module['name']}" tablabel="{$module['tablabel']}"
                                            {if $module['tabid'] eq $DETAIL_ALERT.tab_id}
												selected="selected"
                                            {/if}
									>{$module['tablabel']}</option>
                                {/foreach}
							{elseif $DETAIL_ALERT.source_alert eq 'Indicators'}
                                {foreach $DETAIL_ALERT.element as $indicator}
									<option value="{$indicator['box_score_dataid']}" datarel="{$indicator['datarel']}"
											scale="{$indicator['scale']}" bxdatarel="{$indicator['bxdatarel']}" scaledatarel="{$indicator['scaledatarel']}"
                                            {if $indicator['box_score_dataid'] eq $DETAIL_ALERT.indicator_id}
												selected="selected"
                                            {/if}
									>{$indicator['box_score']}</option>
                                {/foreach}
                        	{/if}

						{/if}
					</select>
				</div>
                {* diff elemnt for alert *}
				<div class="form-group" id="appAlertElementField" {if $AVAIABLE_FIELD eq NULL}style="display: none;"{/if}>
					<label id="codeElementField-title" for="codeElementField">{$MODSTRING.LBL_ALERT_ENTITY_FIELD}</label>
					<select class="form-control" id="codeElementField" name="fieldName" title="{$MODSTRING.LBL_ALERT_ENTITY_FIELD}"
							onchange="SystemAlertUtils.selectElementField(this, '0','{$MODE}', '{$DETAIL_ALERT.field_id}', '{$DETAIL_ALERT.condition_alert}')">
						<option value="">{$MODSTRING.LBL_SELECTION}</option>
						{if $AVAIABLE_FIELD neq NULL}
                            {foreach   $AVAIABLE_FIELD as $field}
								<option value="{$field['fieldName']}" data-type="{$field['fieldType']}" data-uitype="{$field['uiType']}"
                                        {if $field['fieldName'] eq $DETAIL_ALERT.field_name}
											selected="selected"
                                        {/if}
								>{$field['fieldLabel']}</option>
                            {/foreach}
						{/if}
					</select>
				</div>
                {* Condition for evaluation *}
				<div class="form-group" id="appAlertCondition">
					<label for="colorbase">{$MODSTRING.LBL_ALERT_OPERATOR}</label>
					<select class="form-control operator" id="codeElementOperator" name="codeElementOperator" title="{$MODSTRING.LBL_ALERT_OPERATOR}">
						<option value="">{$MODSTRING.LBL_SELECTION}</option>
                        {if  $DETAIL_ALERT.source_alert eq 'Indicators'}
						<option value="less-equal" {if $DETAIL_ALERT.condition_alert == 'less-equal'}selected="selected"{/if}><=</option>
						<option value="greater-equal" {if $DETAIL_ALERT.condition_alert == 'greater-equal'}selected="selected"{/if}>>=</option>
                       {else}
							{if $DETAIL_ALERT.operator neq NULL}
								{foreach $DETAIL_ALERT.operator as $key => $value}
									<option value="{$key}"
											{if {$key} eq $DETAIL_ALERT.condition_alert}
												selected="selected"
											{/if}
									>{$MODSTRING[$value]}</option>
								{/foreach}
							{/if}
						{/if}
					</select>
				</div>
				<div class="form-group">
					<label for="colorbase">{$MODSTRING.LBL_ALERT_ENTITY_VALUE}</label>
					<input type="text" name="codeElementValue" id="codeElementValue" class="form-control {if $PICK_LIST_VALUES neq NULL} hide{/if}" {if $PICK_LIST_VALUES neq NULL}disabled{/if} value="{if $PICK_LIST_VALUES eq NULL}{$DETAIL_ALERT.value_alert}{/if}">
					<select class="form-control operator {if $PICK_LIST_VALUES eq NULL} hide{/if}" name="codeElementValue" id="codeElementValue-select" {if $PICK_LIST_VALUES eq NULL}disabled{/if}>
					{$PICK_LIST_VALUES}
					</select>
				</div>

			</form>
		</div>
		<div class="modal-footer">
            {* close button *}
			<button class="btn btn-default" data-dismiss="modal" type="button" onclick="jQuery ('#createAlert').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});jQuery ('#createAlert').html(''); return false;">{$MODSTRING.LBL_CLOSE}</button>
            {* save button*}
			<button class="btn btn-primary" type="button" id="saveAlert" onclick="SystemAlertUtils.saveAlerts('{$idSystemAlert}')">{$MODSTRING.LBL_SAVE}</button>
		</div>
	</div>
</div>
{/strip}

