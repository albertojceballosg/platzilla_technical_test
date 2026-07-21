<link rel="stylesheet" href="/themes/centaurus/css/libs/datepicker.css" type="text/css" />
<script src="/themes/centaurus/js/bootstrap-datepicker.js"></script>
<script src="/themes/centaurus/js/bootstrap-datepicker.es.js"></script>

<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-12">
			<div class="col-lg-12">
				<h1 class="pull-left"><a href="index.php?module={$MODULE}&action=index">Widget</a></h1>
				<div class="col-lg-8 icon-box pull-right">
				</div>
			</div>
		</div>

	</div>
</div>

<form id="CustomView" onsubmit="return customFormValidate();" name="CustomView" method="post" action="index.php?module={$MODULE}&action=index">
<input type="hidden" name="registrarNuevoWidget" id="registrarNuevoWidget" value="1"/>
<div  class="row">
	<div id="wtwid" class="col-lg-12">
		<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix">
			  <table class="table">
				<tr>
					<td>{$MOD.LBL_MODULES}</td>
					<td>
						<select name="wmodule" id="wmodule" class="form-control" onchange="wloadFields('',this.value);return false;">
							<option value="">Seleccione</option>
							{foreach key=keyMod item=modulo from=$LISTAMODULOS}
								<option value="{$modulo.name}">{$modulo.tablabel}</option>
							{/foreach}
						</select>
					</td>
				</tr>

				<tr id="td_filterFieldDate" style="display: none">
					<td>{$MOD.LBL_FILTER}</td>
					<td>
						<select name="filterFieldDate" id="filterFieldDate" class="form-control"></select>
					</td>
				</tr>

				<tr id="td_filterFieldDateDefault" style="display: none">
					<td>Tiempo</td>
					<td>
						<select name="filterFieldDateDefault" id="filterFieldDateDefault" class="form-control" onchange="loadDateDefault(this.value);return false;">
							<option value="1" selected="">Personalizado</option>
							<option value="2">Hoy</option>
							<option value="3">Última Semana</option>
							<option value="4">Semana Actual</option>
							<option value="5">Último Mes</option>
							<option value="6">Mes Actual</option>
							<option value="7">Últimos 7 días</option>
							<option value="8">Últimos 30 días</option>
							<option value="9">Últimos 60 días</option>
							<option value="10">Últimos 90 días</option>
							<option value="11">Últimos 120 días</option>
						</select>
					</td>
				</tr>

				<tr id="filter_fechaDesde" style="display: none">
					<td>Fecha Desde: </td>
					<td>
						<div class="input-group" style="width: 100%">
							<div class="input-group-addon">
								<i id="fechadesde" class="fa fa-calendar"></i>
							</div>
							<input id="fecha_desde" class="form-control pull-right datepicker" name="fecha_desde" tabindex="" size="11" maxlength="18"
								   value="{$PARAM.fecha_desde}" type="text" readonly>
						</div>
					</td>
				</tr>

				<tr id="filter_fechaHasta" style="display: none">
					<td>Fecha Hasta: </td>
					<td>
						<div class="input-group" style="width: 100%">
							<div class="input-group-addon">
								<i id="fechahasta" class="fa fa-calendar"></i>
							</div>
							<input id="fecha_hasta" class="form-control pull-right datepicker" name="fecha_hasta" tabindex="" size="11" maxlength="18"
								   value="{$PARAM.fecha_hasta}" type="text" readonly>
						</div>
					</td>
				</tr>

				<tr>
					<td>{$MOD.LBL_FIELD_OPERATION}</td>
					<td>
						<select class="form-control" id="fieldoperation" name="fieldoperation" onchange="wloadFieldsFilters(this.value);return false;">
						</select>
					</td>
				</tr>
				<tr id="td_filterField" style="display: none">
					<td>{$MOD.LBL_FILTER}</td>
					<td>
						<select name="filterField" id="filterField" class="form-control"></select>
					</td>
				</tr>
				<tr id="td_numeric_filter" style="display: none">
					<td>{$MOD.LBL_FILTER}</td>
					<td>
						<div class="row col-md-3">
							<select name="orderFilter" id="orderFilter" class="form-control">
								<option value="1" selected="">Mayor</option>
								<option value="2">Menor</option>
								<option value="3">Igual</option>
							</select>
						</div>
						<div class="col-md-3">
							<input type="text" name="filterNumber" id="filterNumber" class="form-control" />
						</div>
					</td>
				</tr>
				<tr>
					<td>{$MOD.LBL_TIPO_CALCULO} </td>
					<td>
						<select name="opcolumn" id="opcolumn" class="form-control" onchange="wloadNumericFields('');return false;" disabled="">
							{foreach key=keyOper item=oper from=$OPERATIONS}
								<option value="{$keyOper}">{$oper}</option>
							{/foreach}
						</select>
					</td>
				</tr>

				<tr id="td_numeric_fields" style="display:none">
					<td>{$MOD.LBL_FIELD_GROUPING} </td>
					<td>
						<select class="form-control" id="fieldgrouping" name="fieldgrouping">
						</select>
					</td>
				</tr>
				<tr>
					<td> {$MOD.LBL_TEXT_WIDGET}</td>
					<td>
						<input type="text" name="textwidget" id="textwidget" class="form-control">
					</td>
				</tr>
				<tr>
					<td> {$MOD.LBL_ICONO_WIDGET}</td>
					<td>
						<div class="radio-inline">
							<input name="icontype" checked="" value="fa fa-user" type="radio"><i class="fa fa-user"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-shopping-cart" type="radio"><i class="fa fa-shopping-cart"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-money" type="radio"><i class="fa fa-money"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-envelope" type="radio"><i class="fa fa-envelope"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-bar-chart-o" type="radio"><i class="fa fa-bar-chart-o"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-thumb-tack" type="radio"><i class="fa fa-thumb-tack"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-cloud-download" type="radio"><i class="fa fa-cloud-download"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-archive" type="radio"><i class="fa fa-archive"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-info-circle" type="radio"><i class="fa fa-info-circle"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-file" type="radio"><i class="fa fa-file"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-list" type="radio"><i class="fa fa-list"></i>
						</div>
						<div class="radio-inline">
							<input name="icontype" value="fa fa-th-large" type="radio"><i class="fa fa-th-large"></i>
						</div>
					</td>
				</tr>
				<tr>
					<td> {$MOD.LBL_COLOR_WIDGET}</td>
					<td>
						<div class="radio-inline">
							<input name="colortype" checked="" value="purple-bg" type="radio"><button class="btn purple-bg" type="button"></button>
						</div>
						<div class="radio-inline">
							<input name="colortype" value="green-bg" type="radio"><button class="btn green-bg" type="button"></button>
						</div>
						<div class="radio-inline">
							<input name="colortype" value="yellow-bg" type="radio"><button class="btn yellow-bg" type="button"></button>
						</div>
						<div class="radio-inline">
							<input name="colortype" value="red-bg" type="radio"><button class="btn red-bg" type="button"></button>
						</div>
						<div class="radio-inline">
							<input name="colortype" value="emerald-bg" type="radio"><button class="btn emerald-bg" type="button"></button>
						</div>
						<div class="radio-inline">
							<input name="colortype" value="gray-bg" type="radio"><button class="btn gray-bg" type="button"></button>
						</div>
					</td>
				</tr>
				<tr>
					<td>{$MOD.LBL_STATUS}</td>
					<td>
						<select name="estatus" id="estatus" class="form-control">
							<option value="1" selected="">Activo</option>
							<option value="0">Inactivo</option>
						</select>
					</td>
				</tr>
				{*
				<tr>
					<td colspan="2" class="text-center">
						<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="" type="submit" name="button" value="Previsualizar" >
						<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}  " >
					</td>
				</tr>
				*}
			</table>

			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="pull-right text-right">
			<button class="btn btn-info" type="button" id="" onclick="previsualizar();">{$MOD.LBL_PREVIEW}</button>
			<button class="btn btn-primary" type="submit" id="btnsave" onclick="">{$MOD.LBL_SAVE}</button>
			<a class="btn btn-warning" type="submit" href="index.php?module={$MODULE}&action=index">{$MOD.LBL_CANCEL_BUTTON}</a>
		</div>
  	</div>
</div>

</form>

<div id="preview"></div>

<script src="/modules/admin_widgets/admin_widgets.js"></script>
<script language="javascript" type="text/javascript">

function customFormValidate()
{ldelim}
    var wmodule = jQuery('#wmodule').val();
    var fieldoperation = jQuery('#fieldoperation').val();
    var fieldgrouping = jQuery('#fieldgrouping').val();
    var opcolumn = jQuery('#opcolumn').val();
    var filternum = jQuery('#filterNumber').val();
    var filterlist = jQuery('#filterField').val();
    var text = jQuery('#textwidget').val();
    var sts = jQuery('#estatus').val();
    // Validar Fecha
	var campofecha = jQuery('#filterFieldDate').val();
    var fend = jQuery('#fecha_hasta').val();
    var fstart = jQuery('#fecha_desde').val();

    var url = "module=admin_widgets&action=admin_widgetsAjax&file=ajaxActions&function=VerifyModule&ajax=true&fld_module="+wmodule+'&estatus='+sts;

	if (wmodule == '') {ldelim}
        alert('Debe seleccionar un módulo')
        return false;
    {rdelim}

    {literal}
    if (jQuery('#td_filterFieldDate').is(':visible')) {
        if (campofecha != '') {
            if (fstart == '' && fend == '') {
                alert('Debe ingresar las fechas desde y hasta')
                jQuery('#fecha_desde').focus()
                return false;
            }

            if (fstart == '' && fend != '') {
                alert('Debe ingresar la fecha desde')
                jQuery('#fecha_desde').focus()
                return false;
            }

            if (fstart != '' && fend == '') {
                alert('Debe ingresar fecha hasta')
                jQuery('#fecha_hasta').focus()
                return false;
			}

            if (fend && fstart) {
                if (fstart > fend) {
                    alert('Fecha desde no puede ser mayor a la fecha hasta')
                    jQuery('#fecha_desde').focus()
                    return false;
				}
			}
		} else {
			alert('Debe escoger un campo fecha')
			jQuery('#filterFieldDate').focus()
			return false;
		}
    }
    {/literal}

    if (jQuery('#td_filterField').is(':visible')){
    	if (filterlist == ''){ldelim}
	    	alert('Seleccione el valor que desea mostrar')
	    	jQuery('#filterField').focus()
	        return false;
	    {rdelim}
    }

    if (jQuery('#td_numeric_filter').is(':visible')){
    	if (filternum == ''){ldelim}
	    	alert('Indique el valor a mostrar')
	    	jQuery('#filterNumber').focus()
	        return false;
	    {rdelim}
	    if(isNaN(filternum)){ldelim}
			alert('El valor debe ser numérico');
			jQuery('#filterNumber').focus()
			return false;
		{rdelim}
    }

    if (fieldoperation == '') {ldelim}
        alert('Seleccione el dato que desea mostrar')
    	jQuery('#fieldoperation').focus()
        return false;
    {rdelim}

    if (opcolumn == '') {ldelim}
        alert('Seleccione el tipo de cálculo')
    	jQuery('#opcolumn').focus()
        return false;
    {rdelim}

    // Si la operación es distinta de conteo
	if (opcolumn != 1 && (fieldgrouping == '' || fieldgrouping == null)) {ldelim}
        alert('Debe seleccionar el Dato a usar para Calcular (Suma o Promedio) ')
    	jQuery('#fieldgrouping').focus()
        return false;
    {rdelim}

    if (text == '') {ldelim}
        alert('Indique el texto del widget')
    	jQuery('#textwidget').focus()
        return false;
    {rdelim}

    var flag = true;
    new Ajax.Request(
		'index.php',
		{ldelim}asynchronous : false,
			cache: false,
			queue: {ldelim}position: 'end',scope: 'command'{rdelim},
			method: 'post',
			postBody : url,
			onSuccess : function(response) {ldelim}
				if (response.responseText.trim() == 'error'){ldelim}
					alert('Solo pueden ser 2 widgets por módulo');
					jQuery('#wmodule').focus()
					flag = false;
				{rdelim}
			{rdelim}
		{rdelim}
	);

    return flag;
{rdelim}

{literal}
function wloadFields(c_index,val){
	jQuery('#fieldoperation').empty();
	jQuery('#fieldgrouping').empty();
    jQuery('#filterField').empty();
    jQuery('#filterFieldDate').empty();

	if (jQuery('#td_numeric_filter').is(':visible')){
		jQuery('#td_numeric_filter').css('display','none')
	}

	if (jQuery('#td_filterField').is(':visible')){
		jQuery('#td_filterField').css('display','none')
	}

	// Hace invisible los campos del filtro fecha
    if (jQuery('#td_filterFieldDate').is(':visible')){
        jQuery('#td_filterFieldDate').css('display','none')
    }

    if (jQuery('#filter_fechaDesde').is(':visible')){
        jQuery('#filter_fechaDesde').css('display','none')
    }

    if (jQuery('#filter_fechaHasta').is(':visible')){
        jQuery('#filter_fechaHasta').css('display','none')
    }

    if (jQuery('#td_filterFieldDateDefault').is(':visible')){
        jQuery('#td_filterFieldDateDefault').css('display','none')
    }

	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: { module: 'admin_widgets', action: 'ajaxActions', function: 'getColumns', Ajax: 'true', fld_module: val }
	}).done(function( items ) {

		var fields = JSON.parse(items);

		var arrFields = [];
		for (var prop in fields) {
		    arrFields.push(fields[prop]);
		}

		jQuery('#fieldoperation').append(jQuery('<option>', {
	        value: '',
	        text : 'Seleccione'
	    }));

		jQuery.each(arrFields, function (i, item) {
			if (item !== null){
				var fieldInfo = item.split('|');
				var fieldLabel = fieldInfo[3];
				var fieldValue = fieldInfo[1];
			    jQuery('#fieldoperation').append(jQuery('<option>', {
			        value: fieldValue,
			        text : fieldLabel
			    }));
			}
		});
	});

	// Llena el selector del campo fecha
    jQuery.ajax({
        type: 'POST',
        url: 'index.php',
        data: { module: 'admin_widgets', action: 'ajaxActions', function: 'getColumnsDate', Ajax: 'true', fld_module: val }
    }).done(function( items ) {

        if (items !== '[]'){
            jQuery('#td_filterFieldDate').css('display','');
            jQuery('#filter_fechaDesde').css('display','');
            jQuery('#filter_fechaHasta').css('display','');
            jQuery('#td_filterFieldDateDefault').css('display','');
			jQuery('#filterFieldDate').empty();
        }
        var fields = JSON.parse(items);

        var arrFields = [];
        for (var prop in fields) {
            arrFields.push(fields[prop]);
        }

        jQuery('#filterFieldDate').append(jQuery('<option>', {
            value: '',
            text : 'Seleccione'
        }));

        jQuery.each(arrFields, function (i, item) {
            if (item !== null){
                var fieldInfo = item.split('|');
                var fieldLabel = fieldInfo[3];
                var fieldValue = fieldInfo[1];
                jQuery('#filterFieldDate').append(jQuery('<option>', {
                    value: fieldValue,
                    text : fieldLabel
                }));
            }
        });
    });
}
{/literal}

{literal}
function wloadNumericFields(c_index){

	var fld_module = jQuery('#wmodule').val();
	var opcolumn = jQuery('#opcolumn').val();

	// Si la operación es conteo
	if (opcolumn == 1){
		jQuery('#td_numeric_fields').css('display','none');
		jQuery('#fieldgrouping').empty();
		return false;
	}else{
		jQuery('#td_numeric_fields').css('display','');
		jQuery('#fieldgrouping').empty();
		jQuery.ajax({
			type: 'POST',
			url: 'index.php',
			data: { module: 'admin_widgets', action: 'ajaxActions', function: 'getNumericColumns', Ajax: 'true', fld_module: fld_module }
		}).done(function( items ) {
            if (items !== '[]') {
                var fields = JSON.parse(items);
				var arrFields = [];
				for (var prop in fields) {
					arrFields.push(fields[prop]);
				}
				jQuery.each(arrFields, function (i, item) {
					if (item !== null) {
						var fieldInfo = item.split('|');
						var fieldLabel = fieldInfo[3];
						var fieldValue = fieldInfo[1];
						jQuery('#fieldgrouping').append(jQuery('<option>', {
							value: fieldValue,
							text: fieldLabel
						}));
					}
				});
			}else{
                jQuery('#td_numeric_fields').css('display','none');
                jQuery('#opcolumn').val(1);
                jQuery('#opcolumn').focus();
                alert('No existen campos númericos para sumar o promediar');
            }
		});
	}
}
{/literal}

{literal}
function wloadFieldsFilters(val){
	jQuery('#filterField').empty();
	jQuery('#orderFilter').val('1');
	jQuery('#filterNumber').empty();
	wloadNumericFields(jQuery('#opcolumn').val('1'));

	if (jQuery('#fieldoperation').val() != ''){
		jQuery('#opcolumn').removeAttr('disabled');
	}else{
		jQuery('#opcolumn').attr('disabled','disabled');

		if (jQuery('#td_numeric_filter').is(':visible')){
			jQuery('#td_numeric_filter').css('display','none')
		}

		if (jQuery('#td_filterField').is(':visible')){
			jQuery('#td_filterField').css('display','none')
		}

	}

	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: { module: 'admin_widgets', action: 'ajaxActions', function: 'getValues', Ajax: 'true', fld_name: val , fld_module : jQuery('#wmodule').val() }
	}).done(function( items ) {
		var fields = JSON.parse(items);

		var arrFields = [];
		for (var prop in fields) {
		    arrFields.push(fields[prop]);
		}

		jQuery.each(arrFields, function (i, item) {
			if (item !== null){
				var fieldInfo = item.split('|');

				if (fieldInfo[0] == '15'){
					var fieldLabel = Utf8.decode(fieldInfo[2]);
					var fieldValue = Utf8.decode(fieldInfo[1]);

					if (jQuery('#td_numeric_filter').is(':visible')){
						jQuery('#td_numeric_filter').css('display','none')
					}else{
						jQuery('#td_filterField').css('display','')
					}
				    jQuery('#filterField').append(jQuery('<option>', {
				        value: fieldValue,
				        text : fieldLabel
				    }));
				}else{
					if (jQuery('#td_filterField').is(':visible')){
						jQuery('#td_filterField').css('display','none')
					}else{
						jQuery('#td_numeric_filter').css('display','')
					}
				}
			}
		});
	});
}
{/literal}

{literal}
function previsualizar(){
	if (customFormValidate() == true){
		var wmodule 		= jQuery("#wmodule").val();
		var fieldoperation 	= jQuery("#fieldoperation").val();
		var fieldgrouping 	= jQuery("#fieldgrouping").val();
		var opcolumn 		= jQuery("#opcolumn").val();
		var filternum       = jQuery('#filterNumber').val();
		var orderfilter     = jQuery('#orderFilter').val();
	    var filterlist      = jQuery('#filterField').val();
	    var text            = jQuery('#textwidget').val();
	    var icon            = jQuery('input[name="icontype"]:checked').val();
	    var color           = jQuery('input[name="colortype"]:checked').val();
		// variables del filtro de fecha
		var filterfielddate 		= jQuery("#filterFieldDate").val();
		var filterfielddatedefault 	= jQuery("#filterFieldDateDefault").val();
		var fechadesde 				= jQuery("#fecha_desde").val();
		var fechahasta 				= jQuery("#fecha_hasta").val();

		jQuery.ajax({
			type: 'POST',
			url: 'index.php',
			data: {
			    module: 'admin_widgets', action: 'admin_widgetsAjax', file: 'getPreview', Ajax: 'true', wmodule: wmodule ,fieldoperation:fieldoperation, fieldgrouping: fieldgrouping,
				opcolumn: opcolumn, filterNumber:filternum, orderFilter:orderfilter, filterField:filterlist, textwidget:text, icontype:icon, colortype:color,
				filterFieldDate: filterfielddate, filterFieldDateDefault: filterfielddatedefault, fecha_desde: fechadesde, fecha_hasta:fechahasta
			}
		}).done(function( items ) {
			jQuery('#preview').html(items);
		});

	}else{
		alert('No puede ser previsualizado el widget! Verifique los datos! ')
	}
}
{/literal}

	jQuery('.datepicker').datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });

{literal}
function loadDateDefault(c_index){

	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		data: { module: 'admin_widgets', action: 'ajaxActions', function: 'getDateValue', Ajax: 'true', valorEntreFechas: c_index }
	}).done(function( items ) {

		var fields = JSON.parse(items);
		if (c_index == 1) {
            jQuery("#fecha_desde").attr("disabled", false);
            jQuery("#fecha_hasta").attr("disabled", false);
			jQuery("#fecha_desde").val('');
            jQuery("#fecha_hasta").val('');
        } else {
            jQuery("#fecha_desde").attr("disabled", true);
            jQuery("#fecha_hasta").attr("disabled", true);
            var arrFields = [];
            for (var prop in fields) {
                arrFields.push(fields[prop]);
            }
            jQuery("#fecha_desde").val(arrFields[0]);
            jQuery("#fecha_hasta").val(arrFields[1]);
        };
    });
}
{/literal}

</script>