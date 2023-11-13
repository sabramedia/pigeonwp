;(function ($, window, document, undefined )
{
	$(document).ready(function(){
		// onload preset the clone dom to work with
		var $pigeonContentValues = $('div.pigeon-content-value-option'),
			contentTemplate = $pigeonContentValues.first();

		$pigeonContentValues.find('button.remove').click(function(e){
			e.preventDefault();
			$(this).closest('div.pigeon-content-value-option').remove();
		});

		$('div.pigeon-add-content-value button').click(function(e){
			e.preventDefault();
			$(this).closest('div').before(contentTemplate.clone(true).find('input').val('').end());
		});

		$('input.pigeon-value-meter').change(function(){
			if($(this).val() == 2 ){ // 2 == disabled
				$(this).closest('tr').next('tr').css({'visibility':'hidden','display':'none'});
			}else{
				$(this).closest('tr').next('tr').css({'visibility':'visible','display':''});
			}
		});

		if( $('#value_meter_disabled').is(':checked') ){
			$('#value_meter_disabled').trigger('change');
		}
	});
})(jQuery, window, document);