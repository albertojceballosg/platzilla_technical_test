{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{*//Hidden fields for modules DetailView//  *}
<input type="hidden" name="parenttab" value="{$CATEGORY}">
<input type="hidden"  name="allselectedboxes" id="allselectedboxes">
{if $MODULE eq 'Documents'}
	<input type="hidden" name="module" value="{$MODULE}">
        <input type="hidden" name="record" value="{$ID}">
        <input type="hidden" name="isDuplicate" value=false>
        <input type="hidden" name="action">
        <input type="hidden" name="return_module">
        <input type="hidden" name="return_action">
        <input type="hidden" name="return_id">
{elseif $MODULE eq 'Calendar'}
	<input type="hidden" name="module" value="Calendar">
        <input type="hidden" name="record" value="{$ID}">
        <input type="hidden" name="activity_mode" value="{$ACTIVITY_MODE}">
        <input type="hidden" name="isDuplicate" value=false>
        <input type="hidden" name="action">
        <input type="hidden" name="return_module">
        <input type="hidden" name="return_action">
        <input type="hidden" name="return_id">
        <input type="hidden" name="user_id" value="{$USER_ID}">
{else}
	<input type="hidden" name="module" value="{$MODULE}">
        <input type="hidden" name="record" value="{$ID}">
        <input type="hidden" name="isDuplicate" value=false>
        <input type="hidden" name="action">
        <input type="hidden" name="return_module">
        <input type="hidden" name="return_action">
        <input type="hidden" name="return_id">
        <input type="hidden" name="mode" value="{$MODE}">
        <input type="hidden" name="profileids" value="{if (!empty ($PROFILE_IDS))}{$PROFILE_IDS|join: ','}{/if}" />
{/if}