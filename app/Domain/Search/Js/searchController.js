/**
 * Header global search: keyboard shortcut, dropdown open/close, arrow-key navigation and
 * desktop positioning between the two header lists.
 *
 * HTMX runs with allowEval=false, so every keyboard behaviour lives here instead of in
 * hx-trigger filters. The dropdown markup itself comes from /hx/search/quick/get.
 */
leantime.searchController = (function () {
    var MIN_CHARS = 2;
    var DESKTOP_MIN_WIDTH = 1201;
    var GAP = 16;

    var root, input, results, toggle;
    var isMac = /Mac|iPhone|iPad/.test(navigator.platform);

    function termLength() {
        return input.value.trim().length;
    }

    function isTypingContext(element) {
        if (!element) {
            return false;
        }
        var tag = element.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || element.isContentEditable;
    }

    function options() {
        return Array.prototype.slice.call(results.querySelectorAll('.searchResult'));
    }

    // Focus stays on the input; aria-activedescendant tells assistive tech which option is active.
    function clearActive() {
        options().forEach(function (option) {
            option.classList.remove('active');
            option.removeAttribute('aria-selected');
        });
        input.removeAttribute('aria-activedescendant');
    }

    function setActive(option) {
        clearActive();
        option.classList.add('active');
        option.setAttribute('aria-selected', 'true');
        if (option.id) {
            input.setAttribute('aria-activedescendant', option.id);
        }
        option.scrollIntoView({ block: 'nearest' });
    }

    function moveActive(delta) {
        var list = options();
        if (!list.length) {
            return;
        }
        var current = list.findIndex(function (option) { return option.classList.contains('active'); });
        var next = current + delta;
        if (next < 0) {
            next = list.length - 1;
        }
        if (next >= list.length) {
            next = 0;
        }
        setActive(list[next]);
    }

    function openResults() {
        if (results.children.length === 0) {
            return;
        }
        root.classList.add('open');
        input.setAttribute('aria-expanded', 'true');
    }

    function closeResults() {
        root.classList.remove('open');
        input.setAttribute('aria-expanded', 'false');
        clearActive();
    }

    function expand() {
        root.classList.add('expanded');
        input.focus();
        input.select();
        if (termLength() >= MIN_CHARS) {
            openResults();
        }
    }

    function collapse() {
        closeResults();
        root.classList.remove('expanded');
    }

    // Desktop: dock the bar to the left of the icon list on the right. The list is a float,
    // so its width is only known at runtime; the CSS width keeps the bar short.
    function positionDesktop() {
        if (window.innerWidth < DESKTOP_MIN_WIDTH) {
            root.style.right = '';
            return;
        }
        var headerInner = root.closest('.headerinner');
        var rightList = root.parentElement;
        if (!headerInner || !rightList) {
            return;
        }
        var headerRect = headerInner.getBoundingClientRect();
        var rightItems = Array.prototype.filter.call(rightList.children, function (item) { return item !== root; });
        var rightEdge = headerRect.right - GAP;
        rightItems.forEach(function (item) {
            var rect = item.getBoundingClientRect();
            if (rect.width > 0 && rect.left < rightEdge) {
                rightEdge = rect.left;
            }
        });
        root.style.right = Math.round(headerRect.right - rightEdge + GAP) + 'px';
    }

    function bindEvents() {
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            if (root.classList.contains('expanded')) {
                collapse();
            } else {
                expand();
            }
        });

        document.addEventListener('keydown', function (event) {
            var key = event.key ? event.key.toLowerCase() : '';
            var commandK = (event.metaKey || event.ctrlKey) && !event.shiftKey && !event.altKey && key === 'k';
            var slash = key === '/' && !event.metaKey && !event.ctrlKey && !event.altKey;
            if (!commandK && !slash) {
                return;
            }
            // Editors own Ctrl+K (TipTap link) and "/" is normal typing elsewhere.
            if (document.activeElement === input || isTypingContext(document.activeElement)) {
                return;
            }
            event.preventDefault();
            expand();
        });

        input.addEventListener('keydown', function (event) {
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    openResults();
                    moveActive(1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    moveActive(-1);
                    break;
                case 'Enter':
                    var active = results.querySelector('.searchResult.active');
                    if (active) {
                        event.preventDefault();
                        active.click();
                    } else if (termLength() < MIN_CHARS) {
                        event.preventDefault();
                    }
                    // Otherwise the form submits to the full results page.
                    break;
                case 'Escape':
                    event.preventDefault();
                    if (root.classList.contains('open')) {
                        closeResults();
                    } else {
                        input.value = '';
                        input.blur();
                        collapse();
                    }
                    break;
            }
        });

        // Don't hit the server for one character; the dropdown just closes.
        input.addEventListener('htmx:beforeRequest', function (event) {
            if (termLength() < MIN_CHARS) {
                event.preventDefault();
                closeResults();
            }
        });

        input.addEventListener('focus', function () {
            root.classList.add('expanded');
            if (termLength() >= MIN_CHARS) {
                openResults();
            }
        });

        results.addEventListener('htmx:afterSwap', function () {
            if (termLength() >= MIN_CHARS && root.contains(document.activeElement)) {
                openResults();
            }
        });

        results.addEventListener('mousemove', function (event) {
            var option = event.target.closest('.searchResult');
            if (option && !option.classList.contains('active')) {
                setActive(option);
            }
        });

        results.addEventListener('click', function (event) {
            var option = event.target.closest('.searchResult');
            if (!option) {
                return;
            }
            closeResults();
            // Hash links open a modal on this page; nothing reloads, so tidy the bar up.
            if (option.getAttribute('href').charAt(0) === '#') {
                input.blur();
                collapse();
            }
        });

        document.addEventListener('click', function (event) {
            if (root.contains(event.target)) {
                return;
            }
            closeResults();
            if (termLength() === 0) {
                root.classList.remove('expanded');
            }
        });

        window.addEventListener('resize', positionDesktop);
        // The project selector swaps over HTMX and can change the left list's width.
        document.body.addEventListener('htmx:afterSettle', positionDesktop);
    }

    function init() {
        root = document.getElementById('globalSearch');
        if (!root) {
            return;
        }
        input = document.getElementById('globalSearchInput');
        results = document.getElementById('globalSearchResults');
        toggle = root.querySelector('.globalSearch__toggle');

        if (!isMac) {
            var hint = root.querySelector('.globalSearch__kbd');
            if (hint) {
                hint.textContent = 'Ctrl K';
            }
        }

        bindEvents();
        positionDesktop();
        // Fonts and avatars can still be loading when DOMContentLoaded fires.
        window.addEventListener('load', positionDesktop);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return {
        focus: expand,
        close: closeResults,
    };
})();
