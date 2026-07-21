{strip}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-default.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-growl.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-bar.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-attached.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-other.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-theme.css" />
<script type="text/javascript" src="include/js/ListView.js"></script>
<script type="text/javascript" src="include/js/search.js"></script>
<script type="text/javascript" src="include/js/Merge.js"></script>
<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
<script type="text/javascript" src="themes/centaurus/js/modernizr.custom.js"></script>
<script type="text/javascript" src="themes/centaurus/js/snap.svg-min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
<script type="text/javascript" src="themes/centaurus/js/notificationFx.js"></script>
<script type="text/javascript">
{literal}
	var typeofdata = {
		C:  [ 'e', 'n' ],
		D:  [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		DT: [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		E:  [ 'e', 'n', 's', 'ew', 'c', 'k' ],
		I:  [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		N:  [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		NN: [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		T:  [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		V:  [ 'e', 'n', 's', 'ew', 'c', 'k' ]
	}, fLabels = {
		c:  {/literal}'{$APP.contains}'{literal},
		e:  {/literal}'{$APP.is}'{literal},
		ew: {/literal}'{$APP.ends_with}'{literal},
		g:  {/literal}'{$APP.greater_than}'{literal},
		h:  {/literal}'{$APP.greater_or_equal}'{literal},
		k:  {/literal}'{$APP.does_not_contains}'{literal},
		l:  {/literal}'{$APP.less_than}'{literal},
		m:  {/literal}'{$APP.less_or_equal}'{literal},
		n:  {/literal}'{$APP.is_not}'{literal},
		s:  {/literal}'{$APP.begins_with}'{literal}
	}, noneLabel;

	function trimfValues (value) {
		var string_array;
		string_array = value.split (':');
		return string_array [ 4 ];
	}

	function updatefOptions (sel, opSelName) {
		var selObj     = document.getElementById (opSelName),
			fieldtype  = null,
			currOption = selObj.options[ selObj.selectedIndex ],
			currField  = sel.options[ sel.selectedIndex ],
			ops, nMaxVal, nLoop, i, label, option;
		if ((currField.value != null) && (currField.value.length != 0)) {
			fieldtype = trimfValues (currField.value);
			fieldtype = fieldtype.replace (/\\'/g, '');
			ops = typeofdata[ fieldtype ];
			if (ops != null) {
				nMaxVal = selObj.length;
				for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
					selObj.remove (0);
				}
				for (i = 0; i < ops.length; i++) {
					label = fLabels [ ops [ i ] ];
					if (label == null) {
						continue;
					}
					option = new Option (fLabels[ ops[ i ] ], ops[ i ]);
					selObj.options[ i ] = option;
					if (currOption != null && currOption.value == option.value) {
						option.selected = true;
					}
				}
			}
		} else {
			nMaxVal = selObj.length;
			for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
				selObj.remove (0);
			}
			selObj.options[ 0 ] = new Option ('None', '');
			if (currField.value == '') {
				selObj.options[ 0 ].selected = true;
			}
		}
	}
{/literal}
</script>
<script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
<script language="javascript">
{literal}
	function checkgroup () {
		if ($ ('group_checkbox').checked) {
			document [ 'change_ownerform_name' ][ 'lead_group_owner' ].style.display = 'block';
			document [ 'change_ownerform_name' ][ 'lead_owner' ].style.display = 'none';
		} else {
			document [ 'change_ownerform_name' ][ 'lead_owner' ].style.display = 'block';
			document [ 'change_ownerform_name' ][ 'lead_group_owner' ].style.display = 'none';
		}
	}

	function callSearch (searchtype) {
		var search_fld_val = jQuery ('input[name=search_field]:checked').val (),
			search_txt_val = encodeURIComponent (jQuery ('#search_text').val ()),
			urlstring      = '';
		if (searchtype == 'Basic') {
			var p_tab = document.getElementsByName ('parenttab');
			urlstring = 'search_field=' + search_fld_val + '&searchtype=BasicSearch&search_text=' + search_txt_val + '&';
			urlstring = urlstring + 'parenttab=' + p_tab[ 0 ].value + '&';
		} else if (searchtype == 'Advanced') {
			checkAdvancedFilter ();
			var advft_criteria = $ ('advft_criteria').value;
			var advft_criteria_groups = $ ('advft_criteria_groups').value;
			urlstring += '&advft_criteria=' + advft_criteria + '&advft_criteria_groups=' + advft_criteria_groups + '&';
			urlstring += 'searchtype=advance&';
		}
		jQuery ('#status').show ();
		new Ajax.Request (
				'index.php',
				{
					queue:      {
						position: 'end', scope: 'command'
					}
					,
					method:     'post',
					postBody:   {/literal}urlstring + 'query=true&file=index&module={$MODULE}&action={$MODULE}Ajax&ajax=true&search=true'{literal},
					onComplete: function (response) {
						jQuery ("#status").hide ();
						var result = response.responseText.split ('&#&#&#');
						jQuery ("#ListViewContents").html (result[ 2 ]);
						if (result[ 1 ] != '') {
							alert (result[ 1 ]);
						}
					}
				}
		);
		return false;
	}

	function alphabetic (module, url, dataid) {
		var i;
		for (i = 1; i <= 26; i++) {
			var data_td_id = 'alpha_' + eval (i);
			getObj (data_td_id).className = 'searchAlph';
		}
		getObj (dataid).className = 'searchAlphselected';
		$ ("status").style.display = "inline";
		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=' + module + '&action=' + module + 'Ajax&file=index&ajax=true&search=true&' + url,
					onComplete: function (response) {
						$ ("status").style.display = "none";
						var result = response.responseText.split ('&#&#&#');
						$ ("ListViewContents").innerHTML = result[ 2 ];
						if (result[ 1 ] != '') {
							alert (result[ 1 ]);
						}
						$ ('basicsearchcolumns').innerHTML = '';
					}
				}
		);
	}
{/literal}
{if $E_PAYMENTMETHOD_FORM}
{literal}
	J (document).ready (function () {
		var html = {/literal}'{$PAYMENTMETHOD_FORM}'{literal};
		OpenPaymentForm (html);
		if (J.browser.msie && (J.browser.version * 1) <= 9) {
			J ("[placeholder]").focus (function () {
				if (J (this).val () == J (this).attr ("placeholder")) {
					J (this).val ("");
				}
			}).blur (function () {
				if (J (this).val () == "") {
					J (this).val (J (this).attr ("placeholder"));
				}
			}).blur ();
			J ("[placeholder]").parents ("form").submit (function () {
				J (this).find ('[placeholder]').each (function () {
					if (J (this).val () == J (this).attr ("placeholder")) {
						J (this).val ("");
					}
				});
			});
		}

		//jQuery time
		var current_fs, next_fs, previous_fs; //fieldsets
		var left, opacity, scale; //fieldset properties which we will animate
		var animating; //flag to prevent quick multi-click glitches
		J (".next").click (function () {
			if (!validatePaymentForm (J (this).attr ("id"))) {
				return false;
			}
			if (animating) {
				return false;
			}
			animating = true;
			current_fs = J (this).parent ();
			next_fs = J (this).parent ().next ();

			//activate next step on progressbar using the index of next_fs
			J ("#progressbar li").eq (J ("fieldset").index (next_fs)).addClass ("active");
			//show the next fieldset
			next_fs.show ();
			//hide the current fieldset with style
			current_fs.animate ({ opacity: 0 }, {
				step:     function (now, mx) {
					//as the opacity of current_fs reduces to 0 - stored in "now"
					//1. scale current_fs down to 80%
					scale = 1 - (1 - now) * 0.2;
					//2. bring next_fs from the right(50%)
					left = (now * 50) + "%";
					//3. increase opacity of next_fs to 1 as it moves in
					opacity = 1 - now;
					current_fs.css ({ 'transform': 'scale(' + scale + ')' });
					next_fs.css ({ 'left': left, 'opacity': opacity });
				},
				duration: 800,
				complete: function () {
					current_fs.hide ();
					animating = false;
				},
				//this comes from the custom easing plugin
				easing:   'easeInOutBack'
			});
		});

		J (".previous").click (function () {
			if (animating) {
				return false;
			}
			animating = true;

			current_fs = J (this).parent ();
			previous_fs = J (this).parent ().prev ();

			//de-activate current step on progressbar
			J ("#progressbar li").eq (J ("fieldset").index (current_fs)).removeClass ("active");

			//show the previous fieldset
			previous_fs.show ();
			//hide the current fieldset with style
			current_fs.animate ({ opacity: 0 }, {
				step:     function (now, mx) {
					//as the opacity of current_fs reduces to 0 - stored in "now"
					//1. scale previous_fs from 80% to 100%
					scale = 0.8 + (1 - now) * 0.2;
					//2. take current_fs to the right(50%) - from 0%
					left = ((1 - now) * 50) + "%";
					//3. increase opacity of previous_fs to 1 as it moves in
					opacity = 1 - now;
					current_fs.css ({ 'left': left });
					previous_fs.css ({ 'transform': 'scale(' + scale + ')', 'opacity': opacity });
				},
				duration: 800,
				complete: function () {
					current_fs.hide ();
					animating = false;
				},
				//this comes from the custom easing plugin
				easing:   'easeInOutBack'
			});
		});

		J ('.container').css ({ 'margin-top': '10px' });
	);

	function IsNumeric (num) {
		return (num >= 0 || num < 0);
	}

	function validatePaymentForm (idstep) {
		switch (idstep) {
			case 'step_1':
				var pm = J ('#payment_method').val ();
				if (pm.match (/Bancaria/g) && J ('#banking_account').val () == '') {
					alert ('Debe ingresar un n\u00famero de cuenta!');
					return false;
				}
				if (J ('#payment_method').val () == 'PayPal' && J ('#payment_months').val () < 3) {
					alert ('Si ha seleccionado PayPal, la cantidad de meses de pago debe ser como m\u00ednimo 3 meses!');
					return false;
				}
				var nc = J ('#banking_account').val ();
				if (pm.match (/Bancaria/g) && nc.length > 30) {
					alert ('El n\u00famero de la cuenta es demasiado largo!');
					return false;
				}
				if (pm.match (/Bancaria/g) && !IsNumeric (nc)) {
					alert ('El n\u00famero de la cuenta debe ser solo n\u00fameros!');
					return false;
				}
				if (J ('#payment_months').val () > 48) {
					alert ('La cantidad m\u00e1xima de meses es 48!');
					return false;
				}
				if (J ('#payment_months').val () < 1) {
					alert ('La cantidad m\u00ednima de meses es 1!');
					return false;
				}
				if (J ('#payment_method').val () == 'PayPal') {
					J ('#rev_banking_account').html ('');
					J ('#banking_account').val ('');
				} else {
					J ('#rev_banking_account').html (' - Cuenta N&deg;: ' + J ('#banking_account').val ())
				}
				J ('#rev_payment_months').html (J ('#payment_months').val ());
				break;
			case 'step_2':
				if (J ('#accountname').val () == '') {
					alert ('El nombre de la cuenta es obligatorio!');
					return false;
				}
				if (J ('#bill_street').val () == '' || J ('#bill_street').val () == 'Direcci\u00f3n (Facturaci\u00f3n)') {
					alert ('La Direcci\u00f3n es obligatoria!');
					return false;
				}
				if (J ('#bill_pobox').val () == '' || J ('#bill_pobox').val () == 'Apdo. de Correos (Facturaci\u00f3n)') {
					alert ('El Apdo. de Correos es obligatorio!');
					return false;
				}
				if (J ('#bill_city').val () == '' || J ('#bill_city').val () == 'Poblaci\u00f3n (Facturaci\u00f3n)') {
					alert ('La Poblaci\u00f3n es obligatorio!');
					return false;
				}
				if (J ('#bill_state').val () == '' || J ('#bill_state').val () == 'Provincia (Facturaci\u00f3n)') {
					alert ('La Provincia es obligatorio!');
					return false;
				}
				if (J ('#bill_code').val () == '' || J ('#bill_code').val () == 'C\u00f3digo Postal (Facturaci\u00f3n)') {
					alert ('El C\u00f3digo Postal es obligatorio!');
					return false;
				}
				if (J ('#bill_country').val () == '' || J ('#bill_country').val () == 'Pa\u00eds (Facturaci\u00f3n)') {
					alert ('El Pa\u00eds es obligatorio!');
					return false;
				}
				J ('#rev_accountname').html (J ('#accountname').val ());
				J ('#rev_bill_street').html (J ('#bill_street').val ());
				J ('#rev_bill_pobox').html (J ('#bill_pobox').val ());
				J ('#rev_bill_city').html (J ('#bill_city').val ());
				J ('#rev_bill_state').html (J ('#bill_state').val ());
				J ('#rev_bill_code').html (J ('#bill_code').val ());
				J ('#rev_bill_country').html (J ('#bill_country').val ());
				break;
		}
		return true;
	}

	function changePayMethod (val) {
		if (val != 'PayPal') {
			J ('.formadomicila').show ();
			J ('#payment_months').val (1);
		} else {
			J ('.formadomicila').hide ();
			J ('#payment_months').val (3);
		}

		//update importe total
		J ('.rev_unit_price').html (J ('#payment_months').val () * J ('#importe_plan').val () + ' &euro;');
	}
{/literal}
{/if}
</script>
{* Bloque de Notificaciones *}
{if (!empty ($NOTIFICATIONS))}
	{foreach $NOTIFICATIONS as $notification}
<div class="alert alert-dismissable notification{if ($notification@iteration > 1)} hidden{/if}" data-id="{$notification.notifyid}" style="background-color: #ffffff;">
	<button type="button" class="close notification-close" data-dismiss="alert" aria-label="close">&times;</button>
	<div>{$notification.design|unescape:"html"}</div>
</div>
	{/foreach}
<script type="text/javascript">
{literal}
	(function (jQuery) {
		jQuery ('.notification').on ('closed.bs.alert', function () {
			var notificationId = jQuery (this).attr ('data-id'),
				arguments      = [
					'module=notifymanager',
					'action=Disable',
					'record=' + encodeURIComponent (notificationId)
				];
			jQuery.ajax ('index.php', {
				data:     arguments.join ('&'),
				dataType: 'text',
				method:   'post'
			}).done (function () {
				jQuery ('.notification.hidden:first').removeClass ('hidden');
			});
		});
	} (jQuery));
{/literal}
</script>
{/if}
{include file='Buttons_List.tpl'}
<div id="ListViewContents">
{include file="modules/rssnews/ListViewEntries.tpl"}
</div>
<div id="massedit" class="layerPopup" style="display:none;width:80%;">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine">
		<tr>
			<td class="layerPopupHeading" align="left" width="60%">{$APP.LBL_MASSEDIT_FORM_HEADER}</td>
			<td>&nbsp;</td>
			<td align="right" width="40%">
				<img onClick="fninvsh('massedit');" title="{$APP.LBL_CLOSE}" alt="{$APP.LBL_CLOSE}" style="cursor:pointer;" src="{'close.gif'|@vtiger_imageurl:$THEME}" align="absmiddle" border="0">
			</td>
		</tr>
	</table>
	<div id="massedit_form_div"></div>
</div>
<script type="text/javascript">
{literal}
	function ajaxChangeStatus (statusname) {
		$ ("status").style.display = "inline";
		var viewid = document.getElementById ('viewname').options[ document.getElementById ('viewname').options.selectedIndex ].value,
			idstring = document.getElementById ('idlist').value,
			searchurl = document.getElementById ('search_url').value,
			tplstart = '&',
			url, urlstring;
		if (gstart != '') {
			tplstart = tplstart + gstart;
		}
		if (statusname == 'status') {
			fninvsh ('changestatus');
			url = '&leadval=' + document.getElementById ('lead_status').options[ document.getElementById ('lead_status').options.selectedIndex ].value;
			urlstring = "module=Users&action=updateLeadDBStatus&return_module=Leads" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
		}
		else if (statusname == 'owner') {
			if ($ ("user_checkbox").checked) {
				fninvsh ('changeowner');
				url = '&owner_id=' + document.getElementById ('lead_owner').options[ document.getElementById ('lead_owner').options.selectedIndex ].value;
				urlstring = {/literal}"module=Users&action=updateLeadDBStatus&return_module={$MODULE}" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl{literal};
			} else {
				fninvsh ('changeowner');
				url = '&owner_id=' + document.getElementById ('lead_group_owner').options[ document.getElementById ('lead_group_owner').options.selectedIndex ].value;
				urlstring = {/literal}"module=Users&action=updateLeadDBStatus&return_module={$MODULE}" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl{literal};
			}
		}
		new Ajax.Request (
			'index.php',
			{
				queue:      { position: 'end', scope: 'command' },
				method:     'post',
				postBody:   urlstring,
				onComplete: function (response) {
					$ ("status").style.display = "none";
					var result = response.responseText.split ('&#&#&#');
					$ ("ListViewContents").innerHTML = result[ 2 ];
					if (result[ 1 ] != '') {
						alert (result[ 1 ]);
					}
					$ ('basicsearchcolumns').innerHTML = '';
				}
			}
		);
	}
{/literal}
</script>
{if $MENSAJE neq ''}
<script type="text/javascript">
{literal}
	(function () {
		// create the notification
		var notification = new NotificationFx ({
			message: {/literal}'<span class="icon fa fa-exclamation-circle fa-2x"></span><p>{$MENSAJE}</p>'{literal},
			layout:                            'bar',
			effect:                            'slidetop',
			type: {/literal}{if $TIPO_MENSAJE EQ 'fail'}'error'{else}'success'{/if},{literal}
			onClose:                           function () {}
		});

		// show the notification
		notification.show ();
	}) ();
{/literal}
</script>
{/if}
<script type="text/javascript">
{$BUILD_SEARCH}
</script>
{/strip}