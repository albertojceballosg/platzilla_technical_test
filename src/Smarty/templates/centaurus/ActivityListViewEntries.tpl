{extends file="base/BaseList.tpl"}
{block name="js"}
<script type="text/javascript" src="include/js/general.js"></script>
{/block}
{block name="scripts"}
<script type="text/javascript">
	var typeofdata = [];
	typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
	typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
	typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
	typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
	typeofdata[ 'T' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
	typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
	typeofdata[ 'C' ] = [ 'e', 'n' ];
	typeofdata[ 'DT' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
	typeofdata[ 'D' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
	var fLabels = [];
	fLabels[ 'e' ] = "{$APP.is}";
	fLabels[ 'n' ] = "{$APP.is_not}";
	fLabels[ 's' ] = "{$APP.begins_with}";
	fLabels[ 'ew' ] = "{$APP.ends_with}";
	fLabels[ 'c' ] = "{$APP.contains}";
	fLabels[ 'k' ] = "{$APP.does_not_contains}";
	fLabels[ 'l' ] = "{$APP.less_than}";
	fLabels[ 'g' ] = "{$APP.greater_than}";
	fLabels[ 'm' ] = "{$APP.less_or_equal}";
	fLabels[ 'h' ] = "{$APP.greater_or_equal}";
	var noneLabel;

	function trimfValues (value) {
		var string_array;
		string_array = value.split (":");
		return string_array[ 4 ];
	}

	function updatefOptions (sel, opSelName) {
		var selObj = document.getElementById (opSelName);
		var fieldtype = null;

		var currOption = selObj.options[ selObj.selectedIndex ];
		var currField = sel.options[ sel.selectedIndex ];

		var fld = currField.value.split (":");
		var tod = fld[ 4 ];
		if (currField.value != null && currField.value.length != 0) {
			fieldtype = trimfValues (currField.value);
			fieldtype = fieldtype.replace (/\\'/g, '');
			ops = typeofdata[ fieldtype ];
			var off = 0;
			if (ops != null) {

				var nMaxVal = selObj.length;
				for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
					selObj.remove (0);
				}
				for (var i = 0; i < ops.length; i++) {
					var label = fLabels[ ops[ i ] ];
					if (label == null) {
						continue;
					}
					var option = new Option (fLabels[ ops[ i ] ], ops[ i ]);
					selObj.options[ i ] = option;
					if (currOption != null && currOption.value == option.value) {
						option.selected = true;
					}
				}
			}
		} else {
			var nMaxVal = selObj.length;
			for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
				selObj.remove (0);
			}
			selObj.options[ 0 ] = new Option ('None', '');
			if (currField.value == '') {
				selObj.options[ 0 ].selected = true;
			}
		}
	}

	function checkgroup () {
		if (document.change_ownerform_name.user_lead_owner[ 1 ].checked) {
			document.change_ownerform_name.lead_group_owner.style.display = "block";
			document.change_ownerform_name.lead_owner.style.display = "none";
		} else {
			document.change_ownerform_name.lead_owner.style.display = "block";
			document.change_ownerform_name.lead_group_owner.style.display = "none";
		}
	}

	{* [ TT11387 ] Correcciones del Calendario - Jesus A. - Se Actualiza la funci�n*}
	function callSearch (searchtype) {
		for (i = 1; i <= 26; i++) {
			var data_td_id = 'alpha_' + eval (i);
			/*getObj(data_td_id).className = 'searchAlph';*/
		}
		gPopupAlphaSearchUrl = '';
		/*search_fld_val= $('bas_searchfield').options[$('bas_searchfield').selectedIndex].value;
		 search_txt_val= encodeURIComponent(document.basicSearch.search_text.value);*/
		search_fld_val = jQuery ('input[name=search_field]:checked').val ();
		search_txt_val = encodeURIComponent (jQuery ('#search_text').val ());
		var urlstring = '';
		if (searchtype == 'Basic') {
			var p_tab = document.getElementsByName ("parenttab");
			urlstring = 'search_field=' + search_fld_val + '&searchtype=BasicSearch&search_text=' + search_txt_val + '&';
			urlstring = urlstring + 'parenttab=' + p_tab[ 0 ].value + '&';
		}
		else if (searchtype == 'Advanced') {
			checkAdvancedFilter ();
			var advft_criteria = $ ('advft_criteria').value;
			var advft_criteria_groups = $ ('advft_criteria_groups').value;
			urlstring += '&advft_criteria=' + advft_criteria + '&advft_criteria_groups=' + advft_criteria_groups + '&';
			urlstring += 'searchtype=advance&'
		}
		jQuery ("#status").show ();
		new Ajax.Request (
			'index.php',
			{
				queue:      { position: 'end', scope: 'command' },
				method:     'post',
				postBody:   urlstring + 'query=true&file=index&module={$MODULE}&action={$MODULE}Ajax&ajax=true&search=true',
				onComplete: function (response) {
					jQuery ("#status").hide ();
					result = response.responseText.split ('&#&#&#');
					jQuery ("#ListViewContents").html (result[ 2 ]);
					if (result[ 1 ] != '') {
						alert (result[ 1 ]);
					}
				}
			}
		);
		return false
	}

	function alphabetic (module, url, dataid) {
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
				postBody:   'module=' + module + '&action=' + module + 'Ajax&file=ListView&ajax=true&search=true&' + url,
				onComplete: function (response) {
					$ ("status").style.display = "none";
					result = response.responseText.split ('&#&#&#');
					$ ("ListViewContents").innerHTML = result[ 2 ];
					if (result[ 1 ] != '') {
						alert (result[ 1 ]);
					}
					$ ('basicsearchcolumns').innerHTML = '';
				}
			}
		);
	}

	function viewSearch () {
		if (!jQuery ("#divsearch").is (':visible')) {
			jQuery ("#imgsearch").removeClass ("fa-search-plus");
			jQuery ("#imgsearch").addClass ("fa-search-minus");
			jQuery ("#divsearch").show ();
		} else {
			jQuery ("#imgsearch").removeClass ("fa-search-minus");
			jQuery ("#imgsearch").addClass ("fa-search-plus");
			jQuery ("#divsearch").hide ();
		}
	}

	function ajaxChangeStatus (statusname) {
		$ ("status").style.display = "inline";
		var viewid = document.massdelete.viewname.value;
		var excludedRecords = document.getElementById ("excludedRecords").value;
		var idstring = document.getElementById ('allselectedboxes').value;
		if (statusname == 'status') {
			fninvsh ('changestatus');
			var url = '&leadval=' + document.getElementById ('lead_status').options[ document.getElementById ('lead_status').options.selectedIndex ].value;
			var urlstring = "module=Users&action=updateLeadDBStatus&return_module=Leads" + url + "&viewname=" + viewid + "&idlist=" + idstring + "&excludedRecords=" + excludedRecords;
		} else if (statusname == 'owner') {
			if ($ ("user_checkbox").checked) {
				fninvsh ('changeowner');
				var url = '&owner_id=' + document.getElementById ('lead_owner').options[ document.getElementById ('lead_owner').options.selectedIndex ].value + '&owner_type=User';
				var urlstring = "module=Users&action=updateLeadDBStatus&return_module={$MODULE}" + url + "&viewname=" + viewid + "&idlist=" + idstring + "&excludedRecords=" + excludedRecords;
			} else {
				fninvsh ('changeowner');
				var url = '&owner_id=' + document.getElementById ('lead_group_owner').options[ document.getElementById ('lead_group_owner').options.selectedIndex ].value + '&owner_type=Group';
				var urlstring = "module=Users&action=updateLeadDBStatus&return_module={$MODULE}" + url + "&viewname=" + viewid + "&idlist=" + idstring + "&excludedRecords=" + excludedRecords;
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
					result = response.responseText.split ('&#&#&#');
					$ ("ListViewContents").innerHTML = result[ 2 ];
					if (result[ 1 ] != '') {
						alert (result[ 1 ]);
					}
					$ ('basicsearchcolumns').innerHTML = '';
				}
			}
		);
	}
</script>
{/block}