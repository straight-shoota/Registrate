jQuery(document).ready(function($) {
	function runListActions(){
		$('table.registrations .row-actions a.edit').click(function(e){
			e.preventDefault();
			name = $(this).attr('rel');
			row = $(this).closest('tr');
			var dialog = Registrate.createDialog({
				modal: true,
				width: 600,
				title: 'Editing ' + name,
				buttons: {},
					/*'Save': function(){
						$(this).dialog('close');
					},
					'Cancel': function(){
						$(this).dialog('close');
					}
				}*/
				open: function(){
					row.addClass('highlight');
				},
				close: function(){
					row.removeClass('highlight');
				}
			});
			jQuery.ajax({
				type: "get",
				url: Registrate.ajaxUrl($(this).attr("href")),
				success: function(xml) {
					if(xml == 0){
						$('.wrap').before('<div class="message error">AJAX-Error</div>');
						return;
					}
					dialog.html(xml);
					//$('form.edit-item').css('background-color', 'red');
					$('form.edit-item').submit(function(e){
						e.preventDefault();
						data = $(this).serialize();
						
						jQuery.ajax({
							type: "post",
							data: data,
							url: Registrate.ajaxUrl(location.href),
							success: function(xml) {
								if(xml == 0){
									Registrate.addMessages($('<div class="message error">AJAX-Error</div>'));
								}else{
									Registrate.addMessages(xml);
								}
								//$('#').html();
								updateListContent();
								dialog.dialog('close');
							}
						});
						
					});
				}
			});
		});
		$('table.registrations .row-actions a.delete').click(function(e){
			e.preventDefault();
			deletionUrl = $(this).attr("href");
			row = $(this).closest('tr');
			name = $(this).attr('rel');
			Registrate.createDialog({
				modal: true,
				//height: 140,
				title: 'Cancel registration?',
				resizable: false,
				buttons: {
					'Cancel registration': function(){
						$(this).dialog('close');
						jQuery.ajax({
							type: 'get',
							url: Registrate.ajaxUrl(deletionUrl),
							success: function(xml){
								Registrate.addMessages(xml);
								$(this).dialog('close');
								row.fadeOut();
							}
						});
					},
					'Close': function(){
						$(this).dialog('close');
					}
				},
				open: function(){
					row.addClass('highlight highlight-delete');
				},
				close: function(){
					row.removeClass('highlight highlight-delete');
				}
			}).html("Do you really want to cancel the registration of " + name + "?");
			
		});
		$('table.registrations .row-actions a.checkin').click(function(e){
			e.preventDefault();
			actionUrl = $(this).attr("href");
			row = $(this).closest('tr');
			name = $(this).attr('rel');
			
			jQuery.ajax({
				type: 'get',
				url: Registrate.ajaxUrl(actionUrl),
				success: function(xml){
					Registrate.addMessages(xml);
					row.fadeOut();
				}
			});
		});
	}
	runListActions();
	
	var listContentUrl = Registrate.ajaxUrl(location.href);
	function updateListContent(url, xml){
		if(url == undefined){
			url = listContentUrl;
		}
		if(xml == undefined){
			$('table.registrations').css({opacity: .4, "background-color": '#eee'});
			$.get(url, function(xml) {
				updateListContent(url, xml);
			});
			return;
		}
		listContentUrl = url;
		$('.list-content').html(xml);
		runListActions();
		$('table.registrations').css({opacity: 1, "background-color": '#FFF'});
	}

	// search as you type
	var SAST = {
		conf: {
			delay: 600,             // time to wait before executing the query (in ms)
			minCharacters: 3      // minimum nr of characters to enter before search
		},
		timer: false,
		query: null,
		init: function() {
			this.form = $('#search-box');
			//$('#search-box #q').keyup(this.handler);
		},
		handler: function(event){
			event.preventDefault();
			
			if(! SAST.timer){
				clearTimeout(SAST.timer);
				SAST.timer = false;
			}

			SAST.query = $('#search-box #q').val();
			if(SAST.query.length >= SAST.conf.minCharacters){
				SAST.timer = setTimeout(SAST.request, SAST.conf.delay);
			}
		},
		request: function(){
			if(SAST.query != $('#search-box #q').val()){
				// search is out of time
				return;
			}
			var ajaxURL = Registrate.ajaxUrl(SAST.form.serialize());
			
			$.get(ajaxURL, function(xml) {
				if(xml <= 0){
					Registrate.addMessages($('<div class="message error">AJAX-Error</div>'));
					return;
				}
				updateListContent(ajaxURL, xml);
			});
		}
	};
	SAST.init();
});