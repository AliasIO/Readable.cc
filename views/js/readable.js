var readable = (function($) {
	var app = {
		rootPath: '',

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
		},

		personal: {
			init: function() {
				$.ajax({
					url: app.rootPath + 'personal/ajax',
					context: $('#articles')
				}).done(function(data) {
					$(this).removeClass('loading').append(data);
				});
			}
		}
	}

	$(function() { app.init(); });

	return app;
})(jQuery);
