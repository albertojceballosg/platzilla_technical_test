// JavaScript Document

function confirmarTicket(t){
	jQuery.post('index.php?module=Calendar&action=AJAX_confirmar&Ajax=true',{'t':t},procesarConfirmarTicket,'json');
}
function procesarConfirmarTicket (r){
	if (r.success){		
		jQuery('.adsdiv_'+r.t).hide('slow');
		jQuery('.event.reg_'+r.t).css( 'opacity', '1'  );
	}	
}

function deleteActivity(t,title){
	if (confirm("Esta seguro que desea eliminar la siguiente tarea?\n'"+title+"'")) {
        jQuery.post('index.php?module=Calendar&action=AJAX_deleteActivity&Ajax=true',{'t':t},procesarDeleteActivity,'json');
	}
}
function procesarDeleteActivity (r){
	if (r.success){
		location.reload();
	} else {
		alert('Error: ' + (r.error || 'No se pudo eliminar la tarea'));
	}
}