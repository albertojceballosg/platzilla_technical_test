<!--*********************************************************************************
<!--*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->
{$DATE_JS}
<script type="text/javascript" src="modules/CustomView/CustomView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/calculator/calc.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/advancefilter.js"></script>
{if $JS_DATEFORMAT eq ''}
	{assign var="JS_DATEFORMAT" value=$APP.NTC_DATE_FORMAT|@parse_calendardate}
{/if}
<input type="hidden" id="jscal_dateformat" name="jscal_dateformat" value="{$JS_DATEFORMAT}" />
<input type="hidden" id="image_path" name="image_path" value="{$IMAGE_PATH}" />

<script language="javascript" type="text/javascript">
function goto_CustomAction(module)
{ldelim}
        document.location.href = "index.php?module="+module+"&action=CustomAction&record={$CUSTOMVIEWID}";
{rdelim}

function mandatoryCheck()
{ldelim}

        var mandatorycheck = false;
        var i,j;
        var manCheck = new Array({$MANDATORYCHECK});
        var showvalues = "{$SHOWVALUES}";
        if(manCheck)
        {ldelim}
                var isError = false;
                var errorMessage = "";
                if (trim(document.CustomView.viewName.value) == "") {ldelim}
                        isError = true;
                        errorMessage += "\n{$MOD.LBL_VIEW_NAME}";
                {rdelim}
                // Here we decide whether to submit the form.
                if (isError == true) {ldelim}
                        alert("{$MOD.Missing_required_fields}:" + errorMessage);
                        return false;
                {rdelim}

		for(i=1;i<=9;i++)
                {ldelim}
                        var columnvalue = document.getElementById("column"+i).value;
                        if(columnvalue != null)
                        {ldelim}
                                for(j=0;j<manCheck.length;j++)
                                {ldelim}
                                        if(columnvalue == manCheck[j])
                                        {ldelim}
                                                mandatorycheck = true;
                                        {rdelim}
                                {rdelim}
                                if(mandatorycheck == true)
                                {ldelim}
					if(($("jscal_field_date_start").value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0) || ($("jscal_field_date_end").value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0))
						return stdfilterdateValidate();
					else
						return true;
                                {rdelim}else
                                {ldelim}
                                        mandatorycheck = false;
                                {rdelim}
                        {rdelim}
                {rdelim}
        {rdelim}
        if(mandatorycheck == false)
        {ldelim}
                alert("{$APP.MUSTHAVE_ONE_REQUIREDFIELD}"+showvalues);
        {rdelim}

        return false;
{rdelim}
{literal}
function wloadFields(c_index,val){
	jQuery('.widgetstatus').show();
	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: { module: 'Settings', action: 'createwidget', function: 'getColumns', Ajax: 'true', fld_module: val }
	}).done(function( html ) {
		jQuery( '#fieldop'+c_index ).html( html );
		jQuery('.widgetstatus').hide();
	});
}
{/literal}
</script>

<div  class="row">
	<div  class="col-lg-12">
		<div class="main-box-body clearfix">
			<div id="wSmall">
				<div class="clearfix">
					<div style="display:none;" class="wobjs-shhd" id="waid1-shhd">
						<div class="col-lg-5 col-md-8 col-sm-12 col-xs-12">
							<div class="main-box infographic-box merged wgraphics" id="waid1">
								<i class="fa fa-eye yellow-bg" id="waid1-ico"></i>
								<span class="value yellow" id="waid1-val">[$W_VAL]</span>
								<span class="headline" id="waid1-lbl">
									Nivel 1
								</span>
							</div>
						</div>
					</div>
					<div style="display:none;" class="wobjs-shhd" id="waid2-shhd">
						<div class="col-md-4 col-sm-6 col-xs-12">
							<div class="main-box small-graph-box red-bg wgraphics" id="waid2">
								<span class="value" id="waid2-val">[$W_VAL]</span>
								<span class="headline" id="waid2-lbl">Nivel 1</span>
								<div class="progress">
									<div style="width: [$W_VAL]%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="[$W_VAL]" role="progressbar" class="progress-bar">
										<span class="sr-only">[$W_VAL]% Completo</span>
									</div>
								</div>
								<span class="subinfo">
									<i class="fa fa-arrow-circle-o-up" id="waid2-ico"></i> <span id="waid2-lbl2">10% Nivel 2</span>
								</span>
							</div>
						</div>
					</div>
					<div style="display:none;" class="wobjs-shhd" id="waid3-shhd">
						<div class="col-lg-3 col-sm-6 col-xs-12">
							<div class="main-box infographic-box wgraphics" id="waid3">
								<i class="fa fa-user red-bg" id="waid3-ico"></i>
								<span class="headline" id="waid3-lbl">Nivel 1</span>
								<span class="value">
									<span class="timer" data-from="0" data-to="[$W_VAL]" data-speed="1000" data-refresh-interval="50">[$W_VAL]</span>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="wtwid" class="col-lg-12" style="display:none;">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
			  <table class="table">

				<tr>
					<td>Ubicaci&oacute;n del widget</td>
					<td>
						<select class="form-control" id="wgraphtabname" name="wgraphtabname" >
							{foreach item=opts from=$MOD_DASH}
								<option value="{$opts.tabname}">{$opts.label}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td>Modulos</td>
					<td>{$LISTAMODULOS_simple}</td>
				</tr>
				<tr>
					<td>{$MOD.LBL_FIELD_COLUMN}</td>
					<td>
						<select class="form-control" id="fieldop" name="fieldop[0]">
							<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Tipo de C&aacute;lculo</td>
					<td>
						<select name="opcolumn[0]" id="opcolumn" class="form-control">
						{$OPERATIONS}
						</select>
					</td>
				</tr>
				<tr>
					<td>
						Etiqueta Nivel 1
					</td>
					<td>
						<input name="label1" id="label1" class="form-control" type="text" value="{$LABEL}" placeholder="Nivel 1" onkeyup="updateLable('',this.value);">
					</td>
				</tr>
				<tr>
					<td>
						Etiqueta Nivel 2
					</td>
					<td>
						<input name="label2" id="label2" class="form-control" type="text" value="{$LABEL}" placeholder="Nivel 2" onkeyup="updateLable(2,this.value);">
					</td>
				</tr>
				<tr>
					<td colspan="2">Condiciones</td>
				</tr>
				<tr>
					<td colspan="2">
						<div id="mnuTab1" >
							{assign var="COL_INDEX" value='0'}
							{include file='Settings/AdvanceFilterWidget.tpl' SOURCE='customview'}
						</div>
					</td>
				</tr>
			</table>


			</div>
		</div>
	</div>
	<div id="wGraph" class="col-lg-12" style="display:none;">
		<header class="main-box-header clearfix">
			<div class="form-group col-lg-4 pull-left">
				<label for="graphlabel">T&iacute;tulo gr&aacute;fico</label>
				<input type="text" class="form-control" id="graphlabel" name="graphlabel" placeholder="Ingrese el t&iacute;tulo del gr&aacute;fico">
			</div>
			<div class="form-group col-lg-3 pull-left">
				<label for="graphtabname">Ubicaci&oacute;n del widget</label>
				<select class="form-control" id="graphtabname" name="graphtabname" >
					{foreach item=opts from=$MOD_DASH}
						<option value="{$opts.tabname}">{$opts.label}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-lg-3 icon-box pull-right">
				<input class="btn btn-success btn-sm" onclick="agregarColumna();" type="button" value="Agregar Columna">
			</div>
		</header>
		<div class="main-box clearfix" id="inner-wGraph">
			{assign var="COL_INDEX" value='1'}
			{include file='Settings/widgetColumnPropertiesGCol.tpl' SOURCE='customview'}
		</div>
	</div>
</div>

{$STDFILTER_JAVASCRIPT}
{$JAVASCRIPT}
<!-- to show the mandatory fields while creating new customview -->
<script language="javascript" type="text/javascript">

var k;
var colOpts;
var manCheck = new Array({$MANDATORYCHECK});
var col_index={$COL_INDEX};
{literal}

function agregarColumna(){
	jQuery('.widgetstatus').show();
	col_index++;
	row_index[col_index]=0;
	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: { module: 'Settings', action: 'createwidget', function: 'addColumns', Ajax: 'true', col_index: col_index }
	}).done(function( html ) {
		jQuery('.widgetstatus').hide();
		jQuery( '#inner-wGraph' ).append( html );
	});
}
var row_index=[];
function addConditionRowNew(col){
	jQuery('.widgetstatus').show();
	if(col!='0')
		var fld_module= jQuery('#wmodulo'+col).val();
	else
		var fld_module= jQuery('#wmodulo').val();

	if(fld_module=='' || fld_module=='-'){
		alert('Debe seleccionar un modulo!');
		return false;
	}
	if(!row_index[col])
		row_index[col]=0;


	if(row_index[col]>=1){
		jQuery('#fcon_'+col+'_'+row_index[col]).show();
	}
	row_index[col]++;

	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: { module: 'Settings', action: 'createwidget', function: 'addConditionRow', Ajax: 'true', col_index: col, row_index: row_index[col],fld_module:fld_module }
	}).done(function( html ) {
		jQuery('.widgetstatus').hide();
		jQuery( '#conditiongrouptable_'+col ).append( html );
	});
}

function updateLable(l,val){
	jQuery('#waid'+widget_type+'-lbl'+l).html(val);
}

if(document.CustomView.record.value == '') {
	for(k=0;k<manCheck.length;k++) {
		selname = "column"+(k+1);
		selelement = document.getElementById(selname);
		if(selelement == null || typeof selelement == 'undefined') continue;
		colOpts = selelement.options;
		for (l=0;l<colOpts.length;l++) {
			if(colOpts[l].value == manCheck[k]) {
				colOpts[l].selected = true;
			}
		}
	}
}

function validateCV() {
	if(checkDuplicate()) {
		return checkAdvancedFilter();
	}
	return false;
}

function checkDuplicate() {
	if(getObj('viewName').value.toLowerCase() == 'all') {
		alert(alert_arr.ALL_FILTER_CREATION_DENIED);
		return false;
	}
	var cvselect_array = new Array('column1','column2','column3','column4','column5','column6','column7','column8','column9')
	for(var loop=0;loop < cvselect_array.length-1;loop++) {
		selected_cv_columnvalue = $(cvselect_array[loop]).options[$(cvselect_array[loop]).selectedIndex].value;
		if(selected_cv_columnvalue != '') {
			for(var iloop=loop+1;iloop < cvselect_array.length;iloop++) {
				selected_cv_icolumnvalue = $(cvselect_array[iloop]).options[$(cvselect_array[iloop]).selectedIndex].value;
				if(selected_cv_columnvalue == selected_cv_icolumnvalue) {
					{/literal}
                        alert('{$APP.COLUMNS_CANNOT_BE_DUPLICATED}');
                        $(cvselect_array[iloop]).selectedIndex = 0;
                        return false;
					{literal}
				}

			}
		}
	}
	return true;
}

function stdfilterdateValidate()
{
	if(!dateValidate("startdate",alert_arr.STDFILTER+" - "+alert_arr.STARTDATE,"OTH"))
	{
		getObj("startdate").focus()
		return false;
	}
	else if(!dateValidate("enddate",alert_arr.STDFILTER+" - "+alert_arr.ENDDATE,"OTH"))
	{
		getObj("enddate").focus()
		return false;
	}
	else
	{
		if (!dateComparison("enddate",alert_arr.STDFILTER+" - "+alert_arr.ENDDATE,"startdate",alert_arr.STDFILTER+" - "+alert_arr.STARTDATE,"GE")) {
                        getObj("enddate").focus()
                        return false
                } else return true;
	}
}
standardFilterDisplay();
{/literal}
</script>
