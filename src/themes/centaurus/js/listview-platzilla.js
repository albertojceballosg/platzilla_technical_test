// JS migrado desde ListView.tpl
// (Agrega aquí los scripts migrados de la plantilla)

// --- Filtros y opciones de búsqueda ---
/**
 * Objeto que almacena los tipos de datos y sus respectivas opciones de filtro.
 * @type {Object}
 */
var typeofdata = {
    'C': [ 'e', 'n' ],
    'D': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
    'DT': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
    'E': [ 'e', 'n', 's', 'ew', 'c', 'k' ],
    'I': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
    'N': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
    'NN': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
    'T': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
    'V': [ 'e', 'n', 's', 'ew', 'c', 'k' ]
};

/**
 * Objeto que almacena las etiquetas de los filtros.
 * @type {Object}
 */
var fLabels = {
    'c':  window.APP ? window.APP.contains : 'contains',
    'e':  window.APP ? window.APP.is : 'is',
    'ew': window.APP ? window.APP.ends_with : 'ends_with',
    'g':  window.APP ? window.APP.greater_than : 'greater_than',
    'h':  window.APP ? window.APP.greater_or_equal : 'greater_or_equal',
    'k':  window.APP ? window.APP.does_not_contains : 'does_not_contains',
    'l':  window.APP ? window.APP.less_than : 'less_than',
    'm':  window.APP ? window.APP.less_or_equal : 'less_or_equal',
    'n':  window.APP ? window.APP.is_not : 'is_not',
    's':  window.APP ? window.APP.begins_with : 'begins_with'
};

/**
 * Función que elimina los espacios en blanco de un valor.
 * @param {string} value - El valor a tratar.
 * @returns {string} El valor sin espacios en blanco.
 */
function trimfValues (value) {
    var string_array = value.split(":");
    return string_array[4];
}

/**
 * Función que actualiza las opciones de filtro en función del campo seleccionado.
 * @param {HTMLElement} sel - El elemento de selección.
 * @param {string} opSelName - El nombre del elemento de selección de opciones.
 */
function updatefOptions(sel, opSelName) {
    var selObj = document.getElementById(opSelName),
        fieldtype = null,
        currOption = selObj.options[selObj.selectedIndex],
        currField = sel.options[sel.selectedIndex],
        ops, nMaxVal, nLoop;

    if (currField.value != null && currField.value.length != 0) {
        fieldtype = trimfValues(currField.value);
        fieldtype = fieldtype.replace(/\\'/g, '');
        ops = typeofdata[fieldtype];
        if (ops != null) {
            nMaxVal = selObj.length;
            for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                selObj.remove(0);
            }
            for (var i = 0; i < ops.length; i++) {
                var label = fLabels[ops[i]];
                if (label == null) {
                    continue;
                }
                var option = new Option(fLabels[ops[i]], ops[i]);
                selObj.options[i] = option;
                if (currOption != null && currOption.value == option.value) {
                    option.selected = true;
                }
            }
        }
    } else {
        nMaxVal = selObj.length;
        for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
            selObj.remove(0);
        }
        selObj.options[0] = new Option('None', '');
        if (currField.value == '') {
            selObj.options[0].selected = true;
        }
    }
}

// --- Grupo y búsqueda avanzada ---
/**
 * Función que verifica si el checkbox de grupo está seleccionado.
 */
function checkgroup () {
    if ($("group_checkbox").checked) {
        document['change_ownerform_name']['lead_group_owner'].style.display = "block";
        document['change_ownerform_name']['lead_owner'].style.display = "none";
    } else {
        document['change_ownerform_name']['lead_owner'].style.display = "block";
        document['change_ownerform_name']['lead_group_owner'].style.display = "none";
    }
}

/**
 * Función que realiza la búsqueda avanzada.
 * @param {string} searchtype - El tipo de búsqueda (Basic o Advanced).
 */
function callSearch (searchtype) {
    var search_fld_val = jQuery('input[name=search_field]:checked').val(),
        search_txt_val = encodeURIComponent(jQuery('#search_text').val()),
        urlstring = '',
        p_tab, advft_criteria, advft_criteria_groups;

    if (searchtype == 'Basic') {
        p_tab = document.getElementsByName("parenttab");
        urlstring = 'search_field=' + search_fld_val + '&searchtype=BasicSearch&search_text=' + search_txt_val + '&';
        urlstring = urlstring + 'parenttab=' + p_tab[0].value + '&';
    } else if (searchtype == 'Advanced') {
        checkAdvancedFilter();
        advft_criteria = $("advft_criteria").value;
        advft_criteria_groups = $("advft_criteria_groups").value;
        urlstring += '&advft_criteria=' + advft_criteria + '&advft_criteria_groups=' + advft_criteria_groups + '&';
        urlstring += 'searchtype=advance&';
    }
    jQuery("#status").show();

    new Ajax.Request('index.php', {
        queue: { position: 'end', scope: 'command' },
        method: 'post',
        postBody: urlstring + 'query=true&file=index&module=' + window.MODULE + '&action=' + window.MODULE + 'Ajax&ajax=true&search=true',
        onComplete: function (response) {
            var result;
            jQuery("#status").hide();
            result = response.responseText.split('&#&#&#');
            jQuery("#ListViewContents").html(result[2]);
            if (result[1] != '') {
                alert(result[1]);
            }
            $("basicsearchcolumns").innerHTML = '';
        }
    });
    return false;
}

/**
 * Función que realiza la búsqueda alfabética.
 * @param {string} module - El módulo.
 * @param {string} url - La URL.
 * @param {string} dataid - El ID de los datos.
 */
function alphabetic(module, url, dataid) {
    var i, data_td_id;

    for (i = 1; i <= 26; i++) {
        data_td_id = 'alpha_' + eval(i);
        getObj(data_td_id).className = 'searchAlph';
    }
    getObj(dataid).className = 'searchAlphselected';
    $("status").style.display = "inline";

    new Ajax.Request('index.php', {
        queue: { position: 'end', scope: 'command' },
        method: 'post',
        postBody: 'module=' + module + '&action=' + module + 'Ajax&file=index&ajax=true&search=true&' + url,
        onComplete: function (response) {
            var result;
            $("status").style.display = "none";
            result = response.responseText.split('&#&#&#');
            $("ListViewContents").innerHTML = result[2];
            if (result[1] != '') {
                alert(result[1]);
            }
            $("basicsearchcolumns").innerHTML = '';
        }
    });
}

// --- Cambio de estado por AJAX ---
/**
 * Función que cambia el estado de un registro mediante AJAX.
 * @param {string} statusname - El nombre del estado.
 */
function ajaxChangeStatus(statusname) {
    var viewid, idstring, searchurl, tplstart, url, urlstring;
    $("status").style.display = "inline";
    viewid = document.getElementById('viewname').options[document.getElementById('viewname').options.selectedIndex].value;
    idstring = document.getElementById('idlist').value;
    searchurl = document.getElementById('search_url').value;
    tplstart = '&';
    if (typeof gstart !== 'undefined' && gstart != '') {
        tplstart = tplstart + gstart;
    }
    if (statusname == 'status') {
        fninvsh('changestatus');
        url = '&leadval=' + document.getElementById('lead_status').options[document.getElementById('lead_status').options.selectedIndex].value;
        urlstring = "module=Users&action=updateLeadDBStatus&return_module=Leads" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
    } else if (statusname == 'owner') {
        if ($("user_checkbox").checked) {
            fninvsh('changeowner');
            url = '&owner_id=' + document.getElementById('lead_owner').options[document.getElementById('lead_owner').options.selectedIndex].value;
            urlstring = "module=Users&action=updateLeadDBStatus&return_module=" + window.MODULE + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
        } else {
            fninvsh('changeowner');
            url = '&owner_id=' + document.getElementById('lead_group_owner').options[document.getElementById('lead_group_owner').options.selectedIndex].value;
            urlstring = "module=Users&action=updateLeadDBStatus&return_module=" + window.MODULE + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
        }
    }
    new Ajax.Request('index.php', {
        queue: { position: 'end', scope: 'command' },
        method: 'post',
        postBody: urlstring,
        onComplete: function (response) {
            var result;
            $("status").style.display = "none";
            result = response.responseText.split('&#&#&#');
            $("ListViewContents").innerHTML = result[2];
            if (result[1] != '') {
                alert(result[1]);
            }
            $("basicsearchcolumns").innerHTML = '';
        }
    });
}

// --- Notas ---
// - Migrado desde ListView.tpl el 21/05/2025.
// - Revisar dependencias de variables Smarty/JS globales.
// - Validar funcionamiento tras la migración.
