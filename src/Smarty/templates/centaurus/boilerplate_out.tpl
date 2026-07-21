{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * Edited by Timemanagement.
   * Developer EV - 2015.05.26
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}

{* PLANTILLA BASE *}

<!DOCTYPE html>
<html>
<head>
	{if $WP eq 'true'}
	{include file="header.wp.tpl"}
	{/if}
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	{*Tílulo de la página o contenido de la etiqueta title*}
	{block name="title"}
	<title>{$MODULE_NAME|@getTranslatedString:$MODULE_NAME} - {$USER} - {$APP.LBL_BROWSER_TITLE}</title>
	{/block}

	<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/bootstrap/bootstrap.min.css" />

	<!-- libraries -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nifty-component.css"  />

	<!-- global styles -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_styles.css?v=1.2" />

	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_custom.css?v=1.1" />

	<!-- this page specific styles -->
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.print.css" type="text/css" media="print" />
	<link rel="stylesheet" href="themes/{$THEME}/css/compiled/calendar.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/morris.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/jquery-jvectormap-1.2.2.css" type="text/css" />

	<!-- Favicon -->

	{include file="base/include/favicon.tpl"}

	<!-- google font libraries -->
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

    <!--[if lt IE 9]>
      <script src="themes/{$THEME}/js/html5shiv.js"></script>
      <script src="themes/{$THEME}/js/respond.min.js"></script>
      <![endif]-->

      {*Se especifican los archivos css necesarios*}
      {block name="css"}
      {/block}

      <script src="themes/{$THEME}/js/jquery.js"></script>
      <script src="themes/{$THEME}/js/bootstrap.js"></script>
      <script src="themes/{$THEME}/js/jquery.nanoscroller.min.js"></script>

      <!-- theme scripts -->
      <script src="themes/{$THEME}/js/scripts.js"></script>
      <script src="themes/{$THEME}/js/pace.min.js"></script>

      <!-- Scripts -->
      <!-- header-vtiger crm name & RSS -->
      <script language="JavaScript" type="text/javascript" src="include/js/json.js"></script>
      <script language="JavaScript" type="text/javascript" src="include/js/general.js?v={$VERSION}"></script>
      <!-- vtlib customization: Javascript hook -->
      <script language="JavaScript" type="text/javascript" src="include/js/vtlib.js?v={$VERSION}"></script>
      <!-- END -->
      <script language="JavaScript" type="text/javascript" id="_current_language_" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
      <script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.compatible.js"></script>
      <script language="JavaScript" type="text/javascript" src="include/js/general.js"></script>
      <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

      {include file="Header.script.inc.tpl"}

      {*Se especifican los archivos js necesarios*}
      {block name="js"}
      {/block}

      <script type="text/javascript">
      jQuery(document).ready(f_localStorage.f_toggleMode);
      </script>

  </head>

  <body class="pace-done">
  	{* include file="Header.settings.inc.tpl" *}
  	<div class="page-wrap container">
  		{* start crm content *}

  		{block name="body"}
  		{/block}

		{block name="scripts"}
		{/block}


