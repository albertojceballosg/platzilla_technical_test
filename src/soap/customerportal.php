<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * @filesource
 ********************************************************************************/

require_once("config.php");
require_once('include/logging.php');
require_once('include/nusoap/nusoap.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('modules/Emails/mail.php');
require_once('modules/HelpDesk/language/en_us.lang.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/VtlibUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
require_once 'modules/Users/Users.php';
// require_once 'fusion/modules/simulador_viajes/simulador_viajes.php';


/** Configure language for server response translation */
global $default_language, $current_language, $app_strings;
if(!isset($current_language)) $current_language = $default_language;

$userid = getPortalUserid();
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

if(!empty($current_user->language))	// EGC idiomas no se cargaba, los cargo ahora según el idioma del usuario
	$current_language = $current_user->language;
$app_strings = return_application_language($current_language);

$log = &LoggerManager::getLogger('customerportal');

error_reporting(0);

$NAMESPACE = 'http://www.vtiger.com/products/crm';
$server = new soap_server;

$server->configureWSDL('customerportal');

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


$server->register(
	'authenticate_user',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'change_password',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'create_ticket',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'response_ticket',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

//for a particular contact ticket list
$server->register(
	'get_tickets_list',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'get_ticket_comments',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'get_combo_values',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'get_KBase_details',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array1'),
	$NAMESPACE);

$server->register(
	'save_faq_comment',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'update_ticket_comment',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
        'close_current_ticket',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'update_login_details',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'send_mail_for_password',
	array('email'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
        'get_ticket_creator',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'get_picklists',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'get_ticket_attachments',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'get_filecontent',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'add_ticket_attachment',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'get_cf_field_details',
	array('id'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
        'get_check_account_id',
	array('id'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

		//to get details of quotes,invoices and documents
$server->register(
	'get_details',
	array('id'=>'xsd:string','block'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'get_account_name',
	array('accountid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

		//to get the products list for the entire account of a contact
$server->register(
	'get_product_list_values',
	array('id'=>'xsd:string','block'=>'xsd:string','sessionid'=>'xsd:string','only_mine'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'get_list_values',
	array('id'=>'xsd:string','block'=>'xsd:string','sessionid'=>'xsd:string','only_mine'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);

$server->register(
	'get_data_graficocustomerportal',
	array('id'=>'xsd:string','module'=>'xsd:string','sessionid'=>'xsd:string' ),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);

$server->register(
	'get_product_urllist',
	array('customerid'=>'xsd:string','productid'=>'xsd:string','block'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);

$server->register(
	'get_pdf',
	array('id'=>'xsd:string','block'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);

$server->register(
	'get_filecontent_detail',
	array('id'=>'xsd:string','folderid'=>'xsd:string','block'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:get_ticket_attachments_array'),
	$NAMESPACE);

$server->register(
	'get_invoice_detail',
	array('id'=>'xsd:string','block'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'get_modules',
	array(),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'show_all',
	array('module'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'get_documents',
	array('id'=>'xsd:string','module'=>'xsd:string','customerid'=>'xsd:string','sessionid'=> 'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'updateCount',
	array('id'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

//to get the Services list for the entire account of a contact
$server->register(
	'get_service_list_values',
	array('id'=>'xsd:string','module'=>'xsd:string','sessionid'=>'xsd:string','only_mine'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

//to get the Project Tasks for a given Project
$server->register(
	'get_project_components',
	array('id'=>'xsd:string','module'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

//to get the Project Tickets for a given Project
$server->register(
	'get_project_tickets',
	array('id'=>'xsd:string','module'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

/* STAR WEBSITE FUNCTIONS */
$server->register(
	'getApplications',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'getModulesofApplication',
	array('applicationsid'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'getInstancesByName',
	array('name'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'getInstructions',
	array('name'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'setInstructions',
	array('name'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'creaInstanciaenModulo',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'createInstance',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'saveRecord',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'get_account_name_by_siccode',
	array('siccode'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'enableApplication',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'checkemail',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'setTestimonial',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'setComment',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'EmailManagerSender',
	array('clientdata'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'checkFrontEnds',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'getPaginas',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getHoteles',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getOfertas',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getTestimonios',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getWebConfiguration',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getServices',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getPlatStatus',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getInstancias',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getCursos',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'setNewUser',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getLecciones',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getPruebas',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getPreguntas',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getimagen',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getWebOrganizationDetails',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'sendTicketByEMail',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getAccountByLocalizator',
	array('localizator'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'createInvoice',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getServiceByName',
	array('name'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'getFlightsBySimulator',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'putPassengerData',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'saveReservation',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'getCustomerRoles',
	array('customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'putCustomerRecord',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getAccountIdByPlatName',
	array('name'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'getCustomerPass',
	array('name'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'getContactIdByEMail',
	array('name'=>'xsd:string','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'sendPasswordReset',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'checkPasswordResetCode',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'setNewPassword',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'satisfactionSurveyGetQuestions',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'satisfactionSurveySave',
	array('fieldname'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'getLastArticleToPosition',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

$server->register(
	'saveArticlePosition',
	array('data'=>'tns:common_array','customerid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
	'getMenuApp',
	array('data'=>'tns:common_array'),
	array('return'=>'xsd:string'),
	$NAMESPACE);

/**
 * START - ebirds travel
 */
$server->register(
	'ebirds_airports',
	array('data'=>'tns:common_array'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

$server->register(
	'ebirds_availabilitySearch',
	array('data'=>'tns:common_array'),
	array('return'=>'tns:field_details_array'),
	$NAMESPACE);

/**
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

/**	function used to get the list of ticket comments
 * @param array $input_array - array which contains the following parameters
 * int $id - customer id
 * string $sessionid - session id
 * int $ticketid - ticket id
 * @return array $response - ticket comments and details as a array with elements comments, owner and createdtime which will be returned from the function get_ticket_comments_list
*/
function get_ticket_comments($input_array)
{
	global $adb,$log,$current_user;
	$adb->println("Entering customer portal function get_ticket_comments");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];

	if(!validateSession($id,$sessionid))
		return null;

	$userid = getPortalUserid();
	$user = new Users();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	if(getFieldVisibilityPermission('HelpDesk', $userid, 'comments') == '1'){
		return null;
	}

	$seed_ticket = new HelpDesk();
	$response = $seed_ticket->get_ticket_comments_list($ticketid);
	return $response;
}

/**	function used to get the combo values ie., picklist values of the HelpDesk module and also the list of products
 *	@param array $input_array - array which contains the following parameters
 =>	int $id - customer id
	string $sessionid - session id
	*	return array $output - array which contains the product id, product name, ticketpriorities, ticketseverities, ticketcategories and module owners list
	*/
function get_combo_values($input_array)
{
	global $adb,$log;
	$adb->println("Entering customer portal function get_combo_values");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];

	if(!validateSession($id,$sessionid))
		return null;

	$output = Array();
	$sql = "select  productid, productname from vtiger_products inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid where vtiger_crmentity.deleted=0";
	$result = $adb->pquery($sql, array());
	$noofrows = $adb->num_rows($result);
	for($i=0;$i<$noofrows;$i++)
	{
		$check = checkModuleActive('Products');
		if($check == false){
			$output['productid']['productid']="#MODULE INACTIVE#";
			$output['productname']['productname']="#MODULE INACTIVE#";
			break;
		}
		$output['productid']['productid'][$i] = $adb->query_result($result,$i,"productid");
		$output['productname']['productname'][$i] = decode_html($adb->query_result($result,$i,"productname"));
	}

	$userid = getPortalUserid();

	//We are going to display the picklist entries associated with admin user (role is H2)
	$roleres = $adb->pquery("SELECT roleid from vtiger_user2role where userid = ?",array($userid));
	$RowCount = $adb->num_rows($roleres);
	if($RowCount > 0){
		$admin_role = $adb->query_result($roleres,0,'roleid');
	}
	$result1 = $adb->pquery("select vtiger_ticketpriorities.ticketpriorities from vtiger_ticketpriorities inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_ticketpriorities.picklist_valueid and vtiger_role2picklist.roleid='$admin_role'", array());
	for($i=0;$i<$adb->num_rows($result1);$i++)
	{
		$output['ticketpriorities']['ticketpriorities'][$i] = $adb->query_result($result1,$i,"ticketpriorities");
	}

	$result2 = $adb->pquery("select vtiger_ticketseverities.ticketseverities from vtiger_ticketseverities inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_ticketseverities.picklist_valueid and vtiger_role2picklist.roleid='$admin_role'", array());
	for($i=0;$i<$adb->num_rows($result2);$i++)
	{
		$output['ticketseverities']['ticketseverities'][$i] = $adb->query_result($result2,$i,"ticketseverities");
	}

	$result3 = $adb->pquery("select vtiger_ticketcategories.ticketcategories from vtiger_ticketcategories inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_ticketcategories.picklist_valueid and vtiger_role2picklist.roleid='$admin_role'", array());
	for($i=0;$i<$adb->num_rows($result3);$i++)
	{
		$output['ticketcategories']['ticketcategories'][$i] = $adb->query_result($result3,$i,"ticketcategories");
	}

	// Gather service contract information
	if(!vtlib_isModuleActive('ServiceContracts')) {
		$output['serviceid']['serviceid']="#MODULE INACTIVE#";
		$output['servicename']['servicename']="#MODULE INACTIVE#";
	} else {
		$servicequery = "SELECT vtiger_servicecontracts.servicecontractsid,vtiger_servicecontracts.subject
							FROM vtiger_servicecontracts
							INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=vtiger_servicecontracts.servicecontractsid
									AND vtiger_crmentity.deleted = 0
							WHERE vtiger_servicecontracts.sc_related_to = ?";
		$params = array($id);
		$showAll = show_all('HelpDesk');
		if($showAll == 'true') {
			$servicequery .= ' OR vtiger_servicecontracts.sc_related_to = (SELECT accountid FROM vtiger_contactdetails WHERE contactid=?)
								OR vtiger_servicecontracts.sc_related_to IN
											(SELECT contactid FROM vtiger_contactdetails WHERE accountid =
													(SELECT accountid FROM vtiger_contactdetails WHERE contactid=?))
							';
			array_push($params, $id);
			array_push($params, $id);
		}
		$serviceResult = $adb->pquery($servicequery,$params);

		for($i=0;$i < $adb->num_rows($serviceResult);$i++){
			$serviceid = $adb->query_result($serviceResult,$i,'servicecontractsid');
			$output['serviceid']['serviceid'][$i] = $serviceid;
			$output['servicename']['servicename'][$i] = $adb->query_result($serviceResult,$i,'subject');
		}
	}

	return $output;

}

/**	function to get the Knowledge base details
 *	@param array $input_array - array which contains the following parameters
 =>	int $id - customer id
	string $sessionid - session id
	*	return array $result - array which contains the faqcategory, all product ids , product names and all faq details
	*/
function get_KBase_details($input_array)
{
	global $adb,$log;
	$adb->println("Entering customer portal function get_KBase_details");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];

	if(!validateSession($id,$sessionid))
		return null;

	$userid = getPortalUserid();
	$result['faqcategory'] = array();
	$result['product'] = array();
	$result['faq'] = array();

	//We are going to display the picklist entries associated with admin user (role is H2)
	$roleres = $adb->pquery("SELECT roleid from vtiger_user2role where userid = ?",array($userid));
	$RowCount = $adb->num_rows($roleres);
	if($RowCount > 0){
		$admin_role = $adb->query_result($roleres,0,'roleid');
	}
	$category_query = "select vtiger_faqcategories.faqcategories from vtiger_faqcategories inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_faqcategories.picklist_valueid and vtiger_role2picklist.roleid='$admin_role'";
	$category_result = $adb->pquery($category_query, array());
	$category_noofrows = $adb->num_rows($category_result);
	for($j=0;$j<$category_noofrows;$j++)
	{
		$faqcategory = $adb->query_result($category_result,$j,'faqcategories');
		$result['faqcategory'][$j] = $faqcategory;
	}

	$check = checkModuleActive('Products');

	if($check == true) {
		$product_query = "select productid, productname from vtiger_products inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid where vtiger_crmentity.deleted=0";
		$product_result = $adb->pquery($product_query, array());
		$product_noofrows = $adb->num_rows($product_result);
		for($i=0;$i<$product_noofrows;$i++)
		{
			$productid = $adb->query_result($product_result,$i,'productid');
			$productname = $adb->query_result($product_result,$i,'productname');
			$result['product'][$i]['productid'] = $productid;
			$result['product'][$i]['productname'] = $productname;
		}
	}
	$faq_query = "select vtiger_faq.*, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime from vtiger_faq " .
		"inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_faq.id " .
		"where vtiger_crmentity.deleted=0 and vtiger_faq.status='Published' order by vtiger_crmentity.modifiedtime DESC";
	$faq_result = $adb->pquery($faq_query, array());
	$faq_noofrows = $adb->num_rows($faq_result);
	for($k=0;$k<$faq_noofrows;$k++)
	{
		$faqid = $adb->query_result($faq_result,$k,'id');
		$moduleid = $adb->query_result($faq_result,$k,'faq_no');
		$result['faq'][$k]['faqno'] = $moduleid;
		$result['faq'][$k]['id'] = $faqid;
		if($check == true) {
			$result['faq'][$k]['product_id']  = $adb->query_result($faq_result,$k,'product_id');
		}
		$result['faq'][$k]['question'] =  nl2br($adb->query_result($faq_result,$k,'question'));
		$result['faq'][$k]['answer'] = nl2br($adb->query_result($faq_result,$k,'answer'));
		$result['faq'][$k]['category'] = $adb->query_result($faq_result,$k,'category');
		$result['faq'][$k]['faqcreatedtime'] = $adb->query_result($faq_result,$k,'createdtime');
		$result['faq'][$k]['faqmodifiedtime'] = $adb->query_result($faq_result,$k,'modifiedtime');

		$faq_comment_query = "select * from vtiger_faqcomments where faqid=? order by createdtime DESC";
		$faq_comment_result = $adb->pquery($faq_comment_query, array($faqid));
		$faq_comment_noofrows = $adb->num_rows($faq_comment_result);
		for($l=0;$l<$faq_comment_noofrows;$l++)
		{
			$faqcomments = nl2br($adb->query_result($faq_comment_result,$l,'comments'));
			$faqcreatedtime = $adb->query_result($faq_comment_result,$l,'createdtime');
			if($faqcomments != '')
			{
				$result['faq'][$k]['comments'][$l] = $faqcomments;
				$result['faq'][$k]['createdtime'][$l] = $faqcreatedtime;
			}
		}
	}
	$adb->println($result);
	return $result;
}

/**	function to save the faq comment
 *	@param array $input_array - array which contains the following values
 => 	int $id - Customer ie., Contact id
	int $sessionid - session id
	int $faqid - faq id
	string $comment - comment to be added with the FAQ
	*	return array $result - This function will call get_KBase_details and return that array
	*/
function save_faq_comment($input_array)
{
	global $adb;
	$adb->println("Entering customer portal function save_faq_comment");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$faqid = (int) $input_array['faqid'];
	$comment = $input_array['comment'];

	if(!validateSession($id,$sessionid))
		return null;

	$createdtime = $adb->formatDate(date('YmdHis'),true);
	if(trim($comment) != '')
	{
		$faq_query = "insert into vtiger_faqcomments values(?,?,?,?)";
		$adb->pquery($faq_query, array('', $faqid, $comment, $createdtime));
	}

	$params = Array('id'=>"$id", 'sessionid'=>"$sessionid");
	$result = get_KBase_details($input_array);

	return $result;
}

/** function to get a list of tickets and to search tickets
 * @param array $input_array - array which contains the following values
 => 	int $id - Customer ie., Contact id
	int $only_mine - if true it will display only tickets related to contact
	otherwise displays tickets related to account it belongs and all the contacts that are under the same account
	int $where - used for searching tickets
	string $match - used for matching tickets
	*	return array $result - This function will call get_KBase_details and return that array
	*/


function get_tickets_list($input_array) {

	require_once('modules/HelpDesk/HelpDesk.php');
	require_once('include/utils/UserInfoUtil.php');

	global $adb,$log;
	global $current_user;
	$log->debug("Entering customer portal function get_ticket_list");

	$user = new Users();
	$userid = getPortalUserid();

	$show_all = show_all('HelpDesk');
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$id = $input_array['id'];
	$only_mine = $input_array['onlymine'];
	$where = $input_array['where']; //addslashes is already added with where condition fields in portal itself
	$match = $input_array['match'];
	$limit = $input_array['limit']?$input_array['limit']:'';
	$sessionid = $input_array['sessionid'];

	if(!validateSession($id,$sessionid))
		return null;

	// Prepare where conditions based on search query
	$join_type = '';
	$where_conditions = '';
	if(trim($where) != '') {
		if($match == 'all' || $match == '') {
			$join_type = " AND ";
		} elseif($match == 'any') {
			$join_type = " OR ";
		}
		$where = explode("&&&",$where);
		$where_conditions = implode($join_type, $where);
	}

	$entity_ids_list = array();
	if($only_mine == 'true' || $show_all == 'false')
	{
		array_push($entity_ids_list,$id);
	}
	else
	{
		$contactquery = "SELECT contactid, accountid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" AND vtiger_crmentity.deleted = 0 " .
			" WHERE (accountid = (SELECT accountid FROM vtiger_contactdetails WHERE contactid = ?)  AND accountid != 0) OR contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id,$id));
		$no_of_cont = $adb->num_rows($contactres);
		for($i=0;$i<$no_of_cont;$i++)
		{
			$cont_id = $adb->query_result($contactres,$i,'contactid');
			$acc_id = $adb->query_result($contactres,$i,'accountid');
			if(!in_array($cont_id, $entity_ids_list))
				$entity_ids_list[] = $cont_id;
			if(!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
				$entity_ids_list[] = $acc_id;
		}
	}

	$focus = new HelpDesk();
	$focus->filterInactiveFields('HelpDesk');
	foreach ($focus->list_fields as $fieldlabel => $values){
		foreach($values as $table => $fieldname){
			$fields_list[$fieldlabel] = $fieldname;
		}
	}

	$query = "SELECT vtiger_troubletickets.*, vtiger_crmentity.smownerid, vtiger_crmentity.description, vtiger_crmentity.createdtime,
				vtiger_ticketcf.cf_629, vtiger_ticketcf.cf_568,vtiger_ticketcf.cf_688,vtiger_ticketcf.cf_693,vtiger_ticketcf.cf_685, '' AS setype
				FROM vtiger_troubletickets
				INNER JOIN vtiger_ticketcf ON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid AND vtiger_crmentity.deleted = 0
				WHERE vtiger_troubletickets.parent_id IN (". generateQuestionMarks($entity_ids_list) .") ";

	// Add conditions if there are any search parameters
	if ($join_type != '' && $where_conditions != '') {
		$query .= " AND (".$where_conditions.")";
	}

	$query .= " ORDER BY vtiger_troubletickets.ticketid DESC  ";


	$params = array($entity_ids_list);
	$fields_list['fecha_de_inicio'] = 'cf_629';
	$fields_list['fecha_de_cierre'] = 'cf_688';
	$fields_list['hours'] = 'cf_693';
	// $fields_list['description'] = 'description';
	$fields_list['description'] = 'cf_685';

	$TicketsfieldVisibilityByColumn = array();
	foreach($fields_list as $fieldlabel=> $fieldname) {
		$TicketsfieldVisibilityByColumn[$fieldname] =
			getColumnVisibilityPermission($current_user->id,$fieldname,'HelpDesk');
	}

	$res = $adb->pquery($query,$params);
	$noofdatatotal = $adb->num_rows($res);
	$query .= $limit;
	$res = $adb->pquery($query,$params);
	$noofdata = $adb->num_rows($res);

	for( $j= 0;$j < $noofdata; $j++)
	{

		$i=0;
		$ticketid = $adb->query_result($res,$j,'ticketid');
		foreach($fields_list as $fieldlabel => $fieldname) {
			if($i==4){
				$fieldlabel='Fecha estimada de finalizaci&oacute;n';
			}

			$fieldper = $TicketsfieldVisibilityByColumn[$fieldname]; //in troubletickets the list_fields has columns so we call this API
			if($fieldper == '1'){
				continue;
			}
			if (($fieldname == 'smownerid') || ($fieldname == 'priority') || ($fieldname == 'parent_id')) {
					continue;
			}
			$output[0]['head'][0][$i]['fielddata'] = $fieldlabel;

			$fieldvalue = $adb->query_result($res,$j,$fieldname);

			if ($fieldname == 'title') {
				$fieldvalue = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$ticketid.'">'.$fieldvalue.'</a>';
			}
			if (($fieldname == 'cf_629') || ($fieldname == 'cf_688') || ($fieldname == 'cf_568') || ($fieldname == 'createdtime')) {



					if ($fieldvalue) $fieldvalue =  date("d-m-Y",strtotime($fieldvalue));

			}

			if($fieldname == 'parent_id') {
				$crmid = $fieldvalue;
				$module = getSalesEntityType($crmid);
				if ($crmid != '' && $module != '') {
					$fieldvalues = getEntityName($module, array($crmid));
					// if($module == 'Contacts')
					// $fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					// elseif($module == 'Accounts')
					// $fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					$fieldvalue = $fieldvalues[$crmid];
				} else {
					$fieldvalue = '';
				}
			}

			$output[1]['data'][$j][$i]['fielddata'] = $fieldvalue;

			$i++;

		}

		$output[0]['head'][0][$i]['fielddata'] = 'ticketid';
		$output[1]['data'][$j][$i]['fielddata'] = $ticketid;
		$output[1]['num_rows'] = $noofdatatotal;

	}

	$log->debug("Exiting customer portal function get_tickets_list");

	return $output;
}

/**	function used to create ticket which has been created from customer portal
 *	@param array $input_array - array which contains the following values
 => 	int $id - customer id
	int $sessionid - session id
	string $title - title of the ticket
	string $description - description of the ticket
	string $priority - priority of the ticket
	string $severity - severity of the ticket
	string $category - category of the ticket
	string $user_name - customer name
	int $parent_id - parent id ie., customer id as this customer is the parent for this ticket
	int $product_id - product id for the ticket
	string $module - module name where as based on this module we will get the module owner and assign this ticket to that corresponding user
	*	return array - currently created ticket array, if this is not created then all tickets list will be returned
	*/
function create_ticket($input_array)
{
	global $adb,$log;
	$adb->println("Inside customer portal function create_ticket");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$title = $input_array['title'];
	$description = $input_array['customerdescription'];
	$priority = $input_array['priority'];
	$severity = $input_array['severity'];
	$category = $input_array['category'];
	$user_name = $input_array['user_name'];
	$parent_id = (int) getAccountIdByPlatName($input_array['plat']);
	$product_id = (int) $input_array['product_id'];
	$module = $input_array['module'];
	//$assigned_to = $input_array['assigned_to'];
	$servicecontractid = $input_array['serviceid'];
	$contactid = $input_array['contactid'];
	$projectid = $input_array['projectid'];
	$horas_soporte = $input_array['horas_soporte'];

	if(!validateSession($id,$sessionid))
		return null;

	$ticket = new HelpDesk();

	$ticket->column_fields['ticket_title'] = $title;
	$ticket->column_fields['description']=$description;
	$ticket->column_fields['ticketpriorities']=$priority;
	$ticket->column_fields['ticketseverities']=$severity;
	$ticket->column_fields['ticketcategories']=$category;
	$ticket->column_fields['ticketstatus']='TICKET_OPEN';
	$ticket->column_fields['hours_limit']=$horas_soporte;
	$ticket->column_fields['cf_685']=$description;
	$ticket->column_fields['cf_569']='Incidencia';

	foreach ($input_array as $clave => $valor) {
		if (substr($clave,0,3) == 'cf_')
			$ticket->column_fields[$clave]=$valor;
	}


	$ticket->column_fields['parent_id']=$parent_id;
	$ticket->column_fields['product_id']=$product_id;

	$defaultAssignee = getDefaultAssigneeId();

	$ticket->column_fields['assigned_user_id']=$defaultAssignee;
	$ticket->column_fields['from_portal'] = 1;

	$ticket->save("HelpDesk");

	$ticketresult = $adb->pquery("select vtiger_troubletickets.ticketid from vtiger_troubletickets
		inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
		where vtiger_crmentity.deleted=0 and vtiger_troubletickets.ticketid = ?", array($ticket->id));
	if($adb->num_rows($ticketresult) == 1)
	{
		$record_save = 1;
		$record_array[0]['new_ticket']['ticketid'] = $adb->query_result($ticketresult,0,'ticketid');
	}
	if($servicecontractid != ''){
		$res = $adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($servicecontractid, 'ServiceContracts', $ticket->id, 'HelpDesk'));
	}
	if($projectid != '') {
		$res = $adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($projectid, 'Project', $ticket->id, 'HelpDesk'));
	}
	if($contactid != ''){
		$res = $adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($ticket->id, 'HelpDesk',$contactid, 'Contacts'));
	}
	if($record_save == 1)
	{
		$adb->println("Ticket from Portal is saved with id => ".$ticket->id);
		return $record_array;
	}
	else
	{
		$adb->println("There may be error in saving the ticket.");
		return null;
	}
}

function response_ticket($ticket)
{
	global $adb,$log;
	$adb->println("Inside customer portal function response_ticket");
	$adb->println($ticket);
	$id = $ticket['id'];
	$sessionid = $ticket['sessionid'];

	if(!validateSession($id,$sessionid))
		return null;
	$sql="select * from vtiger_troubletickets vtt
			where ticketid_time=".$ticket['ticketid'];
	$ef_ticket=$adb->fetchByAssoc($adb->pquery($sql));
	// return array($ticket,$ef_ticket);
	if(empty($ef_ticket))
		return null;
	$sql="UPDATE vtiger_troubletickets set
			solution='".mysql_real_escape_string($ticket['coment'])."',
			work_hours='".$ticket['work_hours']."',
			status='TICKET_PENDING_CONFIRMATION_OF_CUSTOMER'
			where ticketid=".$ef_ticket['ticketid'];
	$adb->pquery($sql);
	$sql="UPDATE vtiger_account set
			horas_soporte=horas_soporte - '".$ticket['work_hours']."'
			where accountid=".$ef_ticket['parent_id'];
	$adb->pquery($sql);
	// return array($sql);
	return array("SUCCESS");


}

/**	function used to update the ticket comment which is added from the customer portal
 *	@param array $input_array - array which contains the following values
 => 	int $id - customer id
	int $sessionid - session id
	int $ticketid - ticket id
	int $ownerid - customer ie., contact id who has added this ticket comment
	string $comments - comment which is added from the customer portal
	*	return void
	*/
function update_ticket_comment($input_array)
{
	global $adb,$mod_strings,$current_user;
	$adb->println("Inside customer portal function update_ticket_comment");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];
	$ownerid = (int) $input_array['ownerid'];
	$comments = $input_array['comments'];

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	if(!validateSession($id,$sessionid))
		return null;

	if(trim($comments) != '') {

		$ticket = CRMEntity::getInstance('HelpDesk');
		$ticket->retrieve_entity_info($ticketid, 'HelpDesk');
		$ticket->id = $ticketid;
		$ticket->mode = 'edit';
		$ticket->column_fields['comments'] = $comments;
		$ticket->column_fields['from_portal'] = 1;
		$ticket->save('HelpDesk');
	}
}

/**	function used to close the ticket
 *	@param array $input_array - array which contains the following values
 => 	int $id - customer id
	int $sessionid - session id
	int $ticketid - ticket id
	*	return string - success or failure message will be returned based on the ticket close update query
	*/
function close_current_ticket($input_array)
{
	global $adb,$mod_strings,$log,$current_user;
	require_once('modules/HelpDesk/HelpDesk.php');
	$adb->println("Inside customer portal function close_current_ticket");
	$adb->println($input_array);

	//foreach($input_array as $fieldname => $fieldvalue)$input_array[$fieldname] = mysql_real_escape_string($fieldvalue);
	$userid = getPortalUserid();

	$current_user->id = $userid;
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];

	if(!validateSession($id,$sessionid))
		return null;

	$focus = new HelpDesk();
	$focus->id = $ticketid;
	$focus->retrieve_entity_info($focus->id,'HelpDesk');
	$focus->mode = 'edit';
	$focus->column_fields = array_map(decode_html, $focus->column_fields);
	$focus->column_fields['ticketstatus'] ='Closed';
	// Blank out the comments information to avoid un-necessary duplication
	$focus->column_fields['comments'] = '';
	// END
	$focus->save("HelpDesk");
	return "closed";
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

/**	function used to change the password for the customer portal
 *	@param array $input_array - array which contains the following values
 => 	int $id - customer id
	int $sessionid - session id
	string $username - customer name
	string $password - new password to change
	*	return array $list - returns array with all the customer details
	*/
function change_password($input_array)
{
	global $adb,$log;
	$log->debug("Entering customer portal function change_password");
	$adb->println($input_array);

	$id = (int) $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$username = $input_array['username'];
	$password = $input_array['password'];
	$version = $input_array['version'];

	if(!validateSession($id,$sessionid))
		return null;

	$list = authenticate_user($username,$password,$version ,'false');
	if(!empty($list[0]['id'])){
		return array('MORE_THAN_ONE_USER');
	}
	$sql = "update vtiger_portalinfo set user_password=? where id=? and user_name=?";
	$result = $adb->pquery($sql, array($password, $id, $username));

	$log->debug("Exiting customer portal function change_password");
	return $list;
}

/**	function used to update the login details for the customer
 *	@param array $input_array - array which contains the following values
 => 	int $id - customer id
	int $sessionid - session id
	string $flag - login/logout, based on this flag, login or logout time will be updated for the customer
	*	return string $list - empty value
	*/
function update_login_details($input_array)
{
	global $adb,$log;
	$log->debug("Entering customer portal function update_login_details");
	$adb->println("INPUT ARRAY for the function update_login_details");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$flag = $input_array['flag'];

	if(!validateSession($id,$sessionid))
		return null;

	$current_time = $adb->formatDate(date('YmdHis'), true);

	if($flag == 'login')
	{
		$sql = "update vtiger_portalinfo set login_time=? where id=?";
		$result = $adb->pquery($sql, array($current_time, $id));
	}
	elseif($flag == 'logout')
	{
		$sql = "update vtiger_portalinfo set logout_time=?, last_login_time=login_time where id=?";
		$result = $adb->pquery($sql, array($current_time, $id));
	}
	$log->debug("Exiting customer portal function update_login_details");
}

/**	function used to send mail to the customer when he forgot the password and want to retrieve the password
 *	@param string $mailid - email address of the customer
 *	return message about the mail sending whether entered mail id is correct or not or is there any problem in mail sending
 */
function send_mail_for_password($mailid)
{
	global $adb,$mod_strings,$log;
	$log->debug("Entering customer portal function send_mail_for_password");
	$adb->println("Inside the function send_mail_for_password($mailid).");

	$sql = "select * from vtiger_portalinfo  where user_name = ? ";
	$res = $adb->pquery($sql, array($mailid));
	$user_name = $adb->query_result($res,0,'user_name');
	$password = $adb->query_result($res,0,'user_password');
	$isactive = $adb->query_result($res,0,'isactive');

	$fromquery = "select vtiger_users.user_name, vtiger_users.email1 from vtiger_users inner join vtiger_crmentity on vtiger_users.id = vtiger_crmentity.smownerid inner join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_crmentity.crmid where vtiger_contactdetails.email =?";
	$from_res = $adb->pquery($fromquery, array($mailid));
	$initialfrom = $adb->query_result($from_res,0,'user_name');
	$from = $adb->query_result($from_res,0,'email1');

	$contents = $mod_strings['LBL_LOGIN_DETAILS'];
	$contents .= "<br><br>".$mod_strings['LBL_USERNAME']." ".$user_name;
	$contents .= "<br>".$mod_strings['LBL_PASSWORD']." ".$password;

	$mail = new PHPMailer();

	$mail->Subject = $mod_strings['LBL_SUBJECT_PORTAL_LOGIN_DETAILS'];
	$mail->Body    = $contents;
	$mail->IsSMTP();

	$mailserverresult = $adb->pquery("select * from vtiger_systems where server_type=?", array('email'));
	$mail_server = $adb->query_result($mailserverresult,0,'server');
	$mail_server_username = $adb->query_result($mailserverresult,0,'server_username');
	$mail_server_password = $adb->query_result($mailserverresult,0,'server_password');
	$smtp_auth = $adb->query_result($mailserverresult,0,'smtp_auth');

	$mail->Host = $mail_server;
	if($smtp_auth == 'true')
	$mail->SMTPAuth = 'true';
	$mail->Username = $mail_server_username;
	$mail->Password = $mail_server_password;
	$mail->From = $from;
	$mail->FromName = $initialfrom;

	$mail->AddAddress($user_name);
	$mail->AddReplyTo($current_user->name);
	$mail->WordWrap = 50;

	$mail->IsHTML(true);

	$mail->AltBody = $mod_strings['LBL_ALTBODY'];
	if($mailid == '')
	{
		$ret_msg = "false@@@<b>".$mod_strings['LBL_GIVE_MAILID']."</b>";
	}
	elseif($user_name == '' && $password == '')
	{
		$ret_msg = "false@@@<b>".$mod_strings['LBL_CHECK_MAILID']."</b>";
	}
	elseif($isactive == 0)
	{
		$ret_msg = "false@@@<b>".$mod_strings['LBL_LOGIN_REVOKED']."</b>";
	}
	elseif(!$mail->Send())
	{
		$ret_msg = "false@@@<b>".$mod_strings['LBL_MAIL_COULDNOT_SENT']."</b>";
	}
	else
	{
		$ret_msg = "true@@@<b>".$mod_strings['LBL_MAIL_SENT']."</b>";
	}

	$adb->println("Exit from send_mail_for_password. $ret_msg");
	$log->debug("Exiting customer portal function send_mail_for_password");
	return $ret_msg;
}

/**	function used to get the ticket creater
 *	@param array $input_array - array which contains the following values
 =>	int $id - customer ie., contact id
	int $sessionid - session id
	int $ticketid - ticket id
	*	return int $creator - ticket created user id will be returned ie., smcreatorid from crmentity table
	*/
function get_ticket_creator($input_array)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_ticket_creator");
	$adb->println("INPUT ARRAY for the function get_ticket_creator");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];

	if(!validateSession($id,$sessionid))
		return null;

	$res = $adb->pquery("select smcreatorid from vtiger_crmentity where crmid=?", array($ticketid));
	$creator = $adb->query_result($res,0,'smcreatorid');
	$log->debug("Exiting customer portal function get_ticket_creator");
	return $creator;
}

/**	function used to get the picklist values
 *	@param array $input_array - array which contains the following values
 =>	int $id - customer ie., contact id
	int $sessionid - session id
	string $picklist_name - picklist name you want to retrieve from database
	*	return array $picklist_array - all values of the corresponding picklist will be returned as a array
	*/
function get_picklists($input_array)
{
	global $adb, $log;
	$log->debug("Entering customer portal function get_picklists");
	$adb->println("INPUT ARRAY for the function get_picklists");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$picklist_name = $adb->sql_escape_string($input_array['picklist_name']);

	if(!validateSession($id,$sessionid))
	return null;

	$picklist_array = Array();

	$admin_role = 'H2';
	$userid = getPortalUserid();
	$roleres = $adb->pquery("SELECT roleid from vtiger_user2role where userid = ?", array($userid));
	$RowCount = $adb->num_rows($roleres);
	if($RowCount > 0){
		$admin_role = $adb->query_result($roleres,0,'roleid');
	}

	$res = $adb->pquery("select vtiger_". $picklist_name.".* from vtiger_". $picklist_name." inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_". $picklist_name.".picklist_valueid and vtiger_role2picklist.roleid='$admin_role'", array());
	for($i=0;$i<$adb->num_rows($res);$i++)
	{
		$picklist_val = $adb->query_result($res,$i,$picklist_name);
		$picklist_array[$i] = $picklist_val;
	}

	$adb->println($picklist_array);
	$log->debug("Exiting customer portal function get_picklists($picklist_name)");
	return $picklist_array;
}

/**	function to get the attachments of a ticket
 *	@param array $input_array - array which contains the following values
 =>	int $id - customer ie., contact id
	int $sessionid - session id
	int $ticketid - ticket id
	*	return array $output - This will return all the file details related to the ticket
	*/
function get_ticket_attachments($input_array)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_ticket_attachments");
	$adb->println("INPUT ARRAY for the function get_ticket_attachments");
	$adb->println($input_array);

	$check = checkModuleActive('Documents');
	if($check == false){
		return array("#MODULE INACTIVE#");
	}
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = $input_array['ticketid'];

	$isPermitted = check_permission($id,'HelpDesk',$ticketid);
	if($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}


	if(!validateSession($id,$sessionid))
	return null;

	$query = "select vtiger_troubletickets.ticketid, vtiger_attachments.*,vtiger_notes.filename,vtiger_notes.filelocationtype from vtiger_troubletickets " .
		"left join vtiger_senotesrel on vtiger_senotesrel.crmid=vtiger_troubletickets.ticketid " .
		"left join vtiger_notes on vtiger_notes.notesid=vtiger_senotesrel.notesid " .
		"inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_notes.notesid " .
		"left join vtiger_seattachmentsrel on vtiger_seattachmentsrel.crmid=vtiger_notes.notesid " .
		"left join vtiger_attachments on vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid " .
		"and vtiger_crmentity.deleted = 0 where vtiger_troubletickets.ticketid =?";

	$res = $adb->pquery($query, array($ticketid));
	$noofrows = $adb->num_rows($res);
	for($i=0;$i<$noofrows;$i++)
	{
		$filename = $adb->query_result($res,$i,'filename');
		$filepath = $adb->query_result($res,$i,'path');

		$fileid = $adb->query_result($res,$i,'attachmentsid');
		$filesize = filesize($filepath.$fileid."_".$filename);
		$filetype = $adb->query_result($res,$i,'type');
		$filelocationtype = $adb->query_result($res,$i,'filelocationtype');
		//Now we will not pass the file content to CP, when the customer click on the link we will retrieve
		//$filecontents = base64_encode(file_get_contents($filepath.$fileid."_".$filename));//fread(fopen($filepath.$filename, "r"), $filesize));

		$output[$i]['fileid'] = $fileid;
		$output[$i]['filename'] = $filename;
		$output[$i]['filetype'] = $filetype;
		$output[$i]['filesize'] = $filesize;
		$output[$i]['filelocationtype'] = $filelocationtype;
	}
	$log->debug("Exiting customer portal function get_ticket_attachments");
	return $output;
}

/**	function used to get the contents of a file
 *	@param array $input_array - array which contains the following values
 =>	int $id - customer ie., contact id
	int $sessionid - session id
	int $fileid - id of the file to which we want contents
	string $filename - name of the file to which we want contents
	*	return $filecontents array with single file contents like [fileid] => filecontent
	*/
function get_filecontent($input_array)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_filecontent");
	$adb->println("INPUT ARRAY for the function get_filecontent");
	$adb->println($input_array);
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$fileid = $input_array['fileid'];
	$filename = $input_array['filename'];
	$ticketid = $input_array['ticketid'];
	if(!validateSession($id,$sessionid))
	return null;

	$query = 'SELECT vtiger_attachments.path FROM vtiger_attachments
	INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
	INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_seattachmentsrel.crmid
	INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid = vtiger_notes.notesid
	INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_senotesrel.crmid
	WHERE vtiger_troubletickets.ticketid = ? AND vtiger_attachments.name = ? AND vtiger_attachments.attachmentsid = ?';
	$res = $adb->pquery($query, array($ticketid, $filename,$fileid));
	if($adb->num_rows($res)>0)
	{
		$filenamewithpath = $adb->query_result($res,0,'path').$fileid."_".$filename;
		$filecontents[$fileid] = base64_encode(file_get_contents($filenamewithpath));
	}
	$log->debug("Exiting customer portal function get_filecontent ");
	return $filecontents;
}

/**	function to add attachment for a ticket ie., the passed contents will be write in a file and the details will be stored in database
 *	@param array $input_array - array which contains the following values
 =>	int $id - customer ie., contact id
	int $sessionid - session id
	int $ticketid - ticket id
	string $filename - file name to be attached with the ticket
	string $filetype - file type
	int $filesize - file size
	string $filecontents - file contents as base64 encoded format
	*	return void
	*/
function add_ticket_attachment($input_array)
{
	global $adb,$log;
	global $root_directory, $upload_badext;
	$log->debug("Entering customer portal function add_ticket_attachment");
	$adb->println("INPUT ARRAY for the function add_ticket_attachment");
	$adb->println($input_array);
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = $input_array['ticketid'];
	$filename = $input_array['filename'];
	$filetype = $input_array['filetype'];
	$filesize = $input_array['filesize'];
	$filecontents = $input_array['filecontents'];

	if(!validateSession($id,$sessionid))
	return null;

	//decide the file path where we should upload the file in the server
	$upload_filepath = decideFilePath();

	$attachmentid = $adb->getUniqueID("vtiger_crmentity");

	//fix for space in file name
	$filename = sanitizeUploadFileName($filename, $upload_badext);
	$new_filename = $attachmentid.'_'.$filename;

	$data = base64_decode($filecontents);
	$description = 'CustomerPortal Attachment';

	//write a file with the passed content
	$handle = @fopen($upload_filepath.$new_filename,'w');
	fputs($handle, $data);
	fclose($handle);

	//Now store this file information in db and relate with the ticket
	$date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);

	$crmquery = "insert into vtiger_crmentity (crmid,setype,description,createdtime) values(?,?,?,?)";
	$crmresult = $adb->pquery($crmquery, array($attachmentid, 'HelpDesk Attachment', $description, $date_var));

	$attachmentquery = "insert into vtiger_attachments(attachmentsid,name,description,type,path) values(?,?,?,?,?)";
	$attachmentreulst = $adb->pquery($attachmentquery, array($attachmentid, $filename, $description, $filetype, $upload_filepath));

	$relatedquery = "insert into vtiger_seattachmentsrel values(?,?)";
	$relatedresult = $adb->pquery($relatedquery, array($ticketid, $attachmentid));

	$user_id = getDefaultAssigneeId();

	require_once('modules/Documents/Documents.php');

	$focus = new Documents();
	$focus->column_fields['notes_title'] = $filename;
	$focus->column_fields['filename'] = $filename;
	$focus->column_fields['filetype'] = $filetype;
	$focus->column_fields['filesize'] = $filesize;
	$focus->column_fields['filelocationtype'] = 'I';
	$focus->column_fields['filedownloadcount']= 0;
	$focus->column_fields['filestatus'] = 1;
	$focus->column_fields['assigned_user_id'] = $user_id;
	$focus->column_fields['folderid'] = 1;
	$focus->parent_id = $ticketid;
	$focus->customer = true;

	$focus->save('Documents');

	$related_doc = 'insert into vtiger_seattachmentsrel values (?,?)';
	$res = $adb->pquery($related_doc,array($focus->id,$attachmentid));

	$tic_doc = 'insert into vtiger_senotesrel values(?,?)';
	$res = $adb->pquery($tic_doc,array($ticketid,$focus->id));
	$log->debug("Exiting customer portal function add_ticket_attachment");
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


/**	function used to get the Account name
 *	@param int $id - Account id
 *	return string $message - Account name returned
 */
function get_account_name($accountid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_account_name");
	$res = $adb->pquery("select accountname from vtiger_account where accountid=?", array($accountid));
	$accountname=$adb->query_result($res,0,'accountname');
	$log->debug("Exiting customer portal function get_account_name");
	return $accountname;
}

/** function used to get the Contact name
 *  @param int $id -Contact id
 * return string $message -Contact name returned
 */
function get_contact_name($contactid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_contact_name");
	$contact_name = '';
	if($contactid != '')
	{
		$sql = "select firstname,lastname from vtiger_contactdetails where contactid=?";
		$result = $adb->pquery($sql, array($contactid));
		$firstname = $adb->query_result($result,0,"firstname");
		$lastname = $adb->query_result($result,0,"lastname");
		$contact_name = $firstname." ".$lastname;
		return $contact_name;
	}
	$log->debug("Exiting customer portal function get_contact_name");
	return false;
}

/**     function used to get the Account id
 **      @param int $id - Contact id
 **      return string $message - Account id returned
 **/

function get_check_account_id($id)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_check_account_id");
	$res = $adb->pquery("select accountid from vtiger_contactdetails where contactid=?", array($id));
	$accountid=$adb->query_result($res,0,'accountid');
	$log->debug("Entering customer portal function get_check_account_id");
	return $accountid;
}


/**	function used to get the vendor name
 *	@param int $id - vendor id
 *	return string $name - Vendor name returned
 */

function get_vendor_name($vendorid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_vendor_name");
	$res = $adb->pquery("select vendorname from vtiger_vendor where vendorid=?", array($vendorid));
	$name=$adb->query_result($res,0,'vendorname');
	$log->debug("Exiting customer portal function get_vendor_name");
	return $name;
}

function get_data_graficocustomerportal($id,$module,$sessionid)
{

	require_once('include/utils/UserInfoUtil.php');
	require_once('modules/Users/Users.php');
	global $adb,$log,$current_user;
	$log->debug("Entering customer portal function get_data_graficocustomerportal");
	$check = checkModuleActive($module);
	if($check == false){
		return array("#MODULE INACTIVE#");
	}
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	if(!validateSession($id,$sessionid))	return null;

	$contactquery = "SELECT  accountid FROM vtiger_contactdetails " .
		" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
		" AND vtiger_crmentity.deleted = 0 " .
		" WHERE (accountid = (SELECT accountid FROM vtiger_contactdetails WHERE contactid = ?)  AND accountid != 0) OR contactid = ?";
	$contactres = $adb->pquery($contactquery, array($id,$id));
	$accountid = $adb->query_result($contactres,0,'accountid');

	$output = array();

	$mesAyo = date("Y")-1;
	$mesAyo.= date("m");
	$querydata = "SELECT  anyomes,consumido,contratado  FROM tmm_graficocustomerportal WHERE accountid = ? AND anyomes >= ? ORDER BY anyomes ASC";
	$resdata = $adb->pquery($querydata, array($accountid,$mesAyo));
	$no_of_cont = $adb->num_rows($resdata);

	for($i=0;$i<$no_of_cont;$i++)
	{
		$anyomes = $adb->query_result($resdata,$i,'anyomes');
		$output[$i]['anyomes'] = $adb->query_result($resdata,$i,'anyomes');
		$output[$i]['consumido'] = $adb->query_result($resdata,$i,'consumido');
		$output[$i]['contratado'] = $adb->query_result($resdata,$i,'contratado');

		if ($output[$i]['anyomes'] >= '201301') {
			$mes = mktime( 0, 0, 0, substr($output[$i]['anyomes'],4,2), 1, substr($output[$i]['anyomes'],0,4) );
			$finMes = date("t",$mes);

			$fechaDesde = substr($output[$i]['anyomes'],0,4).'-'.substr($output[$i]['anyomes'],4,2).'-01';
			$fechaHasta = substr($output[$i]['anyomes'],0,4).'-'.substr($output[$i]['anyomes'],4,2).'-'.$finMes;

			$sql = "SELECT SUM(horas_dedicadas) as horas_dedicadas, accountname FROM vtiger_diarynotes_desarrolladores A
						INNER JOIN vtiger_troubletickets B ON (A.ticketid = B.ticketid)
						INNER JOIN vtiger_account C ON (B.parent_id = C.accountid)
						WHERE DATE(A.date) BETWEEN ? AND ? AND B.parent_id = ?
						GROUP BY B.parent_id ORDER BY 1 DESC";

			$result = $adb->pquery($sql,array($fechaDesde,$fechaHasta,$accountid),true);
			$output[$i]['consumido'] = $adb->query_result($result,0,'horas_dedicadas');
		}
	}

	$log->debug("Exiting customer portal function get_data_graficocustomerportal");
	return $output;
}



/**	function used to get the Quotes/Invoice List
 *	@param int $id - id -Contactid
 *	return string $output - Quotes/Invoice list Array
 */

function get_list_values($id,$module,$sessionid,$only_mine='true')
{
	require_once('modules/'.$module.'/'.$module.'.php');
	require_once('include/utils/UserInfoUtil.php');
	global $adb,$log,$current_user;
	$log->debug("Entering customer portal function get_list_values");
	$check = checkModuleActive($module);
	if($check == false){
		return array("#MODULE INACTIVE#");
	}
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	$focus = new $module();
	$focus->filterInactiveFields($module);
	foreach ($focus->list_fields as $fieldlabel => $values){
		foreach($values as $table => $fieldname){
			$fields_list[$fieldlabel] = $fieldname;
		}
	}

	if(!validateSession($id,$sessionid))
	return null;

	$entity_ids_list = array();
	$show_all=show_all($module);
	if($only_mine == 'true' || $show_all == 'false')
	{
		array_push($entity_ids_list,$id);
	}
	else
	{
		$contactquery = "SELECT contactid, accountid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" AND vtiger_crmentity.deleted = 0 " .
			" WHERE (accountid = (SELECT accountid FROM vtiger_contactdetails WHERE contactid = ?)  AND accountid != 0) OR contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id,$id));
		$no_of_cont = $adb->num_rows($contactres);
		for($i=0;$i<$no_of_cont;$i++)
		{
			$cont_id = $adb->query_result($contactres,$i,'contactid');
			$acc_id = $adb->query_result($contactres,$i,'accountid');
			if(!in_array($cont_id, $entity_ids_list))
			$entity_ids_list[] = $cont_id;
			if(!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
			$entity_ids_list[] = $acc_id;
		}
	}
	if($module == 'Quotes')
	{
		$query = "select distinct vtiger_quotes.*,vtiger_crmentity.smownerid,
		case when vtiger_quotes.contactid is not null then vtiger_quotes.contactid else vtiger_quotes.accountid end as entityid,
		case when vtiger_quotes.contactid is not null then 'Contacts' else 'Accounts' end as setype,
		vtiger_potential.potentialname,vtiger_account.accountid
		from vtiger_quotes
		left join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_quotes.quoteid
		LEFT OUTER JOIN vtiger_account
		ON vtiger_account.accountid = vtiger_quotes.accountid
		LEFT OUTER JOIN vtiger_potential
		ON vtiger_potential.potentialid = vtiger_quotes.potentialid
		where vtiger_crmentity.deleted=0 and (vtiger_quotes.accountid in  (". generateQuestionMarks($entity_ids_list) .") or contactid in (". generateQuestionMarks($entity_ids_list) ."))";
		$params = array($entity_ids_list,$entity_ids_list);
		$fields_list['Related To'] = 'entityid';

	}
	else if($module == 'Invoice')
	{
		$query ="select distinct vtiger_invoice.*, vtiger_crmentity.smownerid,vtiger_invoicecf.*,
		case when vtiger_invoice.contactid !=0 then vtiger_invoice.contactid else vtiger_invoice.accountid end as entityid,
		case when vtiger_invoice.contactid !=0 then 'Contacts' else 'Accounts' end as setype

		FROM vtiger_invoice
		INNER JOIN vtiger_invoicecf ON vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid
		LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid

		WHERE vtiger_crmentity.deleted=0 and (accountid in (". generateQuestionMarks($entity_ids_list) .") or contactid in  (". generateQuestionMarks($entity_ids_list) ."))".
		"ORDER BY vtiger_invoice.invoiceid DESC";
		$params = array($entity_ids_list,$entity_ids_list);
		// $fields_list['Related To'] = 'entityid';
		$fields_list = array();
		$fields_list['Mes'] = 'cf_615';
		$fields_list['Referencia'] = 'subject';
		$fields_list['Total'] = 'total';
		$fields_list['Estado'] = 'invoicestatus';
		$fields_list['Download'] = 'subject';

	}
	else if($module == 'ServiceContracts')
	{
		$query ="select distinct vtiger_servicecontracts.*,vtiger_crmentity.smownerid, sc_related_to as entityid
		from vtiger_servicecontracts
		left join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_servicecontracts.servicecontractsid
		where vtiger_crmentity.deleted=0 and sc_related_to in (". generateQuestionMarks($entity_ids_list) .") ".
		"ORDER BY vtiger_servicecontracts.servicecontractsid DESC";
		$params = array($entity_ids_list);
		$fields_list = array();
		$fields_list['N&uacute;mero Contrato'] = 'contract_no';
		$fields_list['Asunto'] = 'subject';
		$fields_list['Fecha inicio'] = 'start_date';
		$fields_list['Fecha vencimiento'] = 'due_date';
		$fields_list['Estado'] = 'contract_status';
	}
	else if($module == 'ServiceTasks')
	{
		$query ="select *
		from tmm_servicetasks
		where accountid in (". generateQuestionMarks($entity_ids_list) .") ".
		"ORDER BY date_scheduled DESC";
		$params = array($entity_ids_list);
		$fields_list = array();
		$fields_list['# ID'] = 'servicetasksid';
		$fields_list['Tarea'] = 'detail';
		$fields_list['Fecha Programada'] = 'date_scheduled';
		$fields_list['Realizada'] = 'date_executed';
	}
	else if ($module == 'Documents')
	{
		$query ="select vtiger_notes.*, vtiger_crmentity.*, vtiger_senotesrel.crmid as entityid, '' as setype,vtiger_attachmentsfolder.foldername from vtiger_notes " .
		"inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_notes.notesid " .
		"left join vtiger_senotesrel on vtiger_senotesrel.notesid=vtiger_notes.notesid " .
		"LEFT JOIN vtiger_attachmentsfolder ON vtiger_attachmentsfolder.folderid = vtiger_notes.folderid " .
		"where vtiger_crmentity.deleted = 0 and  vtiger_senotesrel.crmid in (".generateQuestionMarks($entity_ids_list).")".
		"ORDER BY vtiger_notes.notesid DESC";
		$params = array($entity_ids_list);
		$fields_list['Related To'] = 'entityid';
	}else if ($module == 'Contacts'){
		$query = "select vtiger_contactdetails.*,vtiger_crmentity.smownerid from vtiger_contactdetails
		 inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
		 where vtiger_crmentity.deleted = 0 and contactid IN (".generateQuestionMarks($entity_ids_list).")";
		$params = array($entity_ids_list);
	}else if ($module == 'Assets') {
		$accountRes = $adb->pquery("SELECT accountid FROM vtiger_contactdetails
						INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						WHERE contactid = ? AND deleted = 0", array($id));
		$accountRow = $adb->num_rows($accountRes);
		if($accountRow) {
		$accountid = $adb->query_result($accountRes, 0, 'accountid');
		$query = "select vtiger_assets.*, vtiger_assets.account as entityid , vtiger_crmentity.smownerid from vtiger_assets
						inner join vtiger_crmentity on vtiger_assets.assetsid = vtiger_crmentity.crmid
						left join vtiger_account on vtiger_account.accountid = vtiger_assets.account
						left join vtiger_products on vtiger_products.productid = vtiger_assets.product
						where vtiger_crmentity.deleted = 0 and account = ?";
		$params = array($accountid);
		$fields_list['Related To'] = 'entityid';
		}
	}else if ($module == 'Project') {
		$query = "SELECT vtiger_project.*, vtiger_crmentity.smownerid
					FROM vtiger_project
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid
					WHERE vtiger_crmentity.deleted = 0 AND vtiger_project.linktoaccountscontacts IN (".generateQuestionMarks($entity_ids_list).")";
		$params = array($entity_ids_list);
		$fields_list['Related To'] = 'linktoaccountscontacts';
	}

	$res = $adb->pquery($query,$params);
	$noofdata = $adb->num_rows($res);

	$columnVisibilityByFieldnameInfo = array();
	if($noofdata) {
		foreach($fields_list as $fieldlabel =>$fieldname ) {
			$columnVisibilityByFieldnameInfo[$fieldname] = getColumnVisibilityPermission($current_user->id,$fieldname,$module);
		}
	}


	for( $j= 0;$j < $noofdata; $j++)
	{
		$i=0;
		foreach($fields_list as $fieldlabel =>$fieldname ) {
			$fieldper = $columnVisibilityByFieldnameInfo[$fieldname];
			if($fieldper == '1' && $fieldname != 'entityid'){
				continue;
			}
			$fieldlabel = getTranslatedString($fieldlabel,$module);

			$output[0][$module]['head'][0][$i]['fielddata'] = $fieldlabel;
			$fieldvalue = $adb->query_result($res,$j,$fieldname);

			if($module == 'Quotes')
			{
				if($fieldname =='subject'){
					$fieldid = $adb->query_result($res,$j,'quoteid');
					$filename = $fieldid.'_Quotes.pdf';
					$fieldvalue = '<a href="index.php?&module=Quotes&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}
				if($fieldname == 'total'){
					$sym = getCurrencySymbol($res,$j,'currency_id');
					$fieldvalue = $sym.$fieldvalue;
				}
			}
			if($module == 'Invoice')
			{

				if($fieldlabel =='Download'){
					$fieldid = $adb->query_result($res,$j,'invoiceid');
					$fieldvalue = '<a href="index.php?downloadfile=true&module=Invoice&action=index&id='.$fieldid.'"><img src="images/pdf_icon.gif" border="0"></a>';
				}
				if($fieldname == 'total'){
					$sym = getCurrencySymbol($res,$j,'currency_id');
					$fieldvalue = $sym.$fieldvalue;
				}
			}
			if($module == 'ServiceContracts')
			{
				if(($fieldname =='start_date')||($fieldname =='due_date')){
					$fieldvalue =  date("d-m-Y",strtotime($fieldvalue));
				}
				if($fieldname =='subject'){
					$fieldid = $adb->query_result($res,$j,'servicecontractsid');
					$fieldvalue = '<a href="index.php?&module=ServiceContracts&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}
			}
			if($module == 'ServiceTasks')
			{
				if($fieldname =='date_scheduled'){
					$fieldvalue =  date("d-m-Y",strtotime($fieldvalue));
				}
				if($fieldname =='date_executed'){
					if($fieldvalue  != '0000-00-00')  $fieldvalue = '<img src="images/realizada.gif" border="0">'; else $fieldvalue='';
				}
			}
			if($module == 'Documents')
			{
				if($fieldname == 'title'){
					$fieldid = $adb->query_result($res,$j,'notesid');
					$fieldvalue = '<a href="index.php?&module=Documents&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}
				if( $fieldname == 'filename'){
					$fieldid = $adb->query_result($res,$j,'notesid');
					$filename = $fieldvalue;
					$folderid = $adb->query_result($res,$j,'folderid');
					$filename = $adb->query_result($res,$j,'filename');
					$fileactive = $adb->query_result($res,$j,'filestatus');
					$filetype = $adb->query_result($res,$j,'filelocationtype');

					if($fileactive == 1){
						if($filetype == 'I'){
							$fieldvalue = '<a href="index.php?&downloadfile=true&folderid='.$folderid.'&filename='.$filename.'&module=Documents&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
						}
						elseif($filetype == 'E'){
							$fieldvalue = '<a target="_blank" href="'.$filename.'" onclick = "updateCount('.$fieldid.');">'.$filename.'</a>';
						}
					}else{
						$fieldvalue = $filename;
					}
				}
				if($fieldname == 'folderid'){
					$fieldvalue = $adb->query_result($res,$j,'foldername');
				}
			}
			if($module == 'Invoice' && $fieldname == 'salesorderid')
			{
				if($fieldvalue != '')
				$fieldvalue = get_salesorder_name($fieldvalue);
			}

			if($module == 'Services'){
				if($fieldname == 'servicename'){
					$fieldid = $adb->query_result($res,$j,'serviceid');
					$fieldvalue = '<a href="index.php?module=Services&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}
				if($fieldname == 'discontinued'){
					if($fieldvalue == 1){
						$fieldvalue = 'Yes';
					}else{
						$fieldvalue = 'No';
					}
				}
				if($fieldname == 'unit_price'){
					$sym = getCurrencySymbol($res,$j,'currency_id');
					$fieldvalue = $sym.$fieldvalue;
				}

			}
			if($module == 'Contacts'){
				if($fieldname == 'lastname' || $fieldname == 'firstname'){
					$fieldid = $adb->query_result($res,$j,'contactid');
					$fieldvalue ='<a href="index.php?module=Contacts&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}
			}
			if($module == 'Project'){
				if($fieldname == 'projectname'){
					$fieldid = $adb->query_result($res,$j,'projectid');
					$fieldvalue = '<a href="index.php?module=Project&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}
			}
			if($fieldname == 'entityid' || $fieldname == 'contactid' || $fieldname == 'accountid' || $fieldname == 'potentialid' || $fieldname == 'account' || $fieldname == 'linktoaccountscontacts') {
				$crmid = $fieldvalue;
				$modulename = getSalesEntityType($crmid);
				if ($crmid != '' && $modulename != '') {
					$fieldvalues = getEntityName($modulename, array($crmid));
					if($modulename == 'Contacts')
					$fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					elseif($modulename == 'Accounts')
					$fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					elseif($modulename == 'Potentials'){
						$fieldvalue = $adb->query_result($res,$j,'potentialname');
					}
				} else {
					$fieldvalue = '';
				}
			}
			if($module == 'Assets' && $fieldname == 'assetname') {
					$assetname = $fieldvalue;
					$assetid = $adb->query_result($res, $j, 'assetsid');
					$fieldvalue = '<a href="index.php?module=Assets&action=index&id='.$assetid.'">'.$assetname.'</a>';
			}
			if($fieldname == 'product' && $module == 'Assets'){
				$crmid= $adb->query_result($res,$j,'product');
				$fres = $adb->pquery('select vtiger_products.productname from vtiger_products where productid=?',array($crmid));
				$productname = $adb->query_result($fres,0,'productname');
				$fieldvalue = '<a href="index.php?module=Products&action=index&id='.$crmid.'">'.$productname.'</a>';
			}
			if($fieldname == 'smownerid'){
				$fieldvalue = getOwnerName($fieldvalue);
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customer portal function get_list_values");
	return $output;

}


/**	function used to get the contents of a file
 *	@param int $id - customer ie., id
 *	return $filecontents array with single file contents like [fileid] => filecontent
 */
function get_filecontent_detail($id,$folderid,$module,$customerid,$sessionid)
{
	global $adb,$log;
	global $site_URL;
	$log->debug("Entering customer portal function get_filecontent_detail ");
	$isPermitted = check_permission($customerid,$module,$id);
	if($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}

	if(!validateSession($customerid,$sessionid))
	return null;

	if($module == 'Documents')
	{
		$query="SELECT filetype FROM vtiger_notes WHERE notesid =?";
		$res = $adb->pquery($query, array($id));
		$filetype = $adb->query_result($res, 0, "filetype");
		updateDownloadCount($id);

		$fileidQuery = 'select attachmentsid from vtiger_seattachmentsrel where crmid = ?';
		$fileres = $adb->pquery($fileidQuery,array($id));
		$fileid = $adb->query_result($fileres,0,'attachmentsid');

		$filepathQuery = 'select path,name from vtiger_attachments where attachmentsid = ?';
		$fileres = $adb->pquery($filepathQuery,array($fileid));
		$filepath = $adb->query_result($fileres,0,'path');
		$filename = $adb->query_result($fileres,0,'name');
		$filename= decode_html($filename);

		$saved_filename =  $fileid."_".$filename;
		$filenamewithpath = $filepath.$saved_filename;
		$filesize = filesize($filenamewithpath );
	}
	else
	{
		$query ='select vtiger_attachments.*,vtiger_seattachmentsrel.* from vtiger_attachments inner join vtiger_seattachmentsrel on vtiger_seattachmentsrel.attachmentsid=vtiger_attachments.attachmentsid where vtiger_seattachmentsrel.crmid =?';

		$res = $adb->pquery($query, array($id));

		$filename = $adb->query_result($res,0,'name');
		$filename = decode_html($filename);
		$filepath = $adb->query_result($res,0,'path');
		$fileid = $adb->query_result($res,0,'attachmentsid');
		$filesize = filesize($filepath.$fileid."_".$filename);
		$filetype = $adb->query_result($res,0,'type');
		$filenamewithpath=$filepath.$fileid.'_'.$filename;

	}
	$output[0]['fileid'] = $fileid;
	$output[0]['filename'] = $filename;
	$output[0]['filetype'] = $filetype;
	$output[0]['filesize'] = $filesize;
	$output[0]['filecontents']=base64_encode(file_get_contents($filenamewithpath));
	$log->debug("Exiting customer portal function get_filecontent_detail ");
	return $output;
}

/** Function that the client actually calls when a file is downloaded
 *
 */
function updateCount($id){
	global $adb,$log;
	$log->debug("Entering customer portal function updateCount");
	$result = updateDownloadCount($id);
	$log->debug("Entering customer portal function updateCount");
	return $result;

}

/**
 * Function to update the download count of a file
 */
function updateDownloadCount($id){
	global $adb,$log;
	$log->debug("Entering customer portal function updateDownloadCount");
	$updateDownloadCount = "UPDATE vtiger_notes SET filedownloadcount = filedownloadcount+1 WHERE notesid = ?";
	$countres = $adb->pquery($updateDownloadCount,array($id));
	$log->debug("Entering customer portal function updateDownloadCount");
	return true;
}

/**	function used to get the Quotes/Invoice pdf
 *	@param int $id - id -id
 *	return string $output - pd link value
 */

function get_pdf($id,$block,$customerid,$sessionid)
{
	global $adb;
	global $current_user,$log,$default_language;
	global $currentModule,$mod_strings,$app_strings,$app_list_strings;
	$log->debug("Entering customer portal function get_pdf");
	$isPermitted = check_permission($customerid,$block,$id);
	if($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}

	if(!validateSession($customerid,$sessionid))
	return null;

	require_once("config.inc.php");
	$current_user = Users::getActiveAdminUser();

	$currentModule = $block;
	$current_language = $default_language;
	$app_strings = return_application_language($current_language);
	$app_list_strings = return_app_list_strings_language($current_language);
	$mod_strings = return_module_language($current_language, $currentModule);

	$_REQUEST['record']= $id;
	$_REQUEST['savemode']= 'file';
	$sequenceNo = getModuleSequenceNumber($block, $id);
	$filenamewithpath='test/product/'.$id.'_'.$block.'_'.$sequenceNo.'.pdf';
	if (file_exists($filenamewithpath) && (filesize($filenamewithpath) != 0))
	unlink($filenamewithpath);

	checkFileAccessForInclusion("modules/$block/CreatePDF.php");
	include("modules/$block/CreatePDF.php");

	if (file_exists($filenamewithpath) && (filesize($filenamewithpath) != 0))
	{
		//we have to pass the file content
		$filecontents[] = base64_encode(file_get_contents($filenamewithpath));
		unlink($filenamewithpath);
		// TODO: Delete the file to avoid public access.
	}
	else
	{
		$filecontents = "failure";
	}
	$log->debug("Exiting customer portal function get_pdf");
	return $filecontents;
}

/**	function used to get the salesorder name
 *	@param int $id -  id
 *	return string $name - Salesorder name returned
 */

function get_salesorder_name($id)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_salesorder_name");
	$res = $adb->pquery(" select subject from vtiger_salesorder where salesorderid=?", array($id));
	$name=$adb->query_result($res,0,'subject');
	$log->debug("Exiting customer portal function get_salesorder_name");
	return $name;
}

function get_invoice_detail($id,$module,$customerid,$sessionid)
{
	require_once('include/utils/UserInfoUtil.php');
	require_once('include/utils/utils.php');

	global $adb,$site_URL,$log,$current_user;
	$log->debug("Entering customer portal function get_invoice_details $id - $module - $customerid - $sessionid");
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$isPermitted = check_permission($customerid,$module,$id);
	if($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}

	if(!validateSession($customerid,$sessionid))
	return null;

	$fieldquery = "SELECT fieldname, columnname, fieldlabel,block,uitype FROM vtiger_field WHERE tabid = ? AND displaytype in (1,2,4) ORDER BY block,sequence";
	$fieldres = $adb->pquery($fieldquery,array(getTabid($module)));
	$nooffields = $adb->num_rows($fieldres);
	$query = "select vtiger_invoice.*,vtiger_crmentity.* ,vtiger_invoicebillads.*,vtiger_invoiceshipads.*,
		vtiger_invoicecf.* from vtiger_invoice
		inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_invoice.invoiceid
		LEFT JOIN vtiger_invoicebillads ON vtiger_invoice.invoiceid = vtiger_invoicebillads.invoicebilladdressid
		LEFT JOIN vtiger_invoiceshipads ON vtiger_invoice.invoiceid = vtiger_invoiceshipads.invoiceshipaddressid
		INNER JOIN vtiger_invoicecf ON vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid
		where vtiger_invoice.invoiceid=?";
	$res = $adb->pquery($query, array($id));

	for($i=0;$i<$nooffields;$i++)
	{
		$fieldname = $adb->query_result($fieldres,$i,'columnname');
		$fieldlabel = getTranslatedString($adb->query_result($fieldres,$i,'fieldlabel'));

		$blockid = $adb->query_result($fieldres,$i,'block');
		$blocknameQuery = "select blocklabel from vtiger_blocks where blockid = ?";
		$blockPquery = $adb->pquery($blocknameQuery,array($blockid));
		$blocklabel = $adb->query_result($blockPquery,0,'blocklabel');

		$fieldper = getFieldVisibilityPermission($module,$current_user->id,$fieldname);
		if($fieldper == '1'){
			continue;
		}

		$fieldvalue = $adb->query_result($res,0,$fieldname);
		if($fieldname == 'subject' && $fieldvalue !='')
		{
			$fieldid = $adb->query_result($res,0,'invoiceid');
			//$fieldlabel = "(Download PDF)  ".$fieldlabel;
			$fieldvalue = '<a href="index.php?downloadfile=true&module=Invoice&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
		}
		if( $fieldname == 'salesorderid' || $fieldname == 'contactid' || $fieldname == 'accountid' || $fieldname == 'potentialid')
		{
			$crmid = $fieldvalue;
			$Entitymodule = getSalesEntityType($crmid);
			if ($crmid != '' && $Entitymodule != '') {
				$fieldvalues = getEntityName($Entitymodule, array($crmid));
				if($Entitymodule == 'Contacts')
				$fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
				elseif($Entitymodule == 'Accounts')
				$fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
				else
				$fieldvalue = $fieldvalues[$crmid];
			} else {
				$fieldvalue = '';
			}
		}
		if($fieldname == 'total'){
			$sym = getCurrencySymbol($res,0,'currency_id');
			$fieldvalue = $sym.$fieldvalue;
		}
		if($fieldname == 'smownerid'){
			$fieldvalue = getOwnerName($fieldvalue);
		}
		$output[0][$module][$i]['fieldlabel'] = $fieldlabel;
		$output[0][$module][$i]['fieldvalue'] = $fieldvalue;
		$output[0][$module][$i]['blockname'] = getTranslatedString($blocklabel,$module);
	}
	$log->debug("Entering customer portal function get_invoice_detail ..");
	return $output;
}

/* Function to get contactid's and account's product details'
 *
 */
function get_product_list_values($id,$modulename,$sessionid,$only_mine='true')
{
	require_once('modules/Products/Products.php');
	require_once('include/utils/UserInfoUtil.php');
	global $current_user,$adb,$log;
	$log->debug("Entering customer portal function get_product_list_values ..");
	$check = checkModuleActive($modulename);
	if($check == false){
		return array("#MODULE INACTIVE#");
	}
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	$entity_ids_list = array();
	$show_all=show_all($modulename);

	if(!validateSession($id,$sessionid))
	return null;

	if($only_mine == 'true' || $show_all == 'false')
	{
		array_push($entity_ids_list,$id);
	}
	else
	{
		$contactquery = "SELECT contactid, accountid FROM vtiger_contactdetails " .
		" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
		" AND vtiger_crmentity.deleted = 0 " .
		" WHERE (accountid = (SELECT accountid FROM vtiger_contactdetails WHERE contactid = ?)  AND accountid != 0) OR contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id,$id));
		$no_of_cont = $adb->num_rows($contactres);
		for($i=0;$i<$no_of_cont;$i++)
		{
			$cont_id = $adb->query_result($contactres,$i,'contactid');
			$acc_id = $adb->query_result($contactres,$i,'accountid');
			if(!in_array($cont_id, $entity_ids_list))
			$entity_ids_list[] = $cont_id;
			if(!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
			$entity_ids_list[] = $acc_id;
		}
	}

	$focus = new Products();
	$focus->filterInactiveFields('Products');
	foreach ($focus->list_fields as $fieldlabel => $values){
		foreach($values as $table => $fieldname){
			$fields_list[$fieldlabel] = $fieldname;
		}
	}
	$fields_list['Related To'] = 'entityid';
	$query = array();
	$params = array();
	$query[] = "SELECT vtiger_products.*,vtiger_seproductsrel.crmid as entityid, vtiger_seproductsrel.setype FROM vtiger_products
		INNER JOIN vtiger_crmentity on vtiger_products.productid = vtiger_crmentity.crmid
		LEFT JOIN vtiger_seproductsrel on vtiger_seproductsrel.productid = vtiger_products.productid
		WHERE vtiger_seproductsrel.crmid in (". generateQuestionMarks($entity_ids_list).") and vtiger_crmentity.deleted = 0 ";
	$params[] = array($entity_ids_list);

	$checkQuotes = checkModuleActive('Quotes');
	if($checkQuotes == true){
		$query[] = "select distinct vtiger_products.*,
			case when vtiger_quotes.contactid is not null then vtiger_quotes.contactid else vtiger_quotes.accountid end as entityid,
			case when vtiger_quotes.contactid is not null then 'Contacts' else 'Accounts' end as setype
			from vtiger_quotes INNER join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_quotes.quoteid
			left join vtiger_inventoryproductrel on vtiger_inventoryproductrel.id=vtiger_quotes.quoteid
			left join vtiger_products on vtiger_products.productid = vtiger_inventoryproductrel.productid
			where vtiger_inventoryproductrel.productid = vtiger_products.productid AND vtiger_crmentity.deleted=0 and (accountid in  (". generateQuestionMarks($entity_ids_list) .") or contactid in (". generateQuestionMarks($entity_ids_list) ."))";
		$params[] = array($entity_ids_list,$entity_ids_list);
	}
	$checkInvoices = checkModuleActive('Invoice');
	if($checkInvoices == true){
		$query[] = "select distinct vtiger_products.*,
			case when vtiger_invoice.contactid !=0 then vtiger_invoice.contactid else vtiger_invoice.accountid end as entityid,
			case when vtiger_invoice.contactid !=0 then 'Contacts' else 'Accounts' end as setype
			from vtiger_invoice
			INNER join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid
			left join vtiger_inventoryproductrel on vtiger_inventoryproductrel.id=vtiger_invoice.invoiceid
			left join vtiger_products on vtiger_products.productid = vtiger_inventoryproductrel.productid
			where vtiger_inventoryproductrel.productid = vtiger_products.productid AND vtiger_crmentity.deleted=0 and (accountid in (". generateQuestionMarks($entity_ids_list) .") or contactid in  (". generateQuestionMarks($entity_ids_list) ."))";
		$params[] = array($entity_ids_list,$entity_ids_list);
	}
	for($k=0;$k<count($query);$k++)
	{
		$res[$k] = $adb->pquery($query[$k],$params[$k]);
		$noofdata[$k] = $adb->num_rows($res[$k]);
		if($noofdata[$k] == 0)
		$output[$k][$modulename]['data'] = '';
		for( $j= 0;$j < $noofdata[$k]; $j++)
		{
			$i=0;
			foreach($fields_list as $fieldlabel=> $fieldname) {
				$fieldper = getFieldVisibilityPermission('Products',$current_user->id,$fieldname);
				if($fieldper == '1' && $fieldname != 'entityid'){
					continue;
				}
				$output[$k][$modulename]['head'][0][$i]['fielddata'] = $fieldlabel;
				$fieldvalue = $adb->query_result($res[$k],$j,$fieldname);
				$fieldid = $adb->query_result($res[$k],$j,'productid');

				if($fieldname == 'entityid') {
					$crmid = $fieldvalue;
					$module = $adb->query_result($res[$k],$j,'setype');
					if ($crmid != '' && $module != '') {
						$fieldvalues = getEntityName($module, array($crmid));
						if($module == 'Contacts')
						$fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
						elseif($module == 'Accounts')
						$fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					} else {
						$fieldvalue = '';
					}
				}

				if($fieldname == 'productname')
				$fieldvalue = '<a href="index.php?module=Products&action=index&productid='.$fieldid.'">'.$fieldvalue.'</a>';

				if($fieldname == 'unit_price'){
					$sym = getCurrencySymbol($res[$k],$j,'currency_id');
					$fieldvalue = $sym.$fieldvalue;
				}
				$output[$k][$modulename]['data'][$j][$i]['fielddata'] = $fieldvalue;
				$i++;
			}
		}
	}
	$log->debug("Exiting function get_product_list_values.....");
	return $output;
}

/*function used to get details of tickets,quotes,documents,Products,Contacts,Accounts
 *	@param int $id - id of quotes or invoice or notes
 *	return string $message - Account informations will be returned from :Accountdetails table
 */
function get_details($id,$module,$customerid,$sessionid)
{
	global $adb,$log,$current_language,$default_language,$current_user;
	require_once('include/utils/utils.php');
	require_once('include/utils/UserInfoUtil.php');
	$log->debug("Entering customer portal function get_details ..");

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$current_language = $default_language;
	$isPermitted = check_permission($customerid,$module,$id);
	if($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}

	if(!validateSession($customerid,$sessionid))
	return null;

	if($module == 'Quotes'){
		$query =  "SELECT
			vtiger_quotes.*,vtiger_crmentity.*,vtiger_quotesbillads.*,vtiger_quotesshipads.*,
			vtiger_quotescf.* FROM vtiger_quotes
			INNER JOIN vtiger_crmentity " .
				"ON vtiger_crmentity.crmid = vtiger_quotes.quoteid
			INNER JOIN vtiger_quotesbillads
				ON vtiger_quotes.quoteid = vtiger_quotesbillads.quotebilladdressid
			INNER JOIN vtiger_quotesshipads
				ON vtiger_quotes.quoteid = vtiger_quotesshipads.quoteshipaddressid
			LEFT JOIN vtiger_quotescf
				ON vtiger_quotes.quoteid = vtiger_quotescf.quoteid
			WHERE vtiger_quotes.quoteid=(". generateQuestionMarks($id) .") AND vtiger_crmentity.deleted = 0";

	}
	else if($module == 'Documents'){
		$query =  "SELECT
			vtiger_notes.*,vtiger_crmentity.*,vtiger_attachmentsfolder.foldername
			FROM vtiger_notes
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_notes.notesid
			LEFT JOIN vtiger_attachmentsfolder
				ON vtiger_notes.folderid = vtiger_attachmentsfolder.folderid
			where vtiger_notes.notesid=(". generateQuestionMarks($id) .") AND vtiger_crmentity.deleted=0";
	}
	else if($module == 'HelpDesk'){
		$query ="SELECT
			vtiger_troubletickets.*,vtiger_crmentity.smownerid,vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime,
			vtiger_ticketcf.*,vtiger_crmentity.description  FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
			INNER JOIN vtiger_ticketcf
				ON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
			WHERE (vtiger_troubletickets.ticketid=(". generateQuestionMarks($id) .") AND vtiger_crmentity.deleted = 0)";
	}
	else if($module == 'Services'){
		$query ="SELECT vtiger_service.*,vtiger_crmentity.*,vtiger_servicecf.*  FROM vtiger_service
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_service.serviceid AND vtiger_crmentity.deleted = 0
			LEFT JOIN vtiger_servicecf
				ON vtiger_service.serviceid = vtiger_servicecf.serviceid
			WHERE vtiger_service.serviceid= (". generateQuestionMarks($id) .")";
	}
	else if($module == 'Contacts'){
		$query = "SELECT vtiger_contactdetails.*,vtiger_contactaddress.*,vtiger_contactsubdetails.*,vtiger_contactscf.*" .
			" ,vtiger_crmentity.*,vtiger_customerdetails.*
		 	FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactaddress
				ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactsubdetails
				ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactscf
				ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid
			LEFT JOIN vtiger_customerdetails
				ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
			WHERE vtiger_contactdetails.contactid = (". generateQuestionMarks($id) .") AND vtiger_crmentity.deleted = 0";
	}
	else if($module == 'Accounts'){
		$query = "SELECT vtiger_account.*,vtiger_accountbillads.*,vtiger_accountshipads.*,vtiger_accountscf.*,
			vtiger_crmentity.* FROM vtiger_account
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_account.accountid
			INNER JOIN vtiger_accountbillads
				ON vtiger_account.accountid = vtiger_accountbillads.accountaddressid
			INNER JOIN vtiger_accountshipads
				ON vtiger_account.accountid = vtiger_accountshipads.accountaddressid
			INNER JOIN vtiger_accountscf
				ON vtiger_account.accountid = vtiger_accountscf.accountid" .
		" WHERE vtiger_account.accountid = (". generateQuestionMarks($id) .") AND vtiger_crmentity.deleted = 0";
	}
	else if ($module == 'Products'){
		$query = "SELECT vtiger_products.*,vtiger_productcf.*,vtiger_crmentity.* " .
		"FROM vtiger_products " .
		"INNER JOIN vtiger_crmentity " .
			"ON vtiger_crmentity.crmid = vtiger_products.productid " .
		"LEFT JOIN vtiger_productcf " .
			"ON vtiger_productcf.productid = vtiger_products.productid " .
		"LEFT JOIN vtiger_vendor
			ON vtiger_vendor.vendorid = vtiger_products.vendor_id " .
		"WHERE vtiger_products.productid = (". generateQuestionMarks($id) .") AND vtiger_crmentity.deleted = 0";
	} else if($module == 'Assets') {
		$query = "SELECT vtiger_assets.*, vtiger_assetscf.*, vtiger_crmentity.*
		FROM vtiger_assets
		INNER JOIN vtiger_crmentity
		ON vtiger_assets.assetsid = vtiger_crmentity.crmid
		INNER JOIN vtiger_assetscf
		ON vtiger_assets.assetsid = vtiger_assets.assetsid
		WHERE vtiger_crmentity.deleted = 0 AND vtiger_assets.assetsid = (". generateQuestionMarks($id) .")";
	} else if ($module == 'Project') {
		$query = "SELECT vtiger_project.*, vtiger_projectcf.*, vtiger_crmentity.*
					FROM vtiger_project
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid
					LEFT JOIN vtiger_projectcf ON vtiger_projectcf.projectid = vtiger_project.projectid
					WHERE vtiger_project.projectid = ? AND vtiger_crmentity.deleted = 0";
	}

	$params = array($id);
	$res = $adb->pquery($query,$params);

	$fieldquery = "SELECT fieldname,columnname,fieldlabel,blocklabel,uitype FROM vtiger_field
		INNER JOIN  vtiger_blocks on vtiger_blocks.blockid=vtiger_field.block WHERE vtiger_field.tabid = ? AND displaytype in (1,2,4)
		ORDER BY vtiger_field.block,vtiger_field.sequence";

	$fieldres = $adb->pquery($fieldquery,array(getTabid($module)));
	$nooffields = $adb->num_rows($fieldres);

	// Dummy instance to make sure column fields are initialized for futher processing
	$focus = CRMEntity::getInstance($module);

	for($i=0;$i<$nooffields;$i++)
	{
		$columnname = $adb->query_result($fieldres,$i,'columnname');
		$fieldname = $adb->query_result($fieldres,$i,'fieldname');
		$fieldid = $adb->query_result($fieldres,$i,'fieldid');
		$blockid = $adb->query_result($fieldres,$i,'block');
		$uitype = $adb->query_result($fieldres,$i,'uitype');

		$blocklabel = $adb->query_result($fieldres,$i,'blocklabel');
		$blockname = getTranslatedString($blocklabel,$module);
		if($blocklabel == 'LBL_COMMENTS' || $blocklabel == 'LBL_IMAGE_INFORMATION'){ // the comments block of tickets is hardcoded in customer portal,get_ticket_comments is used for it
			continue;
		}
		if($uitype == 83){ //for taxclass in products and services
			continue;
		}
		$fieldper = getFieldVisibilityPermission($module,$current_user->id,$fieldname);
		if($fieldper == '1'){
			continue;
		}

		$fieldlabel = getTranslatedString($adb->query_result($fieldres,$i,'fieldlabel'));
		$fieldvalue = $adb->query_result($res,0,$columnname);

		$output[0][$module][$i]['fieldlabel'] = $fieldlabel ;
		$output[0][$module][$i]['blockname'] = $blockname;

		if($columnname == 'parent_id' || $columnname == 'contactid' || $columnname == 'accountid' || $columnname == 'potentialid'
			|| $fieldname == 'account_id' || $fieldname == 'contact_id' || $columnname == 'linktoaccountscontacts')
		{
			$crmid = $fieldvalue;
			$modulename = getSalesEntityType($crmid);
			if ($crmid != '' && $modulename != '') {
				$fieldvalues = getEntityName($modulename, array($crmid));
				if($modulename == 'Contacts')
				$fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
				elseif($modulename == 'Accounts')
				$fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
				else
				$fieldvalue = $fieldvalues[$crmid];
			} else {
				$fieldvalue = '';
			}
		}
		if ($fieldname == 'createdtime')  {
				$fieldvalue =  date("d-m-Y H:i",strtotime($fieldvalue));
		}
		if ( ($fieldname == 'cf_629') || ($fieldname == 'cf_688')) {
				$fieldvalue =  date("d-m-Y",strtotime($fieldvalue));
		}
		if($module=='Quotes')
		{
			if($fieldname == 'subject' && $fieldvalue !=''){
				$fieldid = $adb->query_result($res,0,'quoteid');
				$fieldvalue = '<a href="index.php?downloadfile=true&module=Quotes&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
			}
			if($fieldname == 'total'){
				$sym = getCurrencySymbol($res,0,'currency_id');
				$fieldvalue = $sym.$fieldvalue;
			}
		}
		if($module == 'Documents')
		{
			$fieldid = $adb->query_result($res,0,'notesid');
			$filename = $fieldvalue;
			$folderid = $adb->query_result($res,0,'folderid');
			$filestatus = $adb->query_result($res,0,'filestatus');
			$filetype = $adb->query_result($res,0,'filelocationtype');
			if($fieldname == 'filename'){
				if($filestatus == 1){
					if($filetype == 'I'){
						$fieldvalue = '<a href="index.php?downloadfile=true&folderid='.$folderid.'&filename='.$filename.'&module=Documents&action=index&id='.$fieldid.'" >'.$fieldvalue.'</a>';
					}
					elseif($filetype == 'E'){
						$fieldvalue = '<a target="_blank" href="'.$filename.'" onclick = "updateCount('.$fieldid.');">'.$filename.'</a>';
					}
				}
			}
			if($fieldname == 'folderid'){
				$fieldvalue = $adb->query_result($res,0,'foldername');
			}
			if($fieldname == 'filesize'){
				if($filetype == 'I'){
					$fieldvalue = $fieldvalue .' B';
				}
				elseif($filetype == 'E'){
					$fieldvalue = '--';
				}
			}
			if($fieldname == 'filelocationtype'){
				if($fieldvalue == 'I'){
					$fieldvalue = getTranslatedString('LBL_INTERNAL',$module);
				}elseif($fieldvalue == 'E'){
					$fieldvalue = getTranslatedString('LBL_EXTERNAL',$module);
				}else{
					$fieldvalue = '---';
				}
			}
		}
		if($columnname == 'product_id') {
			$fieldvalues = getEntityName('Products', array($fieldvalue));
			$fieldvalue = '<a href="index.php?module=Products&action=index&productid='.$fieldvalue.'">'.$fieldvalues[$fieldvalue].'</a>';
		}
		if($module == 'Products'){
			if($fieldname == 'vendor_id'){
				$fieldvalue = get_vendor_name($fieldvalue);
			}
		}
		if($module == 'Assets' ){
			if($fieldname == 'account'){
				$accountid = $adb->query_result($res,0,'account');
				$accountres = $adb->pquery("select vtiger_account.accountname from vtiger_account where accountid=?",array($accountid));
				$accountname = $adb->query_result($accountres,0,'accountname');
				$fieldvalue = $accountname;
			}
			if($fieldname == 'product'){
				$productid = $adb->query_result($res,0,'product');
				$productres = $adb->pquery("select vtiger_products.productname from vtiger_products where productid=?",array($productid));
				$productname = $adb->query_result($productres,0,'productname');
				$fieldvalue = $productname;
			}
			if($fieldname == 'invoiceid'){
				$invoiceid = $adb->query_result($res,0,'invoiceid');
				$invoiceres = $adb->pquery("select vtiger_invoice.subject from vtiger_invoice where invoiceid=?",array($invoiceid));
				$invoicename = $adb->query_result($invoiceres,0,'subject');
				$fieldvalue = $invoicename;
			}
		}
		if($fieldname == 'assigned_user_id' || $fieldname == 'assigned_user_id1'){
			$fieldvalue = getOwnerName($fieldvalue);
		}
		if($uitype == 56){
			if($fieldvalue == 1){
				$fieldvalue = 'Yes';
			}else{
				$fieldvalue = 'No';
			}
		}
		// if($module == 'HelpDesk' && $fieldname == 'ticketstatus'){
			// $parentid = $adb->query_result($res,0,'parent_id');
			// $status = $adb->query_result($res,0,'status');
			// if($customerid != $parentid ){ //allow only the owner to delete the ticket
				// $fieldvalue = '';
			// }else{
				// $fieldvalue = $status;
			// }
		// }
		if($fieldname == 'unit_price'){
			$sym = getCurrencySymbol($res,0,'currency_id');
			$fieldvalue = $sym.$fieldvalue;
		}
		$output[0][$module][$i]['fieldvalue'] = $fieldvalue;
	}

	if($module == 'HelpDesk'){
		$ticketid = $adb->query_result($res,0,'ticketid');
		$sc_info = getRelatedServiceContracts($ticketid);
		if (!empty($sc_info)) {
			$modulename = 'ServiceContracts';
			$blocklable = getTranslatedString('LBL_SERVICE_CONTRACT_INFORMATION',$modulename);
			$j=$i;
			for($k=0;$k<count($sc_info);$k++){
				foreach ($sc_info[$k] as $label => $value) {
					$output[0][$module][$j]['fieldlabel']= getTranslatedString($label,$modulename);
					$output[0][$module][$j]['fieldvalue']= $value;
					$output[0][$module][$j]['blockname'] = $blocklable;
					$j++;
				}
			}
		}
	}
	$log->debug("Existing customer portal function get_details ..");
	return $output;
}
/* Function to check the permission if the customer can see the recorde details
 * @params $customerid :: INT contact's Id
 * 			$module :: String modulename
 * 			$entityid :: INT Records Id
 */
function check_permission($customerid, $module, $entityid) {
	global $adb,$log;
	$log->debug("Entering customer portal function check_permission ..");
	$show_all= show_all($module);
	$allowed_contacts_and_accounts = array();
	$check = checkModuleActive($module);
	if($check == false){
		return false;
	}

	if($show_all == 'false')
	$allowed_contacts_and_accounts[] = $customerid;
	else {

		$contactquery = "SELECT contactid, accountid FROM vtiger_contactdetails " .
					" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
					" AND vtiger_crmentity.deleted = 0 " .
					" WHERE (accountid = (SELECT accountid FROM vtiger_contactdetails WHERE contactid = ?) AND accountid != 0) OR contactid = ?";
		$contactres = $adb->pquery($contactquery, array($customerid,$customerid));
		$no_of_cont = $adb->num_rows($contactres);
		for($i=0;$i<$no_of_cont;$i++){
			$cont_id = $adb->query_result($contactres,$i,'contactid');
			$acc_id = $adb->query_result($contactres,$i,'accountid');
			if(!in_array($cont_id, $allowed_contacts_and_accounts))
			$allowed_contacts_and_accounts[] = $cont_id;
			if(!in_array($acc_id, $allowed_contacts_and_accounts) && $acc_id != '0')
			$allowed_contacts_and_accounts[] = $acc_id;
		}
	}
	if(in_array($entityid, $allowed_contacts_and_accounts)) { //for contact's,if they are present in the allowed list then send true
		return true;
	}
	$faqquery = "select id from vtiger_faq";
	$faqids = $adb->pquery($faqquery,array());
	$no_of_faq = $adb->num_rows($faqids);
	for($i=0;$i<$no_of_faq;$i++){
		$faq_id[] = $adb->query_result($faqids,$i,'id');
	}
	switch($module) {
		case 'Products'	: 	$query = "SELECT vtiger_seproductsrel.productid FROM vtiger_seproductsrel
								INNER JOIN vtiger_crmentity
								ON vtiger_seproductsrel.productid=vtiger_crmentity.crmid
								WHERE vtiger_seproductsrel.crmid IN (". generateQuestionMarks($allowed_contacts_and_accounts).")
									AND vtiger_crmentity.deleted=0
									AND vtiger_seproductsrel.productid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							$query = "SELECT vtiger_inventoryproductrel.productid, vtiger_inventoryproductrel.id
													FROM vtiger_inventoryproductrel
													INNER JOIN vtiger_crmentity
													ON vtiger_inventoryproductrel.productid=vtiger_crmentity.crmid
													LEFT JOIN vtiger_quotes
													ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid
													WHERE vtiger_crmentity.deleted=0
														AND (vtiger_quotes.contactid IN (". generateQuestionMarks($allowed_contacts_and_accounts).") or vtiger_quotes.accountid IN (".generateQuestionMarks($allowed_contacts_and_accounts)."))
														AND vtiger_inventoryproductrel.productid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							$query = "SELECT vtiger_inventoryproductrel.productid, vtiger_inventoryproductrel.id
													FROM vtiger_inventoryproductrel
													INNER JOIN vtiger_crmentity
													ON vtiger_inventoryproductrel.productid=vtiger_crmentity.crmid
													LEFT JOIN vtiger_invoice
													ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
													WHERE vtiger_crmentity.deleted=0
														AND (vtiger_invoice.contactid IN (". generateQuestionMarks($allowed_contacts_and_accounts).") or vtiger_invoice.accountid IN (".generateQuestionMarks($allowed_contacts_and_accounts)."))
														AND vtiger_inventoryproductrel.productid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							break;

		case 'Quotes'	:	$query = "SELECT vtiger_quotes.quoteid
								FROM vtiger_quotes
								INNER JOIN vtiger_crmentity
								ON vtiger_quotes.quoteid=vtiger_crmentity.crmid
								WHERE vtiger_crmentity.deleted=0
									AND (vtiger_quotes.contactid IN (". generateQuestionMarks($allowed_contacts_and_accounts).") or vtiger_quotes.accountid IN (".generateQuestionMarks($allowed_contacts_and_accounts)."))
									AND vtiger_quotes.quoteid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							break;

		case 'Invoice'	:	$query = "SELECT vtiger_invoice.invoiceid
								FROM vtiger_invoice
								INNER JOIN vtiger_crmentity
								ON vtiger_invoice.invoiceid=vtiger_crmentity.crmid
								WHERE vtiger_crmentity.deleted=0
									AND (vtiger_invoice.contactid IN (". generateQuestionMarks($allowed_contacts_and_accounts).") or vtiger_invoice.accountid IN (".generateQuestionMarks($allowed_contacts_and_accounts)."))
									AND vtiger_invoice.invoiceid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							break;

		case 'Documents'	: 	$query = "SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_senotesrel.notesid AND vtiger_crmentity.deleted = 0
									WHERE vtiger_senotesrel.crmid IN (". generateQuestionMarks($allowed_contacts_and_accounts) .")
									AND vtiger_senotesrel.notesid = ?";
								$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
								if ($adb->num_rows($res) > 0) {
									return true;
								}
								if(checkModuleActive('Project')) {
									$query = "SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel
										INNER JOIN vtiger_project ON vtiger_project.projectid = vtiger_senotesrel.crmid
										WHERE vtiger_project.linktoaccountscontacts IN (". generateQuestionMarks($allowed_contacts_and_accounts) .")
										AND vtiger_senotesrel.notesid = ?";
									$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
									if ($adb->num_rows($res) > 0) {
										return true;
									}
								}

								$query = "SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel
															INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_senotesrel.notesid AND vtiger_crmentity.deleted = 0
															WHERE vtiger_senotesrel.crmid IN (". generateQuestionMarks($faq_id) .")
															AND vtiger_senotesrel.notesid = ?";
								$res = $adb->pquery($query, array($faq_id,$entityid));
								if ($adb->num_rows($res) > 0) {
									return true;
								}
								break;

		case 'HelpDesk'	:	$query = "SELECT vtiger_troubletickets.ticketid FROM vtiger_troubletickets
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid AND vtiger_crmentity.deleted = 0
									WHERE vtiger_troubletickets.parent_id IN (". generateQuestionMarks($allowed_contacts_and_accounts) .")
									AND vtiger_troubletickets.ticketid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}

							$query = "SELECT vtiger_troubletickets.ticketid FROM vtiger_troubletickets
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
									INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)
									WHERE vtiger_crmentity.deleted = 0 AND
											(vtiger_crmentityrel.crmid IN (SELECT projectid FROM vtiger_project WHERE linktoaccountscontacts IN (". generateQuestionMarks($allowed_contacts_and_accounts) ."))
											OR vtiger_crmentityrel.relcrmid IN (SELECT projectid FROM vtiger_project WHERE linktoaccountscontacts IN (". generateQuestionMarks($allowed_contacts_and_accounts) ."))
										AND vtiger_troubletickets.ticketid = ?)";

							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}

							break;

		case 'Services'	:	$query = "SELECT vtiger_service.serviceid FROM vtiger_service
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid AND vtiger_crmentity.deleted = 0
									LEFT JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid=vtiger_service.serviceid OR vtiger_crmentityrel.crmid=vtiger_service.serviceid)
									WHERE (vtiger_crmentityrel.crmid IN (". generateQuestionMarks($allowed_contacts_and_accounts) .")  OR " .
		 							"(vtiger_crmentityrel.relcrmid IN (".generateQuestionMarks($allowed_contacts_and_accounts).") AND vtiger_crmentityrel.module = 'Services'))
									AND vtiger_service.serviceid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts,$allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}

							$query = "SELECT vtiger_inventoryproductrel.productid, vtiger_inventoryproductrel.id
									FROM vtiger_inventoryproductrel
									INNER JOIN vtiger_crmentity
									ON vtiger_inventoryproductrel.productid=vtiger_crmentity.crmid
									LEFT JOIN vtiger_quotes
									ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid
									WHERE vtiger_crmentity.deleted=0
									AND (vtiger_quotes.contactid IN (". generateQuestionMarks($allowed_contacts_and_accounts).") or vtiger_quotes.accountid IN (".generateQuestionMarks($allowed_contacts_and_accounts)."))
									AND vtiger_inventoryproductrel.productid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}

							$query = "SELECT vtiger_inventoryproductrel.productid, vtiger_inventoryproductrel.id
									FROM vtiger_inventoryproductrel
									INNER JOIN vtiger_crmentity
									ON vtiger_inventoryproductrel.productid=vtiger_crmentity.crmid
									LEFT JOIN vtiger_invoice
									ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
									WHERE vtiger_crmentity.deleted=0
										AND (vtiger_invoice.contactid IN (". generateQuestionMarks($allowed_contacts_and_accounts).") or vtiger_invoice.accountid IN (".generateQuestionMarks($allowed_contacts_and_accounts)."))
										AND vtiger_inventoryproductrel.productid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							break;

		case 'Accounts' : 	$query = "SELECT vtiger_account.accountid FROM vtiger_account " .
									"INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid " .
									"INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.accountid = vtiger_account.accountid " .
									"WHERE vtiger_crmentity.deleted = 0 and vtiger_contactdetails.contactid = ? and vtiger_contactdetails.accountid = ?";
							$res = $adb->pquery($query,array($customerid,$entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							break;

		case 'Assets' : $query = "SELECT vtiger_assets.assetname FROM vtiger_assets
								INNER JOIN vtiger_crmentity ON  vtiger_assets.assetsid = vtiger_crmentity.crmid
								WHERE vtiger_crmentity.deleted = 0 and vtiger_assets.account = ? ";
						$accountid = '';
						$accountRes = $adb->pquery("SELECT accountid FROM vtiger_contactdetails
								INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
								WHERE contactid = ? AND deleted = 0", array($customerid));
						$accountRow = $adb->num_rows($accountRes);
						if($accountRow) {
							$accountid = $adb->query_result($accountRes, 0, 'accountid');
						}
						$res = $adb->pquery($query,array($accountid));
						if ($adb->num_rows($res) > 0) {
							return true;
						}
						break;

		case 'Project'	:	$query = "SELECT vtiger_project.projectid FROM vtiger_project
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid AND vtiger_crmentity.deleted = 0
									WHERE vtiger_project.linktoaccountscontacts IN (". generateQuestionMarks($allowed_contacts_and_accounts) .")
									AND vtiger_project.projectid = ?";
							$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
							if ($adb->num_rows($res) > 0) {
								return true;
							}
							break;

	}
	return false;
	$log->debug("Exiting customerportal function check_permission ..");
}

/* Function to get related Documents for faq
 *  @params $id :: INT parent's Id
 * 			$module :: String modulename
 * 			$customerid :: INT contact's Id'
 */
function get_documents($id,$module,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_documents ..");
	$check = checkModuleActive($module);
	if($check == false){
		return array("#MODULE INACTIVE#");
	}
	$fields_list = array(
	'title' => 'Title',
	'filename' => 'FileName',
	'createdtime' => 'Created Time');

	if(!validateSession($customerid,$sessionid))
	return null;

	$query ="select vtiger_notes.title,'Documents' ActivityType, vtiger_notes.filename,
		crm2.createdtime,vtiger_notes.notesid,vtiger_notes.folderid,
		vtiger_notes.notecontent description, vtiger_users.user_name, vtiger_notes.filelocationtype
		from vtiger_notes
		LEFT join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid
		INNER join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_senotesrel.crmid
		LEFT join vtiger_crmentity crm2 on crm2.crmid=vtiger_notes.notesid and crm2.deleted=0
		LEFT JOIN vtiger_groups
		ON vtiger_groups.groupid = vtiger_crmentity.smownerid
		LEFT join vtiger_users on crm2.smownerid= vtiger_users.id
		where vtiger_crmentity.crmid=?";
	$res = $adb->pquery($query,array($id));
	$noofdata = $adb->num_rows($res);
	for( $j= 0;$j < $noofdata; $j++)
	{
		$i=0;
		foreach($fields_list as $fieldname => $fieldlabel) {
			$output[0][$module]['head'][0][$i]['fielddata'] = $fieldlabel; //$adb->query_result($fieldres,$i,'fieldlabel');
			$fieldvalue = $adb->query_result($res,$j,$fieldname);
			if($fieldname =='title') {
				$fieldid = $adb->query_result($res,$j,'notesid');
				$filename = $fieldvalue;
				$fieldvalue = '<a href="index.php?&module=Documents&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
			}
			if($fieldname == 'filename'){
				$fieldid = $adb->query_result($res,$j,'notesid');
				$filename = $fieldvalue;
				$folderid = $adb->query_result($res,$j,'folderid');
				$filetype = $adb->query_result($res,$j,'filelocationtype');
				if($filetype == 'I'){
					$fieldvalue = '<a href="index.php?&downloadfile=true&folderid='.$folderid.'&filename='.$filename.'&module=Documents&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';
				}else{
					$fieldvalue = '<a target="_blank" href="'.$filename.'">'.$filename.'</a>';
				}
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customerportal function  get_faq_document ..");
	return $output;
}

/* Function to get related projecttasks/projectmilestones for a Project
 *  @params $id :: INT Project's Id
 * 			$module :: String modulename
 * 			$customerid :: INT contact's Id'
 */
function get_project_components($id,$module,$customerid,$sessionid) {
	require_once("modules/$module/$module.php");
	require_once('include/utils/UserInfoUtil.php');

	global $adb,$log;
	$log->debug("Entering customer portal function get_project_components ..");
	$check = checkModuleActive($module);
	if($check == false) {
		return array("#MODULE INACTIVE#");
	}

	if(!validateSession($customerid,$sessionid))
		return null;

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$focus = new $module();
	$focus->filterInactiveFields($module);
	$componentfieldVisibilityByColumn = array();
	$fields_list = array();

	foreach ($focus->list_fields as $fieldlabel => $values){
		foreach($values as $table => $fieldname){
			$fields_list[$fieldlabel] = $fieldname;
			$componentfieldVisibilityByColumn[$fieldname] = getColumnVisibilityPermission($current_user->id,$fieldname,$module);
		}
	}

	if ($module == 'ProjectTask') {
		$query ="SELECT vtiger_projecttask.*, vtiger_crmentity.smownerid
				FROM vtiger_projecttask
				INNER JOIN vtiger_project ON vtiger_project.projectid = vtiger_projecttask.projectid AND vtiger_project.projectid = ?
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid AND vtiger_crmentity.deleted = 0";
	} elseif ($module == 'ProjectMilestone') {
		$query ="SELECT vtiger_projectmilestone.*, vtiger_crmentity.smownerid
				FROM vtiger_projectmilestone
				INNER JOIN vtiger_project ON vtiger_project.projectid = vtiger_projectmilestone.projectid AND vtiger_project.projectid = ?
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projectmilestone.projectmilestoneid AND vtiger_crmentity.deleted = 0";
	}

	$res = $adb->pquery($query,array($id));
	$noofdata = $adb->num_rows($res);

	for( $j= 0;$j < $noofdata; ++$j) {
		$i=0;
		foreach($fields_list as $fieldlabel => $fieldname) {
			$fieldper = $componentfieldVisibilityByColumn[$fieldname];
			if($fieldper == '1'){
				continue;
			}
			$output[0][$module]['head'][0][$i]['fielddata'] = $fieldlabel;
			$fieldvalue = $adb->query_result($res,$j,$fieldname);
			if($fieldname == 'smownerid'){
				$fieldvalue = getOwnerName($fieldvalue);
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customerportal function  get_project_components ..");
	return $output;
}

/* Function to get related tickets for a Project
 *  @params $id :: INT Project's Id
 * 			$module :: String modulename
 * 			$customerid :: INT contact's Id'
 */
function get_project_tickets($id,$module,$customerid,$sessionid) {
	require_once('modules/HelpDesk/HelpDesk.php');
	require_once('include/utils/UserInfoUtil.php');

	global $adb,$log;
	$log->debug("Entering customer portal function get_project_tickets ..");
	$check = checkModuleActive($module);
	if($check == false) {
		return array("#MODULE INACTIVE#");
	}

	if(!validateSession($customerid,$sessionid))
		return null;

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$focus = new HelpDesk();
	$focus->filterInactiveFields('HelpDesk');
	$TicketsfieldVisibilityByColumn = array();
	$fields_list = array();
	foreach ($focus->list_fields as $fieldlabel => $values){
		foreach($values as $table => $fieldname){
			$fields_list[$fieldlabel] = $fieldname;
			$TicketsfieldVisibilityByColumn[$fieldname] = getColumnVisibilityPermission($current_user->id,$fieldname,'HelpDesk');
		}
	}

	$query = "SELECT vtiger_troubletickets.*, vtiger_crmentity.smownerid FROM vtiger_troubletickets
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
		INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)
		WHERE vtiger_crmentity.deleted = 0 AND (vtiger_crmentityrel.crmid = ? OR vtiger_crmentityrel.relcrmid = ?)";

	$params = array($id, $id);
	$res = $adb->pquery($query,$params);
	$noofdata = $adb->num_rows($res);

	for( $j= 0;$j < $noofdata; $j++) {
		$i=0;
		foreach($fields_list as $fieldlabel => $fieldname) {
			$fieldper = $TicketsfieldVisibilityByColumn[$fieldname]; //in troubletickets the list_fields has columns so we call this API
			if($fieldper == '1'){
				continue;
			}
			$output[0][$module]['head'][0][$i]['fielddata'] = $fieldlabel;
			$fieldvalue = $adb->query_result($res,$j,$fieldname);
			$ticketid = $adb->query_result($res,$j,'ticketid');
			if($fieldname == 'title'){
				$fieldvalue = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$ticketid.'">'.$fieldvalue.'</a>';
			}
			if($fieldname == 'parent_id') {
				$crmid = $fieldvalue;
				$entitymodule = getSalesEntityType($crmid);
				if ($crmid != '' && $entitymodule != '') {
					$fieldvalues = getEntityName($entitymodule, array($crmid));
					if($entitymodule == 'Contacts')
					$fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					elseif($entitymodule == 'Accounts')
					$fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
				} else {
					$fieldvalue = '';
				}
			}
			if($fieldname == 'smownerid'){
				$fieldvalue = getOwnerName($fieldvalue);
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customerportal function  get_project_tickets ..");
	return $output;
}

/* Function to get contactid's and account's product details'
 *
 */
function get_service_list_values($id,$modulename,$sessionid,$only_mine='true')
{
	require_once('modules/Services/Services.php');
	require_once('include/utils/UserInfoUtil.php');
	global $current_user,$adb,$log;
	$log->debug("Entering customer portal Function get_service_list_values");
	$check = checkModuleActive($modulename);
	if($check == false){
		return array("#MODULE INACTIVE#");
	}
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	$entity_ids_list = array();
	$show_all=show_all($modulename);

	if(!validateSession($id,$sessionid))
	return null;

	if($only_mine == 'true' || $show_all == 'false')
	{
		array_push($entity_ids_list,$id);
	}
	else
	{
		$contactquery = "SELECT contactid, accountid FROM vtiger_contactdetails " .
		" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
		" AND vtiger_crmentity.deleted = 0 " .
		" WHERE (accountid = (SELECT accountid FROM vtiger_contactdetails WHERE contactid = ?)  AND accountid != 0) OR contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id,$id));
		$no_of_cont = $adb->num_rows($contactres);
		for($i=0;$i<$no_of_cont;$i++)
		{
			$cont_id = $adb->query_result($contactres,$i,'contactid');
			$acc_id = $adb->query_result($contactres,$i,'accountid');
			if(!in_array($cont_id, $entity_ids_list))
			$entity_ids_list[] = $cont_id;
			if(!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
			$entity_ids_list[] = $acc_id;
		}
	}

	$focus = new Services();
	$focus->filterInactiveFields('Services');
	foreach ($focus->list_fields as $fieldlabel => $values){
		foreach($values as $table => $fieldname){
			$fields_list[$fieldlabel] = $fieldname;
		}
	}
	$fields_list['Related To'] = 'entityid';
	$query = array();
	$params = array();

	$query[] = "select vtiger_service.*," .
		"case when vtiger_crmentityrel.crmid != vtiger_service.serviceid then vtiger_crmentityrel.crmid else vtiger_crmentityrel.relcrmid end as entityid, " .
		 "'' as setype from vtiger_service " .
		 "inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_service.serviceid " .
		 "left join vtiger_crmentityrel on (vtiger_crmentityrel.relcrmid=vtiger_service.serviceid or vtiger_crmentityrel.crmid=vtiger_service.serviceid) " .
		 "where vtiger_crmentity.deleted = 0 and " .
		 "( vtiger_crmentityrel.crmid in (".generateQuestionMarks($entity_ids_list).") OR " .
		 "(vtiger_crmentityrel.relcrmid in (".generateQuestionMarks($entity_ids_list).") AND vtiger_crmentityrel.module = 'Services')" .
		 ")";

	$params[] = array($entity_ids_list, $entity_ids_list);

	$checkQuotes = checkModuleActive('Quotes');
	if($checkQuotes == true){
		$query[] = "select distinct vtiger_service.*,
			case when vtiger_quotes.contactid is not null then vtiger_quotes.contactid else vtiger_quotes.accountid end as entityid,
			case when vtiger_quotes.contactid is not null then 'Contacts' else 'Accounts' end as setype
			from vtiger_quotes INNER join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_quotes.quoteid
			left join vtiger_inventoryproductrel on vtiger_inventoryproductrel.id=vtiger_quotes.quoteid
			left join vtiger_service on vtiger_service.serviceid = vtiger_inventoryproductrel.productid
			where vtiger_inventoryproductrel.productid = vtiger_service.serviceid AND vtiger_crmentity.deleted=0 and (accountid in  (". generateQuestionMarks($entity_ids_list) .") or contactid in (". generateQuestionMarks($entity_ids_list) ."))";
		$params[] = array($entity_ids_list,$entity_ids_list);
	}
	$checkInvoices = checkModuleActive('Invoice');
	if($checkInvoices == true){
		$query[] = "select distinct vtiger_service.*,
			case when vtiger_invoice.contactid !=0 then vtiger_invoice.contactid else vtiger_invoice.accountid end as entityid,
			case when vtiger_invoice.contactid !=0 then 'Contacts' else 'Accounts' end as setype
			from vtiger_invoice
			INNER join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid
			left join vtiger_inventoryproductrel on vtiger_inventoryproductrel.id=vtiger_invoice.invoiceid
			left join vtiger_service on vtiger_service.serviceid = vtiger_inventoryproductrel.productid
			where vtiger_inventoryproductrel.productid = vtiger_service.serviceid AND vtiger_crmentity.deleted=0 and (accountid in (". generateQuestionMarks($entity_ids_list) .") or contactid in  (". generateQuestionMarks($entity_ids_list) ."))";
		$params[] = array($entity_ids_list,$entity_ids_list);
	}

	$ServicesfieldVisibilityPermissions = array();
	foreach($fields_list as $fieldlabel=> $fieldname) {
		$ServicesfieldVisibilityPermissions[$fieldname] =
			getFieldVisibilityPermission('Services',$current_user->id,$fieldname);
	}

	for($k=0;$k<count($query);$k++)
	{
		$res[$k] = $adb->pquery($query[$k],$params[$k]);
		$noofdata[$k] = $adb->num_rows($res[$k]);
		if($noofdata[$k] == 0) {
			$output[$k][$modulename]['data'] = '';
		}
		for( $j= 0;$j < $noofdata[$k]; $j++)
		{
			$i=0;
			foreach($fields_list as $fieldlabel=> $fieldname) {
				$fieldper = $ServicesfieldVisibilityPermissions[$fieldname];
				if($fieldper == '1' && $fieldname != 'entityid'){
					continue;
				}
				$output[$k][$modulename]['head'][0][$i]['fielddata'] = $fieldlabel;
				$fieldvalue = $adb->query_result($res[$k],$j,$fieldname);
				$fieldid = $adb->query_result($res[$k],$j,'serviceid');

				if($fieldname == 'entityid') {
					$crmid = $fieldvalue;
					$module = $adb->query_result($res[$k],$j,'setype');
					if($module == ''){
						$module = $adb->query_result($adb->pquery("SELECT setype FROM vtiger_crmentity WHERE crmid = ?", array($crmid)),0,'setype');
					}
					if ($crmid != '' && $module != '') {
						$fieldvalues = getEntityName($module, array($crmid));
						if($module == 'Contacts')
						$fieldvalue = '<a href="index.php?module=Contacts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
						elseif($module == 'Accounts')
						$fieldvalue = '<a href="index.php?module=Accounts&action=index&id='.$crmid.'">'.$fieldvalues[$crmid].'</a>';
					} else {
						$fieldvalue = '';
					}
				}

				if($fieldname == 'servicename')
				$fieldvalue = '<a href="index.php?module=Services&action=index&id='.$fieldid.'">'.$fieldvalue.'</a>';

				if($fieldname == 'unit_price'){
					$sym = getCurrencySymbol($res[$k],$j,'currency_id');
					$fieldvalue = $sym.$fieldvalue;
				}
				$output[$k][$modulename]['data'][$j][$i]['fielddata'] = $fieldvalue;
				$i++;
			}
		}
	}
	$log->debug("Exiting customerportal function get_product_list_values.....");
	return $output;
}


/* Function to get the list of modules allowed for customer portal
 */
function get_modules()
{
	global $adb,$log;
	$log->debug("Entering customer portal Function get_modules");

	// Check if information is available in cache?
	$modules = Vtiger_Soap_CustomerPortal::lookupAllowedModules();
	if($modules === false) {
		$modules = array();

		$query = $adb->pquery("SELECT vtiger_customerportal_tabs.* FROM vtiger_customerportal_tabs
			INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_customerportal_tabs.tabid
			WHERE vtiger_tab.presence = 0 AND vtiger_customerportal_tabs.visible = 1", array());
		$norows = $adb->num_rows($query);
		if($norows) {
			while($resultrow = $adb->fetch_array($query)) {
				$modules[(int)$resultrow['sequence']] = getTabModuleName($resultrow['tabid']);
			}
			ksort($modules); // Order via SQL might cost us, so handling it ourselves in this case
		}
		Vtiger_Soap_CustomerPortal::updateAllowedModules($modules);
	}
	$log->debug("Exiting customerportal function get_modules");
	return $modules;
}

/* Function to check if the module has the permission to show the related contact's and Account's information
 */
function show_all($module){

	global $adb,$log;
	$log->debug("Entering customer portal Function show_all");
	$tabid = getTabid($module);
	if($module=='Tickets'){
		$tabid = getTabid('HelpDesk');
	}
	$query = $adb->pquery("SELECT prefvalue from vtiger_customerportal_prefs where tabid = ?", array($tabid));
	$norows = $adb->num_rows($query);
	if($norows > 0){
		if($adb->query_result($query,0,'prefvalue') == 1){
			return 'true';
		}else {
			return 'false';
		}
	}else {
		return 'false';
	}
	$log->debug("Exiting customerportal function show_all");
}

/* Function to get ServiceContracts information in the tickets module if the ticket is related to ServiceContracts
 */
function getRelatedServiceContracts($crmid){
	global $adb,$log;
	$log->debug("Entering customer portal function getRelatedServiceContracts");
	$module = 'ServiceContracts';
	$sc_info = array();
	if(vtlib_isModuleActive($module) !== true){
		return $sc_info;
	}
	$query = "SELECT * FROM vtiger_servicecontracts " .
	"INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid AND vtiger_crmentity.deleted = 0 " .
	"LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = vtiger_servicecontracts.servicecontractsid " .
	"WHERE (vtiger_crmentityrel.relcrmid = ? and vtiger_crmentityrel.module= 'ServiceContracts')";

	$res = $adb->pquery($query,array($crmid));
	$rows = $adb->num_rows($res);
	for($i=0;$i<$rows;$i++){
		$sc_info[$i]['Subject'] = $adb->query_result($res,$i,'subject');
		$sc_info[$i]['Used Units'] = $adb->query_result($res,$i,'used_units');
		$sc_info[$i]['Total Units'] = $adb->query_result($res,$i,'total_units');
		$sc_info[$i]['Available Units'] = $adb->query_result($res,$i,'total_units')- $adb->query_result($res,$i,'used_units');
	}
	return $sc_info;
	$log->debug("Exiting customerportal function getRelatedServiceContracts");
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

function checkModuleActive($module){
	global $adb,$log;

	$isactive = false;
	$modules = get_modules(true);

	foreach($modules as $key => $value){
		if(strcmp($module,$value) == 0){
			$isactive = true;
			break;
		}
	}
	return $isactive;
}

/**
 *  Function that gives the Currency Symbol
 * @params $result $adb object - resultset
 * $column String column name
 * Return $value - Currency Symbol
 */
function getCurrencySymbol($result,$i,$column){
	global $adb;
	$currencyid = $adb->query_result($result,$i,$column);
	$curr = getCurrencySymbolandCRate($currencyid);
	$value = "(".$curr['symbol'].")";
	return $value;

}

function getDefaultAssigneeId() {
	global $adb;
	$adb->println("Entering customer portal function getPortalUserid");

	// Look the value from cache first
	$defaultassignee = Vtiger_Soap_CustomerPortal::lookupPrefValue('defaultassignee');
	if($defaultassignee === false) {
		$res = $adb->pquery("SELECT prefvalue FROM vtiger_customerportal_prefs WHERE prefkey = 'defaultassignee' AND tabid = 0", array());
		$norows = $adb->num_rows($res);
		if($norows > 0) {
			$defaultassignee = $adb->query_result($res,0,'prefvalue');
			// Update the cache information now.
			Vtiger_Soap_CustomerPortal::updatePrefValue('defaultassignee', $defaultassignee);
		}
	}
	return $defaultassignee;
	$log->debug("Exiting customerportal function getPortalUserid");
}

/* START WEBSITE FUNCTIONS */
function getApplications($data,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid) || $data['frontendsid'] == '')	// EGC
		return null;

	$where="";
	if($data['crmid']>0 && ($data['ua']!=1 && $data['ua']!='on')){
		$where="AND a.`aplicationsid` = ".$data['crmid']." ";
	}
	$sql="SELECT a.*,vat.name as image,vat.path FROM `vtiger_aplications` a
			INNER JOIN `vtiger_crmentity` c ON (c.crmid = a.`aplicationsid` AND c.`deleted`=0)
			INNER JOIN `vtiger_crmentityrel` crel ON crel.`relcrmid`=c.`crmid` AND crel.`crmid`=".$data['frontendsid']."
			LEFT JOIN `vtiger_attachments` vat ON vat.`attachmentsid`=a.app_icon
			WHERE isactive = 1
			".$where."
			order by a.short_name";
	$res = $adb->pquery($sql);
	while($r=$adb->fetchByAssoc($res)){
		if($data['ua']==1 || $data['ua']=='on'){
			$r['url_amigable']=Slug($r['url_amigable']);
		}
		if(($data['ua']==1 || $data['ua']=='on') && $data['app']!='' && $data['app']==$r['url_amigable']){
			$return[]=$r;
		}elseif($data['app']==''){
			$return[]=$r;
		}
	}
	return $return;
}
/**
 * Obtener detalle de aplicación
 * @param int $aplicationsid - Id de Aplicación
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $return - Retorna array con listado de aplicaciones.
 */
function getModulesofApplication($applicationsid,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid))
		return null;

	$where="";
	if($applicationsid>0){
		$where="where a.`aplicationsid` = ".$applicationsid." ";
	}
	$sql="SELECT vlm.*,vat.`attachmentsid`,vat.name as screenshot,vat.path FROM `vtiger_aplications` a
			INNER JOIN `vtiger_crmentity` c ON (c.crmid = a.`aplicationsid` AND c.`deleted`=0)
			LEFT JOIN `vtiger_crmentityrel` crmrel ON (crmrel.crmid = c.crmid)
			LEFT JOIN `vtiger_listadomodulos` vlm ON vlm.`listadomodulosid` = crmrel.`relcrmid`
			INNER JOIN `vtiger_crmentity` c2 ON (c2.crmid = vlm.`listadomodulosid` AND c2.`deleted`=0)
			LEFT JOIN `vtiger_attachments` vat ON vat.`attachmentsid`=vlm.screenshot
			".$where."
			ORDER BY vlm.`titulo`";
	$res = $adb->pquery($sql);
	while($r=$adb->fetchByAssoc($res)){
		$return[]=$r;
	}
	return $return;
}
/**
 * Verificar el nombre de una instancia
 * @param array $params - Array que contiene los siguientes valores:
			=> string name - Nombre de la instancia
			=> boolean contact - Si debe buscar detalle de contacto
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $return Retorna un array con el detalle de la instacia.
 */
function getInstancesByName($params,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid))
		return null;

	$where="";
	if($params['name']!=''){
		$where="where a.`code` LIKE '".$params['name']."' ";
	}
	$sql="SELECT a.*,va.accountname,va.email1 as email FROM `vtiger_instancias` a
			INNER JOIN `vtiger_crmentity` c ON (c.crmid = a.`instanciasid` AND c.`deleted`=0)
			left join vtiger_account va on va.accountid=a.accounts
			".$where."
			order by a.code";
	$res = $adb->pquery($sql);
	while($r=$adb->fetchByAssoc($res)){
		$return[]=$r;
		$acc_id=$r['accounts'];
	}
	if($params['contacts'] && $acc_id){
		$sql="SELECT cd.* FROM `vtiger_contactdetails` cd
				INNER JOIN `vtiger_crmentity` c ON (c.crmid = cd.`contactid` AND c.`deleted`=0)
				where cd.accountid=".$acc_id."
				order by cd.lastname,firstname";
		$res = $adb->pquery($sql);
		while($r=$adb->fetchByAssoc($res)){
			$return[0]['contacts'][]=$r;
		}
	}
	//Chequea si existe la base de datos... (Puede que exista creada por otra madre)
	if(empty($return)){
		$sql_check="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'pg_crm_".$params['name']."'";
		$res = $adb->pquery($sql_check);
		$r=$adb->fetchByAssoc($res);
		if($adb->num_rows($res)>0){
			$return[]=$r;
		}else{
			//check directory
			if(is_dir($params['name'])){
				$return[]=array("fail"=>$params['name']." is a folder...");
			}
		}
	}
	return $return;
}

/*
function createAccount($clientdata){
	require_once('modules/Accounts/Accounts.php');

	$focus = new Accounts();
	$focus->column_fields['accountname']=$clientdata['company'];
	$focus->column_fields['email1']=$clientdata['email'];
	$focus->column_fields['phone']=$clientdata['phone'];
	$focus->column_fields['assigned_user_id']='1';
	$focus->save('Accounts');

	return $focus->id;
}	*/

/**
 * Crear una nueva instancia
 * @param array $clientdata Array con la información necesaria para crear una nueva instancia
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $return Retorna un array con el detalle de la instacia creada.
 */
function creaInstanciaenModulo($clientdata,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid))
		return null;

	$id = createInstanceOnCRM($clientdata);

	$app=setApplications($clientdata);

	//Relate App with Account
	$sql="select accounts from `vtiger_instancias` where instanciasid=".$id;
	$q=$adb->query($sql);
	$acc=$adb->fetchByAssoc($q);
	$sql="INSERT INTO vtiger_crmentityrel VALUES (?,?,?,?,NULL)";
	$adb->pquery($sql,array($acc['accounts'],'Accounts',$clientdata['app'],'aplications'));
	//Add plan Base on install
	$res = $adb->pquery("SELECT serviceid FROM vtiger_service
							INNER JOIN vtiger_crmentity ON (vtiger_service.serviceid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
							WHERE servicecategory = ?", array('Basico'));
	$serviceid=$adb->query_result($res,0,'serviceid');
	if($serviceid){
		$adb->pquery('INSERT INTO vtiger_crmentityrel VALUES (?,?,?,?,NULL)',array($acc['accounts'],'Accounts',$serviceid,'Services'));
	}
	return array('id'=>$id,'setapp'=>$app);
}

function createInstance($clientdata,$customerid,$sessionid){
	// Utiliza funcion de modules/Settings/comunesPlataforma.php
	$name = $clientdata['domain'];
	$_REQUEST=$clientdata;

	$extension = PlatformUtils::getImageExtension ($filename);
	$_REQUEST['file_extension']=$extension;
	creaPlataforma($name);
	//Sube imagen
	//Se sube la imagen
	$filename = $clientdata['filename'];
	$encodeContent = $clientdata['decodeContent'];
	$logo = $name."-logo.".$extension;
	$location=$name.'/test/logo/'.$logo;
	$current = base64_decode($encodeContent);
	file_put_contents($location, $current);
	PlatformUtils::resizeImage ($location,$name.'/test/logo/p'.$logo);


}
/**
 * Habilita aplicacion a una instancia ya creada
 * @param array $clientdata Array con la información de la instacia
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $return Retorna un array con el detalle de lo habilitado.
 */
function enableApplication($clientdata,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid))
		return null;

	$app=getApplicationsbyInstance($clientdata);

	return array('id'=>$focus->id,'setapp'=>$app);
}
/**
 * Verifica correo ingresado
 * @param array $clientdata Array con el correo que ingreso el usuario
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $return Retorna un array con el detalle de lo ingresado.
 */
function checkemail($clientdata,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid))
		return null;
	$res = $adb->pquery("SELECT email1 FROM vtiger_account
							INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=vtiger_account.accountid and deleted=0
							WHERE email1 = ? and email1 != 'evilla@timemanagement.es' ", array($clientdata['email']));
	$ret=array("success"=>'true',"val"=>null);
	if($adb->num_rows($res)>0)
		$ret=array("success"=>'false',"val"=>$adb->query_result($res,0,'email1'));
	return array($ret);
}
/**
 * Busca aplicaciones habilitadas por instancia
 * @param int $instanceapps - ID o ID's de aplicaciones
 * @return array $return Retorna un array con mesaje si falló. Si no, retorna true.
 */
function getApplicationsbyInstance($instanceapps){
	global $adb;
	if(empty($instanceapps))
		return false;

	$sql=" SELECT vl.listadomodulosid,vl.titulo,vlr.cf_961 as tabname,vap.`aplicationsid`,vap.`name` AS appname FROM `vtiger_listadomodulos` vl
			 INNER JOIN `vtiger_listadomoduloscf` vlr ON vlr.`listadomodulosid`=vl.`listadomodulosid`
			 INNER JOIN `vtiger_crmentity` crm ON crm.`crmid`=vl.`listadomodulosid` AND crm.`deleted`=0
			 INNER JOIN `vtiger_crmentityrel` vcrmrel ON vcrmrel.`relcrmid`=crm.`crmid` AND vcrmrel.`crmid` IN (".$instanceapps['app'].")
			 LEFT JOIN `vtiger_aplications` vap ON vap.`aplicationsid`=vcrmrel.`crmid`";
	$result = $adb->pquery($sql,array(),true);
	$modulesstring='';
	while($row = $adb->fetchByAssoc($result)){
		$modules[]=$row;
		if(!empty($row['tabname']))
			$modulesstring.="'".$row['tabname']."',";
	}
	$modulesstring=substr($modulesstring,0,-1);
	$ton=turnOnModules($modulesstring,$instanceapps['domain']);
	return $ton;
}
/**
 * Habilita modulos en instancia
 * @param string $modulesstring - Cadena de modulos por habililtar en la instancia
 * @param string $domain - Instancia donde habilitará los modulos
 * @return array $return Retorna un array con mesaje si falló. Si no, retorna true.
 */
function turnOnModules($modulesstring='',$domain){
	if(empty($modulesstring) || empty($domain))
		return array("ERROR_MOD_STRING"=>$modulesstring,"ERROR_DOMAIN"=>$domain);
	//Se crea una conexión al servidor solo para crear la bd
	global $dbconfig;

	$dbtype		= $dbconfig['db_type'];
	$host		= $dbconfig['db_server'].$dbconfig['db_port'];
	$dbname		= "pg_crm_".$domain;
	$username	= "usr_".$domain;
	$passwd		= md5("usr_".$domain);
	$db =  mysql_connect($dbconfig['db_server'].$dbconfig['db_port'],  $dbconfig['db_username'], $dbconfig['db_password']);
	if(!$db)
		return array("ERROR_DB_CONNECT"=>array($dbconfig['db_server'].$dbconfig['db_port'],  $dbconfig['db_username'], $dbconfig['db_password']));
	mysql_select_db($dbname,$db);

	$sql="UPDATE `vtiger_tab` SET `presence` = '0' WHERE `name` IN (".$modulesstring.") AND `parent`!=''";
	$q=mysql_query($sql,$db);
	$sql = "REPLACE INTO vtiger_profile2tab SELECT profileid, tabid, 0, NULL FROM vtiger_profile, vtiger_tab WHERE vtiger_tab.presence = 0";
	$q=mysql_query($sql,$db);
	mysql_close($dbname);
	if(!$q)
		return array("ERROR_DB_QUERY"=>$sql);
	return true;
}

/*
function split_sql_file($sql, $delimiter)
{
   // Split up our string into "possible" SQL statements.
   $tokens = explode($delimiter, $sql);

   // try to save mem.
   $sql = "";
   $output = array();

   // we don't actually care about the matches preg gives us.
   $matches = array();

   // this is faster than calling count($oktens) every time thru the loop.
   $token_count = count($tokens);
   for ($i = 0; $i < $token_count; $i++)
   {
	  // Don't wanna add an empty string as the last thing in the array.
	  if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
	  {
		 // This is the total number of single quotes in the token.
		 $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
		 // Counts single quotes that are preceded by an odd number of backslashes,
		 // which means they're escaped quotes.
		 $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

		 $unescaped_quotes = $total_quotes - $escaped_quotes;

		 // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
		 if (($unescaped_quotes % 2) == 0)
		 {
			// It's a complete sql statement.
			$output[] = $tokens[$i];
			// save memory.
			$tokens[$i] = "";
		 }
		 else
		 {
			// incomplete sql statement. keep adding tokens until we have a complete one.
			// $temp will hold what we have so far.
			$temp = $tokens[$i] . $delimiter;
			// save memory..
			$tokens[$i] = "";

			// Do we have a complete statement yet?
			$complete_stmt = false;

			for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
			{
			   // This is the total number of single quotes in the token.
			   $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
			   // Counts single quotes that are preceded by an odd number of backslashes,
			   // which means they're escaped quotes.
			   $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

			   $unescaped_quotes = $total_quotes - $escaped_quotes;

			   if (($unescaped_quotes % 2) == 1)
			   {
				  // odd number of unescaped quotes. In combination with the previous incomplete
				  // statement(s), we now have a complete statement. (2 odds always make an even)
				  $output[] = $temp . $tokens[$j];

				  // save memory.
				  $tokens[$j] = "";
				  $temp = "";

				  // exit the loop.
				  $complete_stmt = true;
				  // make sure the outer loop continues at the right point.
				  $i = $j;
			   }
			   else
			   {
				  // even number of unescaped quotes. We still don't have a complete statement.
				  // (1 odd and 1 even always make an odd)
				  $temp .= $tokens[$j] . $delimiter;
				  // save memory.
				  $tokens[$j] = "";
			   }

			} // for..
		 } // else
	  }
   }

   return $output;
}

function remove_remarks($sql)
{
   $lines = explode("\n", $sql);

   // try to keep mem. use down
   $sql = "";

   $linecount = count($lines);
   $output = "";

   for ($i = 0; $i < $linecount; $i++)
   {
	  if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
	  {
		 if (isset($lines[$i][0]) && $lines[$i][0] != "#")
		 {
			$output .= $lines[$i] . "\n";
		 }
		 else
		 {
			$output .= "\n";
		 }
		 // Trading a bit of speed for lower mem. use here.
		 $lines[$i] = "";
	  }
   }

   return $output;

}*/
/**
 * Obtener extension de un archivo
 * @param string $str - Archivo para obtener la extension
 * @return string $ext - Extension del archivo
 */
function obtenerExtensionByName($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}
/*
function obtenerExtension($filepath) {
	$type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
	$allowedTypes = array(
		1,  // [] gif
		2,  // [] jpg
		3,  // [] png
		6   // [] bmp
	);
	if (!in_array($type, $allowedTypes)) {
		return false;
	}
	switch ($type) {
		case 1 :
			$im = 'gif';
		break;
		case 2 :
			$im = 'jpg';
		break;
		case 3 :
			$im = 'png';
		break;
		case 6 :
			$im = 'bmp';
		break;
	}
	return $im;
}
*/


/**	function used to save record from any module
 *	@param array $input_array - array which contains the following values
 => int $id - customer id
	int $sessionid - session id
	string $module - module to record
	string $recordid - recordid for update operations
	array $fields - module's fields
	*	return array -
	*/
function saveRecord($input_array,$customerid,$sessionid)
{
	global $adb,$log,$isCustomer;
	$adb->println("Inside customer portal function saveRecord");
	$adb->println($input_array);

	$isCustomer = true;
	/*$module = $input_array['module'];
	$recordid = $input_array['recordid'];
	$fields = $input_array['fields'];*/
	$input_array['assigned_user_id'] = getDefaultAssigneeId();

	if(!validateSession($customerid,$sessionid))
		return null;

	/*$focus = CRMEntity::getInstance($module);

	if (!empty($recordid)) {
		$focus->id = $recordid;
		$focus->retrieve_entity_info($recordid, $module);
		$focus->mode = 'edit';
	}

	foreach ($fields as $clave => $valor) {
		$focus->column_fields[$clave] = $valor;
	}

	$defaultAssignee = getDefaultAssigneeId();

	$focus->column_fields['assigned_user_id']=$defaultAssignee;

	$focus->save($module);

	$record_array[0][$module]['id'] = $focus->id;
	return $record_array;*/

	if (isset($input_array['fields']['customer_language']) && !empty($input_array['fields']['customer_language'])) {
		//Caso especial. Idioma del usuario que se registra vía WorkFlow
		$_SESSION['customer_language'] = $input_array['fields']['customer_language'];
	}

	return saveRecordModule($input_array);
}
/**
 * Obtener ID de account por siccode
 * @param string $siccode - Codigo para obtener el accountid
 * @return integer $accountid - Retorna ID de account
 */
function get_account_name_by_siccode($siccode)
{
	global $adb,$log;
	$log->debug("Entering customer portal function get_account_name_by_siccode");
	$accountid = getAccountIdBySiccode($siccode);
	$log->debug("Exiting customer portal function get_account_name_by_siccode");
	return $accountid;
}
/**
 * Habilita modulos en instancia
 * @param array $input_array - Array con campos para enviar el Ticket
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $result_array - Retorna un array con detalle de envio.
 */
function sendTicketByEMail($input_array,$customerid,$sessionid)
{
	if(!validateSession($customerid,$sessionid))
		return null;

	$accountid = $input_array['accountid'];
	$localizador = $input_array['localizador'];

	$focus = CRMEntity::getInstance('billetes');

	if ($focus) {
		$result = $focus->sendTicketByEMail($accountid,$localizador);
	}
	$result_array[0][] = $result;

	return $result_array;
}
/**
 * Obtener cuentas por localizador
 * @param string $localizator - Localizador
 * @param int $customerid - ID de contacto
 * @param int $sessionid - ID de session
 * @return array $result_array - Retorna un array con detalle.
 */
function getAccountByLocalizator($localizator,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function getAccountByLocalizator");

	if(!validateSession($customerid,$sessionid))
		return null;

	$query = "SELECT accountid, count(*) as cantidad FROM vtiger_billetes INNER JOIN vtiger_crmentity
							ON (vtiger_billetes.billetesid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
							WHERE localizador = ?
							GROUP BY localizador";
	$res = $adb->pquery($query, array($localizator));


	$accountid=$adb->query_result($res,0,'accountid');
	$cantidad =$adb->query_result($res,0,'cantidad');
	$result_array[0]['accountid'] = $accountid;
	$result_array[0]['cantidad'] = $cantidad;
	$log->debug("Exiting customer portal function getAccountByLocalizator");
	return $result_array;
}
/**
 * Crear factura
 * @param array $input_array - Array con campos de factura.
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return integer $focus->id - ID de factura
 */
function createInvoice($input_array,$customerid,$sessionid)
{
	$focus = CRMEntity::getInstance('Invoice');

	$focus->column_fields['subject'] = $input_array['fields']['subject'];
	$focus->column_fields['account_id'] = $input_array['fields']['accountid'];
	$focus->column_fields['bill_street'] = $input_array['fields']['bill_street'];
	$focus->column_fields['ship_street'] = $input_array['fields']['ship_street'];
	$focus->column_fields['invoicestatus'] = $input_array['fields']['invoicestatus'];

	$focus->column_fields['inventory_currency'] = 1; //Euro
	$_REQUEST['hdnProductId1'] =  $input_array['fields']['productid'];
	$_REQUEST['productDescription1'] = $input_array['fields']['product_description'];
	$_REQUEST['comment1'] = $input_array['fields']['comment'];
	$_REQUEST['qty1'] =  $input_array['fields']['quantity'];
	$_REQUEST['listPrice1'] =  $input_array['fields']['total']/$input_array['fields']['quantity'];
	$_REQUEST['totalProductCount'] = 1;
	$_REQUEST['subtotal'] = $input_array['fields']['total'];
	$_REQUEST['total'] = $input_array['fields']['total'];

	$defaultAssignee = getDefaultAssigneeId();
	$focus->column_fields['assigned_user_id']=$defaultAssignee;

	$focus->save('Invoice');

	if (isset($input_array['fields']['localizator']) && !empty($input_array['fields']['localizator']) && !empty($focus->id)) {
		global $adb;

		$query = "INSERT INTO vtiger_crmentityrel
					SELECT ".$focus->id.", 'Invoice', billetesid, 'billetes', NULL FROM vtiger_billetes INNER JOIN vtiger_crmentity
						ON (vtiger_billetes.billetesid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
						WHERE localizador = ?";

		$adb->pquery($query,array($input_array['fields']['localizator']));
	}
	return $focus->id;
}
/**
 * Obtener ID de servicio por nombre de servicio
 * @param string $name - Nombre de servicio
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return integer $serviceid - ID de Servicio
 */
function getServiceByName($name,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function getServiceByName");

	if(!validateSession($customerid,$sessionid))
		return null;

	$res = $adb->pquery("SELECT serviceid FROM vtiger_service INNER JOIN vtiger_crmentity
							ON (vtiger_service.serviceid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
							WHERE servicename = ?", array($name));
	$serviceid=$adb->query_result($res,0,'serviceid');
	$log->debug("Exiting customer portal function getServiceByName");
	return $serviceid;
}
/**
 * Obtiene vuelos de simulador
 * @param array $input_array - Array con campos de busqueda.
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return null|string $bufferSalida - Reultado de la busqueda
 */
function getFlightsBySimulator($input_array,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function getFlightsBySimulator");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'simulador_viajes';

	$inidate = $input_array['inidate'];
	$enddate = $input_array['enddate'];
	$source = $input_array['source'];
	$target = $input_array['target'];
	$qty = $input_array['qty'];

	$bufferSalida = escribeResultadosConsulta($inidate,$enddate,$source,$target,$qty);

	$log->debug("Exiting customer portal function getFlightsBySimulator");
	return htmlentities($bufferSalida);
}
/**
 * Obtener datos del pasajero
 * @param array $input_array - Array con campos de busqueda.
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return null|string $bufferSalida - Reultado de la busqueda
 */
function putPassengerData($input_array,$customerid,$sessionid)
{
	global $adb,$log,$current_user,$current_language,$default_language,$currentModule;
	$log->debug("Entering customer portal function putPassengerData");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'simulador_viajes';

	$inidate = $input_array['inidate'];
	$enddate = $input_array['enddate'];
	$source = $input_array['source'];
	$target = $input_array['target'];
	$qty = $input_array['qty'];
	$IDA = $input_array['IDA'];
	$VUELTA = $input_array['VUELTA'];

	$bufferSalida = obtieneDatosPasajeros($source,$target,$inidate,$enddate,$qty,$IDA,$VUELTA);


	$log->debug("Exiting customer portal function putPassengerData");
	return htmlentities($bufferSalida);
}

/**
 * Guardar reserva
 * @param array $input_array - Array con campos de busqueda.
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return null|string $bufferSalida - Reultado de la busqueda
 */
function saveReservation($input_array,$customerid,$sessionid)
{
	global $adb,$log,$current_user,$current_language,$default_language,$currentModule;
	$log->debug("Entering customer portal function saveReservation");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'simulador_viajes';

	$idflightsource = $input_array['idflightsource'];
	$idflighttarget = $input_array['idflighttarget'];
	$document_id = $input_array['document_id'];
	$name = $input_array['name'];
	$phone = $input_array['phone'];
	$email = $input_array['email'];


	$bufferSalida = realizaReservacion($idflightsource,$idflighttarget,$document_id,
										$name,$phone,$email);



	$log->debug("Exiting customer portal function saveReservation");
	return htmlentities($bufferSalida);
}
/**
 * Obtener roles
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $result_array - Roles disponibles
 */
function getCustomerRoles($customerid,$sessionid)
{
	global $adb,$log;
	$result_array[0]['roles'] = array(array('roleid' => '', 'rolename' => '-'));
	$log->debug("Entering customer portal function getCustomerRoles");

	if(!validateSession($customerid,$sessionid))
		return $result_array;

	$query = "SELECT roleid,rolename from vtiger_role WHERE iscustomer = 1 ORDER BY parentrole asc";
	$res = $adb->query($query);

	while($row = $adb->fetch_array($res)) {
		$roles[] = $row;
	}

	$result_array[0]['roles'] = $roles;
	$log->debug("Exiting customer portal function getCustomerRoles");
	return $result_array;
}

function putCustomerRecord($input_array,$customerid,$sessionid)
{
	global $adb,$log,$current_user,$current_language,$default_language,$currentModule;
	$log->debug("Entering customer portal function saveReservation");

	if(!validateSession($customerid,$sessionid))
		return null;

}
/**
 * Obtener ID de cuenta por instacia
 * @param string $name - Nombre de instancia
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return integer $accountid - ID de cuenta
 */
function getAccountIdByPlatName($name,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function getAccountIdByPlatName");

	// if(!validateSession($customerid,$sessionid))
		// return null;

	$res = $adb->pquery("SELECT accounts FROM vtiger_instancias
							WHERE code = ?", array($name));
	$accountid =$adb->query_result($res,0,'accounts');
	$log->debug("Exiting customer portal function getAccountIdByPlatName");
	return $accountid;
}
/**
 * Obtener password de customer portal
 * @param integer $id - ID de contacto
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return string $user_password - Password de usuario customer portal
 */
function getCustomerPass($id,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function getCustomerPass");

	if(!validateSession($customerid,$sessionid))
		return null;

	$res = $adb->pquery("SELECT user_password FROM  `vtiger_portalinfo`
							WHERE id = ?", array($id));
	$user_password =$adb->query_result($res,0,'user_password');
	$log->debug("Exiting customer portal function getCustomerPass");
	return $user_password;
}
/**
 * Obtener ID de contacto por email
 * @param string $email - Email de contacto
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return integer $contactid - ID de contacto
 */
function getContactIdByEMail($email,$customerid,$sessionid)
{
	global $adb,$log;
	$log->debug("Entering customer portal function getContactIdByEMail");

	// if(!validateSession($customerid,$sessionid))
		// return null;

	$res = $adb->pquery("SELECT contactid FROM  `vtiger_contactdetails` INNER JOIN vtiger_crmentity
							ON (vtiger_contactdetails.contactid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
							WHERE email = ?", array($email));
	$contactid =$adb->query_result($res,0,'contactid');
	$log->debug("Exiting customer portal function getContactIdByEMail");
	return $contactid;
}
/**
 * Enviar reset de password
 * @param array $input_array - Array con detalle de contacto
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $ret - Retorna array con detalle de reset de password
 */
function sendPasswordReset($input_array,$customerid,$sessionid)
{
	global $adb,$log,$current_user,$current_language,$currentModule;
	$log->debug("Entering customer portal function sendPasswordReset");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'Contacts';

	$email = $input_array['email'];

	$contact = CRMEntity::getInstance('Contacts');
	$ret[0] = $contact->sendPasswordResetLink($email);


	$log->debug("Exiting customer portal function sendPasswordReset");
	return $ret;
}
/**
 * Chekear codigo de reset de password
 * @param array $input_array - Array con detalle de codigo
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $ret - Retorna array con detalle de reset de password
 */
function checkPasswordResetCode($input_array,$customerid,$sessionid) {
	global $log,$current_user,$current_language,$currentModule;
	$log->debug("Entering customer portal function checkPasswordResetCode");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'Contacts';

	$code = $input_array['code'];

	$contact = CRMEntity::getInstance('Contacts');
	$ret[0] = $contact->checkPasswordResetCode($code);
	$log->debug("Exiting customer portal function checkPasswordResetCode");
	return $ret;
}
/**
 * Setea nuevo password
 * @param array $input_array - Array con detalle de contacto
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $ret - Retorna array con detalle de reset de password
 */
function setNewPassword($input_array,$customerid,$sessionid) {
	global $log,$current_user,$current_language,$currentModule;
	$log->debug("Entering customer portal function checkPasswordResetCode");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'Contacts';

	$code = $input_array['code'];
	$pass = $input_array['password'];
	$passconfirmation = $input_array['passwordconfirmation'];

	$contact = CRMEntity::getInstance('Contacts');
	$ret[0] = $contact->setNewPassword($code, $pass, $passconfirmation);
	$log->debug("Exiting customer portal function checkPasswordResetCode");
	return $ret;
}

function satisfactionSurveyGetQuestions($input_array,$customerid,$sessionid) {
	global $log,$current_user,$current_language,$currentModule;
	$log->debug("Entering customer portal function checkPasswordResetCode");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'satisfactionsurvey';

	$token = $input_array['token'];

	$survey = CRMEntity::getInstance('satisfactionsurvey');
	$ret[0] = $survey->getQuestions($token);
	$log->debug("Exiting customer portal function checkPasswordResetCode");
	return $ret;
}

function satisfactionSurveySave($input_array,$customerid,$sessionid) {
	global $log,$current_user,$current_language,$currentModule;
	$log->debug("Entering customer portal function checkPasswordResetCode");

	if(!validateSession($customerid,$sessionid))
		return null;

	$current_language = $current_user->language;
	$currentModule = 'satisfactionsurvey';

	$token = $input_array['token'];
	$answers = $input_array['answers'];

	$survey = CRMEntity::getInstance('satisfactionsurvey');
	$ret[0] = $survey->saveAnswers($token, $answers);
	$log->debug("Exiting customer portal function checkPasswordResetCode");
	return $ret;
}

/**
 * Devuelve el último artículo para registrar su posicionamiento
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con los datos del artículo
 */
function getLastArticleToPosition($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;

	$sql = "SELECT `vtiger_articulos_redactados`.`articulos_redactadosid` AS id, `longtail_seo` as pcp, `cf_1281` as url, MAX(`fecha`) AS max_fecha
			FROM `vtiger_articulos_redactados`
			JOIN `vtiger_articulos_redactadoscf` ON `vtiger_articulos_redactadoscf`.`articulos_redactadosid` = `vtiger_articulos_redactados`.`articulos_redactadosid`
			LEFT JOIN `vtiger_articulos_redactados_pos` ON `vtiger_articulos_redactados_pos`.`articulos_redactadosid` = `vtiger_articulos_redactados`.`articulos_redactadosid`
			WHERE `cf_1291` = 'Publicado' AND fecha_publicacion < DATE_SUB(CURDATE(),INTERVAL 5 DAY) AND `cf_1281` != ''
			GROUP BY `vtiger_articulos_redactados`.`articulos_redactadosid`
			ORDER BY max_fecha LIMIT 1";
	$q=$adb->pquery($sql);
	return array($adb->fetchByAssoc($q));
}

/**
 * Guarda el posicionamiento de un artículo
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con los datos del artículo
 */
function saveArticlePosition($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;

	$date = date('Y-m-d');
	$sql = "INSERT INTO `vtiger_articulos_redactados_pos` (articulos_redactadosid,fecha,google_es,google_com) VALUES
			(".$data['articleId'].",'".$date."',".$data['posGoogleEs'].",".$data['posGoogleCom'].")";
	if ($adb->pquery($sql))
		return 1;
	else
		return 0;
}


function getInstructions($params,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid) || !$params['cliente'])
		return null;
	$sql="SELECT send_instructions,table_instructions,code_instruction FROM  `vtiger_client_instructive` WHERE `key` = '".$params['key']."'";
	// return array($sql);
	$res = $adb->pquery($sql);
	if(!$adb->num_rows($res))
		return array(array('num_rows'=>$adb->num_rows($res)));
	while($r=$adb->fetchByAssoc($res)){
		$ret[]=array(
					 'send_instructions'=>$r['send_instructions'],
					 'table_instructions'=>base64_encode($r['table_instructions']),
					 'code_instruction'=>base64_encode($r['code_instruction']),
					 );
	}
	if($ret[0]['send_instructions']!='SEND'){
		return array();
	}
	return $ret;
}

function setInstructions($params,$customerid,$sessionid){
	global $adb;

	if(!validateSession($customerid,$sessionid))
		return null;
	$client_data=json_decode(base64_decode($params['client_data']));

	$instructions['db']=array('db_name'=>$client_data->dbconfig->db_name,
							  'instruction'=>'DROP DATABASE `'.$client_data->dbconfig->db_name.'`;');
	foreach($client_data->tables as $t){
		$instructions['tables'][]="DROP TABLE `".$client_data->dbconfig->db_name."`.`".$t."`;";
	}
	$table_instructions=$instructions;
	$code=array(
			'function'=>'__drInstruct',
			'path'=>$client_data->root_directory,
		);
	$code_instructions=$code;
	$sql="INSERT INTO `vtiger_client_instructive` set 	`cliente`='".$params['cliente']."',
														`accountid`='".$params['accountid']."',
														`key`='".$params['key']."',
														`ip`='".$params['ip']."',
														`client_data`='".$params['client_data']."',
														`send_instructions`='NOT_SEND',
														`table_instructions`='".json_encode($table_instructions)."',
														`code_instruction`='".json_encode($code_instructions)."'
												";
	$res = $adb->pquery($sql);
	return array($sql);
}
/**
 * Guarda testimonio
 * @param array $clientdata - Array con la información del testimonio
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array - Retorna array con éxito y mensaje para el cliente.
 */
function setTestimonial($clientdata,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;

	$focus = CRMEntity::getInstance('web_testimonios');
	$focus->column_fields['nombre']=$clientdata['data']['title'];
	$focus->column_fields['descripcion']=$clientdata['data']['testimonio'];
	$focus->column_fields['email']=$clientdata['data']['email'];
	$focus->column_fields['habilitado']='0';
	$focus->column_fields['assigned_user_id']='1';
	$focus->column_fields['account']=$clientdata['data']['account'];
	$focus->save('web_testimonios');
	$return_id = $focus->id;
	$focus->save_module_rel($focus->id,'web_testimonios','frontends',$clientdata['frontendsid']);

	return array(array("success"=>true,"msg"=>"El Testimonio fue enviado con &eacute;xito. A la brevedad ser&aacute; analizado y publicado. <br> Gracias por su Testimonio!!"));
}
/**
 * Guarda comentario
 * @param array $clientdata - Array con la información del comentario
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array - Retorna array con éxito y mensaje para el cliente.
 */
function setComment($clientdata,$customerid,$sessionid){
	global $adb;
	if(!validateSession($customerid,$sessionid))
		return null;
	$focus = CRMEntity::getInstance('web_contact');
	$focus->column_fields['name']=$clientdata['data']['name'];
	$focus->column_fields['email']=$clientdata['data']['email'];
	$focus->column_fields['phone']=$clientdata['data']['phone'];
	$focus->column_fields['website']=$clientdata['data']['website'];
	$focus->column_fields['comment']=$clientdata['data']['comment'];
	$focus->column_fields['assigned_user_id']='1';
	$focus->save('web_contact');
	$return_id = $focus->id;

	return array(array("success"=>true,"msg"=>"Su consulta fue enviada con &eacute;xito. A la brevedad ser&aacute; contactado. <br> Gracias por su consulta!!"));
}
/**
 * Consulta existencia de frontends
 * @param array $data - Array con la información del frontend
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array - Retorna array con información detallada del frontend
 */
function checkFrontEnds($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	$sqlcheck="SHOW TABLES LIKE 'vtiger_frontends'";
	$qcheck=$adb->pquery($sqlcheck);
	if($adb->num_rows($qcheck)==0)
		return array(array('error'=>'404'));
	$nameplat = explode('.',$data['HTTP_HOST']);
	// if(!preg_match('/www/i',$nameplat[0]) && $nameplat[0]!=''){
		// $addsql=" where vf.codigo='".$nameplat[0]."' ";
	// }
	$sql="SELECT vf.*,CONCAT(vatt.`path`,vatt.`attachmentsid`,'_',vatt.`name`) AS logo
				,CONCAT(vatt2.`path`,vatt2.`attachmentsid`,'_',vatt2.`name`) AS favico
			FROM `vtiger_frontends` vf
			inner join vtiger_crmentity crm on crm.crmid=vf.frontendsid and crm.deleted=0
			LEFT JOIN `vtiger_attachments` vatt ON vatt.`attachmentsid`=vf.`logo`
			LEFT JOIN `vtiger_attachments` vatt2 ON vatt2.`attachmentsid`=vf.`favico`";
	$query=$adb->query($sql);

	if($adb->num_rows($query)!=0){
		while($fronts=$adb->fetchByAssoc($query)){
			if(isset($data['return']) && $data['return']=='all'){
				unset($fronts['privacy_policy']);
				unset($fronts['term_and_conditions']);
			}
			$retfront[]=$fronts;
			if($fronts['codigo']==$nameplat[0] || $fronts['codigo']==$nameplat[1] || preg_match('/'.$data['HTTP_HOST'].'/i',$fronts['url']) || preg_match('/'.$data['HTTP_HOST'].'/i',$fronts['codigo'])){
				if(!isset($data['return']))
					return array($fronts);
			}
		}
		return $retfront;
	}else{
		$sql="SELECT vf.*,CONCAT(vatt.`path`,vatt.`attachmentsid`,'_',vatt.`name`) AS logo
				,CONCAT(vatt2.`path`,vatt2.`attachmentsid`,'_',vatt2.`name`) AS favico
				FROM `vtiger_frontends` vf
				inner join vtiger_crmentity crm on crm.crmid=vf.frontendsid and crm.deleted=0
				LEFT JOIN `vtiger_attachments` vatt ON vatt.`attachmentsid`=vf.`logo`
				LEFT JOIN `vtiger_attachments` vatt2 ON vatt2.`attachmentsid`=vf.`favico`
				order by frontendsid asc limit 1";

		$query = $adb->query($sql);
		if($adb->num_rows($query)!=0){
			$fronts=$adb->fetchByAssoc($query);
			return $fronts;
		}
	}

	return array(array('error'=>'404'));
}
/**
 * Busca paginas asociadas a un frontend
 * @param array $data - Array con la información del frontend y la pagina
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array - Retorna array con las paginas y el detalle si asi fue pedido
 */
function getPaginas($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	// return array($data);
	if($data['frontendsid']){
		$addsql=" INNER JOIN `vtiger_crmentityrel` crmrel ON crmrel.`crmid`= ".$data['frontendsid']." AND vwp.web_paginasid=crmrel.`relcrmid` ";
	}
	$sql="select * from vtiger_web_paginas vwp
			inner join vtiger_crmentity crm on crm.crmid=vwp.web_paginasid and deleted=0
			".$addsql."
			GROUP BY web_paginasid
			ORDER BY orden DESC";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$pagina = $data['page'];
		if($data['ua']==1 || $data['ua']=='on'){
			if($data['page']==Slug($r['titulo']) || $data['url_friendly']==Slug($r['url_amigable'])){
				$pagina=$r['web_paginasid'];
			}
		}
		if(($pagina==$r['web_paginasid']) ||($data['page']=='' && $r['home']==1)){
			$r['bloques']=getBloques($r['web_paginasid']);
			$r['og_twitter_imagen']=getAttachmentsbyId($r['og_twitter_imagen']);
		}
		$return[]=$r;
	}
	return $return;
}
/**
 * Obtener listado o detalle de hoteles
 * @param array $data - Array con la información de hotel
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el listado o detalle del hotel según corresponda
 */
function getHoteles($data,$customerid,$sessionid){
	global $adb;
	$fields=" vac.`accomodationid`,vac.`name`,vac.`phone`,vac.`state`,vac.`city`,vac.`country`,vac.`geo_lat`,vac.`geo_lng`,vac.`rating`,
			vac.`score`,vac.`roomspricing`,vac.`accommodationtype`,vac.`descripcion`,vac.`address` ";
	if($data['accomodationid']!=0){
		$addsql=" WHERE vac.accomodationid=".$data['accomodationid']." ";
		$fields=" vac.* ";
	}
	$sql="SELECT ".$fields."
			FROM `vtiger_accomodation` vac
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vac.`accomodationid` AND crm.`deleted`=0
			".$addsql."
			ORDER BY name ASC";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['imagenes_hotel']=getimagenbloq($r['accomodationid']);
		$return[]=$r;
	}
	if($data['accomodationid']!=0){
		return array(json_encode($return[0]));
	}
	return $return;
}
/**
 * Obtener listado o detalle de promociones
 * @param integer $promocionesid - Id de una promocion si se require.
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el listado o detalle de la promocion según corresponda
 */
function getOfertas($data,$customerid,$sessionid){
	global $adb;
	// $fields=" vac.`accomodationid`,vac.`name`,vac.`phone`,vac.`state`,vac.`city`,vac.`country`,vac.`geo_lat`,vac.`geo_lng`,vac.`rating`,
			// vac.`score`,vac.`roomspricing`,vac.`accommodationtype`,vac.`descripcion`,vac.`address` ";
	if($data['promocionesid']!=0){
		$addsql=" WHERE vp.promocionesid=".$data['promocionesid']." ";
		$fields=" vac.* ";
	}
	$sql="SELECT vp.* FROM `vtiger_promociones` vp
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vp.`promocionesid` AND crm.`deleted`=0
			";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		// $r['imagenes_hotel']=getimagenbloq($r['accomodationid']);
		$r[strtolower($r['tipo_promo'])]=getRelatedPromo($r['promocionesid'],strtolower($r['tipo_promo']));
		$return[]=$r;
	}
	// if($data['accomodationid']!=0){
		// return array(json_encode($return[0]));
	// }
	return $return;
}
/**
 * Obtener promociones relacionadas
 * @param integer $crmid - ID de relacion
 * @param string $tipo - Tipos de promocion. viaje o alojamiento
 * @return array $return - Retorna un array con el listado o detalle de la promocion según corresponda
 */
function getRelatedPromo($crmid,$tipo){
	global $adb;
	if($tipo=='viaje'){
		$sql="SELECT vv.* FROM vtiger_viajes vv
				INNER JOIN `vtiger_crmentityrel` crmrel ON crmrel.`relcrmid`= vv.viajesid
				WHERE crmrel.`crmid` = ".$crmid;
	}elseif($tipo=='alojamiento'){
		$sql="SELECT vv.* FROM vtiger_accomodation va
				INNER JOIN `vtiger_crmentityrel` crmrel ON crmrel.`relcrmid`= va.accomodationid
				WHERE crmrel.`crmid` = ".$crmid;
	}
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$ret[]=$r;
	}
	return $ret;
}

/**
 * Obtener bloques de paginas
 * @param integer $crmid - ID de relacion
 * @return array $return - Retorna un array con los bloques asociados
 */
function getBloques($crmid){
	global $adb;
	$sql="select vwb.*,crm.*,vu.first_name,vu.last_name, CONCAT(va.path,va.attachmentsid,'_',va.name) AS imagemain_imagen
			from vtiger_web_bloques vwb
			inner join vtiger_crmentity crm on crm.crmid=vwb.web_bloquesid and deleted=0
			inner join vtiger_crmentityrel crmr on crmr.relcrmid=crm.crmid
			left join `vtiger_users` vu on vu.id=crm.smcreatorid
			LEFT JOIN `vtiger_attachments` va ON va.`attachmentsid`=vwb.`imagemain`
			where crmr.crmid=".$crmid." ORDER BY vwb.orden ASC";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['imagenes_bloque']=getimagenbloq($r['crmid']);
		$return[]=$r;
	}
	return $return;
}
/**
 * Consulta en testimonios
 * @param array $data - Datos de consulta
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con los testimonios que se dejaron al frontend
 */
function getTestimonios($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;

	if($data['frontendsid']){
		$addsql=" INNER JOIN `vtiger_crmentityrel` crmrel ON crmrel.`relcrmid`= ".$data['frontendsid']." AND vwt.web_testimoniosid=crmrel.`crmid` ";
	}
	$sql="SELECT vwt.*,crm.*,crmrel.*,va.`accountname`,DATE_FORMAT(crm.`createdtime`,'%M %e of %Y at %H:%i' ) AS fecha, vc.`contactid`,vc.imagename,
				CONCAT(vc.firstname,' ',vc.lastname) as contacto
				FROM `vtiger_web_testimonios` vwt
				inner join vtiger_crmentity crm on crm.crmid=vwt.web_testimoniosid and crm.deleted=0
				".$addsql."
				LEFT JOIN `vtiger_account` va ON va.`accountid`=vwt.account
				LEFT JOIN `vtiger_contactdetails` vc ON vc.`accountid`=va.`accountid` AND vc.email=vwt.email
								AND vc.`contactid` IN (SELECT crmid FROM vtiger_crmentity crm2 WHERE crm2.crmid=vc.contactid AND crm2.deleted=0)
				where vwt.habilitado=1
				GROUP BY vwt.`web_testimoniosid`
				ORDER BY crm.createdtime DESC";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['contactimg']=getAttachments($t['contactid'],$t['imagename']);
		$return[]=$r;
	}
	return $return;
}
/**
 * Obtiene archivo adjunto por id y nombre
 * @param integer $crmid - Id de relacion
 * @param integer $attachname - Nombre de adjunto
 * @return string - Retorna el nombre del archivo adjunto
 */
function getAttachments($crmid,$attachname=''){
	global $adb;
	if(empty($crmid))
		return null;
	if($attachname){
		$where=" where vatt.name='".$attachname."'";
	}
	$sql="select CONCAT(vatt.`path`,vatt.`attachmentsid`,'_',vatt.`name`) AS attachment from `vtiger_attachments` vatt
			INNER JOIN `vtiger_seattachmentsrel` vattrel ON vattrel.attachmentsid=vatt.attachmentsid and vattrel.crmid='".$crmid."'
			".$where;
	$q=$adb->pquery($sql);
	return $adb->fetchByAssoc($q);
}
/**
 * Obtiene archivo adjunto por id
 * @param integer $attachmentsid - Id de relacion
 * @return string - Retorna el nombre del archivo adjunto
 */
function getAttachmentsbyId($attachmentsid){
	global $adb;
	if(empty($attachmentsid))
		return null;
	$sql="select CONCAT(vatt.`path`,vatt.`attachmentsid`,'_',vatt.`name`) AS attachment from `vtiger_attachments` vatt
			WHERE vatt.attachmentsid='".$attachmentsid."'";
	$q=$adb->pquery($sql);
	$r=$adb->fetchByAssoc($q);
	return $r['attachment'];
}
/**
 * Consultar la configuracion de la web
 * @deprecated
 */
function getWebConfiguration($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;

	if(!$data['frontendsid']){
		$sql="SELECT * FROM `vtiger_web_config` WHERE 1";
	}else{
		if($data['frontendsid']){
			$addsql="INNER JOIN `vtiger_crmentityrel` crmrel ON crmrel.`crmid`= ".$data['frontendsid']." AND vwc.`web_configid`=crmrel.`relcrmid`";
		}
		$sql="SELECT vwc.*,CONCAT(vatt.`path`,vatt.`attachmentsid`,'_',vatt.`name`) AS logo
				,CONCAT(vatt2.`path`,vatt2.`attachmentsid`,'_',vatt2.`name`) AS favico
				FROM vtiger_web_config vwc
				".$addsql."
				LEFT JOIN `vtiger_attachments` vatt ON vatt.`attachmentsid`=vwc.`logo`
				LEFT JOIN `vtiger_attachments` vatt2 ON vatt2.`attachmentsid`=vwc.`favico`";
	}
	$q=$adb->pquery($sql);
	return array($adb->fetchByAssoc($q));
}
/**
 * Consulta servicios disponibles por frontend
 * @param array $data - Datos de consulta
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con los servicios disponibles
 */
function getServices($data,$customerid,$sessionid){
	global $adb;
	if(!validateSession($customerid,$sessionid))
		return null;
	if($data['frontendsid']){
		$innerjoin=" INNER JOIN vtiger_crmentityrel rel ON rel.relcrmid = crm.crmid AND rel.crmid =".$data['frontendsid']." ";
	}
	$sql="SELECT vs.*,crm.`description`,vcurr.`currency_code`,vcurr.`currency_name`,vcurr.`currency_symbol`,vcurr.`conversion_rate` FROM `vtiger_service` vs
			INNER JOIN `vtiger_crmentity` crm ON crm.`crmid`=vs.`serviceid` AND crm.`deleted`=0
			$innerjoin
			LEFT JOIN `vtiger_currency_info` vcurr ON vcurr.`id`=vs.`currency_id`
			WHERE ((vs.`expiry_date`>=CURDATE()) OR (vs.`expiry_date`='0000-00-00' OR vs.`expiry_date` IS NULL))
			/*AND vs.`servicecategory`!='Basico'*/
			ORDER BY vs.orden";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['detalle2']=explode("\n",$r['description']);
		$return[]=$r;
	}
	return $return;
}
/**
 * Consulta imagen
 * @param array $data - Datos de consulta
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array imagenes relacionadas
 */
function getimagen($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	if(!$data['crmid'])
		return array();
	$sql="select * from vtiger_imag_web_bloque
				where id=".$data['crmid']." ORDER BY orden ASC";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return $return;
}
/**
 * Consulta imagen por bloque
 * @param array $data - Datos de consulta
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array imagenes relacionadas
 */
function getimagenbloq($crmid){
	global $adb;
	if(!$crmid)
		return array();
	$sql="select * from vtiger_imag_web_bloque
				where id=".$crmid." ORDER BY orden ASC";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return $return;
}
/**
 * Consulta detalle de Empresa
 * @param array $data - Datos de consulta
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el detalle de la empresa
 */
function getWebOrganizationDetails($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;

	$sql="SELECT * FROM `vtiger_organizationdetails` WHERE organization_id = '".$data['id']."'";
	$q=$adb->pquery($sql);
	return array($adb->fetchByAssoc($q));
}
/**
 * Funcion para envío generico de emails a través del emailmanager
 * @param array $data - Datos de para envio:
 *			=> name - Nombre del destinatario
 *			=> email - Email del destinatario
 *			=> event - Codigo de evento emailmanager
 *			=> lang - Idioma de envío.
 *			=> vars - Variables a reemplazar en el template
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con mensaje de envío de email
 */
function EmailManagerSender($data,$customerid,$sessionid){
	global $adb;
	if(!validateSession($customerid,$sessionid))
		return null;
	$ret['success'] = true;
	$ret['msg'] = getTranslatedString('LBL_EMAIL_LINK_HAS_BEEN_SENT');
	return array($ret);
}
/**
 * Obtiene estado de plataforma
 * @param array $data - Datos de consulta
			=> plat - Nombre de plataforma
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el detalle de la cuenta, de donde se obtendra los campos del estado.
 */
function getPlatStatus($data,$customerid,$sessionid){
	global $adb;
	if(!validateSession($customerid,$sessionid) || !$data['plat'])
		return null;

	$sql="SELECT va.* FROM vtiger_account va
			INNER JOIN vtiger_instancias vi ON va.`accountid`=vi.`accounts`
			WHERE vi.`code`='".$data['plat']."'";
	$q=$adb->pquery($sql);
	$return=$adb->fetchByAssoc($q);
	return array($return);
}

/**
 * Obtiene estado de plataforma
 * @param array $data - Datos de consulta
			=> plat - Nombre de plataforma
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el detalle de la cuenta, de donde se obtendra los campos del estado.
 */
function getInstancias($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	// return array($data);
	$sql="SELECT vi.* FROM vtiger_instancias vi
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vi.`instanciasid` and crm.deleted=0";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return $return;
}

/**
 * Obtiene estado de plataforma
 * @param array $data - Datos de consulta
			=> plat - Nombre de plataforma
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el detalle de la cuenta, de donde se obtendra los campos del estado.
 */
function getCursos($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	// return array($data);
	if(!empty($data['ids'])){
		$addsql=" where vfc.`formacion_cursosid` in (".$data['ids'].") ";
	}
	$sql="SELECT vfc.*,CONCAT(vtiger_attachments.`path`,vtiger_attachments.`attachmentsid`,'_',vtiger_attachments.`name`) AS image
			FROM `vtiger_formacion_cursos` vfc
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfc.`formacion_cursosid` and crm.deleted=0
			LEFT JOIN `vtiger_attachments` ON `vtiger_attachments`.`attachmentsid`=vfc.img_curso
			".$addsql;
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['related']=getRelateds($r['formacion_cursosid']);
		$return[]=$r;
	}
	return $return;
}

/**
 * Obtiene estado de plataforma
 * @param array $data - Datos de consulta
			=> plat - Nombre de plataforma
 * @param integer $customerid - ID de contacto
 * @param integer $sessionid - ID de session
 * @return array $return - Retorna un array con el detalle de la cuenta, de donde se obtendra los campos del estado.
 */
function setNewUser($data,$customerid,$sessionid){
	global $adb,$log,$current_user;
	if(!validateSession($customerid,$sessionid))
		return null;
	$user_name=$data['data']['alta_username'];
	list($nombre,$apellido)=explode(" ",$data['data']['alta_name']);

	$user_query = "SELECT user_name FROM vtiger_users WHERE user_name =?";
	$user_result = $adb->pquery($user_query, array($user_name));
	if($adb->num_rows($user_result) > 0) {
		return array(array("success"=>false,"error"=>"El nombre de usuario ya existe!"));
	}

	$focus = new Users();
	$focus->mode='';
	$focus->column_fields['is_admin'] = "off";
	$focus->column_fields['internal_mailer'] = 1;
	$focus->column_fields['roleid'] = "H28";
	$focus->column_fields['homeorder'] = 'ILTI,QLTQ,ALVT,PLVT,CVLVT,HLT,OLV,GRT,OLTSO';
	$focus->column_fields['deleted'] = '0';
	$focus->column_fields['status'] = 'Active';
	$focus->column_fields['currency_id'] = '1';
	$focus->column_fields['currency_symbol_placement'] = '$1.0';
	$focus->column_fields['currency_grouping_pattern'] = '123,456,789';
	$focus->column_fields['theme'] = 'softed';
	$focus->column_fields['date_format'] = 'dd-mm-yyyy';
	$focus->column_fields['time_zone'] = 'UTC';
	$focus->column_fields['user_name'] = $user_name;
	$focus->column_fields['email1'] = $data['data']['alta_email'];
	$focus->column_fields['user_password'] = $data['data']['alta_password'];
	$focus->column_fields['confirm_password'] = $data['data']['alta_password'];
	$focus->column_fields['first_name'] = $nombre;
	$focus->column_fields['last_name'] = $apellido;
	$focus->column_fields['language'] = $data['data']['alta_language'];
	$focus->save("Users");
	$return=array("user_name"=>$user_name,"user_password"=>$data['data']['alta_password'],"success"=>true);
	return array($return);
}

function getRelateds($crmid,$relmodule=array()){
	global $adb;
	$addsql='';
	if(!empty($relmodule)){
		$addsql=" and relmodule in ('".implode("','",$relmodule)."') ";
	}
	$sql="SELECT * FROM  `vtiger_crmentityrel` WHERE  `crmid` =".$crmid." ".$addsql;
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[$r['relcrmid']]=$r;
	}
	return $return;
}

function getRelatedsReldi($relcrmid,$module=array()){
	global $adb;
	$addsql='';
	if(!empty($module)){
		$addsql=" and module in ('".implode("','",$module)."') ";
	}
	$sql="SELECT * FROM  `vtiger_crmentityrel` WHERE  `relcrmid` =".$relcrmid." ".$addsql;
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[$r['crmid']]=$r;
	}
	return $return;
}

function getLecciones($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	// return array($data);
	$sql="SELECT vfl.*,vv.* FROM `vtiger_formacion_lecciones` vfl
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfl.`formacion_leccionesid` and crm.deleted=0
			LEFT JOIN `vtiger_videos` vv ON vv.`idvideo`=vfl.`videoid`";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return $return;
}

function getPruebas($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	// return array($data);
	$sql="SELECT vfp.* FROM `vtiger_formacion_pruebas` vfp
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_pruebasid` and crm.deleted=0
			";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['related']=getRelateds($r['formacion_pruebasid'],array('formacion_preguntas','formacion_cursos'));
		$r['related_lecciones']=getRelatedsReldi($r['formacion_pruebasid'],array('formacion_lecciones'));
		$return[]=$r;
	}
	return $return;
}

function getPreguntas($data,$customerid,$sessionid){
	global $adb;
	// if(!validateSession($customerid,$sessionid))
		// return null;
	// return array($data);
	$sql="SELECT vfp.* FROM `vtiger_formacion_preguntas` vfp
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_preguntasid` AND crm.deleted=0 ";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$r['respuestas']=getRespuestas($r['formacion_preguntasid']);
		$return[]=$r;
	}
	return $return;
}

function getRespuestas($id){
	global $adb;
	$sql="SELECT * FROM  `vtiger_formacion_preguntas_respuestas` WHERE  `formacion_preguntasid` =".$id;
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return $return;
}

/**
 * EBIRDS API - Consulta aeropuertos
 * @param array $data - Datos de consulta
 * @return array $return - Retorna un array con el listado de aeropuertos
 * @deprecated - Cambiado a archivo propio
 */
function ebirds_airports($data){
	global $adb;
	$destino=$data['data']['destino'];
	if($destino==''){
		$destino='EZE';
	}
	if(strlen($destino)<3)
		return null;
	$sql="SELECT * FROM `ebirds_airports`
			WHERE `Name` LIKE '%".$destino."%'
			OR `City` LIKE '%".$destino."%'
			OR `Country` LIKE '%".$destino."%'
			OR `IATA_FAA` LIKE '%".$destino."%'
			OR `ICAO` LIKE '%".$destino."%'";
	$q=$adb->pquery($sql);
	while($r=$adb->fetchByAssoc($q)){
		$return[]=$r;
	}
	return array('airports'=>$return);
}
/**
 * EBIRDS API - Crea session de webservice con el operador EBIRDS
 * @param array $params - Datos de usuario EBIRDS
 * @return array $return - Retorna un array con detalle de session
 * @deprecated - Cambiado a archivo propio
 */
function ebirds_sessionCreate($params){
	// return getcwd();
	// require_once('customerportal/nusoap/lib/nusoap.php');
	$Server_Path="http://www.ebirdtravel.com/webservices/test_flight.php";
	$ebirds_client = new soapclient2($Server_Path, false,'','','','');
	$ebirds_client->soap_defencoding ="UTF-8";
	// return $params;
	$ret=$ebirds_client->call("sessionCreate", $params, $Server_Path, $Server_Path);
	return $ret[0];
}
/**
 * EBIRDS API - Busqueda de vuelos
 * @param array $data - Datos de consulta
 * @return array $return - Retorna un array con el listado de aeropuertos
 * @deprecated - Cambiado a archivo propio
 */
function ebirds_availabilitySearch($data){
	$params=array('username'=>'emanuel',
				  'password'=>'flycaboverde2014',
				  'licenseKey'=>'WE2Y-V2LZ-7SCK-AGER-HA3R'
				);
	// $return=ebirds_sessionCreate($params);



	return array($data);
}


/**
 * Crea una url amigable a partir de un string
 * @param string $string Cadena a transformar en url amigable
 * @param string $slug Caracter separador de cada palabra en la cadena
 * @param string $extra Extra para hacer match dentro de la cadena
 * @return string Retorna la cadena codificada como url
 */
function Slug($string, $slug = '-', $extra = null){

	if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false){
		$string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
	}

	return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']++~i', $slug, $string), $slug));
}

/* Esta funcion devuelve el menú en HTML de la aplicación completa sin estar conectado al sistema */

function getMenuApp($data) {
	global $current_user;

	$userid = 1;
	$user = new Users();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	require_once('Smarty_setup.php');
	$smarty = new vtigerCRM_Smarty;
	$header_array = getHeaderArray(true);
	$data = serialize($smarty);
	$smarty->assign("HEADERS",$header_array);
	$smarty->assign("PREFIJO_URL",'/platzilla/');
	$smarty->assign("BRIEFING",'true');
	$data = $smarty->fetch("centaurus/Header.menu.inc.tpl");

	return $data;
}


/* Begin the HTTP listener service and exit. */
if (!isset($HTTP_RAW_POST_DATA)){
	$HTTP_RAW_POST_DATA = file_get_contents('php://input');
}
$server->service($HTTP_RAW_POST_DATA);

exit();

?>
