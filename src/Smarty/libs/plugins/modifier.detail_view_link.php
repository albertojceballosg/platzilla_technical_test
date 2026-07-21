<?php

	/**
	 * Smarty tab entity id modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla
	 * Type: modifier
	 * Name: module_pluralize
	 * Purpose: get link to show record data in detail view
	 *
	 * @param array $dataLink
	 * @param string $codeInstance
	 * @param string $moduleName
	 *
	 * @return string
	 */
	function smarty_modifier_detail_view_link ($dataLink, $codeInstance, $moduleName) {
		if (!is_array($dataLink) || empty ($dataLink) || empty ($moduleName)) {
			return null;
		}
		$crmId      = $dataLink ['crmId'];
		$linkTitle  = $dataLink ['title'];
		$title 	    = $dataLink ['title'] . ' - ' . $dataLink['progress'] . '%';
		if (empty ($codeInstance)) {
			$url = "<a target='_blank' href='index.php?module={$moduleName}&parenttab=&action=DetailView&record={$crmId}' title='{$title}'>{$linkTitle}</a>";
		} else {
			$url = "<a data-width='950' data-toggle='lightbox' data-parent='' data-gallery='remoteload' data-title=''
			href='index.php?module=Home&action=AjaxHomeUtils&record_id={$crmId}&flmodule={$moduleName}&function=SHOW_AGREEMENT&code={$codeInstance}&Ajax=true'
			title='$title'>$linkTitle</a>";
		}
		return $url;
	}
