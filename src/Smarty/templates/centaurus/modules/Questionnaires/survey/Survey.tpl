<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <!-- bootstrap -->
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/datepicker.css"/>
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-timepicker.css"/>
    <!-- libraries -->
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/font-awesome.css"/>
    <link rel="stylesheet" type="text/css" href="modules/store/survey.css?v=1.1"/>
    <style type="text/css">

    </style>
</head>
<body>
<div id="survey-body">
    <div class="container-survey">
        <div id="presentation-card" class="card">
		     <div class="header">
                <h4><strong>{$QUESTONNAIRE['name']}</strong></h4>
            </div>
			<div class="cover-frontpage">
				<div class="cover-image">
                {if $QUESTONNAIRE['presentation_video'] neq NULL}
                    {if $VIDEO_TYPE eq 'VIMEO'}
							<div style="margin-top: 1em" id="video-">
                            <iframe src="{$QUESTONNAIRE['presentation_video']}" width="505" height="305" frameborder="0"
                                    webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                        </div>
                    {else}
							<div style="margin-top: 1em" id="video-">
								<iframe id="video" class="youtube-video"
                                    src="{$QUESTONNAIRE['presentation_video']}" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen="allowfullscreen">
                            </iframe>
                        </div>
                    {/if}
                {/if}
            </div>

				<div id="survey-feedback" class="right_text">
					<!-- style="{if $QUESTONNAIRE['presentation_video'] neq NULL} top: 360px {else}top: 35px{/if} !important;">-->
					<div class="survey_scroll">
						<p class="text-justify">{$QUESTONNAIRE['descrption']}</p>
                    <p class="text-justify">{$QUESTONNAIRE['objetive']}</p>
                    {if $QUESTONNAIRE['help_text'] neq NULL}
						<p class="text-justify">{$QUESTONNAIRE['help_text']}</p>
                    {/if}
                </div>
				</div>
			</div>
                <div class="buttons">
                    <button type="button" class="next_button" onclick="SurveyUtils.startSurvey(this)">Próximo</button>
                </div>
            </div>
        <div id="questionnaire-car" class="card hide">
            <div class="form">
                {*LEFT-SIDE*}
                <div class="left-side">
                    <form id="survey-{$QUESTONNAIRE['record_id']}">
                        <input type="hidden" name="record" value="{$QUESTONNAIRE['record_id']}">
                        <input type="hidden" name="module" value="store">
                        <input type="hidden" name="action" value="saveSurvey">
                        <input type="hidden" name="surveytoken" value="{$TOKEN}">
                        <input type="hidden" name="businessStage" value="{$STAGE}">
                        <input type="hidden" name="businessType" value="{$TYPE}">
                        <input type="hidden" name="Ajax" value="true">
                        <div class="signup-form s_form">
                            <div class="logo">
                                <img src="data:image/png;base64,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"
                                     alt="Platzilla">
                            </div>
                            {if $STEPS neq NULL}
                                <!-- {$STEPS}  -->
                                <ul class="steps">
                                    {*section  name=step start=1 loop=($STEPS + 1) step=1*}
                                    {foreach $ASKING_FOR as $key => $question}
                                        <li id="step-{$question->getId()}" class="{if $key eq 0}li-active{/if}"></li>
                                    {/foreach}
                                    {*/section*}
                                    <li class="" id="step-{$STEPS}"></li>
                                </ul>
                            {/if}
                            {if $ASKING_FOR neq NULL}
                                {foreach $ASKING_FOR as $key => $question}
                                    <div class="main {if $key eq 0}active{/if}" id="main-{$question->getId()}" relate-step="">
                                        <div class="top-div asking-for-{$question->getId()}">
                                            <h4><strong>{$question->getQuestion()}</strong></h4>
                                        </div>
                                        <p class="asking-for-{$question->getId()}" style="font-style: italic;">{$question->getDescription()}</p>
                                        <div class="survey_scroll">
                                            {$question->getHtmlResponse()}
                                        </div>
                                        {if $key eq 0}
                                            <div class="buttons" style="text-align:center">
                                                <button type="button" class="next_button"
                                                        onclick="SurveyUtils.nextStep(this, '{($key + 1)}', '{$question->getId()}')">Próximo
                                                </button><br>
                                            </div>

                                        {else}
                                            <div class="buttons">
                                                <button type="button" class="previous_button"
                                                        onclick="SurveyUtils.prevStep(this, '{($key - 1)}', '{$question->getId()}')">Anterior
                                                </button>
                                                <button type="button" class="next_button"
                                                        onclick="SurveyUtils.nextStep(this, '{($key + 1)}', '{$question->getId()}')">Próximo
                                                </button>
                                            </div>
                                        {/if}
                                    </div>
                                    {assign var="lastId" value=$question->getId()}
                                {/foreach}
                            {/if}
                            <div class="main" id="main-{($STEPS + 1)}">
                                <div class="top-div">
                                    <p><h4>Datos personales</h4></p>
                                </div>
                                <div class="input-text">
                                    <input class="{*required*}" type="text" name="username" id="user_name"
                                           placeholder="Nombre completo (Opcional)">
                                </div>
                                <div class="input-text">
                                    <input type="text" name="email" id="platzilla_email_survey"   placeholder="Dirección de correo (Opcional)" class="{*required*}">
                                </div>
                                <div class="input-text">
                                    <input type="text" name="phone" placeholder="Número telefónico (Opcional)" class="{*required*}">
                                </div>
                                <div class="input-text">
                                    <input type="text" name="referen" placeholder="Referencia (Opcional)">
                                </div>
                                <div class="buttons final_button">
                                    <button type="button" class="previous_button"
                                            onclick="SurveyUtils.prevStep(this, '{$key}', '{$lastId}')">Anterior
                                    </button>
                                    <button type="button" class="submit_button"
                                            onclick="SurveyUtils.sendSurvey(this, '{($STEPS + 2)}', '{$QUESTONNAIRE['record_id']}')">
                                        Enviar
                                    </button>
                                </div>
                            </div>
                            <div class="main" id="main-{($STEPS + 2)}">
                                <div class="top-div">
                                    <h2></h2>
                                    <p></p>
                                </div>
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                                </svg>
                                <div class="check_box">
                                    <p>Felicitaciones Sr./Sra.&nbsp;<span id="shown_name"></span> ha completado el
                                        cuestionario&nbsp;
                                        satisfactoriamente.</p>
                                </div>
                            </div>
                        </div>
                        <div class="signin-form s_form d-none">
                            <div class="main_signin active">
                                <div class="top-div"><img src="https://imgur.com/R9PWQyL.png">
                                    <h2>Welcome Back</h2>
                                    <p>Log in to continue</p>
                                </div>
                                <div class="sign_in">
                                    <h3>Sign In</h3>
                                </div>
                                <div class="input-text">
                                    <input type="text" placeholder="Username" id="user_signin_name" require>
                                </div>
                                <div class="input-text">
                                    <input type="text" placeholder="E-mail" require>
                                </div>
                                <div class="input-text"><input type="password" placeholder="Password" require></div>
                                <div class="buttons sign_button">
                                    <button class="signin_submit_button">Submit</button>
                                </div>
                            </div>
                            <div class="main_signin">
                                <div class="top-div"><img src="https://imgur.com/R9PWQyL.png">
                                    <h2>Bienvenido de nuevo</h2>
                                    <p>Inicia sesión para continuar</p>
                                </div>
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                                </svg>
                                <div class="check_box">
                                    <p>Felicitaciones Sr./Sra. <span id="shown_signin_name"></span> ya se ha conectado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                {*/LEFT-SIDE*}
                {*RIGHT-SIDE*}
                <div class="right-side row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="header-right-side" style="margin-bottom: 0!important;">
                            <h4 class="account"><strong>{$QUESTONNAIRE['name']}</strong></h4>
                        </div>
                    </div>
                    <div id="survey-save-response" class="col-lg-11 col-md-11 col-xs-11" style="margin-left: 14px">
                        <div id="survey-save-response-loading" class="row hide">
                            <div class="col-md-12" style="margin-top: 50px">
                                <img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;width: 90%; height: 90%" class="{*img-responsive*} center-block" />
                                <span class="help-block" style="text-align: center;">Estamos obtniendo los resultados de su encuenta..</span>
                            </div>
                            <div class="col-md-12">
                                <div style="min-height: 550px">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                    {if $ASKING_FOR neq NULL}
                        {foreach $ASKING_FOR as $key => $question}
                            {if $question->getQuestionGroupId () neq NULL}
                                <!-- {$question->getQuestionGroupId ()} -->
                                <div  id="survey-feedback-g-{$question->getId()}" class="col-lg-12 col-md-12 col-sm-12 col-xs-12  help-tab-video {if $key neq 0} hide{/if}">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                        <p class="text-left">
                                        <span style="font-weight: bold">Grupo:</span>&nbsp;{$question->getQuestionGroupId ()}
                                        </p>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                        {if $question->getquestionStageId () neq NULL}
                                            <p class="text-left">
                                            <span style="font-weight: bold">Tema:</span>&nbsp;{$question->getquestionStageId ()}
                                            </p>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                            {if $question->getUrlVideo() neq NULL}
                                <div id="survey-feedback-v-{$question->getId()}"
                                     class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center help-tab-video {if $key neq 0} hide{/if}">
                                    {assign var="videoType" value="."|explode:$question->getUrlVideo()}
                                    {if in_array('vimeo', $videoType )}
                                        <div  class="center-block"  id="video-">
                                            <iframe src="{$question->getUrlVideo()}" frameborder="0" style="text-align:center"
                                                    webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                        </div>
                                    {else}
                                        <div class="center-block" id="video-" >
                                            <iframe id="video" class="youtube-video"
                                                    src="{$question->getUrlVideo()}" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen="allowfullscreen">
                                            </iframe>
                                        </div>
                                    {/if}
                                </div>
                            {/if}
                            <div id="survey-feedback-{$question->getId()}"  class="col-lg-12 col-md-12 col-sm-12 col-xs-12 help-tab-text{if $key neq 0} hide{/if}">
                                <div class="survey_scroll right_text">
                                    <p style=" margin-bottom: 0.015em!important;"><strong>Ayuda:</strong>&nbsp;</p>
                                    <span class="help-block">{$question->getHelp()}</span>
                                </div>
                            </div>
                        {/foreach}

                    {/if}
                </div>
                {*/RIGHT-SIDE*}
            </div>
        </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap.js"></script>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="modules/store/survey-utls.js"></script>
</div>