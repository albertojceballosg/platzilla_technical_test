{strip}
<div class="row" style="width:100%">
	<div class="col-lg-8" style="margin: 0 auto;float:none;">
		<div class="main-box" style="height:256px;border-top:1px solid #e7ebee;border-left:1px solid #e7ebee;padding:16px;">
			<img src="themes/images/platzi.png" style="width:224px;height:auto;float:left">
			<div style="width:440px;float:left;padding:16px;">
			<h2 style="font-size:32px;font-weight:800;color:#3498db">¡Hola!</h2>
			<br/>
			<br/>
			<p style="font-size:20px;">
				{if $ERROR_EMAIL neq ''}
					{$ERROR_EMAIL}

				{elseif $ERROR_EMAIL eq ''}
				Revisa tu correo, donde encontrarás la información de tu cuenta, gracias!
				{/if}
			</p>
			</div>
		</div>
	</div>
</div>
{/strip}
