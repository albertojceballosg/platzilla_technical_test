{extends file="base/Modal.tpl"}
{block name="modal-id"}modal-guide{/block}
{block name="modal-class" append}modal-help md-effect-8{/block}
{block name="modal-action"}{/block}
{block name="modal-title"}
	<h3 class="modal-help-title">Ayuda relacionada a {$MODULE_NAME|@getTranslatedString:$MODULE_NAME}</h3>
	<ul class="nav nav-tabs" style="padding-left: .7em;">
		{if (count ($TIPS) > 0)}
			<li class="active" style="margin-left: 0; padding-left: 0;">
				<a href="#tab-tips" data-toggle="tab">Platzilla Tips</a>
			</li>
		{/if}
		{if (count ($TUTORIAS_VIDEOS) > 0) || (count ($TUTORIAS_ARTS) > 0)}
			<li{if (count ($TIPS) == 0)} class="active" style="margin-left: 0; padding-left: 0;"{/if}>
				<a href="#tab-walkthroughs" data-toggle="tab">Tutoriales</a>
			</li>
		{/if}
		{if (count ($QUESTIONS) > 0)}
			<li{if (count ($TIPS) == 0) && (count ($TUTORIAS_VIDEOS) == 0) && (count ($TUTORIAS_ARTS) == 0)} class="active" style="margin-left: 0; padding-left: 0;"{/if}>
				<a href="#tab-faq" data-toggle="tab">Preguntas frecuentes</a>
			</li>
		{/if}
		<li id="help-tab"{if (count ($TIPS) == 0) && (count ($TUTORIAS_VIDEOS) == 0) && (count ($TUTORIAS_ARTS) == 0) && (count ($QUESTIONS) == 0)} class="active" style="margin-left: 0; padding-left: 0;"{/if}>
			<a href="#tab-support" data-toggle="tab">Contacto</a>
		</li>
	</ul>
{/block}
