$.extend({
	prompt: function(text, value, title, callback, onOpen){
		if(!onOpen){
			onOpen = $.noop;
		}
		text = text || '';
		title = title || 'Datos';
		value = value || '';
		var html = '<div class="row-fluid clearfix">' + '<div class="col-md-12 col-xs-12 form-group clearfix">' + '<label for="">' + text + '</label>' + '<input type="text" id="input-dialog-form" class="form-control" value="' + value + '" />' + '</div>' + '</div>';
		$('<div />').attr({
			title: title
		}).html(html).dialog({
			open: onOpen,
			buttons: [{
				text: "Ok",
				"class": 'btn btn-primary',
				click: function(){
					if(callback){
						if(callback($('#input-dialog-form').val())){
							$(this).dialog('close');
						}
					}
					else{
						$(this).dialog('close');
					}
				}
			}, {
				text: "Cancelar",
				"class": 'btn btn-default',
				click: function(){
					if(callback){
						callback(false);
					}
					$(this).dialog('close');
				}
			}]
		});
	},
	alert: function(message, title, callback){
		title = title || 'Alerta';
		$('<div />').attr({
			title: title
		}).html("<input autofocus type='hidden' /><div class='mensajes-informativos'>" + message + "</div>").dialog({
			buttons: [{
				text: "Ok",
				"class": 'btn btn-primary',
				click: function(){
					$(this).dialog('close');
					if(callback){
						callback();
					}
				}
			}]
		});
	},
	confirm: function(message, title, callback){
		title = title || 'Confirmar';
		$('<div />').attr({
			title: title
		}).html("<p class='mensajes-informativos'>" + message + "</p>").dialog({
			buttons: [{
				text: "Si",
				"class": 'btn btn-primary',
				click: function(){
					$(this).dialog("close");
					if(callback){
						callback(true);
					}
				}
			}, {
				text: "No",
				"class": 'btn btn-default',
				click: function(){
					$(this).dialog("close");
					if(callback){
						callback(false);
					}
				}
			}]
		});
	},
	block: function(t){
		t || (t = "Enviando datos...");
		$.blockUI({
			message: "<i class='fa fa-spinner fa-spin'></i> " + t,
			css: {
				border: 'none',
				padding: '15px',
				backgroundColor: '#000',
				opacity: .7,
				color: '#fff',
				'font-weight': 'bold'
			},
			overlayCSS: {
				backgroundColor: '#c5d0f4',
				opacity: .3
			}
		});
	}
});