/**
 * Javascript functions for related lists.
 * @author Etienne Gómez (EGC)
 * @copyright Copyright (c) 2013, Timemanagement_
 * @version 1.0 17/11/2013 02:46:32
 * @filesource
 */

function loadRelatedListBlock(urldata,target,imagesuffix,obj) {
	if($('return_module') && $('return_module').value == 'Campaigns'){
		var selectallActivation = $(imagesuffix+'_selectallActivate').value;
		var excludedRecords = $(imagesuffix+'_excludedRecords').value = $(imagesuffix+'_excludedRecords').value;
		var numofRows = $(imagesuffix+'_numOfRows').value;
	}
    var elem = document.getElementById(target),
        typeData = elem.dataset.type;
    if (typeData === 'card') {
        if (obj !== undefined){
            var order = obj.getAttribute('rel');
            if (order === 'ASC') {
                obj.setAttribute('rel', 'DESC')
            } else {
                obj.setAttribute('rel', 'ASC')
            }
            urldata = urldata.replace("ASC", order);
        }

        urldata += '&type=card&relation_id=' + elem.dataset.relation + '&actions=' + elem.dataset.action;
    }

	var showdata = 'show_'+imagesuffix;
	var showdata_element = $(showdata);

	var hidedata = 'hide_'+imagesuffix;
	var hidedata_element = $(hidedata);
	
	var delete_element = $('delete_'+imagesuffix);
	
	var data_el=jQuery('#loading_'+imagesuffix);
	if(isRelatedListBlockLoaded(target,urldata) == true){
		$(target).show();
		if (data_el)
			data_el.hide();
		if (delete_element)
			delete_element.show();
		return;
	}
	var indicator = 'indicator_'+imagesuffix;
	var indicator_element = $(indicator);
	
	if (indicator_element)
		indicator_element.show();
	if (delete_element)
		delete_element.show();
	data_el.show();
	var target_element = $(target);
	
	if ($('platdb')) {
		var platdb = $('platdb').value;
		
		if (platdb != '') {
			urldata+= '&platdb='+platdb;
		}
	}

	new Ajax.Request(
		'index.php',
        {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: urldata,
                onComplete: function(response) {
					var responseData = trim(response.responseText);
      				target_element.update(responseData);
					target_element.show();
					if (showdata_element)
						showdata_element.hide();
					if (hidedata_element)
						hidedata_element.show();
					if (indicator_element)
						indicator_element.hide();
					if (data_el)
						data_el.hide();
					if($('return_module').value == 'Campaigns'){
						var obj = document.getElementsByName(imagesuffix+'_selected_id');
						var relatedModule = imagesuffix.replace('Campaigns_',"");
						$(relatedModule+'_count').innerHTML = numofRows;
						if(selectallActivation == 'true'){
							$(imagesuffix+'_selectallActivate').value='true';
							$(imagesuffix+'_linkForSelectAll').show();
							$(imagesuffix+'_selectAllRec').style.display='none';
							$(imagesuffix+'_deSelectAllRec').style.display='inline';
							var exculdedArray=excludedRecords.split(';');
							if (obj) {
								var viewForSelectLink = showSelectAllLink(obj,exculdedArray);
								$(imagesuffix+'_selectCurrentPageRec').checked = viewForSelectLink;
								$(imagesuffix+'_excludedRecords').value = $(imagesuffix+'_excludedRecords').value+excludedRecords;
							}
						}else{
							$(imagesuffix+'_linkForSelectAll').hide();
							rel_toggleSelect(false,imagesuffix+'_selected_id',relatedModule);
						}
						updateParentCheckbox(obj,imagesuffix);
					}
				}
        }
	);
}

function isRelatedListBlockLoaded(id,urldata){
	var elem = document.getElementById(id);
	if(elem == null || typeof elem == 'undefined' || urldata.indexOf('order_by') != -1 ||
		urldata.indexOf('start') != -1 || urldata.indexOf('withCount') != -1){
		return false;
	}
	var tables = elem.getElementsByTagName('table');
	return tables.length > 0;
}

function hideRelatedListBlock(target, imagesuffix) {
	
	var showdata = 'show_'+imagesuffix;
	var showdata_element = $(showdata);
	
	var hidedata = 'hide_'+imagesuffix;
	var hidedata_element = $(hidedata);
	
	var target_element = $(target);
	if(target_element){
		target_element.hide();
	}
	hidedata_element.hide();
	showdata_element.show();
	$('delete_'+imagesuffix).hide();
}

function disableRelatedListBlock(urldata,target,imagesuffix){
	var showdata = 'show_'+imagesuffix;
	var showdata_element = $(showdata);

	var hidedata = 'hide_'+imagesuffix;
	var hidedata_element = $(hidedata);

	var indicator = 'indicator_'+imagesuffix;
	var indicator_element = $(indicator);
	indicator_element.show();
	
	var target_element = $(target);
	new Ajax.Request(
		'index.php',
        {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: urldata,
                onComplete: function(response) {
					var responseData = trim(response.responseText);
					target_element.hide();
					$('delete_'+imagesuffix).hide();
      				hidedata_element.hide();
					showdata_element.show();
      				indicator_element.hide();
				}
        }
	);
}

function VT_disableFormSubmit(evt) {
    var evt = (evt) ? evt : ((event) ? event : null);
    var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
    if ((evt.keyCode == 13) && (node.type=='text')) {
        node.onchange();
        return false;
    }
    return true;
}