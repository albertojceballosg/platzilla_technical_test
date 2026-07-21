{strip}
    {math equation= rand() assign= "idSummaryReport"}
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
            <td rowspan="2" valign="top">
                <div class="infographic-box" style="width: 30px; padding: 0;">
                    <i class="fa fa-cogs yellow-bg"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="{block name="config_url"}{/block}">CONFIGURACIÓN</a>
                    </li>
                    <li class="active" style="text-transform: uppercase">{$MOD['LBL_REPORT_RAILS']}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{block name="panel_title"}{/block}</td>
        </tr>
        </tbody>
    </table>
    <div class="main-box clearfix">
        {block name="master_report"}{/block}
        {block name="nav_tab"}{/block}
        {block name="body_content"}{/block}
    </div>
    {block name="js"}{/block}
{/strip}