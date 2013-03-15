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
			$('.alert').click(function() { $(this).stop().hide(); });

			app.navBar.init();
		},

		navBar: {
			anchor:    null,
			scrollTop: null,
			direction: null,

			init: function() {
				$(document).bind('scroll', app.navBar.scroll);
			},

			scroll: function() {
				direction = $(document).scrollTop() < app.navBar.scrollTop ? 'up' : 'down';

				if ( direction !== app.navBar.direction ) {
					app.navBar.anchor = $(document).scrollTop() - ( direction === 'up' ? $('.navbar').outerHeight() : 0 );

					app.navBar.direction = direction;
				}

				$('.navbar').css({ top: Math.min(0, Math.max(direction === 'up' ? parseInt($('.navbar').css('top')) : - $('.navbar').outerHeight(), app.navBar.anchor - $(document).scrollTop())) });

				app.navBar.scrollTop = $(document).scrollTop();
			},

			pin: function(instant) {
				app.navBar.direction = 'up';
				app.navBar.anchor    = $(document).scrollTop();
				app.navBar.scrollTop = $(document).scrollTop();

				$(document).unbind('scroll', app.navBar.scroll);

				$('.navbar').animate({ top: 0 }, instant ? 0 : 300);
			}
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
				$('#items').on('change', 'input.keep-unread', function(e) {
					app.items.markAsRead($(this).data('item-id'), $(this).is(':checked') ? 0 : 1);
				});

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

					$(this).blur();

					app.items.vote($(this).data('item-id'), $(this).hasClass('voted') ? 0 : $(this).data('vote'));
				});

				// Register votes
				$('#items').on('click', '.subscribe, .unsubscribe', function(e) {
					e.preventDefault();

					$(this).blur();

					var action = $(this).hasClass('subscribe') ? 'subscribe' : 'unsubscribe';

					if ( action === 'subscribe' ) {
						$(this)
							.removeClass('subscribe')
							.addClass('unsubscribe')
							.html('<i class="icon-minus-sign"></i> Unsubscribe')
							;
					} else {
						$(this)
							.removeClass('unsubscribe')
							.addClass('subscribe')
							.html('<i class="icon-plus-sign"></i> Subscribe')
							;
					}

					app.items.subscribe($(this).data('feed-id'), action);
				});

				/*
				$.ajax({
					url: app.rootPath + app.view + '/items',
					context: $('#items')
				}).done(function(data) {
					$(this).removeClass('loading').append(data);

					$('article.inactive').css({ opacity: .3 });

					app.items.highlightActive(true);
				});
				*/

				$('article.inactive').css({ opacity: .3 });

				app.items.highlightActive(true);
			},

			scrollTo: function(el, instant) {
				if ( !el || !el.length ) {
					return;
				}

				app.navBar.pin(instant);

				$('html')
					.animate({ scrollTop: el.offset().top - parseInt($('body').css('padding-top')) }, instant ? 0 : 300, function() {
						app.items.highlightActive(instant);

						app.navBar.init();
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

								app.items.markAsRead(app.items.activeItemId, app.items.activeItem.find('.keep-unread').is(':checked') ? 0 : 1);
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
					buttonUp     = $('article button.item-vote[data-item-id=' + itemId + '][data-vote=1]'),
					buttonDown   = $('article button.item-vote[data-item-id=' + itemId + '][data-vote=-1]'),
					buttonActive = vote == 1 ? buttonUp : ( vote == -1 ? buttonDown : null )
					;

				buttonUp  .removeClass('btn-inverse voted').find('i').removeClass('icon-white');
				buttonDown.removeClass('btn-inverse voted').find('i').removeClass('icon-white');

				if ( buttonActive ) {
					buttonActive.addClass('btn-inverse voted').find('i').addClass('icon-white');
				}

				$.ajax({
					url: app.rootPath + app.view + '/vote',
					method: 'post',
					data: { item_id: itemId, vote: vote, sessionId: app.sessionId }
				});
			},

			markAsRead: function(itemId, read) {
				$.ajax({
					url: app.rootPath + app.view + '/read',
					method: 'post',
					data: { item_id: itemId, read: read, sessionId: app.sessionId }
				});
			},

			subscribe: function(feedId, action) {
				$.ajax({
					url: app.rootPath + app.view + '/subscribe',
					method: 'post',
					data: { feed_id: feedId, action: action, sessionId: app.sessionId }
				});
			}
		},

		subscriptions: {
			init: function() {
				$('#subscriptions .subscription-remove').click(function() {
					if ( confirm('Are you sure you wish to unsubscribe from ' + $(this).data('feed-name') + '?') ) {
						$(this).closest('li').fadeOut();

						$.ajax({
							url: app.rootPath + 'subscriptions/unscubscribe',
							method: 'post',
							data: { id: $(this).data('feed-id'), sessionId: app.sessionId }
						});
					}
				});
			}
		}
	}

	$(function() { app.init(); });

	return app;
})(jQuery);
