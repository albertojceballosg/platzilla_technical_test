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

require('Smarty/libs/Smarty.class.php');

//vtigerCRM_Smarty No desciende directamente de la clase Smarty debido a que en la versión 3 no se manejan los tags {php}{/php} utilizados en el código de Platzilla, por tanto se utiliza la clase auxiliar que ofrece: SmartyBC, esta dispone de parches para solucionar errores de compatibilidad.

class vtigerCRM_Smarty extends SmartyBC{

  	function __construct(){  //Constructor de la clase

        parent::__construct();

        global $CALENDAR_DISPLAY, $WORLD_CLOCK_DISPLAY, $CALCULATOR_DISPLAY, $CHAT_DISPLAY, $LAST_VIEWED, $current_user, $plat;

        //-- Inicio Definición de directorios --//

        $this->setTemplateDir('Smarty/templates/centaurus');
        $this->setCompileDir('Smarty/templates_c/centaurus');

        $this->setConfigDir('Smarty/configs');
        $this->setCacheDir('Smarty/cache');

        //-- Fin Definición de directorios --//

        //$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
        //$this->assign('app_name', 'vtigerCRM_Smarty');

		$this->assign('CALENDAR_DISPLAY', $CALENDAR_DISPLAY);
 		$this->assign('WORLD_CLOCK_DISPLAY', $WORLD_CLOCK_DISPLAY);
 		$this->assign('CALCULATOR_DISPLAY', $CALCULATOR_DISPLAY);
 		$this->assign('CHAT_DISPLAY', $CHAT_DISPLAY);
		$this->assign('LAST_VIEWED', $LAST_VIEWED);
		$this->assign('CURRENT_USER_ID', isset ($current_user->id) ? $current_user->id : null);

 		// Query For TagCloud only when required
 		if((isset($_REQUEST ['action'])) && ($_REQUEST ['action'] == 'DetailView')) {
			//Added to provide User based Tagcloud
            $this->assign('TAG_CLOUD_DISPLAY', self::lookupTagCloudView($current_user->id) );
 		}
   }


	/** Cache the tag cloud display information for re-use */
	static $_tagcloud_display_cache = array();

	static function lookupTagCloudView($userid) {
		if(!isset(self::$_tagcloud_display_cache[$userid])) {
			//MODIFICADO PARA LIMPIEZA DE MODULOS
			//self::$_tagcloud_display_cache[$userid] = getTagCloudView($userid);
			self::$_tagcloud_display_cache[$userid] = null;
		}
		return self::$_tagcloud_display_cache[$userid];
	}
	/** END */

}

?>
