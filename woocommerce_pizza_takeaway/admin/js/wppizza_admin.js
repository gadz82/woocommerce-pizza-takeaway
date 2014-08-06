
jQuery.fn.ForceNumericOnly =
function()
{
    return this.each(function()
    {
        jQuery(this).keydown(function(e)
        {
            var key = e.charCode || e.keyCode || 0;
            // allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
            // home, end, period, and numpad decimal
            return (
                key == 8 || 
                key == 9 ||
                key == 46 ||
                key == 110 ||
                key == 190 ||
                (key >= 35 && key <= 40) ||
                (key >= 48 && key <= 57) ||
                (key >= 96 && key <= 105));
        });
    });
};

jQuery(document).ready(function($){
	
	$('input').each(function(){
		if($(this).attr('type') == 'number'){
			$(this).ForceNumericOnly();
		}
	});
	
	$('#wp_pizza_nuovo_orario').datetimepicker({
		 datepicker:false,
		 format:'H:i',
		 step: 20
	});
	
	var data = new Date();
	
	var Y,m,d,H,i,ival,hval;
	Y = data.getFullYear();
	m = pad((parseInt(data.getMonth())+1));
	d = data.getDate();
	H = data.getHours();
	i = data.getMinutes();
	
	if(i > 20 && i < 40){
		ival = 40;
		hval = H;
	} else if( i > 20 && i > 40) {
		ival = '00';
		hval = (parseInt(H)+1);
	} else {
		ival = 20;
		hval = H;
	}
	
	function pad(n){return n<10 ? '0'+n : n}
	
	$('#wp_pizza_escludi_orario').datetimepicker({
		 format:'d/m/Y H:i',
		 minDate : d+'/'+m+'/'+Y,
		 minTime : H+':'+i,
		 lang : 'it',
		 step: 20,
		 value : d+'/'+m+'/'+Y+' '+hval+':'+ival
	});

	$("#col-container").on('click', "input[id^=orarioaperto_], input[id^=orariochiuso_]", function(){
		var input = $(this).attr('id').split('_');
		var orario = input[1];
		var type = input[0];

		if(type == 'orarioaperto'){
			if($(this).is(':checked')){
				$('input[id="orariochiuso_'+orario+'"]').removeAttr('checked');
			} else {
				$('input[id="orariochiuso_'+orario+'"]').attr('checked', 'checked');
			}
		}

		if(type == 'orariochiuso'){
			if($(this).is(':checked')){
				$('input[id="orarioaperto_'+orario+'"]').removeAttr('checked');
			} else {
				$('input[id="orarioaperto_'+orario+'"]').attr('checked', 'checked');
			}
		}
		
	});
		$("#button_add_orario").click(function(){
			var post_data = $('form[id="nuovo_orario"]').serialize();
			var new_orario = $('#wp_pizza_nuovo_orario').val();
		
			
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
	 			dataType : "text",
	 			success : function(json){
		 			var j = eval('('+json+')');
		 			if(j.success == 'true' && j.tpl != undefined){
			 			$('table[id="table_orari"]').remove();
			 			$('form[id="tabella_orari"]').find('div').append(j.tpl).show('slow');
		 			} else {
		 				$('#ajax-response').append(j.tpl);
		 				$('table[id="table_orari"]').fadeIn();
		 				clear_ajax_response('fail');
		 			}
		 			$('#wp_pizza_nuovo_orario').val('');
		 		}
				
			});
		
			
		});

		$("#col-container").on('click', 'input[id^="eliminaorario_"]', function(){
			var orario = $(this).attr('id').split('_')[1];
			var post_data = 'wppizza_action=wppizza_elimina_orario&orario='+orario;
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
	 			dataType : "text",
	 			success : function(json){
		 			var j = eval('('+json+')');
		 			if(j.success == 'true'){
			 			$('tr[class="row_'+orario+'"]').remove();
			 			$('#ajax-response').append(j.tpl);
			 			clear_ajax_response('success');
		 			} else {
		 				$('#ajax-response').append(j.tpl);
		 				clear_ajax_response('fail');
		 			}
		 			$('#wp_pizza_nuovo_orario').val('');
		 		}
				
			});
		});

		$("#col-container").on('click', 'input[id^="eliminagiornoescluso_"]', function(){
			var orario = $(this).attr('id').split('_')[1];
			alert(orario);
			var post_data = 'wppizza_action=wppizza_elimina_giorno_escluso&orario='+orario;
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
				dataType : "text",
				success : function(json){
					var j = eval('('+json+')');
					if(j.success == 'true'){
						$('tr[class="e_row_'+orario+'"]').remove();
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('success');
					} else {
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('fail');
					}
					$('#wp_pizza_escludi_orario').val('');
				}
			
			});
		});

		$('#wppizza_salva_orari').on('click', function(){
			var post_data = $('form[id="tabella_orari"]').serialize();
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
	 			dataType : "text",
	 			success : function(json){
		 			var j = eval('('+json+')');
		 			if(j.success == 'true' && j.tbl != undefined){
			 			$('table[id="table_orari"]').remove();
			 			$('form[id="tabella_orari"]').find('div').append(j.tbl).show('slow');
			 			$('#ajax-response').append(j.tpl);
			 			clear_ajax_response('success');
		 			} else {
		 				$('#ajax-response').append(j.tpl);
		 				$('table[id="table_orari"]').fadeIn();
		 				clear_ajax_response('fail');
		 			}
		 			$('#wp_pizza_nuovo_orario').val('');
		 		}
				
			});
		});
		
		$('#button_escludi_orario').on('click', function(){
			var post_data = $('form[id="escludi_orario"]').serialize();
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
				dataType : "text",
				success : function(json){
					var j = eval('('+json+')');
					if(j.success == 'true' && j.tbl != undefined){
						$('table[id="table_giorni_esclusi"]').remove();
						$('form[id="lista_giorni_esclusi"]').find('div').append(j.tbl).show('slow');
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('success');
					} else {
						$('#ajax-response').append(j.tpl);
						$('table[id="table_giorni_esclusi"]').fadeIn();
						clear_ajax_response('fail');
					}
					$('#wp_pizza_nuovo_orario').val('');
				}
			
			});
		});
		
		$('#wppizza_salva_dispo').on('click', function(){
			var post_data = $('form[id="gestione_pizzeria"]').serialize();
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
				dataType : "text",
				success : function(json){
					var j = eval('('+json+')');
					if(j.success == 'true'){
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('success');
					} else {
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('fail');
					}
				}
			
			});
		});
		
		$('#wppizza_salva_giorni_apertura').on('click', function(){
			var post_data = $('form[id="giorni_apertura_pizzeria"]').serialize();
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
				dataType : "text",
				success : function(json){
					var j = eval('('+json+')');
					if(j.success == 'true'){
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('success');
					} else {
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('fail');
					}
				}
			
			});
		});

		$('#wppizza_salva_importo').on('click', function(){
			var post_data = $('form[id="gestione_importo"]').serialize();
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
				dataType : "text",
				success : function(json){
					var j = eval('('+json+')');
					if(j.success == 'true'){
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('success');
					} else {
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('fail');
					}
				}
			
			});
		});

		$('#wppizza_salva_sconto').on('click', function(){
			var post_data = $('form[id="gestione_sconto"]').serialize();
			$.ajax({
				url : window.location.pathname,
				data : post_data,
				type : 'POST',
				dataType : "text",
				success : function(json){
					var j = eval('('+json+')');
					if(j.success == 'true'){
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('success');
					} else {
						$('#ajax-response').append(j.tpl);
						clear_ajax_response('fail');
					}
				}
			
			});
		});
		
		function clear_ajax_response(type){
			$('html,body').scrollTop("#ajax-response");
			if(type == 'success'){
				setTimeout(function(){$('.updated').fadeOut('slow')}, 3000);
 				setTimeout(function(){$('.updated').remove()}, 4000);
			}
			if(type == 'fail'){
				setTimeout(function(){$('.error').fadeOut('slow')}, 3000);
 				setTimeout(function(){$('.error').remove()}, 4000);
			}
		}

});
