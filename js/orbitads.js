var max_ad_slots_info = {"12":"1","3":"1","13":"4","14":"4","2":"2","1":"4","6":"2","4":"4","5":"5","10":"1","9":"2","8":"3"};

function changeFormatOptions() {
    if (jQuery('#'  + 'format').val() != '') {
        jQuery('#'  + 'format').children('option[value=""]').remove();
        var id_dimensioin = jQuery('#format').val();
        var img_prefix = 'txt_';
        var adtypes = jQuery('#ad_type').val();

        if (adtypes == 'text_img') {
            img_prefix = 'txt_and_img_';
        } else if (adtypes == 'text') {
            img_prefix = 'txt_';
        } else {
            img_prefix = 'img_';
        }
        
        jQuery('#trigger_preview').attr('href','http://orbitwork.orbitsoft/users/yants/openadserver/1.1.0.r_1/images/dimensions_preview/'+img_prefix+id_dimensioin+'.png');
        jQuery('#trigger_preview').attr('title',jQuery('#'  + 'format option[selected]').text());
        var preview_file;
        if (('txt_' == img_prefix) || ('txt_and_img_' == img_prefix)) {
            var preview_file = 'http://orbitwork.orbitsoft/users/yants/openadserver/1.1.0.r_1/images/slots_preview/slots_' + id_dimensioin + '.gif';
        } else {
            var preview_file = 'http://orbitwork.orbitsoft/users/yants/openadserver/1.1.0.r_1/images/slots_preview/slots_' + id_dimensioin + '_1.gif';
        }
        if (jQuery('#'  + 'slots_preview').length) {
            jQuery('#'  + 'slots_preview').show();
            var img = jQuery('#'  + 'slots_preview img')[0];
            img.src = preview_file;
        } else {
            var img = document.createElement('div');
            jQuery(img).attr('id',  'slots_preview');
            jQuery(img).attr('align', 'center');
            img.innerHTML= "<table width=\"100%\" style=\"padding-bottom: 10px;\"><tr><td align='center'><img src='" + preview_file + "'></td><td align='center'><span style=\"font-weight: bold;\" id='" + "slots_count'></span>&nbsp;slot(s) available</td></tr></table>";
            jQuery('#' + 'slots_preview_container').append(img);
        }
        if ('img_' == img_prefix) {
            var slotsCount = 1;
        } else {
            var slotsCount = max_ad_slots_info[id_dimensioin];
        }
        jQuery('#'  + 'slots_count').text(slotsCount);
    } else {

    }
}

function changeAdTypes() {
    if  (jQuery('#format').val() != null) changeFormatOptions();
}

function tooglePrice(){
    var allowed = jQuery('#allowed_prices').val();
    if (allowed == 'flatrate') {
        jQuery('#flaterate_prices').show();
        jQuery('#cpm_prices').hide();
    } else if (allowed == 'cpm') {
        jQuery('#cpm_prices').show();
        jQuery('#flaterate_prices').hide();
    } else {
        jQuery('#cpm_prices').show();
        jQuery('#flaterate_prices').show();
    }
}

/* ===== accordion for first step ===== */
(function($){
	var Flexitabs = function(options){
		this.defaults = {
			//CSS-selectors of target elements in DOM for unobtrusive attaching
			containerSelector: '.accordion',
			tabSelector: 'h3',
			contentSelector: '.content',
			//CSS classes for indicate active and inactive tabs and contents of tabs
			activeLinkClass: 'active', //also class for marking initial active tab in your HTML
			inactiveLinkClass: 'inactive'
		}
		this.initialize = function(){
			var flexitabs_object = this;
			$(this.containerSelector).each(function(){
				var storage = {};
				storage.object = this;
				//finding tabs and it's contents in DOM
				storage.tabs = $.grep($(this).find(flexitabs_object.tabSelector), function(tab){
				 	if ($(tab).parents(flexitabs_object.containerSelector)[0] == storage.object)
						return true;
				});
				storage.contents = $.grep($(this).find(flexitabs_object.contentSelector), function(tab){
					if ($(tab).parents(flexitabs_object.containerSelector)[0] == storage.object) 
						return true;
				});
				if (storage.tabs.length <= storage.contents.length) {//avoiding nonfunctional tabs
					var active = 0;
					var container = $(this);
					$(storage.tabs).each(function(i){
						//initializing storage for tabs and contents
						var tab_storage = {};
						var content_storage = {};
						tab_storage.object = content_storage.object = container;
						tab_storage.index = content_storage.index = i;
						//bind click handler to tabs
						$(this).click(function(){
							flexitabs_object.activate(this);
							return false;
						});
						//preselecting active tab from DOM
						if ($(this).hasClass(flexitabs_object.activeLinkClass)) 
							active = i;
						//storing data in jQuery data-storages, assigned to DOM-elements
						$(storage.tabs[i]).data('Flexitabs', tab_storage);
						$(storage.contents[i]).data('Flexitabs', content_storage);
					});
					storage.active = active;
					container.data('Flexitabs', storage); //storing whole tabulator data in container storage
					flexitabs_object.activate(storage.tabs[active]); //initial tab activation
				}
			});
		}
		this.activate = function(tab){
			var index = $(tab).data('Flexitabs').index;
			var storage = $(tab).data('Flexitabs').object;
			var tabs_storage = $(storage).data('Flexitabs').tabs;
			var contents_storage = $(storage).data('Flexitabs').contents;
			
			//deactivation of all tabs and contents
			$(tabs_storage)
				.removeClass(this.activeLinkClass)
				.addClass(this.inactiveLinkClass)
			
			$(contents_storage)
				.eq(index)
					.removeClass(this.inactiveLinkClass)
					.addClass(this.activeLinkClass)				
					.end()
				.not(':eq(' + index + ')')
					.removeClass(this.activeLinkClass)
					.addClass(this.inactiveLinkClass)

			//activation of current clicked tab
			$(tab)
				.removeClass(this.inactiveLinkClass)
				.addClass(this.activeLinkClass)
			
			storage.active = index
		}
		$.extend(this, this.defaults, options);
		this.initialize();
	};
	$(function(){
		new Flexitabs();
	})
})(jQuery);

/**
 * @author Fedor Pudeyan
 */

/* ===== installation progress ===== */

var orbitscriptsads_progress_timer, orbitscriptsads_helper = {
	action: 'orbitscripts_install',
	step: 1
},
orbitscriptsads_progress_allow_animation = 1

function orbitscriptsads_install_progressbar(width, callback, fade){
	jQuery(".meter > span").animate({
		width: width + '%'
	}, 500, 'swing', function(){
		callback()
		if (fade) jQuery(".meter strong").text(width + '%').fadeIn(300)
                    else jQuery(".meter strong").text(width + '%')
	})
	if (fade) jQuery(".meter strong").fadeOut(300)
}

function orbitscriptsads_progress_animate_helper() {
	if (orbitscriptsads_progress_allow_animation)
		orbitscriptsads_progress_animate()
	else
		orbitscriptsads_progress_stop()
}
function orbitscriptsads_progress_animate() {
	jQuery('.meter span span').css('background-position', '0 0').animate({
		backgroundPosition: '(50px 0)'
	}, 2000, 'linear', orbitscriptsads_progress_animate_helper)
}
function orbitscriptsads_progress_stop() {
	jQuery('.meter span span').stop()
}
function orbitscriptsads_install_end() {
	clearInterval(orbitscriptsads_progress_timer)
	orbitscriptsads_progress_allow_animation = 0
}

function orbitscriptsads_install_error(msg) {

}

function orbitscriptsads_install(){
	jQuery.post(ajaxurl, orbitscriptsads_helper, function(response){
		var response = jQuery.parseJSON(response)

		//errors handling
		if (response.status == 'error') {
			orbitscriptsads_install_error(response.description)
			orbitscriptsads_install_end()
		}
		else {
			if (response.status == 'incomplete') {
				//animating progressbar
				orbitscriptsads_install_progressbar(response.width, function(){
					jQuery("#orbitscriptsads_install_txt").html(response.description);
				}, true)
				if (typeof(response.step) != 'undefined') {
					//request next step
					orbitscriptsads_helper.step = response.step
					if (response.progress)
						orbitscriptsads_progress_timer = setInterval("orbitscriptsads_progress_updater()", 2000)
					else
						clearInterval(orbitscriptsads_progress_timer)
					orbitscriptsads_install();
				}
			}
			else {
				//finish
				orbitscriptsads_install_progressbar(response.width, function(){
					jQuery("#orbitscriptsads_install_txt").html(response.description)
					orbitscriptsads_install_end()
				}, true)
                                setTimeout("orbitscriptsads_complete('"+response.link+"')",2000)
			}
		}

	})
}
function orbitscriptsads_complete(link) {
        window.location.href = link
}
function orbitscriptsads_progress_updater(){
	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: "action=orbitscripts_install&progress=" + orbitscriptsads_helper.step,
		success: function(response){
			response = jQuery.parseJSON(response)
			if (response.stop)
				clearInterval(orbitscriptsads_progress_timer)
			else {
				jQuery("#orbitscriptsads_install_txt").html(response.description);
				if (response.width != 0) orbitscriptsads_install_progressbar(response.width, function(){}, false)
			}
		}
	});
}

