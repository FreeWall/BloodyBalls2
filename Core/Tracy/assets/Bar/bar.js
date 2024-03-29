/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

(function(){

	class Panel
	{
		constructor(id) {
			this.id = id;
			this.elem = document.getElementById(this.id);
			this.elem.Tracy = this.elem.Tracy || {};
		}


		init() {
			var elem = this.elem;

			this.init = function() {};
			elem.innerHTML = elem.dataset.tracyContent;
			Tracy.Dumper.init(this.dumps, elem);
			delete elem.dataset.tracyContent;
			delete this.dumps;
			evalScripts(elem);

			draggable(elem, {
				handles: elem.querySelectorAll('h1'),
				start: () => {
					if (!this.is(Panel.FLOAT)) {
						this.toFloat();
					}
					this.focus();
				}
			});

			elem.addEventListener('mousedown', () => {
				this.focus();
			});

			elem.addEventListener('mouseenter', () => {
				clearTimeout(elem.Tracy.displayTimeout);
			});

			elem.addEventListener('mouseleave', () => {
				this.blur();
			});

			elem.addEventListener('mousemove', e => {
				if (e.buttons && !this.is(Panel.RESIZED) && (elem.style.width || elem.style.height)) {
					elem.classList.add(Panel.RESIZED);
				}
			});

			elem.addEventListener('tracy-toggle', () => {
				this.reposition();
			});

			forEach(elem.querySelectorAll('.tracy-icons a'), link => {
				link.addEventListener('click', e => {
					clearTimeout(elem.Tracy.displayTimeout);
					if (link.rel === 'close') {
						this.toPeek();
					} else if (link.rel === 'window') {
						this.toWindow();
					}
					e.preventDefault();
				});
			});

			if (!this.is('tracy-ajax')) {
				Tracy.Toggle.persist(elem);
			}
		}


		is(mode) {
			return this.elem.classList.contains(mode);
		}


		focus(callback) {
			var elem = this.elem;
			if (this.is(Panel.WINDOW)) {
				elem.Tracy.window.focus();
			} else {
				clearTimeout(elem.Tracy.displayTimeout);
				elem.Tracy.displayTimeout = setTimeout(() => {
					elem.classList.add(Panel.FOCUSED);
					elem.style.zIndex = Tracy.panelZIndex + Panel.zIndexCounter++;
					if (callback) {
						callback();
					}
				}, 50);
			}
		}


		blur() {
			var elem = this.elem;
			if (this.is(Panel.PEEK)) {
				clearTimeout(elem.Tracy.displayTimeout);
				elem.Tracy.displayTimeout = setTimeout(() => {
					elem.classList.remove(Panel.FOCUSED);
				}, 50);
			}
		}


		toFloat() {
			this.elem.classList.remove(Panel.WINDOW);
			this.elem.classList.remove(Panel.PEEK);
			this.elem.classList.add(Panel.FLOAT);
			this.elem.classList.remove(Panel.RESIZED);
			this.reposition();
		}


		toPeek() {
			this.elem.classList.remove(Panel.WINDOW);
			this.elem.classList.remove(Panel.FLOAT);
			this.elem.classList.remove(Panel.FOCUSED);
			this.elem.classList.add(Panel.PEEK);
			this.elem.style.width = '';
			this.elem.style.height = '';
			this.elem.classList.remove(Panel.RESIZED);
		}


		toWindow() {
			var offset = getOffset(this.elem);
			offset.left += typeof window.screenLeft === 'number' ? window.screenLeft : (window.screenX + 10);
			offset.top += typeof window.screenTop === 'number' ? window.screenTop : (window.screenY + 50);

			var win = window.open('', this.id.replace(/-/g, '_'), 'left=' + offset.left + ',top=' + offset.top
			+ ',width=' + this.elem.offsetWidth + ',height=' + this.elem.offsetHeight + ',resizable=yes,scrollbars=yes');
			if (!win) {
				return false;
			}

			var doc = win.document;
			doc.write('<!DOCTYPE html><meta charset="utf-8">'
			+ '<script src="?_tracy_bar=js&amp;XDEBUG_SESSION_STOP=1" onload="Tracy.Dumper.init()" async></script>'
			+ '<body id="tracy-debug">'
			);
			doc.body.innerHTML = '<div class="tracy-panel tracy-mode-window" id="' + this.elem.id + '">' + this.elem.innerHTML + '</div>';
			evalScripts(doc.body);
			if (this.elem.querySelector('h1')) {
				doc.title = this.elem.querySelector('h1').textContent;
			}

			win.addEventListener('beforeunload', () => {
				this.toPeek();
				win.close(); // forces closing, can be invoked by F5
			});

			doc.addEventListener('keyup', e => {
				if (e.keyCode === 27 && !e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey) {
					win.close();
				}
			});

			this.elem.classList.remove(Panel.FLOAT);
			this.elem.classList.remove(Panel.PEEK);
			this.elem.classList.remove(Panel.FOCUSED);
			this.elem.classList.remove(Panel.RESIZED);
			this.elem.classList.add(Panel.WINDOW);
			this.elem.Tracy.window = win;
			return true;
		}


		reposition(deltaX, deltaY) {
			var pos = getPosition(this.elem);
			if (pos.width) { // is visible?
				setPosition(this.elem, {left: pos.left + (deltaX || 0), top: pos.top + (deltaY || 0)});
				if (this.is(Panel.RESIZED)) {
					var size = getWindowSize();
					this.elem.style.width = Math.min(size.width, pos.width) + 'px';
					this.elem.style.height = Math.min(size.height, pos.height) + 'px';
				}
			}
		}


		savePosition() {
			var pos = getPosition(this.elem);
			if (this.is(Panel.WINDOW)) {
				localStorage.setItem(this.id, JSON.stringify({window: true}));
			} else if (pos.width) { // is visible?
				localStorage.setItem(this.id, JSON.stringify({right: pos.right, bottom: pos.bottom, width: pos.width, height: pos.height, zIndex: this.elem.style.zIndex - Tracy.panelZIndex, resized: this.is(Panel.RESIZED)}));
			} else {
				localStorage.removeItem(this.id);
			}
		}


		restorePosition() {
			var pos = JSON.parse(localStorage.getItem(this.id));
			if (!pos) {
				this.elem.classList.add(Panel.PEEK);
			} else if (pos.window) {
				this.init();
				this.toWindow() || this.toFloat();
			} else if (this.elem.dataset.tracyContent) {
				this.init();
				this.toFloat();
				if (pos.resized) {
					this.elem.classList.add(Panel.RESIZED);
					this.elem.style.width = pos.width + 'px';
					this.elem.style.height = pos.height + 'px';
				}
				setPosition(this.elem, pos);
				this.elem.style.zIndex = Tracy.panelZIndex + (pos.zIndex || 1);
				Panel.zIndexCounter = Math.max(Panel.zIndexCounter, (pos.zIndex || 1)) + 1;
			}
		}
	}

	Panel.PEEK = 'tracy-mode-peek';
	Panel.FLOAT = 'tracy-mode-float';
	Panel.WINDOW = 'tracy-mode-window';
	Panel.FOCUSED = 'tracy-focused';
	Panel.RESIZED = 'tracy-panel-resized';
	Panel.zIndexCounter = 1;


	class Bar
	{
		init() {
			this.id = 'tracy-debug-bar';
			this.elem = document.getElementById(this.id);

			draggable(this.elem, {
				handles: this.elem.querySelectorAll('li:first-child'),
				draggedClass: 'tracy-dragged',
				stop: () => {
					this.savePosition();
				}
			});

			this.elem.addEventListener('mousedown', e => {
				e.preventDefault();
			});

			this.initTabs(this.elem);
			this.restorePosition();

			(new MutationObserver(() => {
				this.restorePosition();
			})).observe(this.elem, {childList: true, characterData: true, subtree: true});
		}


		initTabs(elem) {
			forEach(elem.getElementsByTagName('a'), link => {
				link.addEventListener('click', e => {
					if (link.rel === 'close') {
						this.close();

					} else if (link.rel) {
						var panel = Debug.panels[link.rel];
						panel.init();

						if (e.shiftKey) {
							panel.toFloat();
							panel.toWindow();

						} else if (panel.is(Panel.FLOAT)) {
							panel.toPeek();

						} else {
							panel.toFloat();
							panel.reposition(-Math.round(Math.random() * 100) - 20, (Math.round(Math.random() * 100) + 20) * (this.isAtTop() ? 1 : -1));
						}
					}
					e.preventDefault();
				});

				link.addEventListener('mouseenter', e => {
					if (!e.buttons && link.rel && link.rel !== 'close' && !elem.classList.contains('tracy-dragged')) {
						var panel = Debug.panels[link.rel];
						panel.focus(() => {
							if (panel.is(Panel.PEEK)) {
								panel.init();

								var pos = getPosition(panel.elem);
								setPosition(panel.elem, {
									left: getOffset(link).left + getPosition(link).width + 4 - pos.width,
									top: this.isAtTop()
										? getOffset(this.elem).top + getPosition(this.elem).height + 4
										: getOffset(this.elem).top - pos.height - 4
								});
							}
						});
					}
				});

				link.addEventListener('mouseleave', () => {
					if (link.rel && link.rel !== 'close' && !elem.classList.contains('tracy-dragged')) {
						Debug.panels[link.rel].blur();
					}
				});
			});
			this.autoHideLabels();
		}


		autoHideLabels() {
			var width = getWindowSize().width;
			forEach(this.elem.children, function (ul) {
				var labels = ul.querySelectorAll('.tracy-label');
				for (var i = labels.length - 1; i >= 0 && ul.clientWidth >= width; i--) {
					labels.item(i).hidden = true;
				}
			});
		}


		close() {
			document.getElementById('tracy-debug').style.display = 'none';
		}


		reposition(deltaX, deltaY) {
			var pos = getPosition(this.elem);
			if (pos.width) { // is visible?
				setPosition(this.elem, {left: pos.left + (deltaX || 0), top: pos.top + (deltaY || 0)});
				this.savePosition();
			}
		}


		savePosition() {
			var pos = getPosition(this.elem);
			if (pos.width) { // is visible?
				localStorage.setItem(this.id, JSON.stringify(this.isAtTop() ? {right: pos.right, top: pos.top} : {right: pos.right, bottom: pos.bottom}));
			}
		}


		restorePosition() {
			var pos = JSON.parse(localStorage.getItem(this.id));
			setPosition(this.elem, pos || {right: 20, bottom: 20});
			this.savePosition();
		}


		isAtTop() {
			var pos = getPosition(this.elem);
			return pos.top < 100 && pos.bottom > pos.top;
		}
	}


	class Debug
	{
		static init(content, dumps) {
			if (!document.documentElement.dataset) {
				throw new Error('Tracy requires IE 11+');
			}

			Debug.layer = document.createElement('div');
			Debug.layer.setAttribute('id', 'tracy-debug');
			Debug.layer.innerHTML = content;
			document.documentElement.appendChild(Debug.layer);
			evalScripts(Debug.layer);
			Tracy.Dumper.init();
			Debug.layer.style.display = 'block';
			Debug.bar.init();

			forEach(document.querySelectorAll('.tracy-panel'), panel => {
				Debug.panels[panel.id] = new Panel(panel.id);
				Debug.panels[panel.id].dumps = dumps;
				Debug.panels[panel.id].restorePosition();
			});

			Debug.captureWindow();
			Debug.captureAjax();
		}


		static loadAjax(content, dumps) {
			forEach(Debug.layer.querySelectorAll('.tracy-panel.tracy-ajax'), panel => {
				Debug.panels[panel.id].savePosition();
				delete Debug.panels[panel.id];
				panel.parentNode.removeChild(panel);
			});

			var ajaxBar = document.getElementById('tracy-ajax-bar');
			if (ajaxBar) {
				ajaxBar.parentNode.removeChild(ajaxBar);
			}

			Debug.layer.insertAdjacentHTML('beforeend', content);
			evalScripts(Debug.layer);
			ajaxBar = document.getElementById('tracy-ajax-bar');
			Debug.bar.elem.appendChild(ajaxBar);

			forEach(document.querySelectorAll('.tracy-panel'), panel => {
				if (!Debug.panels[panel.id]) {
					Debug.panels[panel.id] = new Panel(panel.id);
					Debug.panels[panel.id].dumps = dumps;
					Debug.panels[panel.id].restorePosition();
				}
			});

			Debug.bar.initTabs(ajaxBar);
		}


		static captureWindow() {
			var size = getWindowSize();

			window.addEventListener('resize', () => {
				var newSize = getWindowSize();

				Debug.bar.reposition(newSize.width - size.width, newSize.height - size.height);

				for (var id in Debug.panels) {
					Debug.panels[id].reposition(newSize.width - size.width, newSize.height - size.height);
				}

				size = newSize;
			});

			window.addEventListener('unload', () => {
				for (var id in Debug.panels) {
					Debug.panels[id].savePosition();
				}
			});
		}


		static captureAjax() {
			var header = Tracy.getAjaxHeader();
			if (!header) {
				return;
			}
			var oldOpen = XMLHttpRequest.prototype.open;

			XMLHttpRequest.prototype.open = function() {
				oldOpen.apply(this, arguments);
				if (window.TracyAutoRefresh !== false && arguments[1].indexOf('//') <= 0 || arguments[1].indexOf(location.origin + '/') === 0) {
					this.setRequestHeader('X-Tracy-Ajax', header);
					this.addEventListener('load', function() {
						if (this.getAllResponseHeaders().match(/^X-Tracy-Ajax: 1/mi)) {
							Debug.loadScript('?_tracy_bar=content-ajax.' + header + '&XDEBUG_SESSION_STOP=1&v=' + Math.random());
						}
					});
				}
			};

			if (window.fetch) {
				var oldFetch = window.fetch;
				window.fetch = function(request, options) {
					options = options || {};
					options.headers = new Headers(options.headers || {});
					var url = request instanceof Request ? request.url : request;

					if (window.TracyAutoRefresh !== false && url.indexOf('//') <= 0 || url.indexOf(location.origin + '/') === 0) {
						options.headers.set('X-Tracy-Ajax', header);
						options.credentials = (request instanceof Request && request.credentials) || options.credentials || 'same-origin';

						return oldFetch(request, options).then(function (response) {
							if (response.headers.has('X-Tracy-Ajax') && response.headers.get('X-Tracy-Ajax')[0] === '1') {
								Debug.loadScript('?_tracy_bar=content-ajax.' + header + '&XDEBUG_SESSION_STOP=1&v=' + Math.random());
							}

							return response;
						});
					}

					return oldFetch(request, options);
				};
			}
		}


		static loadScript(url) {
			if (Debug.scriptElem) {
				Debug.scriptElem.parentNode.removeChild(Debug.scriptElem);
			}
			Debug.scriptElem = document.createElement('script');
			Debug.scriptElem.src = url;
			if (nonce) {
				Debug.scriptElem.setAttribute('nonce', nonce);
			}
			document.documentElement.appendChild(Debug.scriptElem);
		}
	}


	function evalScripts(elem) {
		forEach(elem.getElementsByTagName('script'), script => {
			if ((!script.hasAttribute('type') || script.type === 'text/javascript' || script.type === 'application/javascript') && !script.tracyEvaluated) {
				var dolly = script.ownerDocument.createElement('script');
				dolly.textContent = script.textContent;
				if (nonce) {
					dolly.setAttribute('nonce', nonce);
				}
				script.ownerDocument.documentElement.appendChild(dolly);
				script.tracyEvaluated = true;
			}
		});
	}


	var dragging;

	function draggable(elem, options) {
		var dE = document.documentElement, started, deltaX, deltaY, clientX, clientY;
		options = options || {};

		var redraw = function () {
			if (dragging) {
				setPosition(elem, {left: clientX + deltaX, top: clientY + deltaY});
				requestAnimationFrame(redraw);
			}
		};

		var onMove = function(e) {
			if (e.buttons === 0) {
				return onEnd(e);
			}
			if (!started) {
				if (options.draggedClass) {
					elem.classList.add(options.draggedClass);
				}
				if (options.start) {
					options.start(e, elem);
				}
				started = true;
			}

			clientX = e.touches ? e.touches[0].clientX : e.clientX;
			clientY = e.touches ? e.touches[0].clientY : e.clientY;
			return false;
		};

		var onEnd = function(e) {
			if (started) {
				if (options.draggedClass) {
					elem.classList.remove(options.draggedClass);
				}
				if (options.stop) {
					options.stop(e, elem);
				}
			}
			dragging = null;
			dE.removeEventListener('mousemove', onMove);
			dE.removeEventListener('mouseup', onEnd);
			dE.removeEventListener('touchmove', onMove);
			dE.removeEventListener('touchend', onEnd);
			return false;
		};

		var onStart = function(e) {
			e.preventDefault();
			e.stopPropagation();

			if (dragging) { // missed mouseup out of window?
				return onEnd(e);
			}

			var pos = getPosition(elem);
			clientX = e.touches ? e.touches[0].clientX : e.clientX;
			clientY = e.touches ? e.touches[0].clientY : e.clientY;
			deltaX = pos.left - clientX;
			deltaY = pos.top - clientY;
			dragging = true;
			started = false;
			dE.addEventListener('mousemove', onMove);
			dE.addEventListener('mouseup', onEnd);
			dE.addEventListener('touchmove', onMove);
			dE.addEventListener('touchend', onEnd);
			requestAnimationFrame(redraw);
			if (options.start) {
				options.start(e, elem);
			}
		};

		forEach(options.handles, function (handle) {
			handle.addEventListener('mousedown', onStart);
			handle.addEventListener('touchstart', onStart);

			handle.addEventListener('click', function(e) {
				if (started) {
					e.stopImmediatePropagation();
				}
			});
		});
	}


	// returns total offset for element
	function getOffset(elem) {
		var res = {left: elem.offsetLeft, top: elem.offsetTop};
		while (elem = elem.offsetParent) { // eslint-disable-line no-cond-assign
			res.left += elem.offsetLeft; res.top += elem.offsetTop;
		}
		return res;
	}


	function getWindowSize() {
		return {
			width: document.documentElement.clientWidth,
			height: document.compatMode === 'BackCompat' ? window.innerHeight : document.documentElement.clientHeight
		};
	}


	// move to new position
	function setPosition(elem, coords) {
		var win = getWindowSize();
		if (typeof coords.right !== 'undefined') {
			coords.left = win.width - elem.offsetWidth - coords.right;
		}
		if (typeof coords.bottom !== 'undefined') {
			coords.top = win.height - elem.offsetHeight - coords.bottom;
		}
		elem.style.left = Math.max(0, Math.min(coords.left, win.width - elem.offsetWidth)) + 'px';
		elem.style.top = Math.max(0, Math.min(coords.top, win.height - elem.offsetHeight)) + 'px';
	}


	// returns current position
	function getPosition(elem) {
		var win = getWindowSize();
		return {
			left: elem.offsetLeft,
			top: elem.offsetTop,
			right: win.width - elem.offsetWidth - elem.offsetLeft,
			bottom: win.height - elem.offsetHeight - elem.offsetTop,
			width: elem.offsetWidth,
			height: elem.offsetHeight
		};
	}


	function forEach(arr, cb) {
		Array.prototype.forEach.call(arr, cb);
	}


	if (document.currentScript) {
		var nonce = document.currentScript.getAttribute('nonce') || document.currentScript.nonce;
		var contentId = document.currentScript.dataset.id;
	}

	Tracy = window.Tracy || {};
	Tracy.panelZIndex = Tracy.panelZIndex || 20000;
	Tracy.DebugPanel = Panel;
	Tracy.DebugBar = Bar;
	Tracy.Debug = Debug;
	Tracy.getAjaxHeader = () => contentId;

	Debug.bar = new Bar;
	Debug.panels = {};
})();
