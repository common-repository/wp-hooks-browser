jQuery(document).ready(function($) {
	$('#hb_where_to_look').on('change',function() {
		var where = $(this).val();
		if(where == 'both') {
			$('#hb_which_theme, #hb_which_plugin').closest('.hb-field-wrap').hide();
		} else if(where == 'plugins') {
			$('#hb_which_theme').closest('.hb-field-wrap').hide();
			$('#hb_which_plugin').closest('.hb-field-wrap').show();
		} else {
			$('#hb_which_theme').closest('.hb-field-wrap').show();
			$('#hb_which_plugin').closest('.hb-field-wrap').hide();
		
		}
	});
	$('#hb_where_to_look').trigger('change');

	$('#hb_actions_or_filters').on('change',function() {
		var look = $(this).val();
		if(look == 'string') {
			$('.hb-string-val').closest('.hb-field-wrap').fadeIn();
		} else {
			$('.hb-string-val').closest('.hb-field-wrap').fadeOut();
		}
	});
	$('#hb_actions_or_filters').trigger('change');

});
