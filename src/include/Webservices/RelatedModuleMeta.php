<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

/**
 * Description of RelatedModuleMeta
 * TODO to add and extend a way to track many-many and many-one relationships.
 * @author MAK
 */
class RelatedModuleMeta {
	private $module;
	private $relatedModule;

	private function  __construct($module, $relatedModule) {
		$this->module = $module;
		$this->relatedModule = $relatedModule;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $relatedModule
	 * @return RelatedModuleMeta
	 */
	public static function getInstance($module, $relatedModule) {
		return new RelatedModuleMeta($module, $relatedModule);
	}

	public function getRelationMeta() {
		return null;
	}
}
