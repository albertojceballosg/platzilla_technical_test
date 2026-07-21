{literal}
<style>
	.ch-grid {
	margin: 20px 0 0 0;
	padding: 0;
	list-style: none;
	display: block;
	text-align: center;
	width: 100%;
}

.ch-grid:after,
.ch-item:before {
	content: '';
    display: table;
}

.ch-grid:after {
	clear: both;
}

.ch-grid li {
	width: 220px;
	height: 220px;
	display: inline-block;
	margin: 20px;
}

.ch-item {
	width: 100%;
	height: 100%;
	border-radius: 50%;
	position: relative;
	cursor: default;
	box-shadow: 
		inset 0 0 0 0 rgba(0, 0, 0, 0.4),
		inset 0 0 0 16px rgba(255,255,255,0.6),
		0 1px 2px rgba(0,0,0,0.1);
		
	-webkit-transition: all 0.4s ease-in-out;
	-moz-transition: all 0.4s ease-in-out;
	-o-transition: all 0.4s ease-in-out;
	-ms-transition: all 0.4s ease-in-out;
	transition: all 0.4s ease-in-out;
	*background-size: 210px;
	background-repeat: no-repeat;
}

.ch-img-1 { 
	*background-image: url(../img/management.png);
}

.ch-img-2 { 
	*background-image: url(../img/CRM.png);
}

.ch-img-3 { 
	*background-image: url(../img/6.jpg);
}

.ch-info {
	position: absolute;
	width: 100%;
	height: 100%;
	border-radius: 50%;
	opacity: 0;
	
	-webkit-transition: all 0.4s ease-in-out;
	-moz-transition: all 0.4s ease-in-out;
	-o-transition: all 0.4s ease-in-out;
	-ms-transition: all 0.4s ease-in-out;
	transition: all 0.4s ease-in-out;
	
	-webkit-transform: scale(0);
	-moz-transform: scale(0);
	-o-transform: scale(0);
	-ms-transform: scale(0);
	transform: scale(0);
	
	-webkit-backface-visibility: hidden; /*for a smooth font */

}

.ch-info h3 {
	color: #fff;
	text-transform: uppercase;
	position: relative;
	letter-spacing: 2px;
	font-size: 22px;
	margin: 0 30px;
	padding: 40px 0 0 0;
	height: 60px;
	font-family: 'Open Sans', Arial, sans-serif;
	text-shadow: 
		0 0 1px #fff, 
		0 1px 2px rgba(0,0,0,0.3);
}

.ch-info p {
	color: #fff;
	padding: 5px 5px;
	font-style: italic;
	margin: 0 30px;
	font-size: 12px;
	border-top: 1px solid rgba(255,255,255,0.5);
}

.ch-info p a {
	display: block;
	color: #fff;
	color: rgba(255,255,255,0.7);
	font-style: normal;
	font-weight: 700;
	text-transform: uppercase;
	font-size: 9px;
	letter-spacing: 1px;
	padding-top: 4px;
	font-family: 'Open Sans', Arial, sans-serif;
}

.ch-info p a:hover {
	color: #fff222;
	color: rgba(255,242,34, 0.8);
}

.ch-item:hover {
	box-shadow: 
		inset 0 0 0 110px rgba(0, 0, 0, 0.4),
		inset 0 0 0 16px rgba(255,255,255,0.8),
		0 1px 2px rgba(0,0,0,0.1);
	cursor:pointer;
}

.ch-item:hover .ch-info {
	opacity: 1;
	
	-webkit-transform: scale(1);
	-moz-transform: scale(1);
	-o-transform: scale(1);
	-ms-transform: scale(1);
	transform: scale(1);	
}

.oe_slogan {
color: #333333;
font-family: Ubuntu, sans-serif;
text-align: center;
margin-top: 32px;
*margin-bottom: 32px;
}
h2.oe_slogan {
font-size: 40px;
font-weight: 300;
}
[class*='oe_span'] {
float: left;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
padding: 0 16px;
}
.oe_span12 {
width: 928px;
}
.oe_span6 {
width: 100%;
}
.oe_mt32 {
margin-top: 32px !important;
}
.oe_centeralign {
text-align: center;
}
.oe_emph {
font-weight: 400;
}
.oe_big {
font-size: 24px;
}
.oe_centeralign {
text-align: center;
}
div.oe_demo {
position: relative;
border: 1px solid #dedede;
padding: 10px;
}
.oe_picture {
display: block;
max-width: 84%;
max-height: 400px;
margin: 16px 8%;
}
.oe_screenshot {
-webkit-border-radius: 3px;
-moz-border-radius: 3px;
-ms-border-radius: 3px;
-o-border-radius: 3px;
border-radius: 3px;
-webkit-box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.2);
-moz-box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.2);
box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.2);
min-height:180px;
}
div.oe_demo div.oe_demo_footer {
position: absolute;
left: 0;
background-color: rgba(0, 0, 200, 0.5);
opacity: 0.85;
bottom: -1px;
width: 100%;
padding-top: 7px;
padding-bottom: 7px;
color: white;
font-size: 14px;
font-weight: bold;
border-bottom-left-radius: 3px;
border-bottom-right-radius: 3px;
pointer-events: none;
}
.ot_objetive{
margin-left:15px;
width: 650px;
float: left;
}
.oe_form_start {
text-align: center;
}
.oe_domain {
display: inline-block;
margin-left: -145px;
margin-right: 20px;
}
.clear-this{clear:both}
</style>
{/literal}

<div class="post-header clearfix biggermast" style="background-image:url(http://empresafacil.platzilla.com/empresafacil/storage/2014/April/week5/38381_appsHeader1280.jpg);background-size: cover;">
	<div>
		<h1>
			{$MOD.APPLICATIONS_AVALIABLES}
		</h1>
	</div>
</div>
<section id="listapplications" class="row mtop-thirty box-large border-shadowgrey">
		<!--h2 class="oe_slogan">Platzilla Apps</h2>
		<h3 class="oe_slogan">Search Apps &amp; Modules</h3-->
		<ul class="ch-grid">
			{foreach from=$listApplications item=app} 
			<li>
				<div class="ch-item" style="background-image: url({$_URLTOBACK}{$app.path}{$app.app_icon}_{$app.image});" onclick="window.location.href='index.php?module={$MODULE}&action=Detail&record={$app.aplicationsid}';">
					<div class="ch-info">
						<h3>{$app.short_name}</h3>
						<p>
							{$app.name}
							<a href="index.php?module={$MODULE}&action=Detail&record={$app.aplicationsid}">Ver Detalles</a></p>
					</div>
				</div>
			</li>
			{/foreach}
		</ul>
</section>