{* Preload Template for Critical Resources *}
{strip}
{* Critical CSS *}
<link rel="preload" href="themes/{$THEME}/css/listview.css" as="style">
<link rel="preload" href="themes/{$THEME}/css/style.css" as="style">

{* Critical JavaScript *}
<link rel="preload" href="themes/{$THEME}/js/list.js" as="script">
<link rel="preload" href="themes/{$THEME}/js/jquery.dataTables.min.js" as="script">
<link rel="preload" href="themes/{$THEME}/js/bootstrap-timepicker.min.js" as="script">

{* Critical Fonts *}
<link rel="preload" href="themes/{$THEME}/fonts/font-awesome.woff2" as="font" type="font/woff2" crossorigin>

{* Preconnect to external resources *}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://fonts.googleapis.com">

{* Preload Templates *}
<link rel="prefetch" href="Smarty/templates/centaurus/ListViewEntries.tpl">
<link rel="prefetch" href="Smarty/templates/centaurus/ListViewContents.tpl">

{* Module-specific resources *}
{if $MODULE_NAME}
    <link rel="preload" href="modules/{$MODULE_NAME}/resources/List.js" as="script">
    <link rel="preload" href="modules/{$MODULE_NAME}/resources/List.css" as="style">
{/if}
{/strip}
