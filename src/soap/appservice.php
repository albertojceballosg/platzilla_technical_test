<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/

require_once("config.php");
require_once('include/logging.php');
require_once('include/nusoap/lib/nusoap.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('modules/Emails/mail.php');
require_once('modules/HelpDesk/language/en_us.lang.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/VtlibUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
require_once 'modules/Users/Users.php';
// require_once 'fusion/modules/simulador_viajes/simulador_viajes.php';
ini_set('default_socket_timeout', 180);

/** Configure language for server response translation */
global $default_language, $current_language, $app_strings;
if(!isset($current_language)) $current_language = $default_language;

$userid = getPortalUserid();
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

if(!empty($current_user->language))	// EGC idiomas no se cargaba, los cargo ahora seg�n el idioma del usuario
	$current_language = $current_user->language;
$app_strings = return_application_language($current_language);

$log = &LoggerManager::getLogger('appservice');

error_reporting(0);

$NAMESPACE = 'http://time.platzilla.com/soap/appservice';
$server = new soap_server;

$server->configureWSDL('appservice');

$server->wsdl->addComplexType(
	'common_array',
	'complexType',
	'array',
	'',
	array(
		'fieldname' => array('name'=>'fieldname','type'=>'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'common_array1',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:common_array[]')
	),
	'tns:common_array'
);

/*
$server->wsdl->addComplexType(
	'add_contact_detail_array',
    'complexType',
    'array',
    '',
	array(
    	'salutation' => array('name'=>'salutation','type'=>'xsd:string'),
        'firstname' => array('name'=>'firstname','type'=>'xsd:string'),
        'phone' => array('name'=>'phone','type'=>'xsd:string'),
        'lastname' => array('name'=>'lastname','type'=>'xsd:string'),
        'mobile' => array('name'=>'mobile','type'=>'xsd:string'),
		'accountid' => array('name'=>'accountid','type'=>'xsd:string'),
        'leadsource' => array('name'=>'leadsource','type'=>'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'field_details_array',
	'complexType',
    'array',
    '',
	array(
    	'fieldlabel' => array('name'=>'fieldlabel','type'=>'xsd:string'),
        'fieldvalue' => array('name'=>'fieldvalue','type'=>'xsd:string'),
	)
);
$server->wsdl->addComplexType(
	'field_datalist_array',
    'complexType',
    'array',
    '',
	array(
    	'fielddata' => array('name'=>'fielddata','type'=>'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'product_list_array',
	'complexType',
	'array',
	'',
	array(
		'productid' => array('name'=>'productid','type'=>'xsd:string'),
		'productname' => array('name'=>'productname','type'=>'xsd:string'),
		'productcode' => array('name'=>'productcode','type'=>'xsd:string'),
		'commissionrate' => array('name'=>'commissionrate','type'=>'xsd:string'),
		'qtyinstock' => array('name'=>'qtyinstock','type'=>'xsd:string'),
		'qty_per_unit' => array('name'=>'qty_per_unit','type'=>'xsd:string'),
		'unit_price' => array('name'=>'unit_price','type'=>'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'get_ticket_attachments_array',
    'complexType',
    'array',
    '',
	array(
    	'files' => array(
			'fileid'=>'xsd:string','type'=>'tns:xsd:string',
			'filename'=>'xsd:string','type'=>'tns:xsd:string',
			'filesize'=>'xsd:string','type'=>'tns:xsd:string',
			'filetype'=>'xsd:string','type'=>'tns:xsd:string',
			'filecontents'=>'xsd:string','type'=>'tns:xsd:string'
		),
	)
);
*/

$server->register(
	'authenticate_user',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);


/*
* START - ebirds travel
*/
$server->register(
	'ebirds_airports',
	array('data'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'ebirds_availabilitySearch',
	array('data'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

/*
* END - ebirds travel
*/

/**
 * Helper class to provide functionality like caching etc...
 */
class Vtiger_Soap_CustomerPortal {

	/** Preference value caching */
	static $_prefs_cache = array();
	static function lookupPrefValue($key) {
		if(self::$_prefs_cache[$key]) {
			return self::$_prefs_cache[$key];
		}
		return false;
	}
	static function updatePrefValue($key, $value) {
		self::$_prefs_cache[$key] = $value;
	}

	/** Sessionid caching for re-use */
	static $_sessionid = array();
	static function lookupSessionId($key) {
		if(isset(self::$_sessionid[$key])) {
			return self::$_sessionid[$key];
		}
		return false;
	}
	static function updateSessionId($key, $value) {
		self::$_sessionid[$key] = $value;
	}

	/** Store available module information */
	static $_modules = false;
	static function lookupAllowedModules() {
		return self::$_modules;
	}
	static function updateAllowedModules($modules) {
		self::$_modules = $modules;
	}

}


function getPortalUserid() {
	global $adb,$log;
	$log->debug("Entering customer portal function getPortalUserid");

	// Look the value from cache first
	$userid = Vtiger_Soap_CustomerPortal::lookupPrefValue('userid');
	if($userid === false) {
		$res = $adb->pquery("SELECT prefvalue FROM vtiger_customerportal_prefs WHERE prefkey = 'userid' AND tabid = 0", array());
		$norows = $adb->num_rows($res);
		if($norows > 0) {
			$userid = $adb->query_result($res,0,'prefvalue');
			// Update the cache information now.
			Vtiger_Soap_CustomerPortal::updatePrefValue('userid', $userid);
		}
	}
	return $userid;
	$log->debug("Exiting customerportal function getPortalUserid");
}


/**	function used to authenticate whether the customer has access or not
 *	@param string $username - customer name for the customer portal
 *	@param string $password - password for the customer portal
 *	@param string $login - true or false. If true means function has been called for login process and we have to clear the session if any, false means not called during login and we should not unset the previous sessions
 *	return array $list - returns array with all the customer details
 */
function authenticate_user($username,$password,$version,$login = 'true')
{
	global $adb,$log;
	$adb->println("Inside customer portal function authenticate_user($username, $password, $login).");
	include('vtigerversion.php');
	if(version_compare($version,'5.1.0','>=') == 0){
		$list[0] = "NOT COMPATIBLE";
  		return $list;
	}
	$username = $adb->sql_escape_string($username);
	$password = $adb->sql_escape_string($password);

	$current_date = date("Y-m-d");
	//$sql = "select id, user_name, user_password,last_login_time, support_start_date, support_end_date, firstname, lastname, cf_800, cf_801, cf_802, cf_803, cf_804, cf_805
	$sql = "select id, user_name, user_password,last_login_time, support_start_date, support_end_date, firstname, lastname
				from vtiger_portalinfo
				inner join  vtiger_contactdetails on vtiger_portalinfo.id= vtiger_contactdetails.contactid
				inner join  vtiger_contactscf on vtiger_contactscf.contactid= vtiger_contactdetails.contactid
				inner join vtiger_customerdetails on vtiger_portalinfo.id=vtiger_customerdetails.customerid
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_portalinfo.id
				where vtiger_crmentity.deleted=0
							and user_name=?
							and user_password = ?
							and isactive=1
							and vtiger_customerdetails.portal=1
							and vtiger_customerdetails.support_end_date >= ?";
	$result = $adb->pquery($sql, array($username, $password, $current_date));
	$err[0]['err1'] = "MORE_THAN_ONE_USER";
	$err[1]['err1'] = "INVALID_USERNAME_OR_PASSWORD";

	$num_rows = $adb->num_rows($result);

	if($num_rows > 1)		return $err[0];//More than one user
	elseif($num_rows <= 0)		return $err[1];//No user

	$customerid = $adb->query_result($result,0,'id');

	$list[0]['id'] = $customerid;
	$list[0]['user_name'] = $adb->query_result($result,0,'user_name');
	$list[0]['user_password'] = $adb->query_result($result,0,'user_password');
	$list[0]['last_login_time'] = $adb->query_result($result,0,'last_login_time');
	$list[0]['support_start_date'] = $adb->query_result($result,0,'support_start_date');
	$list[0]['support_end_date'] = $adb->query_result($result,0,'support_end_date');

	$list[0]['firstname'] = $adb->query_result($result,0,'firstname');
	$list[0]['lastname'] = $adb->query_result($result,0,'lastname');
	$list[0]['accountid'] = $accountid;

	/*
	$list[0]['tab_HelpDesk'] = $adb->query_result($result,0,'cf_800');
	$list[0]['tab_Notifications'] = $adb->query_result($result,0,'cf_801');
	$list[0]['tab_Invoice'] = $adb->query_result($result,0,'cf_802');
	$list[0]['tab_ServiceContracts'] = $adb->query_result($result,0,'cf_803');
	$list[0]['tab_ServiceTasks'] = $adb->query_result($result,0,'cf_804');
	$list[0]['tab_GraficoCustomerPortal'] = $adb->query_result($result,0,'cf_805');
	$list[0]['tab_GANTT'] = $adb->query_result($result,0,'gantt');

	$list[0]['planname'] = '';
	$list[0]['unidadesusadas'] = 0;
	$list[0]['unidadescontratadas'] = 0;
	$list[0]['diastranscurridos'] = 0;
	$list[0]['diascontratados'] = 0;*/

	$res = $adb->pquery("select accountid from vtiger_contactdetails where contactid=?", array($customerid));
	$accountid=$adb->query_result($res,0,'accountid');


	//  BUSCO NOMBRE DE CONTRATO
	/*
	$query ="select SC.servicecontractsid, SC.subject,
		((To_days(CURDATE())-To_days(SC.start_date))+1) as diastranscurridos,
		((To_days(SC.due_date)-To_days(SC.start_date))+1) as diascontratados
	from vtiger_servicecontracts SC
	left join vtiger_crmentity CRM on CRM.crmid=SC.servicecontractsid
	where (CRM.deleted=0) and  (SC.start_date <= now()) AND (now() <= SC.due_date) AND (SC.contract_status  like 'Activo' ) and (SC.sc_related_to = ?)
	ORDER BY SC.servicecontractsid DESC";
	$resultcontrato = $adb->pquery($query, array($accountid));
	$noofSC = $adb->num_rows($resultcontrato);
	for($i=0;$i<$noofSC;$i++)
	{
		$servicecontractsid = $adb->query_result($resultcontrato,$i,'servicecontractsid');
		$servicecontractssubject = $adb->query_result($resultcontrato,$i,'subject');
		$servicecontractsDiasTranscurridos = $adb->query_result($resultcontrato,$i,'diastranscurridos');
		$servicecontractsDiasContratados = $adb->query_result($resultcontrato,$i,'diascontratados');
		$queryhoras = "SELECT SUM(S.qty_per_unit) as horastotal
						FROM vtiger_service S
						INNER JOIN vtiger_crmentityrel REL ON (REL.crmid = ?) AND (REL.module LIKE 'ServiceContracts') AND (REL.relmodule LIKE 'Services') AND (REL.relcrmid = S.serviceid)
						INNER JOIN vtiger_servicecf SCF ON (SCF.serviceid = S.serviceid) and (SCF.cf_686 = 'Diario') and (SCF.cf_687 = 'Mensual')
						WHERE (S.service_usageunit = 'Hours')
						AND (S.servicecategory = 'support')";
		$reshoras = $adb->pquery($queryhoras, array($servicecontractsid));
		$nooffields = $adb->num_rows($reshoras);
		if ($nooffields > 0) {
				$horas = $adb->query_result($reshoras,0,'horastotal');
				$list[0]['planname'] = $servicecontractssubject;
				$list[0]['unidadescontratadas'] = $horas;
				$list[0]['diastranscurridos'] = $servicecontractsDiasTranscurridos;
				$list[0]['diascontratados'] = $servicecontractsDiasContratados;
				break;
		}
	}

	$sql = "SELECT SUM(horas_dedicadas) as horas_dedicadas, accountname FROM vtiger_diarynotes_desarrolladores A
				INNER JOIN vtiger_troubletickets B ON (A.ticketid = B.ticketid)
				INNER JOIN vtiger_account C ON (B.parent_id = C.accountid)
				WHERE
				(MONTH(A.date) = MONTH(CURDATE()) ) AND (YEAR(A.date) = YEAR(CURDATE()))
				AND B.parent_id = ? ORDER BY 1 DESC";

	//Se toman en cuentas las horas reportadas por los desarrolladores
	$reshoras2 = $adb->pquery($sql, array($accountid));
	$nooffields = $adb->num_rows($reshoras2);
	if ($nooffields > 0) {
			$horas2 = $adb->query_result($reshoras2,0,'horas_dedicadas');
			$list[0]['unidadesusadas'] = $horas2;
	}*/

	//During login process we will pass the value true. Other times (change password) we will pass false

	if($login != 'false')
	{
		$sessionid = makeRandomPassword();

		unsetServerSessionId($customerid);

		$sql="insert into vtiger_soapservice values(?,?,?,NULL)";
		$result = $adb->pquery($sql, array($customerid,'customer' ,$sessionid));

		$list[0]['sessionid'] = $sessionid;
	}

	return $list;
}



/**	Function used to validate the session
 *	@param int $id - contact id to which we want the session id
 *	@param string $sessionid - session id which will be passed from customerportal
 *	return true/false - return true if valid session otherwise return false
 **/
function validateSession($id, $sessionid)
{
	global $adb;
	$adb->println("Inside function validateSession($id, $sessionid)");

	$server_sessionid = getServerSessionId($id);

	$adb->println("Checking Server session id and customer input session id ==> $server_sessionid == $sessionid");

	if($server_sessionid == $sessionid)
	{
		$adb->println("Session id match. Authenticated to do the current operation.");
		return true;
	}
	else
	{
		$adb->println("Session id does not match. Not authenticated to do the current operation.");
		return false;
	}
}


/**	Function used to get the session id which was set during login time
 *	@param int $id - contact id to which we want the session id
 *	return string $sessionid - return the session id for the customer which is a random alphanumeric character string
 **/
function getServerSessionId($id)
{
	global $adb;
	$adb->println("Inside the function getServerSessionId($id)");

	//To avoid SQL injection we are type casting as well as bound the id variable. In each and every function we will call this function
	$id = (int) $id;

	$sessionid = Vtiger_Soap_CustomerPortal::lookupSessionId($id);
	if($sessionid === false) {
		$query = "select * from vtiger_soapservice where type='customer' and id=?";
		$sessionid = $adb->query_result($adb->pquery($query, array($id)),0,'sessionid');
		Vtiger_Soap_CustomerPortal::updateSessionId($id, $sessionid);
	}
	return $sessionid;
}

/**	Function used to unset the server session id for the customer
 *	@param int $id - contact id to which customer we want to unset the session id
 **/
function unsetServerSessionId($id)
{
	global $adb,$log;
	$log->debug("Entering customer portal function unsetServerSessionId");
	$adb->println("Inside the function unsetServerSessionId");

	$id = (int) $id;
	Vtiger_Soap_CustomerPortal::updateSessionId($id, false);

	$adb->pquery("delete from vtiger_soapservice where type='customer' and id=?", array($id));
	$log->debug("Exiting customer portal function unsetServerSessionId");
	return;
}

/*
* START - ebirds travel
*/

function ebirds_airports($data){
	global $adb;
	$destino=$data['destino'];
	if($destino==''){
		$destino='EZE';
	}
	// if(strlen($destino)<3)
		// return new soap_fault('Client', '', 'Put your name!');
	$sql="SELECT * FROM `ebirds_airports`
			WHERE (`Name` LIKE '%".$destino."%'
			OR `City` LIKE '%".$destino."%'
			OR `Country` LIKE '%".$destino."%'
			OR `IATA_FAA` LIKE '%".$destino."%'
			OR `ICAO` LIKE '%".$destino."%')
			AND `IATA_FAA`!=''";
	$q=$adb->query($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return $return;
}

function ebirds_sessionCreate($params){
	// return getcwd();
	// require_once('customerportal/nusoap/lib/nusoap.php');
	// return $params;
	$Server_Path="http://www.ebirdtravel.com/webservices/test_flight.php";
	$ebirds_client = new nusoap_client($Server_Path, false,'','','','',180);
	$ebirds_client->soap_defencoding ="UTF-8";
	$ret=$ebirds_client->call("sessionCreate", $params, $Server_Path, $Server_Path);
	return $ret[0];
}

function ebirds_SearchFlys($search){
	// return getcwd();
	// require_once('customerportal/nusoap/lib/nusoap.php');
	$Server_Path="http://www.ebirdtravel.com/webservices/test_flight.php";
	$ebirds_client = new nusoap_client($Server_Path, false,'','','','',180);
	$ebirds_client->soap_defencoding ="UTF-8";
	$ret=$ebirds_client->call("availabilitySearch", $search, $Server_Path, $Server_Path);
	return $ret;

}

function ebirds_availabilitySearch($data){
	$params=array('username'=>$data['username'],
				  'password'=>$data['password'],
				  'licenseKey'=>$data['licenseKey']
				);
	// return array($params);
	$access=ebirds_sessionCreate($params);
	// return array($access);

	$search=array('sessionKey'=>$access['sessionKey'],
				  'language'=>$data['language'],
				  'currency'=>$data['currency'],
				  'departureAirport'=>array($data['departureAirport'],'airportcode'),
				  'returnAirport'=>array($data['returnAirport'],'airportcode'),
				  'departureDate'=>$data['departureDate'],
				  'returnDate'=>$data['returnDate'],
				  'adults'=>$data['adults'],
				  'children'=>$data['children'],
				  'babys'=>$data['babys'],
				  'advancedDetails'=>$data['advancedDetails'],
				  'class'=>$data['class'], //Economy, Business or First (optional)
				  'carrier'=>$data['carrier'], //The carrier code (e.g. KL for KLM) (optional)
				  'flexible'=>$data['flexible'], //Flexible dates (0 = no flexible dates; 1 = flexible dates) (optional)
				  'discountLevel'=>$data['discountLevel']//Spanish discounts. One of the discountlevels shown in Apendix A.3. (optional)
				);
	// return array($search);
	$flys=ebirds_SearchFlys($search);
	// return array($flys);

	if(is_array($flys))
	foreach($flys as $k => $fly){
		//ida
		// return array($fly['legs'][0]['item'][0]['departure']['departureTime']);
		//Verifica si tiene escalas...
			//ida
		if(isset($fly['legs'][0]['item'][0])){
			$vuelo_e1=$fly['legs'][0]['item'][0];
			$cantidad_de_paradas_go=count($fly['legs'][0]['item']);
			$ultimo_go=$cantidad_de_paradas_go-1;
			$vuelo_e2=$fly['legs'][0]['item'][$ultimo_go];
			$ticket=$fly['legs'][0]['item'][0]['class'];
		}else{
			$vuelo_e1=$fly['legs'][0]['item'];
			$vuelo_e2=$fly['legs'][0]['item'];
			$ticket=$fly['legs'][0]['item']['class'];
		}
		//vuelta
		if(isset($fly['legs'][1]['item'][0])){
			$vuelo_v1=$fly['legs'][1]['item'][0];
			$cantidad_de_paradas_back=count($fly['legs'][1]['item']);
			$ultimo_back=$cantidad_de_paradas_back-1;
			$vuelo_v2=$fly['legs'][1]['item'][$ultimo_back];
		}else{
			$vuelo_v1=$fly['legs'][1]['item'];
			$vuelo_v2=$fly['legs'][1]['item'];
		}
		$departure_go['time']=$vuelo_e1['departure']['departureTime'];
		$departure_go['date']=$vuelo_e1['departure']['departureDate'];
		$departure_go['airport']=$vuelo_e1['departureAirport']['airportCode'];

		$arrival_go['time']=$vuelo_e2['arrival']['arrivalTime'];
		$arrival_go['date']=$vuelo_e2['arrival']['arrivalDate'];
		$arrival_go['airport']=$vuelo_e2['arrivalAirport']['airportCode'];

		$horasvuelo_ida=date('H:i', mktime(0,$vuelo_e1['duration']));
		//vuelta
		$departure_back['time']=$vuelo_v1['departure']['departureTime'];
		$departure_back['date']=$vuelo_v1['departure']['departureDate'];
		$departure_back['airport']=$vuelo_v1['departureAirport']['airportCode'];
		// $cantidad_de_paradas_back=count($fly['legs'][1]['item']);
		// $ultimo_back=$cantidad_de_paradas_back-1;
		$arrival_back['time']=$vuelo_v2['arrival']['arrivalTime'];
		$arrival_back['date']=$vuelo_v2['arrival']['arrivalDate'];
		$arrival_back['airport']=$vuelo_v2['arrivalAirport']['airportCode'];

		$horasvuelo_vuelta=date('H:i', mktime(0,$vuelo_v1['duration']));


		$total=$fly['totalPrice'];
		$currency=$fly['currency'];

		$vuelos[$k]=array(
						'go_departure_time'=>$departure_go['time'],
						'go_departure_date'=>$departure_go['date'],
						'go_departure_arp'=>$departure_go['airport'],
						'go_arrival_time'=>$arrival_go['time'],
						'go_arrival_data'=>$arrival_go['date'],
						'go_arrival_arp'=>$arrival_go['airport'],
						'go_duration'=>$horasvuelo_ida,
						'back_departure_time'=>$departure_back['time'],
						'back_departure_date'=>$departure_back['date'],
						'back_departure_arp'=>$departure_back['airport'],
						'back_arrival_time'=>$arrival_back['time'],
						'back_arrival_data'=>$arrival_back['date'],
						'back_arrival_arp'=>$arrival_back['airport'],
						'back_duration'=>$horasvuelo_vuelta,
						'ticket'=>$ticket,
						'total'=>$total,
						'currency'=>$currency,
					);
	}

	return $vuelos;
}

/*
* END - ebirds travel
*/

/**
 * Create a web friendly URL slug from a string.
 */
function Slug($string, $slug = '-', $extra = null){

	if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false){
		$string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
	}

	return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']++~i', $slug, $string), $slug));
}


/* Begin the HTTP listener service and exit. */
if (!isset($HTTP_RAW_POST_DATA)){
	$HTTP_RAW_POST_DATA = file_get_contents('php://input');
}
$server->service($HTTP_RAW_POST_DATA);

exit();

?>
