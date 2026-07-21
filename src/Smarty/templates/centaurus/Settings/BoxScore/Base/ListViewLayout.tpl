{strip}
    {math equation= rand() assign= "idBoxScore"}
    {block name="css"}{/block}
    <div id="email-box" class="clearfix summary-header">
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        {/if}
        <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
            <tbody>
            <tr>
                <td rowspan="2" style="vertical-align: top">
                    <div class="infographic-box" style="width: 30px; padding: 0;">
                        {block name="fa_icon"}{/block}
                    </div>
                </td>
                <td class="heading2" style="vertical-align: bottom">
                    <ol class="breadcrumb">
                        <li>
                            <a href="{block name="config_url"}{/block}">CONFIGURACIÓN</a>
                        </li>
                        <li class="active"
                            style="text-transform: uppercase">{$MOD_STRINGS['LBL_BOX_SCORE_INVENTORY']}</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="small" style="vertical-align: top">{$MOD_STRINGS['BOX_SCORE_INVENTORY_DES']}</td>
            </tr>
            </tbody>
        </table>
        <div class="main-box clearfix">
            {block name="nav_tab"}{/block}
            {block name="body_content"}{/block}
            <div class="main-box-body clearfix">&nbsp;</div>
        </div>
    </div>
    {block name="indicators_modal"}{/block}
    {block name="js"}{/block}
{/strip}