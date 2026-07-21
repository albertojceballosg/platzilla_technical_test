{assign var="labelWords" value= 4} {*$label|str_word_count:0:'áéíóúñ'*}
{if ($keyval != '')}
    {if (in_array (intval ($keyid), array (1, 2, 7, 14, 52, 2206)))}
        {assign var="strLen" value=$keyval|strlen}
        <div class="col-md-6">
            <div class="col-md-5">
                <div class="label-input">
                    <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                            id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
                </div>
            </div>
            <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block">
                <div class="input-group"> {* readonly="readonly" *}
                    {if $keyid neq 52}
                        <a href="#" id="{$keyfldname}" data-type="text" data-value="{$keyval}" data-pk="0" data-container="body"
                            data-title="{$label}" class="editable editable-click {if $strLen gte 150}protip{/if}"
                            data-placement="top" {if $strLen gte 150}data-pt-title="{$keyval}" {/if}
                            {if intval ($keyid) eq 7}data-is-null="{if isset($data.is_null_value) && $data.is_null_value}true{else}false{/if}"
                        {/if} data-url='index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}'>
                        <span id="dtlview_{$label}" class="form-control"
                            style="overflow: hidden;width: 100%;vertical-align: top;{if ($keyval|strlen) gte 25}height: 2.5em;line-height: 1.25em !important;padding-top: 0;{else}padding-top: 0.5em;{/if}"
                            data-toggle="tooltip">
                            {if intval ($keyid) eq 7}
                                {if isset($data.is_null_value) && $data.is_null_value}
                                    &nbsp;_&nbsp;
                                {else}
                                    {$keyval}
                                {/if}
                            {else}
                                {$keyval}
                                {*$keyval|truncate:45*}
                            {/if}
                            <!-- wa09-10-24--->
                        </span>
                    </a>
                {else }
                    <span id="dtlview_{$label}" class="form-control" style="overflow-x: hidden;width: 100%;"
                        data-toggle="tooltip">{$keyval}</span>
                {/if}
            </div>
        </div>
    </div>
{elseif ($keyid == 4)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input" style="margin-left: 2px !important;">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help"
                        style="font-size:0.2em;"></span>{*<i class="fa fa-cubes"></i>*}{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group" style="width: 100%;">
                {*<span class="input-group-addon label-readonly "></span>*}
                <span class="form-control  {* label-readonly *} b-left" style="overflow-x: hidden;width: 100%"
                    data-toggle="tooltip">{$keyval}</span>
            </div>
        </div>
    </div>
{elseif (in_array (intval ($keyid), array (5, 23)))}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help"
                        style="font-size:0.2em;"></span>{* <i class="fa fa-calendar"></i>&nbsp&nbsp; *}{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                {if $keyid neq 70}
                    <a href="#" id="{$keyfldname}" data-value="{$keyval}" data-type="date" data-pk="1" data-container="body"
                        data-title="{$label}" class="editable editable-click" data-placement="top"
                        data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                        <span class="form-control b-left" style="overflow-x: hidden;width: 100% "
                            data-toggle="tooltip">{$keyval}</span>
                    </a>
                {else }
                    <span class="form-control b-left" style="overflow-x: hidden;width: 100% "
                        data-toggle="tooltip">{$keyval}</span>
                {/if}
                {*<span class="input-group-addon" style="overflow-x: hidden; "><i class="fa fa-calendar"></i></span> *}

            </div>
        </div>
    </div>
{elseif ($keyid == 9)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span><i
                        class="fa">%</i>&nbsp&nbsp;{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                {*<span class="input-group-addon label-readonly"><i class="fa">%</i></span> *}
                <input type="hidden" id="hdtxt_IsAdmin" value="{$keyadmin}" />
                <a href="#" id="{$keyfldname}" data-value="{$keyval}" data-type="number" data-pk="1" data-container="body"
                    data-title="{$label}" class="editable editable-click" data-placement="top"
                    data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                    <span class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                        style="overflow-x: hidden;width: 100%" data-toggle="tooltip">{$keyval} %</span>
                </a>
            </div>
        </div>
    </div>
{elseif ($keyid == 10)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help"
                        style="font-size:0.2em;"></span>{* <i class="fa fa-cogs"></i>&nbsp; *}{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                {*<span class="input-group-addon label-readonly"><i class="fa fa-cogs"></i></span>*}
                <span id="dtlview_{$label}" class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                    style="overflow-x: hidden;width: 100%" data-toggle="tooltip">{$keyval}</span>
            </div>
        </div>
    </div>
{elseif ($keyid == 11)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span><i
                        class="fa fa-{if ($keyfldname == 'phone')}phone{elseif $keyfldname eq 'mobile' || $keyfldname eq 'num_cel'}mobile{elseif $keyfldname eq 'fax'}fax{else}home{/if}"></i>&nbsp;{$label}
                </label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                {*<span class="input-group-addon label-readonly"><i class="fa fa-{if ($keyfldname == 'phone')}phone{elseif $keyfldname eq 'mobile' || $keyfldname eq 'num_cel'}mobile{elseif $keyfldname eq 'fax'}fax{else}home{/if}"></i></span>*}
                <a href="#" id="{$keyfldname}" data-value="{$keyval}" data-type="tel" data-pk="1" data-container="body"
                    data-title="{$label}" class="editable editable-click" data-placement="top"
                    data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                    <span id="dtlview_{$label}" class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                        style="overflow-x: hidden;width: 100%" data-toggle="tooltip">{$keyval}</span>
                </a>
            </div>
        </div>
    </div>
{elseif (in_array (intval ($keyid), array (13, 104)))}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help"
                        style="font-size:0.2em;"></span>{* <i class="fa fa-envelope"></i>&nbsp; *}{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                {*<div class="input-group-addon label-readonly"><i class="fa fa-envelope"></i></div>*}
                <span class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                    style="overflow-x: hidden;width: 100%" data-toggle="tooltip"><a href="mailto:{$keyval}"
                        target="_blank">{$keyval}</a></span>
                {*
                    <a href="#" id="{$keyfldname}" data-value="{$keyval}" data-type="email" data-pk="1"
                       data-title="{$label}" class="editable editable-click" data-placement="top"
                       style="float: left; font-size: small"
                       data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">Editar&nbsp;{$label}
                    </a> *}
            </div>
        </div>
    </div>
{elseif (in_array (intval ($keyid), array (15, 31, 32))) || (($keyid == 16) && (!$keyoptions[0][3]))}
    {foreach $keyoptions as $arr}
        {if ($arr[0] == $APP.LBL_NOT_ACCESSIBLE) && ($arr[2] == 'selected')}
            {assign var=keyval value=$APP.LBL_NOT_ACCESSIBLE}
            {assign var=fontval value='red'}
        {elseif ($arr[0]|regex_replace:"/.*<font.*/":"FOUND") == "FOUND" && ($arr[2] == 'selected')}
            {* Valor obsoleto que viene con tag <font color='red'> *}
            {assign var=keyval value=$arr[0]|regex_replace:"/(<font[^>]*>|<\/font>)/":""}
            {assign var=fontval value='red'}
        {else}
            {assign var=fontval value=''}
        {/if}
    {/foreach}
    <div class="col-md-6">
        {if empty($keyval) || $keyval == ' '}
            {assign var="keyval" value="&nbsp-&nbsp;"}

        {/if}
        <div class="col-md-5">
            <div class="label-input">
                <label for="txtbox_{$label}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group"> {* readonly="readonly"*}
                {assign var=bgColor value=''}
                {assign var=textColor value=''}
                {assign var=tooltipText value=''}
                {if $keyfldname eq 'work_situation' || $keyfldname eq 'project_situation'}
                    {assign var=cleanVal value=$keyval|trim}
                    {if $cleanVal eq 'Óptima'}
                        {assign var=bgColor value='#2E7D32'}
                        {assign var=textColor value='white'}
                        {if $keyfldname eq 'work_situation'}
                            {assign var=tooltipText value='El trabajo progresa rápido gastando menos o lo justo.'}
                        {else}
                            {assign var=tooltipText value='El proyecto progresa rápido manteniéndose dentro del presupuesto.'}
                        {/if}
                    {elseif $cleanVal eq 'En control'}
                        {assign var=bgColor value='#8BC34A'}
                        {assign var=textColor value='white'}
                        {if $keyfldname eq 'work_situation'}
                            {assign var=tooltipText value='El trabajo cumple el cronograma y el presupuesto.'}
                        {else}
                            {assign var=tooltipText value='El progreso del proyecto está en los márgenes de control del 5% (riesgo de descontrol) y dentro del presupuesto.'}
                        {/if}
                    {elseif $cleanVal eq 'Alerta de eficiencia'}
                        {assign var=bgColor value='#1976D2'}
                        {assign var=textColor value='white'}
                        {if $keyfldname eq 'work_situation'}
                            {assign var=tooltipText value='Se está cumpliendo el tiempo, pero a un costo mayor (poca rentabilidad).'}
                        {else}
                            {assign var=tooltipText value='Se está cumpliendo el tiempo dentro de las bandas de control del 5%, pero a un costo mayor (poca rentabilidad).'}
                        {/if}
                    {elseif $cleanVal eq 'Retraso operativo'}
                        {assign var=bgColor value='#FF9800'}
                        {assign var=textColor value='white'}
                        {if $keyfldname eq 'work_situation'}
                            {assign var=tooltipText value='Estamos lentos, pero aún no nos hemos pasado del presupuesto.'}
                        {else}
                            {assign var=tooltipText value='Hay retraso, pero aún no se ha pasado del presupuesto.'}
                        {/if}
                    {elseif $cleanVal eq 'Crítica'}
                        {assign var=bgColor value='#D32F2F'}
                        {assign var=textColor value='white'}
                        {if $keyfldname eq 'work_situation'}
                            {assign var=tooltipText value='El peor escenario: vamos tarde y ya gastamos más de lo previsto.'}
                        {else}
                            {assign var=tooltipText value='Hay retraso y ya se ha gastado más de lo previsto.'}
                        {/if}
                    {/if}
                {/if}

                <a href="#" id="{$keyfldname}" data-type="select" data-value="{$keyval}" data-pk="1" data-container="body"
                    data-title="{$label}" class="editable editable-click" data-placement="top"
                    data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                    <span id="dtlview_{$label}" class="form-control"
                        style="{if $keyfldname eq 'work_situation' || $keyfldname eq 'project_situation' && $bgColor neq ''}background-color: {$bgColor} !important; color: {$textColor} !important; font-weight: bold; border: 2px solid {$bgColor};{else}color: {if (!empty ($fontval))}{$fontval} !important{else}inherit{/if};{/if} overflow: hidden;width: 100%;{if ($keyval|strlen) gte 25}height: 40px;line-height: 1.35em !important;padding-top: 0;{else}padding-top: 0.5em;{/if}"
                        {if ($keyfldname eq 'work_situation' || $keyfldname eq 'project_situation') && $tooltipText neq ''}title="{$tooltipText}"
                            data-original-title="{$tooltipText}" {/if} data-toggle="tooltip">{if $APP.$keyval!=''}{$APP.$keyval}
                        {elseif $MOD.$keyval!=''}{$MOD.$keyval}
                        {elseif empty($keyval) || $keyval == ' '}&nbsp;
                        - &nbsp;{else}{$keyval}
                        {/if}</span>
                </a>
            </div>
        </div>
    </div>
{elseif ($keyid == 33) || (($keyid == 16) && ($keyoptions[0][3]))}
    {assign var="DETAILVIEW_WORDWRAP_WIDTH" value="70"}
    {foreach item=sel_val from=$keyoptions }
        {if $sel_val[2] eq 'selected'}
            {if $selected_val neq ''}
                {assign var=selected_val value=$selected_val|cat:', '}
            {/if}
            {assign var=selected_val value=$selected_val|cat:$sel_val[0]}
        {/if}
    {/foreach}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help"
                        style="font-size:0.2em;"></span>{*<i class="fa fa-reorder"></i>&nbsp; *}{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group" style="width: 100%;"> {*readonly="readonly"*}
                {*<span class="input-group-addon label-readonly"><i class="fa fa-reorder"></i></span>*}
                <a href="#" id="{$keyfldname}" data-value="{$keyval}" data-type="select" data-pk="1" data-container="body"
                    data-title="{$label}" class="editable editable-click" data-placement="top"
                    data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                    <span id="dtlview_{$label}" class="form-control  {* label-readonly *} b-left"
                        style="overflow-x: hidden;width: 100%"
                        data-toggle="tooltip">{$selected_val|replace:"\n":"<br>&nbsp;&nbsp;"}</span>
                </a>
            </div>
        </div>
    </div>
{elseif ($keyid == 17)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                <span class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                    style="overflow-x: hidden;width: 100%" title="" data-toggle="tooltip">
                    <a href="{$keyval}" target="_blank" title="">
                        {if $keyval|strlen gt 45}{$keyval|truncate:45}{else}{$keyval}{/if}
                    </a>
                </span>
            </div>
        </div>
    </div>
{elseif ($keyid == 19)}
    {if ($label == $MOD.LBL_ADD_COMMENT)}
        {assign var=keyval value=''}
    {/if}
    <span class="detailedViewTextBox" style="cursor: default !important background-color: #eee !important;"
        {*readonly="readonly"*} data-toggle="tooltip">
        {$keyval|regex_replace:"/(^|[\n ])([\w]+?:\/\/.*?[^ \"\n\r\t<]*)/":"\\1<a href=\"\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:\/[^ \"\t\n\r<]*)?)/":"\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)/i":"\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>"|regex_replace:"/,\"|\.\"|\)\"|\)\.\"|\.\)\"/":"\""|replace:"\n":"<br>&nbsp;"}
    </span>
{elseif ($keyid == 21)}
    {assign var="strLen" value=$keyval|strlen}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block">
            <div class="input-group">
                <a href="#" id="{$keyfldname}" data-type="textarea" data-pk="1" data-value='{$keyval|strip_tags:false}'
                    data-title="{$label}" data-container="body" class="editable-pre-wrapped editable editable-click"
                    data-placement="top"
                    data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}"><span
                        id="dtlview_{$label}" class="form-control scroll-fino{if $strLen gte 150} protip{/if}"
                        style="overflow-y: auto; width: 100%; resize: vertical; word-break: break-word; height:100%;{if ($keyval|strlen) gt 51} max-height: 10em;{else} max-height: 5em;{/if}line-height: 1.35em !important;"
                        data-toggle="tooltip" {if ($strLen gte 150) && ($strLen lt 1200)} data-pt-title='{$keyval}'
                        {elseif $strLen gte 1200} data-pt-title='{$keyval|truncate:1200}' data-pt-width="600"
                        {/if}>{$keyval|strip_tags:false|truncate:1500}<br /></span></a>
            </div>
        </div>
    </div>
{elseif ($keyid == 53)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group">
                {if $keyadmin eq 1}
                    <span id="dtlview_{$label}" class="form-control" {*readonly="readonly"*}
                        style="overflow-x: hidden;width: 100%" data-toggle="tooltip">{$keyval}</span>
                    {* <a rel="wa" href="{$keyseclink.0}">{$keyval}</a> *}
                {else}
                    <a href="#" id="{$keyfldname}" data-type="select" data-pk="1" data-title="{$label}" data-container="body"
                        class="editable editable-click" data-placement="top"
                        data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                        <span id="dtlview_{$label}" class="form-control" {*readonly="readonly"*}
                            style="overflow-x: hidden;width: 100%" data-toggle="tooltip"> {$keyval}</span>
                    </a>
                {/if}
            </div>
        </div>
    </div>
{elseif ($keyid == 56)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input" style="margin: 0.35em 0!important;">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>
                    {*<i class="fa fa-{if (strtolower ($keyval) == 'yes')}check{else}minus{/if}-square"></i>*}{$label}
                </label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="" style="display: block;">
            <div class="input-group">
                {*<span class="input-group-addon label-readonly"><i class="fa fa-{if (strtolower ($keyval) == 'yes')}check{else}minus{/if}-square"></i></span>*}
                <span id="dtlview_{$label}" class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                    style="overflow-x: hidden;width: 100%;padding-top: 0.9em;"
                    data-toggle="tooltip">{if (strtolower ($keyval) == 'yes')}Sí{else}{$keyval}{/if}</span>
            </div>
        </div>
    </div>
{elseif ($keyid == 71)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help"
                        style="font-size:0.2em;"></span>{* <i class="fa fa-money"></i>&nbsp; *}{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group" style="width: 100%;">
                {*<span class="input-group-addon label-readonly"><i class="fa fa-money"></i></span>*}
                <input type="hidden" id="hdtxt_IsAdmin" value="{$keyadmin}" />
                <a href="#" id="{$keyfldname}" data-value="{$keyval}" data-type="text" data-pk="1" data-container="body"
                    data-title="{$label}" class="editable editable-click" data-placement="top"
                    data-url="index.php?module={$MODULE}&action=SaveFromDetailView&Ajax=true&record={$ID}">
                    <span class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                        style="overflow-x: hidden;width: 100%"
                        data-toggle="tooltip">{if $keyval gt 0}{$keyval}{/if}{$ORGANIZATION_CURRENCY.currency_symbol}</span>
                </a>
            </div>
        </div>
    </div>
{elseif ($keyid == 99)}
    {$CHANGE_PW_BUTTON}
{elseif ($keyid == 101)}
    <div id="progress_{$keyval.relmodule}">
        <div id="progress_lbl_{$keyval.relmodule}"
            style="float:left; margin-left: 0%; width: {$keyval.progress}%; margin-top: 4px; background:#32ff50; font-weight: bold; text-shadow: 1px 1px 0 #fff;text-align:center;">
            {$keyval.progress}
            %
        </div>
        <input type="hidden" id="progress_fldname_{$keyval.relmodule}" value="{$keyfldname}" />
    </div>
{elseif ($keyid == 115)}
    {$keyval}
{elseif (in_array (intval ($keyid), array (116, 117)))}
    {if ($keyadmin == 1) || ($keyid == 117)}
        <div class="col-md-6">
            <div class="col-md-5">
                <div class="label-input">
                    <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                            id="helpDV{$keyid}_{$keyfldname}" name="help"
                            style="font-size:0.2em;"></span>{* <i class="fa fa-database"></i>&nbsp; *}{$label}</label>
                </div>
            </div>
            <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
                <div class="input-group" style="width: 100%;">
                    {*<span class="input-group-addon label-readonly"><i class="fa fa-database"></i></span>*}
                    <span class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                        style="overflow-x: hidden;width: 100%" data-toggle="tooltip">{$keyval}</span>
                </div>
            </div>
        </div>
    {else}
        {$keyval}
    {/if}
{elseif ($keyid == 156)}
    {if ($smarty.request.record != $CURRENT_USERID) && ($keyadmin == 1)}
        <span
            id="dtlview_{$label}">{if $APP.$keyval!=''}{$APP.$keyval}{elseif $MOD.$keyval!=''}{$MOD.$keyval}{else}{$keyval}{/if}
            &nbsp;</span>
    {else}
        <div class="col-md-6">
            <div class="col-md-5">
                <div class="label-input">
                    <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                            id="helpDV{$keyid}_{$keyfldname}" name="help"
                            style="font-size:0.2em;"></span>{* <i class="fa fa-check-square"></i>&nbsp; *}{$label}</label>
                </div>
            </div>
            <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
                <div class="input-group" style="width: 100%;">
                    {*<span class="input-group-addon label-readonly"><i class="fa fa-check-square"></i></span>*}
                    <span id="dtlview_{$label}" class="form-control  {* label-readonly *} b-left" {*readonly="readonly"*}
                        style="overflow-x: hidden;width: 100%" data-toggle="tooltip">{$keyval}</span>
                </div>
            </div>
        </div>
    {/if}
{elseif ($keyid == 256)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group" style="width: 100%;">
                <span id="dtlview_{$label}" class="detailedViewTextBox" {*readonly="readonly"*}
                    style="overflow-x: hidden;width: 100%"
                    data-toggle="tooltip">{$keyval|regex_replace:"/(^|[\n ])([\w]+?:\/\/.*?[^ \"\n\r\t<]*)/":"\\1<a href=\"\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:\/[^ \"\t\n\r<]*)?)/":"\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)/i":"\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>"|regex_replace:"/,\"|\.\"|\)\"|\)\.\"|\.\)\"/":"\""|replace:"\n":"<br>&nbsp;"}
                    &nbsp;</span>
            </div>
        </div>
    </div>
{elseif ($keyid == 258)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <div class="input-group" style="width: 40%; height: 20%;">
                <div style="border: 1px solid #DDDDDD; border-radius: 15px; padding: 5px;">{$keyval}</div>
            </div>
        </div>
    </div>
{elseif ($keyid eq 2208) && ($TABLE_FIELDS neq NULL)}
    <div class="col-md-12">
        <div class="label-input">
            <label for="td_{$keyfldname}"><span id="helpDV{$keyid}_{$keyfldname}" name="help"
                    style="font-size:0.2em;"></span>{$label}</label>
        </div>
    </div>
    {include file="TableFieldDetailView.tpl"}
{elseif ($keyid == 5006)}
    <div class="col-md-6">
        {if ($keyval|strpos:'vimeo.com')}
            <div id="video-{$keyfldname}" class="embed-responsive embed-responsive-16by9 video" data-vimeo-url="{$keyval}">
            </div>
            <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
        {elseif ($keyval|strpos:'youtube.com')}
            <div class="embed-responsive embed-responsive-16by9 video">
                <iframe class="embed-responsive-item" src="{$keyval}" allow="autoplay; fullscreen" allowfullscreen=""
                    frameborder="0">
                </iframe>
            </div>
        {else}
            <div class="well well-sm">
                <P class="text-left">Video no encontado!</P>
            </div>
        {/if}
    </div>
{elseif ($keyid == 8192)}
    {if (!empty ($keyoptions))}
        {assign var='dummy' value=array_search($keyval, $keyoptions)}
        {if ($dummy === false)}
            {assign var='selectedChoicePosition' value=-1}
        {else}
            {assign var='selectedChoicePosition' value=$dummy}
        {/if}
        <div class="col-md-12">
            <div class="col-md-12">
                <div class="label-input" style="text-align: left;">
                    <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                            id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
                </div>
            </div>
            <div class="form-group col-md-12 field-container" id="td_{$fldname}">
                <div class="input-group" style="width: 100%;">
                    <div class="pipeline-chart">
                        {* style="width: calc((100% - 28px) / {count ($keyoptions)});" title="{$choice}"*}
                        {foreach $keyoptions as $index => $choice}
                            <button type="button" class="pipeline-element{if ($selectedChoicePosition >= $index)} selected{/if}"
                                style="width: calc((100% - 28px) / {count ($keyoptions)}); line-height: 1.1em!important;"
                                title="{$choice}" disabled="disabled">{$choice}</button>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    {/if}
{elseif ($keyid == 5010)}
    <div class="col-md-12">xxxxxxxx
        {$keyval}
    </div>
{elseif ($keyid neq 70)}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="" style="display: block;">
            <div class="input-group" style="width: 100%;">
                <span id="dtlview_{$label}" class="form-control" {*readonly="readonly"*} style=" "
                    data-toggle="tooltip">{$keyval}</span>
            </div>
        </div>
    </div>
{/if}
{elseif ($keyid == 4096)}
{if (!empty ($FIELD_ATTACHMENTS[$keyfldname]))}
    <div class="col-md-6">
        <div class="col-md-5">
            <div class="label-input">
                <label for="td_{$keyfldname}" {if $labelWords gte 3}style="line-height: 1.25em !important;" {/if}><span
                        id="helpDV{$keyid}_{$keyfldname}" name="help" style="font-size:0.2em;"></span>{$label}</label>
            </div>
        </div>
        <div class="form-group col-md-7 data-input" id="td_{$keyfldname}" style="display: block;">
            <ul class="col-md-12 attachments-container" style="list-style: none;">
                {foreach $FIELD_ATTACHMENTS[$keyfldname] as $attachment}
                    <li class="col-md-3 attachment" style="margin-bottom: 3px; position: relative; width: 100%;">
                        <a href="{$attachment.uri}" title="{$attachment.name}" target="_blank">
                            <span class="attachment-name">{$attachment.name}</span><span class="attachment-size">
                                ({number_format ($attachment.size, 2, '.', '')}
                                KB)</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}
{/if}