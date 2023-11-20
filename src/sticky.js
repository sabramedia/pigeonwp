(function ($, window, document, undefined ){
	$(document).ready(function(){
		$('body').append('<nav class=\"pigeon-widget-status pigeon-widget-status-sticky\"><div class=\"pigeon-widget-status-wrap wrap\"></div></nav>');
		Pigeon.widget.status();
	});
})(jQuery, window, document);
