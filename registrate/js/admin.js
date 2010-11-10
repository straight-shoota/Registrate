var Registrate = {};
Registrate.ajaxUrl = function(url){
	return ajaxurl + "?" + url.replace(/^.+\?/, "").replace(/page=/, "action=");
};
Registrate.createDialog = function(options){
	var elem = jQuery('<div class="registrate_dialog" id="registrate-dialog"><div class="ajaxLoaderImg"></div></div>');
	jQuery('#wpwrap').prepend(elem);
	return elem.dialog(options);
};
Registrate.addMessages = function(xml){
	jQuery('.messages').append(xml);
	jQuery('.messages .fade').click(function(e){
		jQuery(this).fadeOut();
	}).delay(5000).fadeOut();
};
jQuery(document).ready(function($) {
	/*$('#wpwrap').prepend('<div class="ajaxLoaderImg"></div>');
	var Registrate_showLoader = function() {
		$('#ajaxLoaderImg').show();
	};
	var Registrate_hideLoader = function() {
		$('#ajaxLoaderImg').hide();
	};*/
	
	
	/// ##LOG##
	$('.log-data-more').hide().parent().prepend('<a href="#" class="log-data-toggle">show details</a>');
	
	$('a.log-data-toggle').toggle(function(e){
		e.preventDefault();
		$(this).html('hide details')
		$(this).siblings('.log-data-more').show();
		$(this).siblings('.log-data-less').hide();
	}, function(e){
		e.preventDefault();
		$(this).html('show details');
		$(this).siblings('.log-data-more').hide();
		$(this).siblings('.log-data-less').show();
	});
});