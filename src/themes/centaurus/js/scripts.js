$(function($) {
	setTimeout(function() {
		$('#content-wrapper > .row').css({
			opacity: 1
		});
	}, 200);

	$('#sidebar-nav .dropdown-toggle').on('click', function (e) {
		e.preventDefault();

		var $item = $(this).parent();

		if (!$item.hasClass('open')) {
			$item.parent().find('.open .submenu').slideUp('fast');
			$item.parent().find('.open').toggleClass('open');
		}

		$item.toggleClass('open');

		if ($item.hasClass('open')) {
			$item.children('.submenu').slideDown('fast');
		}
		else {
			$item.children('.submenu').slideUp('fast');
		}
	});

	$('body').on('mouseenter', '#page-wrapper.nav-small #sidebar-nav .dropdown-toggle', function (e) {
		var $sidebar = $(this).parents('#sidebar-nav');

		if ($( document ).width() >= 992) {
			var $item = $(this).parent();

			$item.addClass('open');
			$item.children('.submenu').slideDown('fast');
		}
	});

	$('body').on('mouseleave', '#page-wrapper.nav-small #sidebar-nav > .nav-pills > li', function (e) {
		var $sidebar = $(this).parents('#sidebar-nav');

		if ($( document ).width() >= 992) {
			var $item = $(this);

			if ($item.hasClass('open')) {
				$item.find('.open .submenu').slideUp('fast');
				$item.find('.open').removeClass('open');
				$item.children('.submenu').slideUp('fast');
			}

			$item.removeClass('open');
		}
	});

	$('#make-small-nav').click(function (e) {
		if ($('#history-bar').is(":visible")){
			if ($(window).width() < 1280){
				$('#page-wrapper').toggleClass('nav-small');
				$('#header-navbar').toggleClass('nav-small');
				$('#history-bar').hide();
				$('#content-wrapper').css("width", "auto");
			}else{
				$('#page-wrapper').toggleClass('nav-small');
				$ ('#header-navbar').toggleClass ('nav-small');
				if ($('#page-wrapper').hasClass('nav-small')){
					$('#content-wrapper').css("width", function(){ return $(window).width() - 304});
				}else{
					$('#content-wrapper').css("width", function(){ return $(window).width() - 458});
				}
			}
		}else{
			$('#page-wrapper').toggleClass('nav-small');
			$ ('#header-navbar').toggleClass ('nav-small');
		}

		if ($("#page-wrapper").hasClass("nav-small")){
			$('<ul class="submenu" style="display: none;"><li><a href="#" class="a-menu dropdown-toggle" onclick="return HelpUtils.showHelp (this);"><span class="a-menu nombremodulo-menu" style=""> ¿Necesitas Ayuda?</span></a></li></ul>').appendTo('#help-min');
		}else{
			console.log("no");
			$('#help-min ul').remove();
		}
	});

	$('.mobile-search').click(function(e) {
		e.preventDefault();

		$('.mobile-search').addClass('active');
		$('.mobile-search form input.form-control').focus();
	});

	$(document).mouseup(function (e) {
		var container = $('.mobile-search');

		if (!container.is(e.target) // if the target of the click isn't the container...
			&& container.has(e.target).length === 0) // ... nor a descendant of the container
		{
			container.removeClass('active');
		}
	});

	$('.fixed-leftmenu #col-left').nanoScroller({
		alwaysVisible: true,
		iOSNativeScrolling: false,
		preventPageScrolling: true,
		contentClass: 'col-left-nano-content'
	});

	// build all tooltips from data-attributes
	$("[data-toggle='tooltip']").each(function (index, el) {
		$(el).tooltip({
			placement: $(this).data("placement") || 'top'
		});
	});


});

$.fn.removeClassPrefix = function(prefix) {
	this.each(function(i, el) {
		var classes = el.className.split(" ").filter(function(c) {
			return c.lastIndexOf(prefix, 0) !== 0;
		});
		el.className = classes.join(" ");
	});
	return this;
};

(function($,sr){
	// debouncing function from John Hann
	// http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
	var debounce = function (func, threshold, execAsap) {
		var timeout;

		return function debounced () {
			var obj = this, args = arguments;
			function delayed () {
				if (!execAsap)
					func.apply(obj, args);
				timeout = null;
			};

			if (timeout)
				clearTimeout(timeout);
			else if (execAsap)
				func.apply(obj, args);

			timeout = setTimeout(delayed, threshold || 100);
		};
	}
	// smartresize
	jQuery.fn[sr] = function(fn){	return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'smartresize');

function writesRecentActivities(){
	var  url = 'module=Home&action=HomeAjax&file=RecentActivityAjax&ajax=true';

	jQuery('#news-feed').html("");
	jQuery('#nf-no-activity').hide();
	jQuery('#cargando').show();

	new Ajax.Request(
		'index.php',
		{ async: false,
			cache: false,
			queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody:url,
			onComplete: function(response) {
		        // Elimina caracteres basura incluidos en la respuesta del ajax
                response.responseText = response.responseText.replace(/^[\s\ufeff\xA0]+|[\s\uFEFF\xA0]+$/g, '');
				if ( response.responseText  != '' ) {
					var recentActivity_Arr;
                    recentActivity_Arr =  JSON.parse(response.responseText);
                    paintRecentActivities(recentActivity_Arr);
				}else{
					jQuery('#nf-no-activity').show();
					jQuery('#cargando').hide();
				}
			}
		}
	);
}

function paintRecentActivities(noticias){
	var elemento = "";
	jQuery('#cargando').hide();

	jQuery.each(noticias, function (index, noticia) {

		elemento = '<div class="prog-row"><div class="row"> <div class="user-thumb">'+
		'<a href="#"><img src="'+ ((noticia.imagename != "")? noticia.imagename : 'themes/centaurus/img/photo.png') + '" alt=""></a>'+
		'</div><div class="user-details"><h4><a href="#">'+noticia.last_name
		+'</a></h4><span class="text-muted3" style="font-size: .9em;">'+noticia.tipo
		+': <a href="index.php?module='+noticia.module+'&parenttab=&action=DetailView&record='
		+noticia.recordid+'"><span>'+noticia.label_entity
		+'</span></a> en <a href="'+noticia.url_module+'"><span>'+noticia.module+'</span></a></span><p>El '
		+noticia.action_date+'</p></div></div> <div class="row" style="width: 95%;text-align: center;margin-left: auto;margin-right: auto;padding: 0;margin-bottom: 0;"> <hr style="text-align: center;margin-bottom: 0;border-top: 1px solid #c6c6c6;"></div></div>';

		jQuery( '<li>'+elemento+'</li>' ).appendTo( "#news-feed" ).show('slow');
	});

jQuery( '<li><div style="height: 100px;"></div></li>' ).appendTo( "#news-feed" );
}

(function ($) {
	"use strict";
	$(document).ready(function () {
		$('#nav-history').click(function (e) {
			if ($('.right-sidebar').hasClass('open-right-bar')) {
				$('.right-sidebar').removeClass('open-right-bar');
				jQuery('#history-bar').hide();
				$('#content-wrapper').css('width', 'auto');
			}else{
				$('.right-sidebar').toggleClass('open-right-bar');
				jQuery('#history-bar').show();
				if($(window).width() > 991 && $(window).width() < 1500){
					$('#content-wrapper').css('width', function(){return $(window).width() - 304});
				}else if($(window).width() >= 1500){
					$('#content-wrapper').css('width', '79%');
				}
				writesRecentActivities();
			}
		});

		$('#refresh-activity').click(function (e) {
			writesRecentActivities();
		});


		/*==Nice Scroll ==*/
		if ($.fn.niceScroll) {
			$(".right-stat-bar").niceScroll({
				cursorcolor: "#3498DB",
				cursorborder: "0px solid #fff",
				cursorborderradius: "0px",
				cursorwidth: "3px"
			});
		}

	});


})(jQuery);