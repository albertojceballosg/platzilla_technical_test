<link rel="stylesheet" href="/themes/centaurus/css/libs/datepicker.css" type="text/css" />
<script src="/themes/centaurus/js/bootstrap-datepicker.js"></script>
<script src="/themes/centaurus/js/bootstrap-datepicker.es.js"></script>

<form action="index.php?module={$MODULE}&action=SaveEditWidget" onsubmit="return customFormValidate();" id="SaveEditWidget" method="post" name="index" onsubmit="">
    <div class="row">
        <div class="col-lg-12">
            <div class="col-lg-9 pull-left">
                <h1><a href="index.php?module={$MODULE}&action=index">{$MOD.LBL_EDIT_ADMIN_WIDGET} </a></h1>
            </div>
            <div class="col-lg-3 pull-right text-right">
                <button class="btn btn-primary" type="submit" id="btnsave" onclick="">{$MOD.LBL_SAVE}</button>
                <a class="btn btn-warning" type="submit" href="index.php?module={$MODULE}&action=index">{$MOD.LBL_CANCEL_BUTTON}</a>
            </div>
        </div>
    </div>

    <div  class="row">
        <div id="wtwid" class="col-lg-12">
            <div class="main-box no-header clearfix">
                <div class="main-box-body clearfix">
                    <table class="table">
                        <tr>
                            <td>Módulo</td>
                            <td>
                                {foreach key=keyMod item=modulo from=$LISTAMODULOS}
                                    {if $CONFIGWIDGET.fld_module eq $modulo.name}
                                        {assign var=modulewidget value=$modulo.tablabel}
                                        {assign var=wmodule value=$modulo.name}
                                    {/if}
                                {/foreach}
                                <input type="text" value="{$modulewidget}" class="form-control" disabled="">
                                <input type="hidden" name="wmodule" id="wmodule" value="{$wmodule}" class="form-control" >
                            </td>
                        </tr>
                        <tr id="td_filterFieldDate" {if $CONFIGWIDGET.campofecha eq ''} style="display:none" {/if}">
                            <td>{$MOD.LBL_FILTER}</td>
                            <td>
                                <select name="filterFieldDate" id="filterFieldDate" class="form-control">
                                    {foreach key=keyMod item=camposDate from=$CONFIGWIDGET.campoFechas}
                                        <option value="{$camposDate.value}" {if $CONFIGWIDGET.campofecha eq $camposDate.value} selected="selected" {/if}>{$camposDate.label}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>

                        <tr id="td_filterFieldDateDefault" {if $CONFIGWIDGET.campofecha eq ''} style="display:none" {/if}">
                            <td>Tiempo</td>
                            <td>
                                <select name="filterFieldDateDefault" id="filterFieldDateDefault" class="form-control" onchange="loadDateDefault(this.value);return false;">
                                    <option value="1" {if $CONFIGWIDGET.tiempofecha eq 1} selected="selected" {/if}>Personalizado</option>
                                    <option value="2" {if $CONFIGWIDGET.tiempofecha eq 2} selected="selected" {/if}>Hoy</option>
                                    <option value="3" {if $CONFIGWIDGET.tiempofecha eq 3} selected="selected" {/if}>Última Semana</option>
                                    <option value="4" {if $CONFIGWIDGET.tiempofecha eq 4} selected="selected" {/if}>Semana Actual</option>
                                    <option value="5" {if $CONFIGWIDGET.tiempofecha eq 5} selected="selected" {/if}>Último Mes</option>
                                    <option value="6" {if $CONFIGWIDGET.tiempofecha eq 6} selected="selected" {/if}>Mes Actual</option>
                                    <option value="7" {if $CONFIGWIDGET.tiempofecha eq 7} selected="selected" {/if}>Últimos 7 días</option>
                                    <option value="8" {if $CONFIGWIDGET.tiempofecha eq 8} selected="selected" {/if}>Últimos 30 días</option>
                                    <option value="9" {if $CONFIGWIDGET.tiempofecha eq 9} selected="selected" {/if}>Últimos 60 días</option>
                                    <option value="10" {if $CONFIGWIDGET.tiempofecha eq 10} selected="selected" {/if}>Últimos 90 días</option>
                                    <option value="11" {if $CONFIGWIDGET.tiempofecha eq 11} selected="selected" {/if}>Últimos 120 días</option>
                                </select>
                            </td>
                        </tr>

                        <tr id="filter_fechaDesde" {if $CONFIGWIDGET.campofecha eq ''} style="display:none" {/if}">
                            <td>Fecha Desde: </td>
                            <td>
                                <div class="input-group" style="width: 100%">
                                    <div class="input-group-addon">
                                        <i id="fechadesde" class="fa fa-calendar"></i>
                                    </div>
                                    <input id="fecha_desde" class="form-control pull-right datepicker" name="fecha_desde" tabindex="" size="11"
                                           maxlength="18" value="{$CONFIGWIDGET.fechadesde}" type="text" readonly {if $CONFIGWIDGET.tiempofecha neq 1} disabled{/if}>
                                </div>
                            </td>
                        </tr>

                        <tr id="filter_fechaHasta" {if $CONFIGWIDGET.campofecha eq ''} style="display:none" {/if}">
                            <td>Fecha Hasta: </td>
                            <td>
                                <div class="input-group" style="width: 100%">
                                    <div class="input-group-addon">
                                        <i id="fechahasta" class="fa fa-calendar"></i>
                                    </div>
                                    <input id="fecha_hasta" class="form-control pull-right datepicker" name="fecha_hasta" tabindex="" size="11"
                                           maxlength="18" value="{$CONFIGWIDGET.fechahasta}" type="text" readonly {if $CONFIGWIDGET.tiempofecha neq 1} disabled{/if}>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>{$MOD.LBL_FIELD_OPERATION} </td>
                            <td>
                                <select class="form-control" id="fieldoperation" name="fieldoperation" onchange="wloadFieldsFilters(this.value)">
                                    {foreach key=keyMod item=moduleField from=$CONFIGWIDGET.moduleFields}
                                        <option value="{$moduleField.value}" {if $CONFIGWIDGET.fieldoperation eq $moduleField.value} selected="selected" {/if}>{$moduleField.label}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr id="td_filterField" style="display: none">
                            <td>{$MOD.LBL_FILTER}</td>
                            <td>
                                <input type="hidden" name="filterF" id="filterF" value="{$CONFIGWIDGET.filterfield}" class="form-control" >
                                <select name="filterField" id="filterField" class="form-control"></select>
                            </td>
                        </tr>
                        <tr id="td_numeric_filter" style="display: none">
                            <td>{$MOD.LBL_FILTER}</td>
                            <td>
                                <div class="row col-md-3">
                                    <select name="orderFilter" id="orderFilter" class="form-control">
                                        <option value="1" {if $CONFIGWIDGET.orderfilter eq 1 }selected="selected"{/if}>Mayor</option>
                                        <option value="2" {if $CONFIGWIDGET.orderfilter eq 2 }selected="selected"{/if}>Menor</option>
                                        <option value="3" {if $CONFIGWIDGET.orderfilter eq 3 }selected="selected"{/if}>Igual</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="filterNumber" id="filterNumber" class="form-control" value="{$CONFIGWIDGET.filternumber}"/>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tipo de C&aacute;lculo </td>
                            <td>
                                <select name="opcolumn" id="opcolumn" class="form-control" onchange="wloadNumericFields('');return false;">
                                    {foreach key=keyOper item=oper from=$OPERATIONS}
                                        <option value="{$keyOper}" {if $CONFIGWIDGET.operation.value eq $keyOper} selected="selected" {/if}>{$oper}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>

                        <tr id="td_numeric_fields" {if $CONFIGWIDGET.operation eq 1} style="display:none" {/if} >
                            <td>{$MOD.LBL_FIELD_GROUPING}</td>
                            <td>
                                <select class="form-control" id="fieldgrouping" name="fieldgrouping">
                                    {if $CONFIGWIDGET.operation neq 1}
                                        {foreach key=keyMod item=moduleField from=$CONFIGWIDGET.moduleNumericFields}
                                            <option value="{$moduleField.value}" {if $CONFIGWIDGET.fieldgrouping.value eq $moduleField.value} selected="selected" {/if}>{$moduleField.label}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td> {$MOD.LBL_TEXT_WIDGET}</td>
                            <td>
                                <input type="text" name="textwidget" id="textwidget" class="form-control" value="{$CONFIGWIDGET.texto}">
                            </td>
                        </tr>
                        <tr>
                            <td> {$MOD.LBL_ICONO_WIDGET}</td>
                            <td>
                                <input type="hidden" name="iconowidget" id="iconowidget" value="{$CONFIGWIDGET.icono}" class="form-control">
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
                                <input type="hidden" name="colorwidget" id="colorwidget" value="{$CONFIGWIDGET.color}" class="form-control" >
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
                                    <option value="1" {if $CONFIGWIDGET.estatus eq 1 }selected="selected"{/if}>Activo</option>
                                    <option value="0" {if $CONFIGWIDGET.estatus eq 0 }selected="selected"{/if}>Inactivo</option>
                                </select>
                            </td>
                        </tr>

                        <input type="hidden" id="record" name="record" value="{$RECORD}" />

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
<script language="javascript">

    {literal}
    jQuery(function(){
        jQuery("input[name='icontype'][value='"+jQuery('#iconowidget').val()+"']" ).attr('checked',true);
        jQuery("input[name='colortype'][value='"+jQuery('#colorwidget').val()+"']" ).attr('checked',true);
        wloadFieldsFilters(jQuery('#fieldoperation').val())
    });

    function wloadFieldsFilters(val){
        jQuery('#filterField').empty();
        jQuery('#filterNumber').empty();

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
                if (item !== null) {
                    var fieldInfo = item.split('|');
                    if (fieldInfo[0] == '15'){
                        var fieldLabel = Utf8.decode(fieldInfo[2]);
                        var fieldValue = Utf8.decode(fieldInfo[1]);
                        if (jQuery('#td_numeric_filter').is(':visible')) {
                            jQuery('#td_numeric_filter').css('display','none')
                        } else {
                            jQuery('#td_filterField').css('display','')
                        }

                        jQuery('#filterField').append(jQuery('<option>', {
                            value: fieldValue,
                            text : fieldLabel
                        }));
                        if (jQuery('#filterF').val() != '0') {
                            jQuery('#filterField').val(jQuery('#filterF').val())
                        }
                    } else {
                        if (jQuery('#td_filterField').is(':visible')) {
                            jQuery('#td_filterField').css('display','none')
                        } else {
                            jQuery('#td_numeric_filter').css('display','')
                        }
                    }
                }
            });
        });
    }

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
                data: { module: 'graficosgenerales', action: 'ajaxActions', function: 'getNumericColumns', Ajax: 'true', fld_module: fld_module }
            }).done(function( items ) {
                if (items !== '[]') {
                    var fields = JSON.parse(items);
                    var arrFields = [];
                    for (var prop in fields) {
                        arrFields.push(fields[prop]);
                    }
                    console.log(arrFields);
                    jQuery.each(arrFields, function (i, item) {
                        if (item !== null){
                            var fieldInfo = item.split('|');
                            var fieldLabel = fieldInfo[3];
                            var fieldValue = fieldInfo[1];
                            jQuery('#fieldgrouping').append(jQuery('<option>', {
                                value: fieldValue,
                                text : fieldLabel
                            }));
                        }
                    });
                } else {
                    jQuery('#td_numeric_fields').css('display','none');
                    jQuery('#opcolumn').val(1);
                    jQuery('#opcolumn').focus();
                    alert('No existen campos númericos para sumar o promediar');
                }
            });
        }
    }

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

    function customFormValidate() {
        var wmodule = jQuery('#wmodule').val();
        var fieldoperation = jQuery('#fieldoperation').val();
        var fieldgrouping = jQuery('#fieldgrouping').val();
        var opcolumn = jQuery('#opcolumn').val();
        var filternum = jQuery('#filterNumber').val();
        var filterlist = jQuery('#filterField').val();
        var text = jQuery('#textwidget').val();
        var sts = jQuery('#estatus').val();
        // Validar Fecha
        var fend = jQuery('#fecha_hasta').val();
        var fstart = jQuery('#fecha_desde').val();

        var url = "module=admin_widgets&action=admin_widgetsAjax&file=ajaxActions&function=VerifyModule&ajax=true&fld_module="+wmodule+'&estatus='+sts;


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

        if (jQuery('#td_filterField').is(':visible')) {
            if (filterlist == '' || filterlist == null) {
                alert('Seleccione el valor que desea mostrar')
                jQuery('#filterField').focus()
                return false;
            }
        }

        if (jQuery('#td_numeric_filter').is(':visible')) {
            if (filternum == ''  || filternum == null) {
                alert('Indique el valor a mostrar')
                jQuery('#filterNumber').focus()
                return false;
            }
            if(isNaN(filternum)) {
                alert('El valor debe ser numérico');
                jQuery('#filterNumber').focus()
                return false;
            }
        }

        if (fieldoperation == '') {
            alert('Seleccione el dato que desea mostrar')
            jQuery('#fieldoperation').focus()
            return false;
        }

        if (opcolumn == '') {
            alert('Seleccione el tipo de cálculo')
            jQuery('#opcolumn').focus()
            return false;
        }

        if (opcolumn != 1 && (fieldgrouping == '' || fieldgrouping == null)) {
            alert('Debe seleccionar el Dato a usar para Calcular (Suma o Promedio) ')
            jQuery('#fieldgrouping').focus()
            return false;
        }

        if (text == '') {
            alert('Indique el texto del widget')
            jQuery('#textwidget').focus()
            return false;
        }

        var flag = true;
        new Ajax.Request(
            'index.php',
            {asynchronous : false,
                cache: false,
                queue: {position: 'end',scope: 'command'},
                method: 'post',
                postBody : url,
                onSuccess : function(response) {
                    if (response.responseText.trim() == 'error') {
                        alert('Solo pueden ser 2 widgets por módulo');
                        jQuery('#wmodule').focus()
                        flag = false;
                    }
                }
            }
        );

        return flag;
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