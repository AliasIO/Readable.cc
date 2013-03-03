var readable = (function($) {
	var app = {
		log: function(message) {
			if ( typeof window.console !== 'undefined' && typeof console.log !== 'undefined' ) {
				console.log(message);

				return true;
			}
		},

		init: function() {
			$('.alert .close').click(function() {
				$(this).parent().hide();
			});
		}
	}

	$(function() { app.init(); });

	return app;
})(jQuery);
