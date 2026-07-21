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

<!-- PLANTILLA BASE DE NAVEGACIÓN-->

<!DOCTYPE html>
<html>

<head>
  {if $WP eq 'true'}
    {include file="header.wp.tpl"}
  {/if}
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

  <title>{$MODULE_NAME|@getTranslatedString:$MODULE_NAME} - {$USER} - {$APP.LBL_BROWSER_TITLE}</title>

  <!-- bootstrap -->
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/bootstrap/bootstrap.min.css" />

  <!-- libraries -->
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/font-awesome.css" />
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nifty-component.css" />
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/protip.min.css" />
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/textarea-popover.css" />

  <!-- global styles -->
  <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_styles.css" />


  <!-- this page specific styles -->
  <link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.css" type="text/css" />
  <link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.print.css" type="text/css" media="print" />
  <link rel="stylesheet" href="themes/{$THEME}/css/compiled/calendar.css" type="text/css" media="screen" />
  <link rel="stylesheet" href="themes/{$THEME}/css/libs/morris.css" type="text/css" />
  <link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
  <link rel="stylesheet" href="themes/{$THEME}/css/libs/jquery-jvectormap-1.2.2.css" type="text/css" />

  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192" href="favicon/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
  <link rel="manifest" href="favicon/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">

  <!-- google font libraries -->
  <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet'
    type='text/css'>

  <!--[if lt IE 9]>
    <script src="themes/{$THEME}/js/html5shiv.js"></script>
    <script src="themes/{$THEME}/js/respond.min.js"></script>
  <![endif]-->

  <!-- global scripts -->

  <script src="themes/{$THEME}/js/jquery.js"></script>
  <script src="themes/{$THEME}/js/bootstrap.js"></script>
  <script src="themes/{$THEME}/js/jquery.nanoscroller.min.js"></script>

  <!-- theme scripts -->
  <script src="themes/{$THEME}/js/scripts.js"></script>
  <script src="themes/{$THEME}/js/pace.min.js"></script>
  <script src="themes/{$THEME}/js/protip.min.js"></script>
  <script src="include/js/protip-init.js"></script>

  <!-- Scripts -->
  <!-- Global user settings -->
  <script type="text/javascript">
    var gUserDateFormat = '{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}';
  </script>
  <!-- header-vtiger crm name & RSS -->
  <script language="JavaScript" type="text/javascript" src="include/js/json.js"></script>
  <script language="JavaScript" type="text/javascript" src="include/js/general.js?v={$VERSION}"></script>
  <!-- vtlib customization: Javascript hook -->
  <script language="JavaScript" type="text/javascript" src="include/js/vtlib.js?v={$VERSION}"></script>
  <!-- END -->
  <script language="JavaScript" type="text/javascript" id="_current_language_"
    src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}">
  </script>
  <script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.compatible.js"></script>


  {include file="Header.script.inc.tpl"}

</head>

<body class="theme-blue pace-done">
  {* include file="Header.settings.inc.tpl" *}
  <div id="theme-wrapper">

    <!-- Tip: El código dentro de las secciones 'block' permite que los descendientes de esta plantilla puedan modificar dicho contenido, el resto del código fuera de tales secciones permanece igual en el resto de la jerarquía. -->

    {block name="cabecera"}{/block}

    <!-- Inicio Definición de menú lateral de plantilla -->
    {block name="menu-lateral"}
      <div id="page-wrapper" style="">
        <div class="row">
          <div id="content-wrapper" style="margin-left:0px;">
            <div class="row">
              <div class="col-lg-12">
              {/block}
              <!-- Fin Definición de menú lateral de plantilla -->

{* start crm content *}