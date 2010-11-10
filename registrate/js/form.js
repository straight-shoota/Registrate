jQuery(document).ready(function($) {
	var op = $('#inactive-fields').length == 0 ? 'edit' : 'create';
	$('.field-sortables li').css('cursor', '-moz-grab').mousedown(function(e){
		$(this).css('cursor', '-moz-grabbing');
	}).mouseup(function(e){
		$(this).css('cursor', '-moz-grab');
	});
	$('.field-sortables').sortable({
		connectWith: op == 'edit' ? false : '.field-sortables',
		placeholder: 'sortable-placeholder',
		axis: op == 'edit' ? 'y' : false,
		containment: op == 'edit' ? '#active-fields' : false,
		update: function(event, ui){
			var status = ui.item.parent().attr('id') == 'active-fields';
			//alert(ui.item.parent().attr('id') + ": " + status);
			ui.item.find('input[type=checkbox]').attr('checked', status ? 'checked' : null);
			if(status){
				ui.item.addClass('checked');
			}else{
				ui.item.removeClass('checked');
			}
		},
		start: function(event, ui){
			//ui.item.animate({width: "20em"});
			if(op == 'create') {
				ui.item.css('width', "20em");
				var e = ui.item.parent().attr('id') == 'inactive-fields' ? 'active-fields' : 'inactive-fields';
				$('#' + e).addClass('zone-highlight');
			}
		},
		stop: function(event, ui){
			if(op == 'create') {
				ui.item.css('width', "auto");
				$('.field-sortables').removeClass('zone-highlight');
			}
		}
	});
	$('.field-settings-toggle a').toggle(
		function(){
			$(this.hash).slideDown();
		},function(){
			$(this.hash).slideUp();
		}
	);
	

	$('a.preview-button').click(function(){
		$($(this).attr('href') + '-preview').html($('textarea' + $(this).attr('href')).val()).fadeIn("fast");
		return false;
	});
	var registrate_event_form_date = function(){
		var date = $(this).val();
		if(date.match(/(\d{4})-(\d{2})-(\d{2})/)) {
			date = new Date(RegExp.$1, RegExp.$2 - 1, RegExp.$3);
		}else{
			alert("invalud");
			return;
		}
		var days = Math.floor((date.getTime() - (new Date()).getTime()) / 86400000);
		var time = 'in {d} days';
		if(days < 0){
			days = days * -1;
			time = '{d} days ago';
		}
		if (days <= 1) {
			days = 1;
		}
		$('#date-' + $(this).attr('id') + '-preview').html(time.replace(/\{d\}/, days));
	};
	$('input.datefield').each(registrate_event_form_date).change(registrate_event_form_date);
	function registrate_date_format(date) {
		var tage = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
		var monate = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
		return tage[date.getDay()] + ", " + date.getDate() + ". " + monate[date.getMonth()] + " " + date.getFullYear();
	}
	
	$('select#form').change(function(){
		$('#form-edit').attr('href', $('#form-edit').attr('href').replace(/&form=.+(&|$)/, '&form=' + $(this).val() + '&'));
	});
});