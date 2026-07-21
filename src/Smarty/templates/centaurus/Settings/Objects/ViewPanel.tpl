{strip}
    {block name="css"}{/block}
    {math equation= rand() assign= "idViewPanel"}
    <div id="email-box" class="clearfix" style="padding-bottom: 20px;">
    <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
        <tbody>
        <tr>
            <td rowspan="2" valign="top">
                <div class="infographic-box" style="width: 30px; padding: 0;"><i
                            class="{block name="fa_class"}{/block}"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                    </li>
                    <li class="active" style="text-transform: uppercase">{block name="panel_name"}{/block}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{block name="panel_descripction"}{/block}</td>
        </tr>
        </tbody>
    </table>
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    <div class="main-box clearfix">
        {block name="hidden-data"}{/block}
        {block name="nav_tabs"}{/block}
        <div class="main-box-body clearfix">
            {block name="content"}{/block}
        </div>
    </div>
    {block name="js"}{/block}
{/strip}