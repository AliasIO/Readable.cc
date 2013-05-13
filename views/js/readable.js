(function(app, $) {
	app.duration = { fade: 300, scroll: 300 };
	app.excludes = [];
	app.mobile   = false;

	app.log = function(message) {
		if ( typeof window.console !== 'undefined' && typeof console.log !== 'undefined' ) {
			console.log(message);

			return true;
		}
	};

	app.trackEvent = function(category, action, label, value) {
		if ( typeof _gaq !== 'undefined' ) {
			_gaq.push([ '_trackEvent', category, action, label ? label : null, value ? value : null ]);
		}
	};

	app.trackPageView = function(url) {
		if ( typeof _gaq !== 'undefined' ) {
			_gaq.push([ '_trackPageview', url ]);
		}
	};

	app.init = function() {
		$('.contact-email').text(app.email.replace(' ', '@')).attr('href', 'mailto:' + app.email.replace(' ', '@'));

		// Hide alerts on click
		$(document).on('click', '.alert .alert-cancel', function(e) {
			e.stopPropagation();

			$('#overlay, .alert').hide();
		});

		// Hide alerts on click
		$(document).on('click', '.alert', function() {
			if ( $(this).hasClass('alert-sticky') ) {
				return;
			}

			$('#overlay, .alert').hide();
		});

		$(window).resize(function() {
			$('.alert-float').outerWidth($('#contents').width());

			app.mobile = $(document).width() < 850;

			app.duration = app.mobile ? { fade: 0, scroll: 0 } : { fade: 300, scroll: 300 };
		}).resize();

		switch ( app.controller ) {
			case 'index':
			case 'reading':
			case 'saved':
			case 'feed':
				app.items.init();

				break;
			case 'subscriptions':
				app.subscriptions.init();

				break;
		}

		app.navBar.init();

		return app;
	};

	// Display alert
	app.notice = function(message) {
		$('#overlay, .alert').hide();

		$('<div class="alert alert-float">' + message + '</div>')
			.stop()
			.hide()
			.outerWidth($('#contents').width())
			.appendTo('#contents')
			.show()
			;

		$('#overlay').show();

		return app;
	};

	app.notSignedIn = function() {
		app.notice($('.modal-signin').html());

		return app;
	};

	app.navBar = {
		height: 0,
		anchor: null,
		previousScrollTop: 0,

		init: function() {
			$(document).bind('scroll', app.navBar.scroll);

			if ( $('#items').length ) {
				$(document).bind('scroll', app.items.infiniteScroll);
			}

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
	};

	app.items = {
		activeItemId: null,
		activeItem: null,
		pageTop: 0,
		previousItem: null,
		nextItem: null,
		page: app.page,
		lastRequestedPage: 0,
		noMoreItems: false,

		init: function() {
			var
				scrolled = false
				;

			app.items.updateItemCount();

			Mousetrap.bind(['j', 's', 'space'], function() {
				var next = app.items.activeItem ? app.items.activeItem.nextAll('article').first() : $('#items article').first();

				if ( app.items.noMoreItems ) {
					app.notice($('.modal-no-more-items').html());
				}

				if ( !next[0] ) {
					return;
				}

				app.items.expand(  next, true);
				app.items.scrollTo(next, true);

				return false;
			});

			Mousetrap.bind(['k', 'w'], function() {
				var previous = app.items.activeItem ? app.items.activeItem.prevAll('article').first() : $('#items article').first();

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

			Mousetrap.bind([ 'm', 'A' ], function() {
				if ( app.controller === 'reading' ) {
					app.notice($('.modal-mark-all-read').html());

					$('button.mark-all-read-confirm').focus();
				}
			});

			Mousetrap.bind([ 's' ], function() {
				if ( app.signedIn && app.items.activeItem ) {
					app.items.activeItem.find('.item-save').trigger('click');
				}
			});

			Mousetrap.bind('escape', function() {
				$('#overlay, .alert').hide();
			});

			$(document).on('click', '.mark-all-read', function(e) {
				e.preventDefault();

				console.log('x'+app.controller);

				if ( app.controller === 'reading' ) {
					app.notice($('.modal-mark-all-read').html());

					$('button.mark-all-read-confirm').focus();
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

			if ( $('#page-head span').length > 1 ) {
				$('#page-head span').css({ display: 'block' });

				$('#page-head span:not(:first-child)').css({ opacity: 0 });

				setInterval(function() {
					$('#page-head span:first-child')
						.stop()
						.css({ zIndex: 0 })
						.appendTo($('#page-head p'))
						.animate({ opacity: 0 }, 'slow', function() {
							$('#page-head span:first-child')
								.stop()
								.css({ opacity: 0, top: 10, zIndex: 1 })
								.animate({ opacity: 1, top: 0 }, 'slow')
								;
						})
				}, 6000);
			}

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

			$(document).on('click', '.mark-all-read-confirm', function(e) {
				e.stopPropagation();

				$(this).blur();

				app.items.markAllAsRead();
			});

			app.items.itemsAdded();

			return app.items;
		},

		itemsAdded: function() {
			app.trackPageView(app.controller + '/page/' + app.items.page);

			$('#items .pagination').hide();

			var i = 0;

			$($('#items article').get().reverse()).each(function() {
				if ( $.inArray($(this).data('item-id'), app.excludes) === -1 ) {
					app.excludes.push($(this).data('item-id'));
				}

				// Add separator betweens items of different date
				var
					date     = $(this).data('item-date'),
					nextDate = $(this).next('article').data('item-date')
					;

				if ( nextDate && date !== nextDate ) {
					if ( !$(this).next('.date-separator').length ) {
						$(this).after('<p class="date-separator"><span>' + nextDate + '</span></p>');
					}
				}

				// Keep only the last 200 articles in the DOM
				if ( i ++ < 200 ) {
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

			if ( !el.hasClass('processed') ) {
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

				// Images at start of paragraph
				el.find('img').each(function() {
					if ( !$(this).closest('p').text().trim().length ) {
						$(this).closest('p').addClass('no-text');
					}
				});

				el.find('p > img:first-child, p > a:first-child > img:first-child').each(function() {
					if ( $(this).closest('p').text().trim().length && $(this).closest('p').html().match(/^\s*(<a [^>]+>\s*)?<img /) ) {
						$(this).addClass('image-left');
					}
				});

				// Images at end of paragraph or alone in paragraph
				el.find('p > img:last-child, p > a:last-child > img:last-child').each(function() {
					if ( $(this).closest('p').text().trim().length && $(this).closest('p').html().match(/<img [^>]+>(\s*<\/a>)?\s*$/) ) {
						$(this).addClass('image-right');
					}
				});

				// Feature first image not preceded by text
				el.find('p:not(:last-child):first-child > a:first-child > img:first-child:not(.image-alone), p:not(:last-child):first-child > img:first-child:not(.image-alone)').each(function() {
					if ( $(this).closest('p').html().match(/^\s*(<a [^>]+>\s*)?<img /) ) {
						$(this).addClass('feature');

						// Remove newline directly after feature image
						$(this).closest('p').find('.feature + br').remove();
					}
				});

				// Don't align paragraphs with images next to feature image
				el.find('img:not(.feature)').each(function() {
					$(this).closest('p').css({ clear: 'both' });
				});

				// Clean up line breaks
				el.find('br:only-child').each(function() {
					$(this).closest('p').remove();
				});

				el.addClass('processed')
			}

			return app.items;
		},

		scrollTo: function(el, instant) {
			if ( !el || !el.length ) {
				return;
			}

			app.navBar.pin(instant);

			$('html,body')
				.animate({ scrollTop: el.offset().top - app.items.pageTop }, instant ? 0 : app.duration.scroll)
				.promise()
				.done(function() {
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

						if ( this === $('#items article').last()[0] ) {
							app.items.loadMore();
						}

						// Hide floating alerts
						$('.alert-float').trigger('click');
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

				app.items.updateItemCount(-1);

				$.ajax({
					url: '/' + app.controller + '/read/' + app.args,
					method: 'post',
					data: { item_id: itemId, sessionId: app.sessionId }
				});
			}

			return app.items;
		},

		markAllAsRead: function() {
			if ( app.signedIn && app.controller === 'reading' ) {
				$.ajax({
					url: '/' + app.controller + '/read/' + app.args,
					method: 'post',
					data: { item_id: 'all', sessionId: app.sessionId }
				})
				.done(function() {
					location = location;
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

			if ( $(document).scrollTop() > $(document).height() - ( $(window).height() * 5 ) ) {
				app.items.loadMore();
			}

			return app.items;
		},

		loadMore: function() {
			if ( app.items.noMoreItems || app.items.page + 1 <= app.items.lastRequestedPage ) {
				return;
			}

			$('#items').append('<div class="loading"></div>');

			$(document).unbind('scroll', app.items.infiniteScroll);

			app.items.lastRequestedPage = app.items.page + 1;

			var data = { page: app.items.page + 1 };

			// Excludes ensures displayed items that may not have been marked read don't get loaded again
			if ( app.notSignedIn && app.controller === 'index' || app.controller === 'reading' ) {
				data.excludes = app.excludes.join(' ');
			}

			$.ajax({
				url: '/' + app.controller + '/items/' + app.args,
				data: data,
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
		},

		updateItemCount: function(diff) {
			if ( app.controller != 'reading' ) {
				return
			}

			if ( diff && app.itemCount <= 1000 ) {
				app.itemCount += diff;
			}

			$('#item-count span').text(app.itemCount > 1000 ? '1000+' : Math.max(0, app.itemCount));

			if ( !app.itemCount ) {
				$('#item-count:visible').hide();
			} else {
				$('#item-count:hidden').show();
			}
		}
	};

	app.subscriptions = {
		init: function() {
			$('#subscriptions button').click(function() {
				$(this).blur();

				var
					id     = $(this).data('feed-id'),
					action = $(this).hasClass('subscribe') ? 'subscribe' : 'unsubscribe'
					;

				if ( action === 'subscribe' ) {
					$(this).removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i>&nbsp;Unsubscribe');
				} else {
					$(this).removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i>&nbsp;Subscribe');
				}

				$.ajax({
					url: '/subscriptions/' + action,
					method: 'post',
					data: { id: id, sessionId: app.sessionId }
				});
			});

			$('#suggestions button').click(function() {
				$(this).blur();

				var
					url    = $(this).data('url'),
					action = $(this).hasClass('subscribe') ? 'subscribe' : 'unsubscribe'
					;

				if ( action === 'subscribe' ) {
					$(this).removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i>&nbsp;Unsubscribe');
				} else {
					$(this).removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i>&nbsp;Subscribe');
				}

				$.ajax({
					url: '/subscriptions/' + action,
					method: 'post',
					data: { url: url, sessionId: app.sessionId }
				});
			});

			$('#form-subscriptions-subscribe').submit(function(e) {
				e.preventDefault();

				var
					input        = $(this).find('input[name  = url]'),
					controlGroup = input.closest('.control-group'),
					button       = $(this).find('button'),
					loading      = $(this).find('.loading'),
					message      = $(this).find('.message'),
					url          = input.val()
					;

				button.attr('disabled', 'disabled').blur();
				input .attr('disabled', 'disabled');

				message.removeClass('error').hide();

				controlGroup.removeClass('error');

				loading.css({ display: 'inline-block' });

				$.ajax({
					url: '/subscriptions/subscribe',
					method: 'post',
					data: { url: url, sessionId: app.sessionId }
				}).always(function() {
					button.removeAttr('disabled');
					input .removeAttr('disabled');

					loading.css({ display: 'none' });
				}).done(function(data) {
					message.html('Subscription added! <a href="/feed/view/' + data.feed_id + '">Click here to view the feed</a>.').show();
				}).fail(function(xhr) {
					data = $.parseJSON(xhr.responseText);

					controlGroup.addClass('error');

					message.addClass('error').html(data.message).show();
				});
			});

			return app.subscriptions;
		}
	};

	app.init();
}(readable, jQuery));
