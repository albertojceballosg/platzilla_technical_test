<?php

include_once('modules/todotasks/todotasks.php');

global $adb,$app_strings;


	 $queryProyectos = "Select * from vtiger_proyectos p join vtiger_crmentity crm on (crm.crmid = proyectosid ) where deleted = 0";

	 $resultProyectos = $adb->pquery($queryProyectos);

	while ($row = $adb->fetchByAssoc($resultProyectos)) {

		$hitos = array();

		$sql = "SELECT h.hitoid,name,hitostate,hcf.* , 
			(select count(*) from vtiger_troubletickets t join vtiger_crmentity crm on (t.ticketid = crm.crmid) 
				where hitoid = h.hitoid and proyectoid = h.proyectosid  and deleted = 0 ) as totaltickets,
			(select count(*) from vtiger_troubletickets t join vtiger_crmentity crm on (t.ticketid = crm.crmid) 
				where hitoid = h.hitoid and proyectoid = h.proyectosid  and deleted = 0 and t.status = 'TICKET_COMPLETED') as ticketscompletados
			FROM vtiger_hito h join vtiger_hitocf hcf on (hcf.hitoid = h.hitoid) 
			join vtiger_crmentity crm on (crm.crmid = h.hitoid) 
			where crm.deleted = 0  and h.proyectosid =? ";

			$pruebaproyecto = $row['proyectosid'];
	

		$result = $adb->pquery($sql,array($pruebaproyecto));

		while ($reg = $adb->fetchByAssoc($result)) {
			$hito = array();
			$hito['id'] = $reg['hitoid'];
			$hito['name'] = $reg['name'];
			$hito['state'] = $reg['hitostate'];
			$hito['progreso'] = $reg['ticketscompletados'] / $reg['totaltickets'] * 100;

			// consultando los HelpDesk de cada hito 
			$sqlHelpDeksk = "SELECT t.ticketid,t.ticket_no,t.status,title ,crm.smownerid,
					t.customerdescription,t.start_date,t.end_estimated_date
					FROM `vtiger_troubletickets` t 
					join vtiger_crmentity crm on (crm.crmid = t.ticketid) 
					WHERE hitoid = ? and deleted = 0 ";
					$result2 = $adb->pquery($sqlHelpDeksk,array($reg['hitoid']));

			$tickets = array();
			while ($row = $adb->fetchByAssoc($result2)) {
				$ticket = array();
				$ticket['ticketid'] = $row['ticketid'];
				$ticket['ticket_no'] = $row['ticket_no'];
				$ticket['status'] = ($row['status'] == 'TICKET_COMPLETED') ? 1 : '';
				$ticket['title'] = $row['title'];
				$ticket['customerdescription'] = $row['customerdescription'];
				$ticket['start_date'] = $row['start_date'];
				$ticket['end_estimated_date'] = $row['end_estimated_date'];
				$ticket['smownerid'] = $row['smownerid'];
				array_push($tickets,$ticket);

				
				$focus = new todotasks();
				$focus->column_fields['title'] = $ticket['title'];
				$focus->column_fields['description'] = $ticket['customerdescription'];
				$focus->column_fields['date_start'] = $ticket['start_date'];
				$focus->column_fields['date_start_expected'] = $ticket['start_date'];
				$focus->column_fields['date_end'] = $ticket['end_estimated_date'];
				$focus->column_fields['date_expected'] = $ticket['end_estimated_date'];
				$focus->column_fields['executed'] = $ticket['status'];
				$focus->column_fields['assigned_user_id'] = $ticket['smownerid'];
				$focus->save('todotasks');
				$return_id = $focus->id;				

				$queryRL = "INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUES (?,?,?,?)";
				$adb->pquery($queryRL,array($reg['hitoid'],'hito',$return_id,'todotasks'));
				

				unset($ticket);

			}

			$hito['tickets'] = $tickets;
			unset($tickets);

			array_push($hitos,$hito);
			unset($hito);
		}





echo "<pre> $pruebaproyecto   ".print_r($hitos,true)."</pre>";











	}







		



?>