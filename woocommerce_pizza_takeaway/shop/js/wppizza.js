jQuery(document).ready(function($){
	calcola_prezzo();
	$('#label_ingredienti_extra, #label_ingredienti_base').on('click', function(){
		var id = $(this).attr('id');
		if(id == 'label_ingredienti_extra' && $('#container_iextra').is(':hidden')){
			$('#container_ibase').hide();
			$('#container_iextra').show();
			$(this).addClass('label_selected');
			$('#label_ingredienti_base').removeClass('label_selected');
		}else if(id == 'label_ingredienti_base' && $('#container_ibase').is(':hidden')){
			$('#container_iextra').hide();
			$('#container_ibase').show();
			$(this).addClass('label_selected');
			$('#label_ingredienti_extra').removeClass('label_selected');
		}
	});
	
	$('input[id^="iextra_"]').on('click', function(){
		var id = parseInt($(this).attr('id').split('_')[1]);
		var prezzo = $('#extraprice_'+id).val();
		var price = $('.entry-summary').find('span[class="amount"]').html().replace(/\u20ac/g, '');
		
		if($(this).is(':checked')){
			var new_price = '&euro;'+(parseFloat(price)+parseFloat(prezzo)).toFixed(2);
			var operation = 'add';
		} else {
			var new_price = '&euro;'+(parseFloat(price)-parseFloat(prezzo)).toFixed(2);
			var operation = 'remove';
		}
		$('.entry-summary').find('span[class="amount"]').html(new_price);
		
		var wppizza_pid = $('input[name="wppizza_pid"]').val();
	
		put_ingrediente(wppizza_pid, id, new_price, prezzo, $.trim($('label[for="iextra_'+id+'"]').html()), operation);
	});
	
	$('input[id^="ibase_"]').on('click', function(){
		var ingr = $(this).attr('id').split('_')[1];
		
		if($(this).is(':checked')){
			var operation = 'add';
		} else {
			var operation = 'remove';
		}
				
		var wppizza_pid = $('input[name="wppizza_pid"]').val();
	
		remove_ingrediente_base(wppizza_pid, ingr, operation);
	});
	
	function put_ingrediente(id_prodotto, id_attributo, nuovo_prezzo, prezzo_ingrediente, desc_ingrediente, operation){
		var post_data = {
				'id_prodotto' : id_prodotto,
				'id_attributo' : id_attributo,
				'nuovo_prezzo' : nuovo_prezzo,
				'prezzo_ingrediente' : prezzo_ingrediente,
				'desc_ingrediente' : desc_ingrediente,
				'wppizza_operation' : 'wppizza_ingrediente_extra_'+operation
		};
		
		$.ajax({
			url : window.location.pathname,
			data : post_data,
			type : 'POST',
			dataType : "text",
			success : function(res){
				if(res != 'ok'){
					alert('Error!');
				}
			}
		});
	}

	function remove_ingrediente_base(id_prodotto, ingrediente, operation){
		var post_data = {
				'id_prodotto' : id_prodotto,
				'ingrediente' : ingrediente,
				'wppizza_operation' : 'wppizza_ingrediente_base_'+operation
		};
		
		$.ajax({
			url : window.location.pathname,
			data : post_data,
			type : 'POST',
			dataType : "text",
			success : function(res){
				if(res != 'ok'){
					alert('Error!');
				}
			}
		});
	}
	
	function calcola_prezzo(){
		var price = $('.entry-summary').find('span[class="amount"]').html().replace(/\u20ac/g, '');
		var new_price = price;
		$('input[id^="iextra_"]').each(function(){
			if($(this).is(':checked')){
				var id = parseInt($(this).attr('id').split('_')[1]);
				var prezzo = $('#extraprice_'+id).val();
				new_price = (parseFloat(new_price)+parseFloat(prezzo)).toFixed(2);
			}
		});
		$('.entry-summary').find('span[class="amount"]').html('&euro;'+new_price);
	}
	
	
});