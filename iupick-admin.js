jQuery(function(){


	jQuery('#create_iupick_shipping').click(function(e){

		e.preventDefault();


		if( jQuery('#iupick_shipping_vendor').val() === '' 
			|| jQuery('#iupick_tracking_number').val() === ''
			|| jQuery('#iupick_length').val() === ''
			|| jQuery('#iupick_width').val() === ''
			|| jQuery('#iupick_height').val() === ''
			|| jQuery('#iupick_weight').val() === '' ){

			alert("Debes registrar las medidas, la compañia y el numero de rastreo.");
			return false;
		}

		if( !confirm('¿Estas seguro que deseas registrar el envio?') ){
			return false;

		}


		var data = {
			'action': 'iupick_add_tracking',
			'order_id': jQuery('#iupick_order_id').val(),
			'nonce': jQuery('#iupick_field_nonce').val(),
			'shipping_vendor': jQuery('#iupick_shipping_vendor').val(), 
			'tracking_number': jQuery('#iupick_tracking_number').val(),
			'length': jQuery('#iupick_length').val(),
			'width': jQuery('#iupick_width').val(),
			'height': jQuery('#iupick_height').val(),
			'weight': jQuery('#iupick_weight').val()

		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {

			if( response.status == 'ok' ){

				jQuery( '#iupick-packages' ).html( response.html );
				alert('Se registro correctamente el envio.');

				jQuery('#iupick_shipping_vendor').val('');
				jQuery('#iupick_tracking_number').val('');

				jQuery('#iupick_length').val(''),
				jQuery('#iupick_width').val(''),
				jQuery('#iupick_height').val(''),
				jQuery('#iupick_weight').val('')

			}else{

				//jQuery( '#iupick-packages' ).html( response.html );

				alert('Ocurrio un error al registrar el envio.');
			}

		}, 'json');

	});

});