{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<!-- Matomo -->
<script type="text/javascript">
	/*ggc  Marcado para evitar que active google analitics en mi local
    var _paq = _paq || []; */
	/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
	/* GGC 20250212
	    _paq.push(['trackPageView']);
	    _paq.push(['enableLinkTracking']);
	    (function() {
	        var u="//analytic.timemanagement.es/";
	        _paq.push(['setTrackerUrl', u+'piwik.php']);
	        _paq.push(['setSiteId', '4']);
	        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
	        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
	    })();
		Fin coment GGC */
</script>
<!-- End Matomo Code -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<!--GGC <script async src="https://www.googletagmanager.com/gtag/js?id=UA-130879802-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){
		dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-130879802-1');
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script type="text/javascript">
	J = jQuery.noConflict();
</script>
<!-- asterisk Integration -->
{if $USE_ASTERISK eq 'true'}
	<script type="text/javascript" src="include/js/asterisk.js"></script>
	<script type="text/javascript">
		if (typeof(use_asterisk) == 'undefined') use_asterisk = true;
	</script>
{/if}
<!-- END -->

{* vtlib customization: Inclusion of custom javascript and css as registered *}
{if $HEADERSCRIPTS}
	<!-- Custom Header Script -->
	{foreach item=HEADERSCRIPT from=$HEADERSCRIPTS}
		<script type="text/javascript" src="{$HEADERSCRIPT->linkurl}"></script>
	{/foreach}
	<!-- END -->
{/if}
{* END *}

<script type='text/javascript'>
	{if ($APD.periodo_prueba.ini eq '0' && ($APD.estado_plataforma neq 'Activa')) && ($smarty.get.module neq 'Home' || $smarty.get.action neq 'customer')}
		J(document).ready(function() {ldelim}
		jQuery('#cortina').css({ldelim}'opacity':'0.9','background':'#fff'{rdelim});
		jQuery('#cortina').fadeIn(function(){ldelim}
		jQuery('#tableMenu').remove();
		jQuery('.level2Bg').remove();
		jQuery('.small .homePageButtons').remove();
		jQuery('.small .showPanelBg').remove();
		{rdelim});
		{rdelim});
	{/if}
	{literal}
		function OpenClosehome() {
			var blackout = document.getElementById('blackout');
			var homepopup = document.getElementById('homepopup');

			if (blackout.style.display == 'block') {
				blackout.style.display = 'none';
				homepopup.style.display = 'none';
			} else {
				blackout.style.display = 'block';
				homepopup.style.display = 'block';
			}
		}

		function OpenClosecortina() {
			var cortina = document.getElementById('cortina');

			if (cortina.style.display == 'block') {
				cortina.style.display = 'none';
			} else {
				cortina.style.display = 'block';
			}
		}

		function UnifiedSearch_SelectModuleForm(obj) {
			if ($('UnifiedSearch_moduleform')) {
				// If we have loaded the form already.
				UnifiedSearch_SelectModuleFormCallback(obj);
			} else {
				$('status').show();
				new Ajax.Request(
					'index.php',
					{queue: {position: 'end', scope: 'command'},
					method: 'post',
					postBody: 'module=Home&action=HomeAjax&file=UnifiedSearchModules&ajax=true',
					onComplete: function(response) {
						$('status').hide();
						$('UnifiedSearch_moduleformwrapper').innerHTML = response.responseText;
						UnifiedSearch_SelectModuleFormCallback(obj);
					}
				});
		}
		}

		function UnifiedSearch_SelectModuleFormCallback(obj) {
			fnvshobjsearch(obj, 'UnifiedSearch_moduleformwrapper');
		}

		function UnifiedSearch_SelectModuleToggle(flag) {
			Form.getElements($('UnifiedSearch_moduleform')).each(
				function(element) {
					if (element.type == 'checkbox') {
						element.checked = flag;
					}
				}
			);
		}

		function UnifiedSearch_SelectModuleCancel() {
			$('UnifiedSearch_moduleformwrapper').hide();
		}

		function UnifiedSearch_SelectModuleSave() {
			var UnifiedSearch_form = document.forms.UnifiedSearch;
			UnifiedSearch_form.search_onlyin.value = Form.serialize($('UnifiedSearch_moduleform')).replace(/search_onlyin=/g,
				'').replace(/&/g, ',');
			UnifiedSearch_SelectModuleCancel();
		}

	{/literal}
</script>
<!-- End -->

<script>
	var gVTTheme  = '{$THEME}';
	function fetch_clock()
	{ldelim}
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
		method: 'post',
		postBody: 'module=Utilities&action=UtilitiesAjax&file=Clock',
		onComplete: function(response)
		{ldelim}
		$("clock_cont").innerHTML = response.responseText; execJS($('clock_cont'));
		{rdelim}
		{rdelim}
	);

	{rdelim}

	function fetch_calc()
	{ldelim}
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
		method: 'post',
		postBody: 'module=Utilities&action=UtilitiesAjax&file=Calculator',
		onComplete: function(response)
		{ldelim}
		$("calculator_cont").innerHTML = response.responseText; execJS($('calculator_cont'));
		{rdelim}
		{rdelim}
	);
	{rdelim}
</script>

<script type="text/javascript">
	{literal}
		function QCreate(qcoptions) {
			var module = qcoptions.options[qcoptions.options.selectedIndex].value;
			if (module != 'none') {
				$("status").style.display = "inline";
				if (module == 'Events') {
					module = 'Calendar';
					var urlstr = '&activity_mode=Events';
				} else if (module == 'Calendar') {
					module = 'Calendar';
					var urlstr = '&activity_mode=Task';
				} else {
					var urlstr = '';
				}
				new Ajax.Request(
					'index.php',
					{queue: {position: 'end', scope: 'command'},
					method: 'post',
					postBody: 'module=' + module + '&action=' + module + 'Ajax&file=QuickCreate' + urlstr,
					onComplete: function(response) {
						$("status").style.display = "none";
						$("qcform").style.display = "inline";
						$("qcform").innerHTML = response.responseText;
						// Evaluate all the script tags in the response text.
						var scriptTags = $("qcform").getElementsByTagName("script");
						for (var i = 0; i < scriptTags.length; i++) {
							var scriptTag = scriptTags[i];
							eval(scriptTag.innerHTML);
						}
						eval($("qcform"));
						posLay(qcoptions, "qcform");
					}
				}
			);
		} else {
			hide('qcform');
		}
		}

		function getFormValidate(divValidate) {
			var st = document.getElementById('qcvalidate');
			eval(st.innerHTML);
			for (var i = 0; i < qcfieldname.length; i++) {
				var curr_fieldname = qcfieldname[i];
				if (window.document.QcEditView[curr_fieldname] != null) {
					var type = qcfielddatatype[i].split("~")
					var input_type = window.document.QcEditView[curr_fieldname].type;
					if (type[1] == "M") {
						if (!qcemptyCheck(curr_fieldname, qcfieldlabel[i], input_type))
							return false
					}
					switch (type[0]) {
						case "O":
							break;
						case "V":
							break;
						case "C":
							break;
						case "DT":
							if (window.document.QcEditView[curr_fieldname] != null && window.document.QcEditView[
									curr_fieldname].value.replace(/^\s+/g, '').replace(/\s+$/g, '').length != 0) {
								if (type[1] == "M")
									if (!qcemptyCheck(type[2], qcfieldlabel[i], getObj(type[2]).type))
										return false
								if (typeof(type[3]) == "undefined") var currdatechk = "OTH"
								else var currdatechk = type[3]

								if (!qcdateTimeValidate(curr_fieldname, type[2], qcfieldlabel[i], currdatechk))
									return false
								if (type[4]) {
									if (!dateTimeComparison(curr_fieldname, type[2], qcfieldlabel[i], type[5], type[6], type[
											4]))
										return false

								}
							}
							break;
						case "D":
							if (window.document.QcEditView[curr_fieldname] != null && window.document.QcEditView[
									curr_fieldname].value.replace(/^\s+/g, '').replace(/\s+$/g, '').length != 0) {
								if (typeof(type[2]) == "undefined") var currdatechk = "OTH"
								else var currdatechk = type[2]

								if (!qcdateValidate(curr_fieldname, qcfieldlabel[i], currdatechk))
									return false
								if (type[3]) {
									if (!qcdateComparison(curr_fieldname, qcfieldlabel[i], type[4], type[5], type[3]))
										return false
								}
							}
							break;
						case "T":
							if (window.document.QcEditView[curr_fieldname] != null && window.document.QcEditView[
									curr_fieldname].value.replace(/^\s+/g, '').replace(/\s+$/g, '').length != 0) {
								if (typeof(type[2]) == "undefined") var currtimechk = "OTH"
								else var currtimechk = type[2]

								if (!timeValidate(curr_fieldname, qcfieldlabel[i], currtimechk))
									return false
								if (type[3]) {
									if (!timeComparison(curr_fieldname, qcfieldlabel[i], type[4], type[5], type[3]))
										return false
								}
							}
							break;
						case "I":
							if (window.document.QcEditView[curr_fieldname] != null && window.document.QcEditView[
									curr_fieldname].value.replace(/^\s+/g, '').replace(/\s+$/g, '').length != 0) {
								if (window.document.QcEditView[curr_fieldname].value.length != 0) {
									if (!qcintValidate(curr_fieldname, qcfieldlabel[i]))
										return false
									if (type[2]) {
										if (!qcnumConstComp(curr_fieldname, qcfieldlabel[i], type[2], type[3]))
											return false
									}
								}
							}
							break;
						case "N":
							//case "NN" :
							if (window.document.QcEditView[curr_fieldname] != null && window.document.QcEditView[
									curr_fieldname].value.replace(/^\s+/g, '').replace(/\s+$/g, '').length != 0) {
								if (window.document.QcEditView[curr_fieldname].value.length != 0) {
									if (typeof(type[2]) == "undefined") var numformat = "any"
									else var numformat = type[2]

									if (type[0] == "NN") {

										if (!numValidate(curr_fieldname, qcfieldlabel[i], numformat, true))
											return false
									} else {
										if (!numValidate(curr_fieldname, qcfieldlabel[i], numformat))
											return false
									}
									if (type[3]) {
										if (!numConstComp(curr_fieldname, qcfieldlabel[i], type[3], type[4]))
											return false
									}
								}
							}
							break;
						case "E":
							if (window.document.QcEditView[curr_fieldname] != null && window.document.QcEditView[
									curr_fieldname].value.replace(/^\s+/g, '').replace(/\s+$/g, '').length != 0) {
								if (window.document.QcEditView[curr_fieldname].value.length != 0) {
									var etype = "EMAIL"
									if (!qcpatternValidate(curr_fieldname, qcfieldlabel[i], etype))
										return false
								}
							}
							break;
					}
				}
			}
			//added to check Start Date & Time,if Activity Status is Planned.//start
			for (var j = 0; j < qcfieldname.length; j++) {
				curr_fieldname = qcfieldname[j];
				if (window.document.QcEditView[curr_fieldname] != null) {
					if (qcfieldname[j] == "date_start") {
						var datelabel = qcfieldlabel[j]
						var datefield = qcfieldname[j]
						var startdatevalue = window.document.QcEditView[datefield].value.replace(/^\s+/g, '').replace(/\s+$/g,
							'')
					}
					if (qcfieldname[j] == "time_start") {
						var timelabel = qcfieldlabel[j]
						var timefield = qcfieldname[j]
						var timeval = window.document.QcEditView[timefield].value.replace(/^\s+/g, '').replace(/\s+$/g, '')
					}
					if (qcfieldname[j] == "eventstatus" || qcfieldname[j] == "taskstatus") {
						var statusvalue = window.document.QcEditView[curr_fieldname].options[window.document.QcEditView[
							curr_fieldname].selectedIndex].value.replace(/^\s+/g, '').replace(/\s+$/g, '')
						var statuslabel = qcfieldlabel[j++]
					}
				}
			}
			if (statusvalue == "Planned") {
				var dateelements = splitDateVal(startdatevalue)
				var hourval = parseInt(timeval.substring(0, timeval.indexOf(":")))
				var minval = parseInt(timeval.substring(timeval.indexOf(":") + 1, timeval.length))
				var dd = dateelements[0]
				var mm = dateelements[1]
				var yyyy = dateelements[2]

				var chkdate = new Date()
				chkdate.setYear(yyyy)
				chkdate.setMonth(mm - 1)
				chkdate.setDate(dd)
				chkdate.setMinutes(minval)
				chkdate.setHours(hourval)
				if (!comparestartdate(chkdate)) return false;


			} //end
			return true;
		}
	</script>
{/literal}
<script>
	function openwin()
	{ldelim}
	window.open("index.php?module=Users&action=about_us", "aboutwin", "height=520,width=515,top=200,left=300")
	{rdelim}
</script>

<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/hopscotch.css">
<script src="themes/{$THEME}/js/hopscotch.js"></script>

<script type="text/javascript">
	if (jQuery) {ldelim}
	var placementRight = 'right';
	var placementLeft = 'left';

	if (jQuery('body').hasClass('rtl')) {ldelim}
	placementRight = 'left';
	placementLeft = 'right';
	{rdelim}

	// Define the tour!
	var tour = {ldelim}
	id: "centaurus-intro",
		i18n: {ldelim}
		nextBtn: "Siguiente",
		prevBtn: "Atrás",
		doneBtn: "Listo",
		skipBtn: "Saltar",
		closeTooltip: "Cerrar"
	{rdelim},
	steps: [
			{ldelim}
			target: "li-Control Hoy",
			title: "¿Cómo marcha todo?",
			content: "Consulta gráficos de indicadores claves de tu negocio. Mide avances y programa tareas.",
			placement: placementRight,
			yOffset: -2,
			{rdelim},
			{ldelim}
			target: 'li-Negocio',
			title: "¡Comienza a vender más!",
			content: "Gestiona aquí prospectos y oportunidades. Organiza acciones de venta y logra más cierres.",
			placement: placementRight,
			zindex: 999,
			yOffset: -5,
			{rdelim},
			{ldelim}
			target: 'li-La Empresa',
			title: "¡Organiza todo!",
			content: "Gestiona pedidos, productos, facturas y más.",
			placement: placementRight,
			zindex: 999,
			onNext: ["openHelp"],
			yOffset: -5,
			{rdelim},
			{ldelim}
			target: 'config-tool-bar',
			title: "Aprende",
			content:
			"Encuentra ayudas, casos de USO y artículos especializados para sacar el máximo provecho a CRM- Fácil.",
			placement: placementLeft,
			zindex: 999,
			xOffset: -455,
			onPrev: ["closeHelp"],
			{rdelim}
		],
		showPrevButton: true,
		onEnd: function() {ldelim}
		jQuery('#config-tool-bar').addClass('closed');
	{rdelim}

	{rdelim};


	jQuery(document).ready(function() {ldelim}

	hopscotch.registerHelper('openHelp', function() {ldelim}
	jQuery('#config-tool-bar').removeClass('closed');
	{rdelim});

	hopscotch.registerHelper('closeHelp', function() {ldelim}
	jQuery('#config-tool-bar').addClass('closed');
	{rdelim});

	{rdelim});
	{rdelim}
</script>