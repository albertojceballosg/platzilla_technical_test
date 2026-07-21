function asignarDatosRespuesta(asunto,conversacionid,accountid,ticketid,contactid) {
	document.getElementById('subject').value = 'Re:'+asunto;
	document.getElementById('conversacionid').value = conversacionid;
	if (document.getElementById('accountid')) {
		document.getElementById('accountid').value = accountid;
		actualizarContactos(accountid,contactid);
	}
	if (document.getElementById('ticketid'))
		document.getElementById('ticketid').value = ticketid;
}

function actualizarContactos(valor,contactid) {
	new Ajax.Request(
		'index.php',
		{
			queue: {
				position: 'end', 
				scope: 'command'
			},
			method: 'post',
			postBody: 'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=ConsultarContactos&accountid='+valor+'&contactid='+contactid,
			onComplete: function(response) {
				ctrl = document.getElementById('listaContactos');
				ctrl.innerHTML = response.responseText;
			}
		});
}

function actualizarListaPaginas(id,funcion,regInicial) {
	var url = 'module=notificaciones&action=ActivityAjax&ajax=true&Funcion='+funcion+'&regInicial='+regInicial+'&ticketid='+jQuery('#ticketid').val();
	actualizarLista(id,url);
}

function actualizarListaSegunFiltro(id,funcion,panel) {
	var filterStatus = '';
	
	if (panel != 'sent')
		filterStatus = jQuery('#filterStatus'+panel).val();
	
	var filterModule = jQuery('#filterModule'+panel).val();
	var filterRegister = jQuery('#filterRegister'+panel).val();
	var url = 'module=notificaciones&action=ActivityAjax&ajax=true&Funcion='+funcion+'&regInicial=1&filtro='+filterStatus+'&filtermodule='+filterModule+'&filterregister='+filterRegister;
	actualizarLista(id,url);
}

function actualizarLista(id,url) {
	new Ajax.Request(
		'index.php',
		{
			queue: {
				position: 'end', 
				scope: 'command'
			},
			method: 'post',
			postBody: url,
			onComplete: function(response) {
				ctrl = document.getElementById(id);
				ctrl.innerHTML = response.responseText;
			}
		});
}

var iNumRows = -1;

function agregarDocumentacionNotificacion(id) {

	ctrlTable = document.getElementById(id);
	if (ctrlTable) {
		if (iNumRows == -1)
			iNumRows = (ctrlTable.rows.length);
		else
			iNumRows++;
			
		var row=ctrlTable.insertRow(ctrlTable.rows.length);
		var x1=row.insertCell(0);
		var x2=row.insertCell(1);
		row.id = 'row'+iNumRows;
		x1.innerHTML='<input type="file" id="file'+iNumRows+'" name="file[]" />';
		x2.innerHTML='';
		x1.className = 'crmTableRow small lineOnTop';
		x2.className = 'crmTableRow small lineOnTop';
	}
}

function cargarDetalleCliente(conversacionid,notificacionid) {
	ctrlRow = document.getElementById('rowCli_'+conversacionid);
	new Ajax.Request(
		'index.php',
		{
			queue: {
				position: 'end', 
				scope: 'command'
			},
			method: 'post',
			postBody: 'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=CargarDetalleCliente&conversacionid='+conversacionid+'&notificacionid='+notificacionid,
			onComplete: function(response) {
				
				ctrl = document.getElementById('detalleNotificacion');
				ctrl.innerHTML = response.responseText;
				if (ctrlRow) {
					var previousFont = ctrlRow.style.fontWeight;
					ctrlRow.style.fontWeight = 'normal';
					if (previousFont == 'bold') {
						jQuery('#pendingNotifications').html(parseInt(jQuery('#pendingNotifications').html())-1);
						if (parseInt(jQuery('#pendingNotifications').html()) == 0)
							jQuery('#pendingNotifications').hide();
					}
				}
				window.location = '#noti'+notificacionid;
			}
		});
}

function cargarDetalle(conversacionid,notificacionid) {
	ctrlRow = document.getElementById('row_'+conversacionid);
	ctrl = document.getElementById('detalleNotificacion');
	ctrl.innerHTML = '';
	new Ajax.Request(
		'index.php',
		{
			queue: {
				position: 'end', 
				scope: 'command'
			},
			method: 'post',
			postBody: 'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=CargarDetalle&conversacionid='+conversacionid+'&notificacionid='+notificacionid,
			onComplete: function(response) {
				ctrl = document.getElementById('detalleNotificacion');
				jQuery('#conversacionid').val(conversacionid);
				jQuery('#notificacionid').val(notificacionid);
				ctrl.innerHTML = response.responseText;
				if (ctrlRow)
					ctrlRow.style.fontWeight = 'normal';

				window.location = '#noti'+notificacionid;
			}
		});
}

function notificationFrmValidate() {
	var ret = true, ret2 = false;
	var sAlert = '';
	jQuery('#TextoMensaje').val(jQuery('#editor').html());
	if (document.getElementById('accountid')) {
		jQuery('#formanotificacion input[type="checkbox"]').each(function(i) {
			if (jQuery(this).prop('checked'))
				ret2 |= true;
		});

		if (!ret2) {
			ret = false;
			sAlert += "* Debe seleccionar al menos un contacto\n";
		}
	}
	
	
	if (jQuery.trim(jQuery('#subject').val()) == '') {
		ret = false;
		sAlert += "* Debe introducir un asunto para el mensaje\n";
	}
	
	if (!ret)
		alert(sAlert);
	
	return ret;
}

function resetNotificationForm() {
	jQuery('#accountid').val('');
	actualizarContactos('');
	jQuery('#subject').val('');
	CKEDITOR.instances['TextoMensaje'].setData('');
}

function cargarFormaNotificacion() {
	jQuery('#formaNotificacion').html('');
	
	url = 'index.php?module=notificaciones&action=ActivityAjax&ajax=true&Funcion=EnviarNotificacion';
	
	if (jQuery('#conversacionid').val())
		url+= '&conversacionid='+jQuery('#conversacionid').val();
		
	if (jQuery('#notificacionid').val())
		url+= '&notificacionid='+jQuery('#notificacionid').val();
	
	if (jQuery('#accountid').val())
		url+= '&accountid='+jQuery('#accountid').val();
		
	if (jQuery('#ticketid').val())
		url+= '&ticketid='+jQuery('#ticketid').val();
		
	if (jQuery('#subject').val())
		url+= '&subject=Re:'+jQuery('#subject').val();
	
	jQuery.ajax({
			method: "POST",
			url: url,
		})
		.done(function( result ) {
			jQuery('#formaNotificacion').html(result);
		});
	
}

/* [ TT11375 ] Notificaciones para “Mi Cuenta en Platzilla” - Pedidos Información Johana Romero 11/10/2016 */

function saveOptionsMail(){
	var arr_check = [];
	var arr_uncheck = [];
	jQuery.each(jQuery('input[name=chek_status]:checked', table.fnGetNodes()), function(key,value){
		arr_check.push(jQuery(value).attr('id'))
	});
	
	jQuery.each(jQuery('input[name=chek_status]', table.fnGetNodes()).not(':checked'), function(key,value){
		arr_uncheck.push(jQuery(value).attr('id'))
	});
		
	new Ajax.Request(
	'index.php',
	{
		queue: {
			position: 'end', 
			scope: 'command'
		},
		method: 'post',
		postBody: 'module=Home&action=HomeAjax&ajax=true&file=SaveOpcEmail&opciones='+arr_check+'&notCheck='+arr_uncheck,
		onComplete: function(response) {
			location.reload();
		}
	});
}

jQuery(document).ready(function() {
		table = jQuery('#emails_conf').dataTable({
			'info': false,
			'sDom': 'lTfr<"clearfix">tip',
			"bSort": false,
			'oTableTools': {
	            'aButtons': 
	                {
	                    "bHeader" : false
	                }
	        },
			"oLanguage": {
			  	"sProcessing":     "Procesando...",
			    "sLengthMenu":     "Mostrar _MENU_ registros",
			    "sZeroRecords":    "No se encontraron resultados",
			    "sEmptyTable":     "Ningún dato disponible en esta tabla",
			    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
			    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
			    "sInfoPostFix":    "",
			    "sSearch":         "Buscar:",
			    "sUrl":            "",
			    "sInfoThousands":  ",",
			    "sLoadingRecords": "Cargando...",
			    "oPaginate": {
			        "sFirst":    "Primero",
			        "sLast":     "Último",
			        "sNext":     "Siguiente",
			        "sPrevious": "Anterior"
			    },
			    "oAria": {
			        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
			        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
			    }
			}
		});		
	})