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
			$('.alert')
				.css({ opacity: 0, marginTop: -40 })
				.animate({ opacity: 1, marginTop: 0 }, 300)
				;

			$('.alert .close').click(function() {
				$(this).parent()
					.animate({ opacity: 0, marginTop: -40 }, 200)
			});
		},

		personal: {
			init: function() {
				var cutOff = 120;

				$.ajax({
					url: app.rootPath + 'personal/ajax',
					context: $('#articles')
				}).done(function(data) {
					$(this).removeClass('loading').append(data);

					$(document).scroll();
				});

				$(document).scroll(function(e) {
					var offset = $(this).scrollTop();

					$('article').each(function() {
						var
							top    = $(this).position().top - offset,
							bottom = $(this).position().top - offset + $(this).outerHeight()
							;

						if ( top < cutOff && bottom > cutOff ) {
							$(this).stop().animate({ opacity: 1 }, 200);
						} else {
							$(this).stop().animate({ opacity: .3 }, 200);
						}
					});
				}).scroll();
			}
		}
	}

	$(function() { app.init(); });

	return app;
})(jQuery);
