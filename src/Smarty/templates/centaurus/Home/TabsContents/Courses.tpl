{strip}
    <style>
        .carousel {
            margin-bottom: 0;
            padding: 0 40px 30px 40px;
        }

        /* The controlsy */
        .carousel-control {
            left: -12px;
            height: 40px;
            width: 40px;
            background: none repeat scroll 0 0 #222222;
            border: 4px solid #FFFFFF;
            border-radius: 23px 23px 23px 23px;
            margin-top: 90px;
        }

        .carousel-control.right {
            right: -12px;
        }

        /* The indicators */
        .carousel-indicators {
            right: 50%;
            top: auto;
            bottom: -10px;
            margin-right: -19px;
        }

        /* The colour of the indicators */
        .carousel-indicators li {
            background: #cecece;
        }

        .carousel-indicators .active {
            background: #428bca;
        }

        .category h2 {
            font-weight: bold;
        }

        @media (min-width: 900px) and (max-width: 1300px) {
            .category h2 {
                font-size: 1.55em;
                margin-left: 0px !important;
                padding-left: 0 !important;
            }

            .carousel {
                margin-left: 5px !important;
            }

            .carousel-control {
                left: -8px;
            }

            .platzilla-course-tab > li > a {
                max-width: 10em;
                text-align: center;
                line-height: 0.5cm;
                padding-left: 5px!important;
                padding-right: 5px!important;
            }
        }

        @media (min-width: 1400px) and (max-width: 1580px) {
            .category h2 {
                font-size: 1.55em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }

            .carousel {
                margin-left: 5px !important;
            }

            .carousel-control {
                left: -8px;
            }
            .platzilla-course-tab > li > a {
                max-width: 10em;
                text-align: center;
                line-height: 0.5cm;
                padding-left: 5px!important;
                padding-right: 5px!important;
            }
        }

        @media (min-width: 1600px) and (max-width: 1800px) {
            .category h2 {
                font-size: 1.55em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            .platzilla-course-tab > li > a {
                max-width: 10em;
                text-align: center;
                line-height: 0.5cm;
                padding-left: 5px!important;
                padding-right: 5px!important;
            }
        }
    </style>
    {assign var='colors' value=array('yellow', 'green', 'blue', 'red')}
{* Filtrar categorías con al menos un curso activo *}
{assign var='filteredCategories' value=[]}
{foreach $CATEGORIES as $category}
    {assign var='hasActive' value=false}
    {assign var='coursesByAudience' value=$COURSES_SERIES[$category->getName()]}
    {foreach $coursesByAudience as $audience => $courses}
        {foreach $courses as $course}
            {if !$IS_INSTANCE || $course->getStatus() == 'ACTIVE'}
                {assign var='hasActive' value=true}
                {break}
            {/if}
        {/foreach}
        {if $hasActive}{break}{/if}
    {/foreach}
    {if $hasActive}
        {append var='filteredCategories' value=$category}
    {/if}
{/foreach}
{assign var='audiences' value=array_keys($COURSES_DATA)}
    {* main div *}
    <div class="main-box clearfix">
        <header class="main-box-header clearfix">
            <h1 class="text-center" style="padding-left: 0!important; font-weight: bold">Microcursos de emprendimiento y
                &nbsp;gestión</h1>
            {if (!$IS_INSTANCE)}
                <div class="text-right hidden">
                    <a href="index.php?module=Courses&action=EditView" class="btn btn-primary">Crear curso</a>
                </div>
            {/if}
        </header>
        {* body page *}
        <div class="main-box-body clearfix">
            <div class="row">
                {if $filteredCategories|@count gt 0}
{assign var='SELECTED_TAB_COURSE' value=$filteredCategories[0]->getName()}
<ul class="nav nav-tabs platzilla-course-tab">
    {foreach $filteredCategories as $category}
        {math equation= rand() assign= "idRef"}
        <li {if ($SELECTED_TAB_COURSE eq $category->getName())} class="active"{/if}>
            <a data-toggle="tab"
               href="#CATEGORY-TAB-{$category->getId()}">{$category->getName()}</a>
        </li>
    {/foreach}
</ul>
{/if}
                <div class="tab-content">
                    {foreach $filteredCategories as $category}
                        <div id="CATEGORY-TAB-{$category->getId()}"
                             class="tab-pane fade in {if ($SELECTED_TAB_COURSE eq $category->getName())} active{/if}">
                            {assign var='COURSES_DATA' value=$COURSES_SERIES[$category->getName()]}
                            {assign var='audiences' value=array_keys($COURSES_DATA)}
                            {foreach $audiences as $audiIndex =>  $audience}
                                {assign var="index" value=$audiIndex|cat:$category->getId()}
                                {* carousel *}
                                {include file="Home/TabsContents/CourseSeries.tpl"}
                            {/foreach}
                        </div>
                    {/foreach}
                    {* body page *}
                </div>
            </div>
        </div>
    </div>
    {* main div
    </div>
    </div>*}
    <script>
        {* literal}
        jQuery(document).ready(function () {
            jQuery('#Carousel-{/literal}{$index}{literal}').carousel({
                interval: 5000
            });
        });
        {/literal *}
        jQuery(document).ready(function () {
            {literal}
            jQuery('#footer-bar').attr('style', 'position: fixed!important;');
            {/literal}
        });
    </script>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
{/strip}