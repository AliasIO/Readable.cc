/*jslint browser: true, nomen: true, plusplus: true, sloppy: false, white: true */

(function(app, $) {
	'use strict';

	app.PREF_LINKS_NEW_TAB = 1;

	app.duration = { fade: 300, scroll: 300 };
	app.excludes = [];
	app.mobile   = false;

	app.trackEvent = function(category, action, label, value) {
		if ( window._gaq ) {
			window._gaq.push([ '_trackEvent', category, action, label || null, value || null ]);
		}
	};

	app.trackPageView = function(url) {
		if ( window._gaq ) {
			window._gaq.push([ '_trackPageview', url ]);
		}
	};

	app.init = function() {
		var doubleTap = false;

		$('.contact-email').text(app.email.replace(' ', '@')).attr('href', 'mailto:' + app.email.replace(' ', '@'));

		$('header h2')
			.on('click', function(e) {
				e.stopPropagation();

				$('header ul').first().toggleClass('collapsed');
			}).on('touchstart', function(e) {
				e.preventDefault();

				$(e.target).trigger('click');
			});

		$('header ul ul').parent().on('click', function() {
			$(this).find('ul').toggleClass('collapsed');
		});

		$(document)
			.on('click', function(e) {
				if ( !$(e.target).closest('header ul').length ) {
					$('header ul, header ul ul').addClass('collapsed');
				}

				if ( !$(e.target).closest('.share').length ) {
					$('.article-buttons').find('.share ul').hide();
				}
			})
			.on('click', 'button.item-share', function(e) {
				$(this).trigger('blur').parent().find('ul').toggle();
			})
			// Hide alerts on click
			.on('click', '.alert .alert-cancel', function(e) {
				e.stopPropagation();

				$('#overlay, .alert').hide();
			})
			// Hide alerts on click
			.on('click', '.alert', function() {
				if ( $(this).hasClass('alert-sticky') ) {
					return;
				}

				$('#overlay, .alert').hide();
			})
			// Collapse item on double tap
			.on('touchstart', 'article', function() {
				if ( $(this).data('item-id') === app.items.activeItemId && app.items.activeItem.hasClass('expanded') ) {
					if ( doubleTap ) {
						app.items.collapse();
					} else {
						doubleTap = true;

						setTimeout(function() {
							doubleTap = false;
						}, 500);
					}
				}
			});

		if ( app.prefs.externalLinks === app.PREF_LINKS_NEW_TAB ) {
			$(document).on('click', 'a', function(e) {
				if ( this.hostname && this.hostname !== window.location.hostname ) {
					$(this).attr('target', '_blank');
				}
			});
		}

		$(window).resize(function() {
			$('.alert-float').outerWidth($('#contents').width());

			app.mobile = $(document).width() < 600;

			app.duration = app.mobile ? { fade: 0, scroll: 0 } : { fade: 300, scroll: 300 };
		}).resize();

		switch ( app.controller ) {
			case 'Index':
			case 'Reading':
			case 'Saved':
			case 'Feed':
			case 'Folder':
				app.items.init();

				break;
			case 'Settings':
				app.settings.init();

				break;
			case 'Pay':
				app.pay.init();

				break;
			case 'Subscriptions':
				app.subscriptions.init();

				break;
			case 'Signup':
				app.signup.init();

				break;
			case 'Search':
				app.items.init();
				app.search.init();

				break;
		}

		app.header.init();

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

	app.header = {
		height: 0,
		anchor: null,
		previousScrollTop: 0,

		init: function() {
			$(document).bind('scroll', app.header.scroll);

			if ( $('#items').length ) {
				$(document).bind('scroll', app.items.infiniteScroll);
			}

			$(document).scroll();

			app.header.height = $('header').outerHeight();

			return app.header;
		},

		scroll: function() {
			var
				scrollTop   = $(document).scrollTop(),
				headerTop   = Math.min(0, Math.max(parseInt($('header').css('top'), 10) - ( scrollTop - app.header.previousScrollTop ), - app.header.height))
				;

			if ( headerTop <= 0 && headerTop >= - app.header.height ) {
				$('header').css({ top: headerTop });
			}

			app.header.previousScrollTop = scrollTop;

			// Read line
			if ( scrollTop > app.items.pageTop ) {
				$('#items-read-line:hidden').fadeIn(app.duration.fade);
			} else {
				$('#items-read-line:visible').fadeOut(app.duration.fade);
			}

			return app.header;
		},

		pin: function(instant) {
			$(document).unbind('scroll', app.header.scroll);

			app.header.previousScrollTop = $(document).height();

			$('header').stop().animate({ top: 0 }, instant ? 0 : app.duration.scroll);

			return app.header;
		}
	};

	app.items = {
		activeItemId: null,
		activeItem: null,
		pageTop: 0,
		page: app.page,
		lastRequestedPage: 0,
		noMoreItems: app.noMoreItems !== undefined ? app.noMoreItems : false,

		init: function() {
			var
				scrolled = false
				;

			app.items.updateItemCount();

			// Keyboard shortcuts
			$(document).keydown(function(e) {
				var next, previous;

				switch ( e.keyCode ) {
					case 32: // Space
						if ( $(':focus').length ) {
							return;
						}

						e.preventDefault();
					case 74: // j
						next = app.items.activeItem ? app.items.activeItem.nextAll('article').first() : $('#items article').first();

						if ( app.items.noMoreItems ) {
							app.notice($('.modal-no-more-items').html());
						}

						if ( !next[0] ) {
							return;
						}

						app.items.expand(  next, true);
						app.items.scrollTo(next, true);

						break;
					case 75: // k
						previous = app.items.activeItem ? app.items.activeItem.prevAll('article').first() : $('#items article').first();

						if ( !previous[0] ) {
							return;
						}

						app.items.expand(  previous, true);
						app.items.scrollTo(previous, true);

						break;
					case 36: // Home
						app.header.pin(true).init();

						return;
					case 13: // Enter
					case 79: // o
						if ( app.items.activeItem ) {
							app.items.activeItem.trigger('click');
						}

						break;
					case 67: // c
						if ( app.items.activeItem ) {
							app.items.collapse();
						}

						break;
					case 65: // A
					case 77: // m
						if ( app.controller === 'Reading' || app.controller === 'Folder' ) {
							app.notice($('.modal-mark-all-read').html());

							$('button.mark-all-read-confirm').focus();
						}

						break;
					case 83: // s
						if ( app.signedIn && app.items.activeItem ) {
							app.items.activeItem.find('.item-save').trigger('click');
						}

						break;
					case 27: // Escape
						$('#overlay, .alert').hide();

						break;
					default:
						return;
				}
			});

			$(document).on('click', '.mark-all-read', function(e) {
				e.preventDefault();

				if ( app.controller === 'Reading' || app.controller === 'Folder' ) {
					app.notice($('.modal-mark-all-read').html());

					$('button.mark-all-read-confirm').focus();
				}
			});

			$(document).ajaxError(function(e, xhr) {
				if ( xhr.status === 403 ) {
					if ( !app.signedIn ) {
						app.notSignedIn();
					} else {
						if ( window.confirm('Your session has expired. Sign back in?') ) {
							window.location = app.rootPath + 'signin';
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
			$(document).scroll(function() {
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
						});
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

				$(this).trigger('blur');

				app.items.vote($(this).data('item-id'), $(this).hasClass('voted') ? 0 : $(this).data('vote'));
			});

			// Register votes
			$('#items').on('click', 'article.active .subscription', function(e) {
				var
					feedId = $(this).data('feed-id'),
					action = $(this).hasClass('subscribe') ? 'subscribe' : 'unsubscribe'
					;

				e.preventDefault();

				$(this).trigger('blur');

				app.items.subscribe(feedId, action);
			});

			$('#items').on('click', 'article.active .item-save', function(e) {
				e.preventDefault();

				$(this).trigger('blur');

				app.items.save($(this).data('item-id'), $(this).hasClass('saved') ? 0 : 1);
			});

			$(document).on('click', '.mark-all-read-confirm', function(e) {
				e.stopPropagation();

				$(this).trigger('blur');

				app.items.markAllAsRead();
			});

			app.items.itemsAdded();

			return app.items;
		},

		itemsAdded: function() {
			var i = 0;

			app.trackPageView(app.controller.toLowerCase() + '/page/' + app.items.page);

			$('#items .pagination').hide();

			$($('#items article').get().reverse()).each(function() {
				var
					placeHolder,
					el       = $(this),
					date     = el.data('item-date'),
					nextDate = el.next('article').data('item-date')
					;

				// Add separator betweens items of different date
				if ( $.inArray(el.data('item-id'), app.excludes) === -1 ) {
					app.excludes.push(el.data('item-id'));
				}

				if ( nextDate && date !== nextDate ) {
					if ( !el.next('.date-separator').length ) {
						el.after('<p class="date-separator"><span>' + nextDate + '</span></p>');
					}
				}

				// Keep only the last 200 articles in the DOM
				if ( i ++ < 200 ) {
					return true;
				}

				placeHolder = $('<div>');

				placeHolder.addClass('placeholder').height(el.outerHeight(true) - 1);

				el.replaceWith(placeHolder);
			});

			app.items.activeItemId = null;

			app.items.findActive(true);

			app.items.page ++;

			return app.items;
		},

		lazyLoadImages: function(el) {
			if ( el.hasClass('lazyLoaded') ) {
				return;
			}

			el.addClass('lazyLoaded');

			el.find('img').each(function() {
				var
					src, img,
					self = $(this)
					;

				if ( self.data('src') ) {
					src = self.data('src');

					img = $('<img>').hide().appendTo('body').attr('src', src);

					img
						.on('load', function() {
							if ( img.width() > 50 && img.height() > 50 ) {
								self
									.attr('src', src)
									.removeAttr('data-src')
									.width(img.width())
									;

								img.remove();
							} else {
								self.remove();

								app.items.removeEmptyElements(el);
							}
						})
						.on('error', function() {
							self.remove();
						});
				}
			});
		},

		removeEmptyElements: function(el) {
			el.find('.item-contents *').filter(function() {
				var self = $(this);

				return (
					self.prop('tagName').toLowerCase() !== 'img' &&
					self.prop('tagName').toLowerCase() !== 'br' &&
					!self.find('img').length &&
					!$.trim(self.text())
					);
			}).remove();
		},

		expand: function(el, instant) {
			$('article:not([data-item-id=' + el.data('item-id') + ']), .date-separator')
				.stop()
				.animate({ opacity: 0.3 }, instant ? 0 : app.duration.fade)
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

				// Lazy load current (if needed) and next article's images
				app.items.lazyLoadImages(el);
				app.items.lazyLoadImages(el.nextAll('article').first());

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

				// Remove empty elements
				app.items.removeEmptyElements(el);

				el.addClass('processed');
			}

			return app.items;
		},

		collapse: function(instant) {
			var el = app.items.activeItem;

			if ( el ) {
				$('article:not([data-item-id=' + el.data('item-id') + ']), .date-separator')
					.stop()
					.animate({ opacity: 1 }, instant ? 0 : app.duration.fade)
					;

				el
					.removeClass('expanded')
					.addClass('collapsed')
					.find('.item-wrap')
					.stop()
					.slideUp(instant ? 0 : app.duration.scroll)
					;

				app.items.scrollTo(el, instant);
			}
		},

		scrollTo: function(el, instant) {
			if ( !el || !el.length ) {
				return;
			}

			app.header.pin(instant);

			$('html,body')
				.animate({ scrollTop: el.offset().top - app.items.pageTop }, instant ? 0 : app.duration.scroll)
				.promise()
				.done(function() {
					app.items.findActive(instant);

					app.header.init();
				});

			return app.items;
		},

		findActive: function(instant) {
			var offset = $(document).scrollTop() - $('#contents').position().top;

			$($('#items article').get().reverse()).each(function() {
				var
					el     = $(this),
					top    = el.position().top - offset,
					bottom = top + el.outerHeight(true)
					;

				if ( top <= app.items.pageTop + 5 && bottom >= app.items.pageTop ) {
					if ( app.items.activeItemId !== el.data('item-id') ) {
						if ( app.items.activeItem ) {
							app.items.activeItem
								.stop()
								.animate({ opacity: 0.3 }, instant ? 0 : app.duration.fade)
								.removeClass('active')
								.addClass('inactive')
								;
						}

						app.items.activeItemId = el.data('item-id');
						app.items.activeItem   = el;

						el.removeClass('inactive').addClass('active');

						if ( el.hasClass('collapsed') ) {
							$('article, .date-separator').stop().animate({ opacity: 1 }, instant ? 0 : app.duration.fade);
						} else {
							el.stop().animate({ opacity: 1 }, instant ? 0 : app.duration.fade);

							$('article:not([data-item-id=' + app.items.activeItemId + ']), .date-separator').stop().animate({ opacity: 0.3 }, instant ? 0 : app.duration.fade);
						}

						app.items.markAsRead(app.items.activeItemId);

						if ( !$('#items-footer').length && this === $('#items article').last()[0] ) {
							app.items.loadMore();
						}

						// Hide floating alerts
						$('.alert-float').trigger('click');

						// Collapse navigation
						$('header ul, header ul ul').addClass('collapsed');
					}

					return false;
				}
			});

			return app.items;
		},

		vote: function(itemId, vote) {
			var buttonUp, buttonDown, buttonLastActive, buttonActive;

			app.trackEvent('app.items', 'vote', vote);

			if ( !app.signedIn ) {
				app.notSignedIn();

				return;
			}

			buttonUp         = $('article .item-vote[data-item-id=' + itemId + '][data-vote=1]');
			buttonDown       = $('article .item-vote[data-item-id=' + itemId + '][data-vote=-1]');
			buttonLastActive = $('article .item-vote[data-item-id=' + itemId + '].voted');
			buttonActive     = vote === 1 ? buttonUp : ( vote === -1 ? buttonDown : null );

			buttonUp  .removeClass('btn-inverse voted');
			buttonDown.removeClass('btn-inverse voted');

			if ( buttonActive ) {
				buttonActive.addClass('btn-inverse voted');
			}

			$.ajax({
				url: app.rootPath + 'read/vote/',
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
			var el;

			if ( app.signedIn && itemId ) {
				el = $('article[data-item-id=' + itemId + ']');

				if ( el.hasClass('read') ) {
					return;
				}

				el.addClass('read');

				app.items.updateItemCount(-1);

				$.ajax({
					url: app.rootPath + 'read/markRead/',
					method: 'post',
					data: { item_id: itemId, sessionId: app.sessionId }
				});
			}

			return app.items;
		},

		markAllAsRead: function() {
			var data;

			if ( app.signedIn && ( app.controller === 'Reading' || app.controller === 'Folder' ) ) {
				data = { item_id: 'all', sessionId: app.sessionId };

				if ( app.controller === 'Folder' ) {
					data.folder_id = app.args[0] || null;
				}

				$.ajax({
					url: app.rootPath + 'read/markRead/',
					method: 'post',
					data: data
				})
				.done(function() {
					window.location = window.location;
				});
			}

			return app.items;
		},

		save: function(itemId, save) {
			var button;

			app.trackEvent('app.items', 'save');

			if ( !app.signedIn ) {
				app.notSignedIn();

				return;
			}

			button = $('article .item-save[data-item-id=' + itemId + ']');

			if ( save ) {
				button.addClass('btn-inverse saved').html('<i class="entypo install"></i>&nbsp;Saved');
			} else {
				button.removeClass('btn-inverse saved').html('<i class="entypo install"></i>&nbsp;Save');
			}

			if ( app.signedIn ) {
				$.ajax({
					url: app.rootPath + 'read/save/',
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
			var el;

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
				url: app.rootPath + 'read/subscribe/',
				method: 'post',
				data: { feed_id: feedId, action: action, sessionId: app.sessionId }
			}).fail(function() {
				if ( action === 'unsubscribe' ) {
					el.removeClass('subscribe').addClass('unsubscribe').html('<i class="entypo squared-minus"></i>&nbsp;Unsubscribe');
				} else {
					el.removeClass('unsubscribe').addClass('subscribe').html('<i class="entypo squared-plus"></i>&nbsp;Subscribe');
				}
			});

			return app.items;
		},

		infiniteScroll: function() {
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
			var data = { page: app.items.page + 1 };

			if ( app.items.noMoreItems || app.items.page + 1 <= app.items.lastRequestedPage ) {
				return;
			}

			$('#items').append('<div class="loading"></div>');

			$(document).unbind('scroll', app.items.infiniteScroll);

			app.items.lastRequestedPage = app.items.page + 1;

			// Excludes ensures displayed items that may not have been marked read don't get loaded again
			if ( app.signedIn && ( app.controller === 'Index' || app.controller === 'Reading' ) ) {
				data.excludes = app.excludes.join(' ');
			}

			$.ajax({
				url: app.rootPath + app.controller.toLowerCase() + '/items/' + app.args.join('/'),
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
			if ( app.controller !== 'Reading' && app.controller !== 'Folder' ) {
				return;
			}

			if ( diff && app.itemCount <= 1000 ) {
				app.itemCount += diff;
			}

			app.itemCount = Math.max(0, app.itemCount);

			$('.item-count span').text(app.itemCount > 1000 ? '1000+' : app.itemCount);

			if ( app.itemCount ) {
				$('.active .item-count:hidden').show();
			} else {
				$('.active .item-count:visible').hide();
			}
		}
	};

	app.settings = {
		init: function() {
			$('#form-settings-delete').on('submit', function() {
				if ( window.confirm('This action can not be undone!\n\nAre you sure you wish to delete your account?') ) {
					return true;
				}

				return false;
			});
		}
	};

	app.pay = {
		init: function() {
			$('#name').focus();

			$('#amount, #months').on('change blur keyup', function() {
				$('#total-amount').text('$' + $('#amount').val() * $('#months').val());
			}).trigger('change');
		}
	};

	app.subscriptions = {
		init: function() {
			$('#subscriptions-grouped h3').on('click', function() {
				$(this).next('table').toggle();
			});

			$('#subscriptions-grouped button').on('click', function() {
				var
					el     = $(this),
					id     = el.data('feed-id'),
					action = el.hasClass('subscribe') ? 'subscribe' : 'unsubscribe'
					;

				el.trigger('blur');

				if ( action === 'subscribe' ) {
					el.removeClass('subscribe').addClass('btn-danger unsubscribe').html('Unsubscribe');
				} else {
					el.removeClass('unsubscribe').removeClass('btn-danger').addClass('subscribe').html('Subscribe');
				}

				$.ajax({
					url: app.rootPath + 'subscriptions/' + action,
					method: 'post',
					data: { id: id, sessionId: app.sessionId }
				});
			});

			$('#table-subscriptions-suggestions button').on('click', function() {
				var
					el     = $(this),
					url    = el.data('url'),
					action = el.hasClass('subscribe') ? 'subscribe' : 'unsubscribe'
					;

				el.trigger('blur');

				if ( action === 'subscribe' ) {
					el.removeClass('subscribe').addClass('btn-danger unsubscribe').html('Unsubscribe');
				} else {
					el.removeClass('unsubscribe').removeClass('btn-danger').addClass('subscribe').html('Subscribe');
				}

				$.ajax({
					url: app.rootPath + 'subscriptions/' + action,
					method: 'post',
					data: { url: url, sessionId: app.sessionId }
				});
			});

			$('#form-subscriptions-subscribe').submit(function(e) {
				var
					el           = $(this),
					inputUrl     = el.find('input[name=url]'),
					inputFolder  = el.find('select[name=folder]'),
					controlGroup = inputUrl.closest('.control-group'),
					button       = el.find('button'),
					loading      = el.find('.loading'),
					message      = el.find('.message'),
					url          = inputUrl.val(),
					folderId     = inputFolder.val()
					;

				e.preventDefault();

				button     .attr('disabled', 'disabled').trigger('blur');
				inputUrl   .attr('disabled', 'disabled');
				inputFolder.attr('disabled', 'disabled');

				message.removeClass('error').hide();

				controlGroup.removeClass('error');

				loading.css({ display: 'inline-block' });

				$.ajax({
					url: app.rootPath + 'subscriptions/subscribe',
					method: 'post',
					data: { url: url, folderId: folderId, sessionId: app.sessionId }
				}).always(function() {
					button     .removeAttr('disabled');
					inputUrl   .removeAttr('disabled');
					inputFolder.removeAttr('disabled');

					loading.css({ display: 'none' });
				}).done(function(data) {
					message.html('Subscription added! <a href="/feed/view/' + data.feed_id + '">Click here to view the feed</a>.').show();
				}).fail(function(xhr) {
					var data = $.parseJSON(xhr.responseText);

					controlGroup.addClass('error');

					message.addClass('error').html(data.message).show();
				});
			});

			$('#subscriptions-grouped select').on('change', function() {
				var id = $(this).data('feed-id');

				$.ajax({
					url: app.rootPath + 'subscriptions/folder',
					method: 'post',
					data: { id: id, folderId: $(this).val(), sessionId: app.sessionId }
				});
			});
		}
	};

	app.search = {
		init: function() {
			$('form').on('submit', function(e) {
				e.preventDefault();

				if ( $('#query').val() ) {
					window.location = app.rootPath + 'search/query/' + $('#query').val() + ( $('#feed').val() ? '/' + $('#feed').val() : '' );
				}
			});
		}
	};

	app.signup = {
		init: function() {
			$('#email').focus();
		}
	};

	app.init();
}(readable, jQuery));
