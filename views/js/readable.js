var readable = (function($) {
	var app = {
		rootPath: '',
		page: '',

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

			$('.alert').click(function() {
				$(this).animate({ opacity: 0, marginTop: -40 }, 200)
			});
		},

		items: {
			activeItemId: null,
			activeItem: null,
			previousItem: null,
			nextItem: null,

			init: function() {
				var
					scrolled = false
					;

				Mousetrap.bind('j', function() { app.items.scrollTo(app.items.nextItem,     true); });
				Mousetrap.bind('k', function() { app.items.scrollTo(app.items.previousItem, true); });

				// Add space to allow the last item to be scrolled to the top of the page
				$(window).resize(function() {
					$('body').css({ paddingBottom: $(window).height() - 200 });
				}).resize();

				// Highlight visible item on scroll
				$(document).scroll(function(e) {
					scrolled = true;
				});

				setInterval(function() {
					if ( scrolled ) {
						scrolled = false;

						app.items.highlightActive();
					}
				}, 200);

				// Expand collapsed item when clicked
				$('#items').on('click', 'article.collapsed', function(e) {
					e.preventDefault();

					var self = $(this);

					$(this).find('.item-wrap').stop().slideDown(300, function() {
						$(self).removeClass('collapsed');

						app.items.scrollTo($(self));
					});
				});

				// Scroll to inactive item when clicked
				$('#items').on('click', 'article.inactive', function(e) {
					e.preventDefault();

					app.items.scrollTo($(this));
				});

				// Register votes
				$('#items').on('click', '.item-vote', function(e) {
					e.preventDefault();

					$(this).attr('disabled', 'disabled').blur();

					app.items.vote($(this).data('item-id'), $(this).hasClass('voted') ? 0 : $(this).data('vote'));
				});

				$.ajax({
					url: app.rootPath + 'personal/items',
					context: $('#items')
				}).done(function(data) {
					$(this).removeClass('loading').append(data);

					$('article.inactive').css({ opacity: .3 });

					app.items.highlightActive(true);
				});
			},

			scrollTo: function(el, instant) {
				if ( !el.length ) {
					return;
				}

				$('html, body')
					.animate({ scrollTop: el.offset().top - parseInt($('body').css('padding-top')) }, instant ? 0 : 300, function() {
						app.items.highlightActive(instant);
					});
			},

			highlightActive: function(instant) {
				var
					cutOff = parseInt($('body').css('padding-top')) + 90,
					offset = $(document).scrollTop()
					;

				$('#items article').each(function() {
					var
						top    = $(this).position().top - offset,
						bottom = top + $(this).outerHeight()
						;

					if ( top <= cutOff && bottom >= cutOff ) {
						if ( app.items.activeItemId !== $(this).data('item-id') ) {
							if ( app.items.activeItem ) {
								app.items.activeItem
									.stop()
									.animate({ opacity: .3 }, instant ? 0 : 200)
									.removeClass('active')
									.addClass('inactive')
									;
							}

							$(this)
								.stop()
								.animate({ opacity: 1 }, instant ? 0 : 200)
								.removeClass('inactive')
								.addClass('active')
								;

							app.items.activeItemId = $(this).data('item-id');

							app.items.activeItem   = $(this);
							app.items.previousItem = $(this).prev();
							app.items.nextItem     = $(this).next();
						}

						return false;
					}
				});
			},

			vote: function(itemId, vote) {
				var
					buttonUp   = $('article button.item-vote[data-item-id=' + itemId + '][data-vote=1]'),
					buttonDown = $('article button.item-vote[data-item-id=' + itemId + '][data-vote=-1]')
					;

				$.ajax({
					url: app.rootPath + 'personal/vote',
					method: 'post',
					data: { item_id: itemId, vote: vote }
				}).done(function(data) {
					buttonUp  .removeClass('btn-inverse voted').find('i').removeClass('icon-white');
					buttonDown.removeClass('btn-inverse voted').find('i').removeClass('icon-white');

					var button = data.vote == 1 ? buttonUp : ( data.vote == -1 ? buttonDown : null );

					if ( button ) {
						button.addClass('btn-inverse voted').find('i').addClass('icon-white');
					}
				})
				.always(function(data) {
					buttonUp  .removeAttr('disabled');
					buttonDown.removeAttr('disabled');
				});
			}
		}
	}

	$(function() { app.init(); });

	return app;
})(jQuery);
