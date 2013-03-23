var readable = (function($) {
	var app = {
		duration: 300,
		excludes: [],
		view: '',
		args: '',
		sessionId: '',
		signedIn: false,

		log: function(message) {
			if ( typeof window.console !== 'undefined' && typeof console.log !== 'undefined' ) {
				console.log(message);

				return true;
			}
		},

		init: function() {
			// Hide alerts on click
			$(document).on('click', '.alert', function() {
				if ( $(this).hasClass('alert-sticky') ) {
					return;
				}

				if ( $(this).hasClass('alert-float') ) {
					$(this).stop().fadeOut(app.duration);
				} else {
					$(this).stop().hide();
				}
			});

			$(window).resize(function() {
				$('.alert-float').outerWidth($('#contents').width());
			}).resize();

			app.navBar.init();

			return app;
		},

		// Display alert
		notice: function(message, instant) {
			$('.alert').hide();

			$('<div class="alert alert-float">' + message + '</div>')
				.stop()
				.hide()
				.outerWidth($('#contents').width())
				.appendTo('#contents')
				.fadeIn(instant ? 0 : app.duration)
				;

			return app;
		},

		notSignedIn: function() {
			app.notice($('.modal-signin').html());

			return app;
		},

		navBar: {
			anchor:    null,
			scrollTop: null,
			direction: null,

			init: function() {
				$(document).bind('scroll', app.navBar.scroll);
				$(document).bind('scroll', app.items.infiniteScroll);

				return app.navBar;
			},

			scroll: function() {
				direction = $(document).scrollTop() < app.navBar.scrollTop ? 'up' : 'down';

				if ( direction !== app.navBar.direction ) {
					app.navBar.anchor = $(document).scrollTop() - ( direction === 'up' ? $('.navbar').outerHeight() : 0 );

					app.navBar.direction = direction;
				}

				$('.navbar').css({ top: Math.min(0, Math.max(direction === 'up' ? parseInt($('.navbar').css('top')) : - $('.navbar').outerHeight(), app.navBar.anchor - $(document).scrollTop())) });

				app.navBar.scrollTop = $(document).scrollTop();

				return app.navBar;
			},

			pin: function(instant) {
				app.navBar.direction = 'up';

				$(document).unbind('scroll', app.navBar.scroll);

				$('.navbar').stop().animate({ top: 0 }, instant ? 0 : app.duration);

				return app.navBar;
			}
		},

		items: {
			activeItemId: null,
			activeItem: null,
			pageTop: 0,
			previousItem: null,
			nextItem: null,
			page: 1,
			lastRequestedPage: 0,

			init: function() {
				var
					scrolled = false
					;

				Mousetrap.bind('j',    function() { app.items.scrollTo(app.items.nextItem    , true); });
				Mousetrap.bind('k',    function() { app.items.scrollTo(app.items.previousItem, true); });
				Mousetrap.bind('home', function() { app.navBar.pin(true).init(); });

				Mousetrap.bind('o', function() {
					if ( app.items.activeItem ) {
						app.items.activeItem.click();
					}
				});

				$(document).ajaxError(function(e, xhr) {
					if ( xhr.status === 403 ) {
						if ( !app.signedIn ) {
							app.notSignedIn();
						} else {
							if ( confirm('Your session has expired. Sign back in?') ) {
								location = '/signin';
							}
						}
					}
				});

				app.items.pageTop = $('#contents').position().top;

				app.items.nextItem = $('#items article').first();

				// Add space to allow the last item to be scrolled to the top of the page
				$(window).resize(function() {
					$('body').css({ paddingBottom: $(window).height() - 200 });

					app.items.pageTop = ( $(window).height() - app.items.pageTop ) / 3;

					$('#items-read-line').css({ top: app.items.pageTop });
				}).resize();

				// Highlight visible item on scroll
				$(document).scroll(function(e) {
					scrolled = true;
				});

				setInterval(function() {
					if ( scrolled ) {
						scrolled = false;

						app.items.findActive();
					}
				}, 200);

				// Expand collapsed item when clicked
				$('#items').on('click', 'article.collapsed', function(e) {
					e.preventDefault();

					app.items.expand($(this));
				});

				// Scroll to inactive item when clicked
				$('#items').on('click', 'article.inactive', function(e) {
					e.preventDefault();

					app.items.scrollTo($(this));
				});

				// Register votes
				$('#items').on('click', 'article.active .item-vote', function(e) {
					e.preventDefault();

					$(this).blur();

					app.items.vote($(this).data('item-id'), $(this).hasClass('voted') ? 0 : $(this).data('vote'));
				});

				// Register votes
				$('#items').on('click', 'article.active .subscription', function(e) {
					e.preventDefault();

					$(this).blur();

					var
						feedId = $(this).data('feed-id'),
						action = $(this).hasClass('subscribe') ? 'subscribe' : 'unsubscribe'
						;

					app.items.subscribe(feedId, action);
				});

				$('#items').on('click', 'article.active .item-save', function(e) {
					e.preventDefault();

					$(this).blur();

					app.items.save($(this).data('item-id'), $(this).hasClass('saved') ? 0 : 1);
				});

				app.items.itemsAdded();

				return app.items;
			},

			itemsAdded: function() {
				var i = 0;

				$($('#items article').get().reverse()).each(function() {
					if ( $.inArray($(this).data('item-id'), app.excludes) === -1 ) {
						app.excludes.push($(this).data('item-id'));
					}

					// Keep only the last 50 articles in the DOM
					if ( i ++ < 50 ) {
						return true;
					}

					var placeHolder = $('<div>');

					placeHolder.addClass('placeholder').height($(this).outerHeight(true) - 1);

					$(this).replaceWith(placeHolder);
				});

				app.items.activeItemId = null;

				app.items.findActive(true);

				app.items.page ++;

				return app.items;
			},

			expand: function(el, instant) {
				$('article:not([data-item-id=' + el.data('item-id') + '])')
					.stop()
					.animate({ opacity: .3 }, instant ? 0 : app.duration)
					;

				el
					.removeClass('collapsed')
					.addClass('expanded')
					.animate({ opacity: 1 }, instant ? 0 : app.duration)
					.find('.item-wrap')
					.stop()
					.slideDown(instant ? 0 : app.duration)
					;

				return app.items;
			},

			scrollTo: function(el, instant) {
				if ( !el || !el.length ) {
					return;
				}

				app.navBar.pin(instant);

				$('html,body')
					.animate({ scrollTop: el.offset().top - app.items.pageTop }, instant ? 0 : app.duration, function() {
						app.items.findActive(instant);

						app.navBar.init();
					});

				return app.items;
			},

			findActive: function(instant) {
				var
					offset = $(document).scrollTop() - $('#contents').position().top
					;

				$($('#items article').get().reverse()).each(function() {
					var
						top    = $(this).position().top - offset,
						bottom = top + $(this).outerHeight(true)
						;

					if ( top <= app.items.pageTop + 5 && bottom >= app.items.pageTop ) {
						if ( app.items.activeItemId !== $(this).data('item-id') ) {
							if ( app.items.activeItem ) {
								app.items.activeItem
									.stop()
									.animate({ opacity: .3 }, instant ? 0 : app.duration)
									.removeClass('active')
									.addClass('inactive')
									;

								app.items.markAsRead(app.items.activeItemId);
							}

							app.items.activeItemId = $(this).data('item-id');
							app.items.activeItem   = $(this);

							$(this).removeClass('inactive').addClass('active');

							if ( $(this).hasClass('collapsed') ) {
								$('article').stop().animate({ opacity: 1 }, instant ? 0 : app.duration);
							} else {
								$(this).stop().animate({ opacity: 1 }, instant ? 0 : app.duration);

								$('article:not([data-item-id=' + app.items.activeItemId + '])').stop().animate({ opacity: .3 }, instant ? 0 : app.duration);
							}

							// Hide floating alerts
							$('.alert-float').click();
						}

						app.items.previousItem = $(this).prev();
						app.items.nextItem     = $(this).next();

						return false;
					}
				});

				return app.items;
			},

			vote: function(itemId, vote) {
				if ( !app.signedIn ) {
					app.notSignedIn();

					return;
				}

				var
					buttonUp         = $('article .item-vote[data-item-id=' + itemId + '][data-vote=1]'),
					buttonDown       = $('article .item-vote[data-item-id=' + itemId + '][data-vote=-1]'),
					buttonLastActive = $('article .item-vote[data-item-id=' + itemId + '].voted'),
					buttonActive     = vote == 1 ? buttonUp : ( vote == -1 ? buttonDown : null )
					;

				buttonUp  .removeClass('btn-inverse voted');
				buttonDown.removeClass('btn-inverse voted');

				if ( buttonActive ) {
					buttonActive.addClass('btn-inverse voted');
				}

				$.ajax({
					url: '/' + app.view + '/vote/' + app.args,
					method: 'post',
					data: { item_id: itemId, vote: vote, sessionId: app.sessionId }
				}).fail(function() {
					buttonUp  .removeClass('btn-inverse voted');
					buttonDown.removeClass('btn-inverse voted');

					if ( buttonLastActive ) {
						buttonLastActive.addClass('btn-inverse voted');
					}
				});

				return app.items;
			},

			markAsRead: function(itemId) {
				if ( app.signedIn && itemId ) {
					var el = $('article[data-item-id=' + itemId + ']');

					if ( el.hasClass('read') ) {
						return;
					}

					el.addClass('read');

					$.ajax({
						url: '/' + app.view + '/read/' + app.args,
						method: 'post',
						data: { item_id: itemId, sessionId: app.sessionId }
					});
				}

				return app.items;
			},

			save: function(itemId, save) {
				if ( !app.signedIn ) {
					app.notSignedIn();

					return;
				}

				var button = $('article .item-save[data-item-id=' + itemId + ']');

				if ( save ) {
					button.addClass('btn-inverse saved').html('<i class="entypo install"></i> Saved');
				} else {
					button.removeClass('btn-inverse saved').html('<i class="entypo install"></i> Save');
				}

				if ( app.signedIn ) {
					$.ajax({
						url: '/' + app.view + '/save/' + app.args,
						method: 'post',
						data: { item_id: itemId, save: save, sessionId: app.sessionId }
					}).fail(function() {
						if ( save ) {
							button.removeClass('btn-inverse saved').html('<i class="entypo install"></i> Save');
						} else {
							button.addClass('btn-inverse saved').html('<i class="entypo install"></i> Saved');
						}
					});
				}

				return app.items;
			},

			subscribe: function(feedId, action) {
				if ( !app.signedIn ) {
					app.notSignedIn();

					return;
				}

				el = $('article .subscription[data-feed-id=' + feedId + ']');

				if ( action === 'subscribe' ) {
					el.removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i> Unsubscribe');
				} else {
					el.removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i> Subscribe');
				}

				$.ajax({
					url: '/' + app.view + '/subscribe/' + app.args,
					method: 'post',
					data: { feed_id: feedId, action: action, sessionId: app.sessionId }
				}).fail(function(data) {
					if ( action === 'unsubscribe' ) {
						el.removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i> Unsubscribe');
					} else {
						el.removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i> Subscribe');
					}
				});

				return app.items;
			},

			infiniteScroll: function(e) {
				if ( $('#items-footer').length ) {
					$(document).unbind('scroll', app.items.infiniteScroll);

					return;
				}

				if ( app.items.page + 1 > app.items.lastRequestedPage && $(document).scrollTop() > $(document).height() - ( $(window).height() * 2 ) ) {
					$(document).unbind('scroll', app.items.infiniteScroll);

					app.items.lastRequestedPage = app.items.page + 1;

					$.ajax({
						url: '/' + app.view + '/items/' + app.args,
						data: { page: app.items.page + 1, excludes: app.excludes.join(' ') },
						context: $('#items')
					}).done(function(data) {
						if ( data ) {
							$(this).append(data);

							app.items.itemsAdded();

							$(document).bind('scroll', app.items.infiniteScroll);
						}
					});
				}

				return app.items;
			}
		},

		subscriptions: {
			init: function() {
				$('#subscriptions .unsubscribe').click(function() {
					if ( confirm('Are you sure you wish to unsubscribe from ' + $(this).data('feed-name') + '?') ) {
						$(this).closest('li').fadeOut();

						$.ajax({
							url: '/subscriptions/unsubscribe',
							method: 'post',
							data: { id: $(this).data('feed-id'), sessionId: app.sessionId }
						});
					}
				});

				return app.subscriptions;
			}
		}
	}

	$(function() { app.init(); });

	return app;
})(jQuery);
