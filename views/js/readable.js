var readable = (function($) {
	var app = {
		duration: { fade: 300, scroll: 300 },
		excludes: [],
		email: '',
		controller: '',
		args: '',
		mobile: false,
		sessionId: '',
		signedIn: false,

		log: function(message) {
			if ( typeof window.console !== 'undefined' && typeof console.log !== 'undefined' ) {
				console.log(message);

				return true;
			}
		},

		trackEvent: function(category, action, label, value) {
			if ( typeof _gaq !== 'undefined' ) {
				_gaq.push([ '_trackEvent', category, action, label ? label : null, value ? value : null ]);
			}
		},

		trackPageView: function(url) {
			if ( typeof _gaq !== 'undefined' ) {
				_gaq.push([ '_trackPageview', url ]);
			}
		},

		init: function() {
			$('.contact-email').text(app.email.replace(' ', '@')).attr('href', 'mailto:' + app.email.replace(' ', '@'));

			// Hide alerts on click
			$(document).on('click', '.alert', function() {
				if ( $(this).hasClass('alert-sticky') ) {
					return;
				}

				if ( $(this).hasClass('alert-float') ) {
					$(this).stop().fadeOut(app.duration.fade);
				} else {
					$(this).stop().hide();
				}
			});

			$(window).resize(function() {
				$('.alert-float').outerWidth($('#contents').width());

				app.mobile = $(document).width() < 850;

				app.duration = app.mobile ? { fade: 0, scroll: 0 } : { fade: 300, scroll: 300 };
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
				.fadeIn(instant ? 0 : app.duration.fade)
				;

			return app;
		},

		notSignedIn: function() {
			app.notice($('.modal-signin').html());

			return app;
		},

		navBar: {
			height: 0,
			anchor: null,
			previousScrollTop: 0,

			init: function() {
				$(document).bind('scroll', app.navBar.scroll);
				$(document).bind('scroll', app.items.infiniteScroll);

				$(document).scroll();

				app.navBar.height = $('.navbar').outerHeight();

				return app.navBar;
			},

			scroll: function() {
				var
					scrollTop   = $(document).scrollTop(),
					navBarTop   = Math.min(0, Math.max(parseInt($('.navbar').css('top')) - ( scrollTop - app.navBar.previousScrollTop ), - app.navBar.height));
					;

				if ( navBarTop <= 0 && navBarTop >= - app.navBar.height ) {
					$('.navbar').css({ top: navBarTop });
				}

				app.navBar.previousScrollTop = scrollTop;

				// Read line
				if ( scrollTop > app.items.pageTop ) {
					$('#items-read-line:hidden').fadeIn(app.duration.fade);
				} else {
					$('#items-read-line:visible').fadeOut(app.duration.fade);
				}

				return app.navBar;
			},

			pin: function(instant) {
				$(document).unbind('scroll', app.navBar.scroll);

				app.navBar.previousScrollTop = $(document).height();

				$('.navbar').stop().animate({ top: 0 }, instant ? 0 : app.duration.scroll);

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

				Mousetrap.bind(['j', 's', 'space'], function() {
					var next = app.items.activeItem ? app.items.activeItem.next('article') : $('#items article').first();

					if ( !next[0] ) {
						return;
					}

					app.items.expand(  next, true);
					app.items.scrollTo(next, true);

					return false;
				});

				Mousetrap.bind(['k', 'w'], function() {
					var previous = app.items.activeItem ? app.items.activeItem.prev('article') : $('#items article').first();

					if ( !previous[0] ) {
						return;
					}

					app.items.expand(  previous, true);
					app.items.scrollTo(previous, true);

					return false;
				});

				Mousetrap.bind('home', function() { app.navBar.pin(true).init(); });

				Mousetrap.bind(['o', 'enter'], function() {
					if ( app.items.activeItem ) {
						app.items.activeItem.click();
					}

					return false;
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
				app.trackPageView(app.controller + '/page/' + app.items.page);

				var i = 0;

				$($('#items article').get().reverse()).each(function() {
					if ( $.inArray($(this).data('item-id'), app.excludes) === -1 ) {
						app.excludes.push($(this).data('item-id'));
					}

					// Keep only the last 200 articles in the DOM
					if ( i ++ < 200 ) {
						return true;
					}

					var placeHolder = $('<div>');

					placeHolder.addClass('placeholder').height($(this).outerHeight(true) - 1);

					$(this).replaceWith(placeHolder);
				});

				// Left align first image if not preceded by text
				$('article p:first-child > a:first-child > img:first-child, article p:first-child > img:first-child').each(function() {
					if ( $(this).parent().html().match(/^\s*<img /) && $(this).parent().parent().html().match(/^\s*<(p|a)/) ) {
						$(this).addClass('feature');
					}
				});

				// Don't align paragraphs with images next to feature image
				$('article img:not(.feature)').each(function() {
					$(this).closest('p').css({ clear: 'both' });
				});

				app.items.activeItemId = null;

				app.items.findActive(true);

				app.items.page ++;

				return app.items;
			},

			expand: function(el, instant) {
				$('article:not([data-item-id=' + el.data('item-id') + '])')
					.stop()
					.animate({ opacity: .3 }, instant ? 0 : app.duration.fade)
					;

				el
					.removeClass('collapsed')
					.addClass('expanded')
					.animate({ opacity: 1 }, instant ? 0 : app.duration.fade)
					.find('.item-wrap')
					.stop()
					.slideDown(instant ? 0 : app.duration.scroll)
					;

				// Firefox doesn't load hidden iframes
				el.find('iframe').each(function() {
					$(this).attr('src', $(this).attr('src'));
				});

				// Remove small images, mainly tracking pixels and smiley faces
				el.find('img').each(function() {
					if ( $(this).width() <= 50 || $(this).height() <= 50 ) {
						var parent = $(this).parent();

						$(this).remove();

						if ( parent.html() == '' ) {
							parent.remove();
						}
					}
				});

				return app.items;
			},

			scrollTo: function(el, instant) {
				if ( !el || !el.length ) {
					return;
				}

				app.navBar.pin(instant);

				$('html,body')
					.animate({ scrollTop: el.offset().top - app.items.pageTop }, instant ? 0 : app.duration.scroll, function() {
						app.items.findActive(instant);

						app.navBar.init();
					});

				return app.items;
			},

			findActive: function(instant) {
				var offset = $(document).scrollTop() - $('#contents').position().top;

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
									.animate({ opacity: .3 }, instant ? 0 : app.duration.fade)
									.removeClass('active')
									.addClass('inactive')
									;
							}

							app.items.activeItemId = $(this).data('item-id');
							app.items.activeItem   = $(this);

							$(this).removeClass('inactive').addClass('active');

							if ( $(this).hasClass('collapsed') ) {
								$('article').stop().animate({ opacity: 1 }, instant ? 0 : app.duration.fade);
							} else {
								$(this).stop().animate({ opacity: 1 }, instant ? 0 : app.duration.fade);

								$('article:not([data-item-id=' + app.items.activeItemId + '])').stop().animate({ opacity: .3 }, instant ? 0 : app.duration.fade);
							}

							app.items.markAsRead(app.items.activeItemId);

							if ( $(this).is(':last-child') ) {
								app.items.loadMore();
							}

							// Hide floating alerts
							$('.alert-float').click();
						}

						return false;
					}
				});

				return app.items;
			},

			vote: function(itemId, vote) {
				app.trackEvent('app.items', 'vote', vote);

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
					url: '/' + app.controller + '/vote/' + app.args,
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
						url: '/' + app.controller + '/read/' + app.args,
						method: 'post',
						data: { item_id: itemId, sessionId: app.sessionId }
					});
				}

				return app.items;
			},

			save: function(itemId, save) {
				app.trackEvent('app.items', 'save');

				if ( !app.signedIn ) {
					app.notSignedIn();

					return;
				}

				var button = $('article .item-save[data-item-id=' + itemId + ']');

				if ( save ) {
					button.addClass('btn-inverse saved').html('<i class="entypo install"></i>&nbsp;Saved');
				} else {
					button.removeClass('btn-inverse saved').html('<i class="entypo install"></i>&nbsp;Save');
				}

				if ( app.signedIn ) {
					$.ajax({
						url: '/' + app.controller + '/save/' + app.args,
						method: 'post',
						data: { item_id: itemId, save: save, sessionId: app.sessionId }
					}).fail(function() {
						if ( save ) {
							button.removeClass('btn-inverse saved').html('<i class="entypo install"></i>&nbsp;Save');
						} else {
							button.addClass('btn-inverse saved').html('<i class="entypo install"></i>&nbsp;Saved');
						}
					});
				}

				return app.items;
			},

			subscribe: function(feedId, action) {
				app.trackEvent('app.items', 'subscribe', action);

				if ( !app.signedIn ) {
					app.notSignedIn();

					return;
				}

				el = $('article .subscription[data-feed-id=' + feedId + ']');

				if ( action === 'subscribe' ) {
					el.removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i>&nbsp;Unsubscribe');
				} else {
					el.removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i>&nbsp;Subscribe');
				}

				$.ajax({
					url: '/' + app.controller + '/subscribe/' + app.args,
					method: 'post',
					data: { feed_id: feedId, action: action, sessionId: app.sessionId }
				}).fail(function(data) {
					if ( action === 'unsubscribe' ) {
						el.removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i>&nbsp;Unsubscribe');
					} else {
						el.removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i>&nbsp;Subscribe');
					}
				});

				return app.items;
			},

			infiniteScroll: function(e) {
				if ( $('#items-footer').length ) {
					$(document).unbind('scroll', app.items.infiniteScroll);

					return;
				}

				if ( $(document).scrollTop() > $(document).height() - ( $(window).height() * 2 ) ) {
					app.items.loadMore();
				}

				return app.items;
			},

			loadMore: function() {
				if ( app.items.page + 1 <= app.items.lastRequestedPage ) {
					return;
				}

				$(document).unbind('scroll', app.items.infiniteScroll);

				app.items.lastRequestedPage = app.items.page + 1;

				$.ajax({
					url: '/' + app.controller + '/items/' + app.args,
					data: { page: app.items.page + 1, excludes: app.excludes.join(' ') },
					context: $('#items')
				}).done(function(data) {
					$('#items .loading').remove();

					if ( data ) {
						$(this).append(data);

						app.items.itemsAdded();

						$(document).bind('scroll', app.items.infiniteScroll);
					}
				});

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
