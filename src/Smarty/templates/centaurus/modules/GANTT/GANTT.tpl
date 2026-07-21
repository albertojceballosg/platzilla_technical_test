<!-- this page specific scripts  -->
<script src="include/js/dhtmlxgantt/codebase/dhtmlxgantt.js" type="text/javascript" charset="utf-8"></script>
<script src="include/js/dhtmlxgantt/codebase/ext/dhtmlxgantt_tooltip.js"></script>
<link rel="stylesheet" href="include/js/dhtmlxgantt/codebase/dhtmlxgantt.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="include/js/dhtmlxgantt/codebase/skins/dhtmlxgantt_skyblue.css" type="text/css" media="screen" title="no title" charset="utf-8">
<script src="include/js/dhtmlxgantt/codebase/locale/locale_es.js" charset="utf-8"></script>

<style type="text/css">
{literal}

	*html, body{ height:100%; padding:0px; margin:0px;}
	.gantt_add {display:none}
	.gantt_grid_head_cell.gantt_grid_head_add.gantt_last_cell {display:none}
	.gantt_tooltip {max-width:300px}
	.drop_mnu{
		position:absolute;
		left:0px;
		top:0px;
		z-index:1000000001;
		border-left:1px solid #d3d3d3;
		border-right:1px solid #d3d3d3;
		border-bottom:1px solid #d3d3d3;
		display:none;
		padding:0px;
		text-align:left;
		background-color:#ffffcc;
		margin-top: 0px; /* added */
	}

{/literal}
</style>

{if $FORMA_COMPLETA eq 'true'}
	<div class="row">
		<div class="col-lg-12">
			<h1>GANTT</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2 class="pull-left"></h2>
					
					<div class="filter-block pull-right">
						<form name="GANTT" id="formGANTT" method="GET" action="index.php">
							<input type="hidden" name="module" value="gantt">
							<input type="hidden" name="action" value="index">
							<input type="hidden" name="parenttab" value="{$PARENTTAB}">
							<input type="hidden" name="inidate" id="inidate" value="{$INIDATE}">
							<input type="hidden" name="enddate" id="enddate" value="{$ENDDATE}">
							<div class="form-group pull-left">
								{$ACCOUNTS}
							</div>
							<div class="form-group pull-left">
								<select class="form-control" id="moduleplan" name="moduleplan" onchange="selectModulePlan()">
								<option value="" selected>{"LBL_MODULE_PLAN"|getTranslatedString}</option>
								{$MODULEPLAN}
								</select>
							</div>							
							<div class="form-group pull-left">
								<select name="items" class="form-control">
									<option value="1" {$SELECT_PROJECTS}>{"PROJECTS"|getTranslatedString}</option>
									<option value="2" {$SELECT_PROJECTS_HITOS}>{"PROJECTS_HITOS"|getTranslatedString}</option>
									<option value="3" {$SELECT_PROJECTS_HITOS_TASK}>{"PROJECTS_HITOS_TASK"|getTranslatedString}</option>
								</select>
							</div>
							<div id="reportrange" class="pull-right daterange-filter" style="  margin-top: 2px;">
								<i class="icon-calendar"></i>
								<span></span> <b class="caret"></b>
							</div>
						</form>
					</div>
				</header>
				
				<div class="main-box-body clearfix">
					<div class="table-responsive" style="height:410px;">
						{$GANTT}
					</div>
				</div>
			</div>
		</div>
	</div>
	

<script type="text/javascript">
{literal}
function selectModulePlan(){

		var moduleplan = jQuery("#moduleplan").val();

		if(moduleplan == '' || moduleplan == 'undefined'){

			alert('Debe seleccionar un módulo!');

			return false;
		
		}else{

		  if(jQuery('#inidate').val()=='' || jQuery('#inidate').val()==''){
			var start=moment();
			var end=moment();
		    jQuery('#inidate').val(start.format('DD-MM-YYYY'));
		    jQuery('#enddate').val(end.format('DD-MM-YYYY'));
		  }
		  if(jQuery("[name=account]").val()==''){
			alert('Debe seleccionar una cuenta!');
			return false;
		  }
		  jQuery('#formGANTT').submit();

	}
}


function selectAccount(){

	var moduleplan = '';

	if(jQuery("#moduleplan").length > 0){

		moduleplan = jQuery("#moduleplan").val();

		if(moduleplan == ''){
			alert(alert_arr.SELECT_MODULE_PLAN);
			jQuery("#moduleplan").focus();
			return false;
		}else{
			return true;
		}

	}else{
		return true;
	}



}


{/literal}
</script>


	<script type="text/javascript">
	{literal}
	jQuery(document).ready(function() {
		jQuery('#reportrange').daterangepicker({
			//startDate: moment().subtract('days', 29),
			startDate: moment(),
			endDate: moment(),
			minDate: '01/01/2012',
			//maxDate: '12/31/2014',
			//dateLimit: { days: 60 },
			showDropdowns: true,
			showWeekNumbers: true,
			timePicker: false,
			timePickerIncrement: 1,
			timePicker12Hour: true,
			ranges: {
				'Hoy': [moment(), moment()],
				'Ayer': [moment().subtract('days', 1), moment().subtract('days', 1)],
				'\u00daltimos 7 d\u00edas': [moment().subtract('days', 6), moment()],
				'\u00daltimos 30 d\u00edas': [moment().subtract('days', 29), moment()],
				'Este mes': [moment().startOf('month'), moment().endOf('month')],
				'Mes anterior': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
			},
			opens: 'left',
			buttonClasses: ['btn btn-default'],
			applyClass: 'btn-small btn-primary',
			cancelClass: 'btn-small',
			format: 'DD-MM-YYYY',
			separator: ' a ',
			locale: {
				applyLabel: 'Buscar',
				fromLabel: 'Desde',
				toLabel: 'A',
				customRangeLabel: 'Rango',
				daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
				monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
				firstDay: 1
			}
		 },
		 function(start, end) {
		  jQuery('#inidate').val(start.format('DD-MM-YYYY'));
		  jQuery('#enddate').val(end.format('DD-MM-YYYY'));
		  jQuery('#reportrange span').html(start.format('D MMMM, YYYY') + ' - ' + end.format('D MMMM, YYYY'));
		  if(jQuery("[name=account]").val()==''){
			alert('Debe seleccionar una cuenta!');
			return false;
		  }
		  jQuery('#formGANTT').submit();
		 }
	  );
	  jQuery("[name=account]").change(function(){
		  if(jQuery('#inidate').val()=='' || jQuery('#inidate').val()==''){
			var start=moment();
			var end=moment();
		    jQuery('#inidate').val(start.format('DD-MM-YYYY'));
		    jQuery('#enddate').val(end.format('DD-MM-YYYY'));
		  }
		  //jQuery('#reportrange span').html(start.format('D MMMM, YYYY') + ' - ' + end.format('D MMMM, YYYY'));
		  if(jQuery("[name=account]").val()==''){
			return false;
		  }
		  if(jQuery("[name=account]").val()!='' && jQuery("#moduleplan").val()!=''){
		  	jQuery('#formGANTT').submit();
		  }
		  //jQuery('#formGANTT').submit();
	  });
	  jQuery("[name=items]").change(function(){
		  if(jQuery('#inidate').val()=='' || jQuery('#inidate').val()==''){
			var start=moment();
			var end=moment();
		    jQuery('#inidate').val(start.format('DD-MM-YYYY'));
		    jQuery('#enddate').val(end.format('DD-MM-YYYY'));
		  }
		  if(jQuery("[name=account]").val()==''){
			alert('Debe seleccionar una cuenta!');
			return false;
		  }

		  if(jQuery("#moduleplan").val()!=''){
		  	alert(alert_arr.SELECT_MODULE_PLAN);
			jQuery("#moduleplan").focus();
			return false;
		  }

		  jQuery('#formGANTT').submit();
	  });
	  //Set the initial state of the picker label
		{/literal}
			{if $INIDATE}
				jQuery('#reportrange span').html("{$INIDATE} a {$ENDDATE}");
			{else}
				jQuery('#reportrange span').html("Seleccione Rango");
			{/if}
		{literal}
	});
	{/literal}
	</script>
	
{/if}
	
	<!-- this page specific scripts -->
	<script src="themes/centaurus/js/moment.min.js"></script>
	<script src="themes/centaurus/js/daterangepicker.js"></script>
	
	


{*	
<form name="GANTT" method="POST" action="index.php">
<input type="hidden" name="module" value="GANTT">
<input type="hidden" name="action" value="index">
<input type="hidden" name="parenttab" value="{$PARENTTAB}">
<table border=0 cellspacing=0 cellpadding=0 width=100% class=small>
<tr>
<td width="20%" class="dvtCellLabel" id="td_inidate" align=right>
{"LBL_ACCOUNT"|getTranslatedString}
</td>
<td>
{$ACCOUNTS}
</td>
<td width="20%" class="dvtCellLabel" id="td_inidate" align=right>
{"LBL_ITEMS"|getTranslatedString}
</td>
<td>
<select name="items" class="small">
	<option value="1" {$SELECT_PROJECTS}>{"PROJECTS"|getTranslatedString}</option>
	<option value="2" {$SELECT_PROJECTS_HITOS}>{"PROJECTS_HITOS"|getTranslatedString}</option>
	<option value="3" {$SELECT_PROJECTS_HITOS_TASK}>{"PROJECTS_HITOS_TASK"|getTranslatedString}</option>
</select>
</td>
</tr>
<tr>
<div class="form-group col-lg-6" id="td_fecha_desde">
				<font color="red"></font>
				<label>{"LBL_INIDATE"|getTranslatedString} 
																																													<font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
								</label> 			<!--/td>
			<td width="30%" align=left id="tdinfo_fecha_desde" -->
				<div class="input-group" style="width: 100%;">
					<div class="input-group-addon">
						<i class="fa fa-calendar" id="jscal_trigger_fecha_desde"></i>
					</div>

								
				
				<input name="inidate" tabindex="" id="jscal_field_inidate" type="text" class="form-control pull-right" size="11" maxlength="18" value="{$INIDATE}">
				
				
								
				

				

				<script type="text/javascript" id='massedit_calendar_inidate'>
			Calendar.setup ({ldelim}
				inputField : "jscal_field_inidate", ifFormat : "{$CALENDAR_DATEFORMAT}", showsTime : false, button : "jscal_trigger_inidate", singleClick : true, step : 1
			{rdelim})
		</script>
				 
				</div>
			</div>

<div class="form-group col-lg-6" id="td_fecha_hasta">
				<font color="red"></font>
				<label>{"LBL_ENDDATE"|getTranslatedString}
																																													<font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
								</label> 			<!--/td>
			<td width="30%" align=left id="tdinfo_fecha_desde" -->
				<div class="input-group" style="width: 100%;">
					<div class="input-group-addon">
						<i class="fa fa-calendar" id="jscal_trigger_fecha_desde"></i>
					</div>

								
				
				<input name="inidate" tabindex="" id="jscal_field_enddate" type="text" class="form-control pull-right" size="11" maxlength="18" value="{$ENDDATE}">
				
				
								
				

				

				<script type="text/javascript" id='massedit_calendar_enddate'>
			Calendar.setup ({ldelim}
				inputField : "massedit_calendar_enddate", ifFormat : "{$CALENDAR_DATEFORMAT}", showsTime : false, button : "jscal_trigger_inidate", singleClick : true, step : 1
			{rdelim})
		</script>
				 
				</div>
			</div>

	
	
</tr>
<tr><td>
<input type="submit" value=" Buscar " class="btn btn-primary">
</td></tr>
<tr><td style="height:2px"></td></tr>
</table>
</form>
</div>
{/if}
<div class="row">
<div class="col-lg-12">
<div class="main-box clearfix">
{$GANTT}
</div>
</div>
</div>
*}