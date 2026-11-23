/* 墨记 Moji Notes 前端脚本 */
(function () {
	'use strict';
	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
	var root = document.documentElement;

	/* ---------- 深色模式 ---------- */
	var themeBtn = $('#theme-toggle');
	if (themeBtn) {
		themeBtn.addEventListener('click', function () {
			var cur = root.getAttribute('data-theme') ||
				(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
			var next = cur === 'dark' ? 'light' : 'dark';
			root.setAttribute('data-theme', next);
			try { localStorage.setItem('moji-theme', next); } catch (e) {}
		});
	}

	/* ---------- 手机菜单 ---------- */
	var menuBtn = $('#menu-toggle'), nav = $('#site-nav');
	if (menuBtn && nav) {
		menuBtn.addEventListener('click', function () {
			var open = nav.classList.toggle('open');
			menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	/* ---------- 复制 ---------- */
	function copyText(text, btn, label) {
		var done = function () {
			btn.textContent = '已复制 ✓';
			setTimeout(function () { btn.textContent = label; }, 1500);
		};
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(done, function () { fallback(text); done(); });
		} else { fallback(text); done(); }
	}
	function fallback(text) {
		var ta = document.createElement('textarea');
		ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
		document.body.appendChild(ta); ta.select();
		try { document.execCommand('copy'); } catch (e) {}
		document.body.removeChild(ta);
	}
	$$('[data-copy]').forEach(function (b) {
		var label = b.textContent;
		b.addEventListener('click', function () {
			var el = $(b.getAttribute('data-copy'));
			if (el) copyText(el.textContent.trim(), b, label);
		});
	});

	/* ---------- 失效图片：显示提示框而不是空白 ---------- */
	function markLost(img) {
		if (img.getAttribute('data-lost')) return;
		img.setAttribute('data-lost', '1');
		var box = document.createElement('span');
		box.className = 'img-lost';
		var host = '';
		try { host = new URL(img.currentSrc || img.src, location.href).host; } catch (e) {}
		box.innerHTML = '<b>图片加载失败</b><small></small>';
		box.querySelector('small').textContent = host ? '原图在 ' + host + ' 上已经找不到了' : '原图已经找不到了';
		img.style.display = 'none';
		img.parentNode.insertBefore(box, img.nextSibling);
	}
	$$('.prose img').forEach(function (img) {
		if (img.complete && img.naturalWidth === 0 && img.src) markLost(img);
		else img.addEventListener('error', function () { markLost(img); });
	});

	/* ---------- 代码块：加标题栏和复制按钮 ---------- */
	$$('.prose pre').forEach(function (pre) {
		if (pre.parentNode.classList.contains('code')) return;
		var code = pre.querySelector('code');
		var lang = '';
		var m = ((code && code.className) || pre.className || '').match(/(?:language|lang)-([\w+#-]+)/);
		if (m) lang = m[1];
		var box = document.createElement('div');
		box.className = 'code';
		var bar = document.createElement('div');
		bar.className = 'code-bar';
		bar.innerHTML = '<span class="dots"><i></i><i></i><i></i></span><span></span><button type="button">复制</button>';
		bar.children[1].textContent = pre.getAttribute('data-title') || lang;
		pre.parentNode.insertBefore(box, pre);
		box.appendChild(bar);
		box.appendChild(pre);
		var btn = bar.querySelector('button');
		btn.addEventListener('click', function () { copyText(pre.innerText, btn, '复制'); });
	});

	/* ---------- 浏览量：同一浏览器 6 小时内只算一次 ---------- */
	(function () {
		var cfg = window.MOJI || {};
		if (!cfg.viewApi || !window.fetch) return;
		var k = 'moji-v-' + cfg.postId, now = Date.now();
		try { if (now - (+localStorage.getItem(k) || 0) < 6 * 3600 * 1000) return; localStorage.setItem(k, String(now)); } catch (e) {}
		fetch(cfg.viewApi, { method: 'POST', credentials: 'omit' }).then(function (r) { return r.ok ? r.json() : null; }).then(function (d) {
			if (!d || !d.counted) return;
			var n = d.views, s = n >= 10000 ? (Math.round(n / 1000) / 10) + '万' : n.toLocaleString('en-US');
			$$('[data-views]').forEach(function (el) { el.textContent = s; });
		}).catch(function () {});
	})();

	/* ---------- 侧栏：复制链接、回到顶部 ---------- */
	$$('[data-copy-link]').forEach(function (b) {
		b.addEventListener('click', function () {
			var url = b.getAttribute('data-copy-link'), old = b.textContent;
			var done = function () { b.textContent = '已复制 ✓'; b.classList.add('ok'); setTimeout(function () { b.textContent = old; b.classList.remove('ok'); }, 1600); };
			if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () { window.prompt('复制这个链接', url); });
			else window.prompt('复制这个链接', url);
		});
	});
	$$('[data-to-top]').forEach(function (b) {
		b.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
	});

	/* ---------- 目录高亮与阅读进度 ---------- */
	var toc = $('#toc'), prose = $('#prose');
	if (toc && prose) {
		var links = $$('a[data-t]', toc);
		var heads = links.map(function (a) { return document.getElementById(a.getAttribute('data-t')); });
		var bar = $('#toc-bar'), pct = $('#toc-pct');
		var ticking = false;
		var update = function () {
			ticking = false;
			var r = prose.getBoundingClientRect();
			var total = r.height - window.innerHeight * 0.6;
			var p = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : 1;
			if (bar) bar.style.width = (p * 100).toFixed(1) + '%';
			if (pct) pct.textContent = Math.round(p * 100) + '%';
			var active = 0;
			heads.forEach(function (h, i) { if (h && h.getBoundingClientRect().top < 140) active = i; });
			links.forEach(function (a, i) { a.classList.toggle('on', i === active); });
		};
		window.addEventListener('scroll', function () {
			if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
		}, { passive: true });
		update();
	}

	/* ---------- 搜索弹窗 ---------- */
	var modal = $('#search-modal'), input = $('#search-input'), results = $('#search-results'), all = $('#search-all');
	if (!modal || !input) return;
	var cfg = window.MOJI || {};
	var timer = null, seq = 0, lastFocus = null;
	var initial = results.innerHTML;

	function openSearch() {
		lastFocus = document.activeElement;
		modal.hidden = false;
		document.body.style.overflow = 'hidden';
		input.focus(); input.select();
	}
	function closeSearch() {
		modal.hidden = true;
		document.body.style.overflow = '';
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}
	$$('[data-open-search]').forEach(function (el) {
		el.addEventListener('click', function (e) { e.preventDefault(); openSearch(); });
	});
	modal.addEventListener('click', function (e) { if (e.target === modal) closeSearch(); });

	function esc(s) {
		return String(s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	function hl(text, q) {
		var safe = esc(text);
		if (!q) return safe;
		var re = new RegExp('(' + esc(q).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
		return safe.replace(re, '<mark>$1</mark>');
	}
	function item(o) {
		return '<a class="c-' + esc(o.color) + '" href="' + esc(o.url) + '"><span class="sw"></span><b>' + o.title +
			'</b><span class="k">' + esc(o.k) + '</span><small>' + o.sub + '</small></a>';
	}
	function render(data, q) {
		var html = '';
		if (data.posts && data.posts.length) {
			html += '<h5>笔记</h5>' + data.posts.map(function (p) {
				return item({ color: p.color, url: p.url, title: hl(p.title, q), k: p.stage + ' · ' + p.date, sub: hl(p.snippet, q) });
			}).join('');
		}
		if (data.terms && data.terms.length) {
			html += '<h5>专题与标签</h5>' + data.terms.map(function (t) {
				return item({ color: t.color, url: t.url, title: hl(t.name, q), k: t.count + ' 篇', sub: esc(t.kind + (t.desc ? ' · ' + t.desc : '')) });
			}).join('');
		}
		if (!html) html = '<div class="tip"><b>没找到</b>换个关键词，或者试试英文名</div>';
		results.innerHTML = html;
		var first = $('a', results);
		if (first) first.classList.add('sel');
		if (all) {
			all.hidden = !(data.total > (data.posts ? data.posts.length : 0));
			all.textContent = '查看全部 ' + data.total + ' 条 →';
			all.href = data.searchUrl || (cfg.searchUrl + '?s=' + encodeURIComponent(q));
		}
	}
	function search(q) {
		var my = ++seq;
		if (!q) { results.innerHTML = initial; if (all) all.hidden = true; return; }
		results.setAttribute('aria-busy', 'true');
		fetch(cfg.searchApi + (cfg.searchApi.indexOf('?') > -1 ? '&' : '?') + 'q=' + encodeURIComponent(q), { credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
			.then(function (d) { if (my === seq) render(d, q); })
			.catch(function () {
				if (my === seq) results.innerHTML = '<div class="tip"><b>搜索出错了</b>按回车跳到搜索结果页</div>';
			})
			.then(function () { results.removeAttribute('aria-busy'); });
	}
	input.addEventListener('input', function () {
		clearTimeout(timer);
		var q = input.value.trim();
		timer = setTimeout(function () { search(q); }, 180);
	});

	document.addEventListener('keydown', function (e) {
		var k = e.key;
		if ((e.metaKey || e.ctrlKey) && k && k.toLowerCase() === 'k') {
			e.preventDefault();
			if (modal.hidden) openSearch(); else closeSearch();
			return;
		}
		if (modal.hidden) {
			if (k === '/' && !/input|textarea|select/i.test(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
				e.preventDefault(); openSearch();
			}
			return;
		}
		if (k === 'Escape') { closeSearch(); return; }
		if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'Enter') {
			var items = $$('a', results);
			var i = items.findIndex(function (a) { return a.classList.contains('sel'); });
			if (k === 'Enter') {
				if (items[i]) { e.preventDefault(); window.location.href = items[i].href; }
				return; // 没有选中项时提交表单，跳到搜索结果页
			}
			if (!items.length) return;
			e.preventDefault();
			i = k === 'ArrowDown' ? Math.min(items.length - 1, i + 1) : Math.max(0, i - 1);
			items.forEach(function (a, j) { a.classList.toggle('sel', j === i); });
			items[i].scrollIntoView({ block: 'nearest' });
		}
	});
})();
