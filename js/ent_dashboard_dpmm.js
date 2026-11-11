/* ==============================================================================
   FILE: js/ent_dashboard_dpmm.js
   PURPOSE: Tab switching & iframe navigation for Direct Payments workspace.
   ============================================================================== */

(function () {
    'use strict';

    var frame = document.getElementById('paymentFrame');
    var title = document.getElementById('paymentTitle');
    var loading = document.getElementById('paymentLoading');
    var tabs = document.querySelectorAll('.payment-tab');
    var themeUrl = '';

    try {
        themeUrl = new URL('css/payment-content.css', window.location.href).href;
    } catch (e) {
        themeUrl = 'css/payment-content.css';
    }

    // Function to activate a selected tab
    function selectTab(tab, navigate) {
        if (!tab) return;

        // 1. Update visual active state on tabs
        for (var i = 0; i < tabs.length; i++) {
            var active = (tabs[i] === tab);
            if (active) {
                tabs[i].classList.add('active');
                tabs[i].setAttribute('aria-current', 'page');
            } else {
                tabs[i].classList.remove('active');
                tabs[i].removeAttribute('aria-current');
            }
        }

        // 2. Update page title and iframe title
        var tabText = tab.textContent.trim();
        if (title) {
            title.textContent = tabText;
        }
        document.title = 'Direct Payments - ' + tabText;

        // 3. Navigate iframe if requested
        if (frame) {
            frame.title = tabText;
            if (navigate) {
                if (loading) loading.hidden = false;
                frame.style.visibility = 'hidden';
                var targetHref = tab.getAttribute('href');
                if (targetHref) {
                    frame.src = targetHref;
                }
            }
        }
    }

    // Select tab based on URL hash (e.g. #amend, #approvals)
    function fromHash() {
        var hash = window.location.hash.slice(1) || 'add';
        for (var i = 0; i < tabs.length; i++) {
            if (tabs[i].getAttribute('data-section') === hash) {
                selectTab(tabs[i], true);
                return;
            }
        }
    }

    // Keep the highlight aligned with the actual iframe page after navigation,
    // form submissions and browser history, not only after a tab click.
    function syncLoadedTab() {
        var current = new URL(frame.contentWindow.location.href);
        for (var i = 0; i < tabs.length; i++) {
            var target = new URL(tabs[i].getAttribute('href'), window.location.href);
            if (current.pathname !== target.pathname) continue;
            var mode = target.searchParams.get('cmdopt');
            if (mode && current.searchParams.get('cmdopt') !== mode) {
                // POST responses may omit cmdopt from the URL. Retain the
                // currently selected Add/Amend tab in that case.
                if (current.searchParams.has('cmdopt') || !tabs[i].classList.contains('active')) continue;
            }
            selectTab(tabs[i], false);
            window.history.replaceState(null, '', '#' + tabs[i].getAttribute('data-section'));
            return;
        }
    }

    // Bind click events on all tabs
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function (event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();

            // Immediately activate clicked tab
            selectTab(this, true);

            // Update URL hash cleanly without forcing page jump
            var section = this.getAttribute('data-section');
            if (section) {
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', '#' + section);
                } else {
                    window.location.hash = section;
                }
            }
        });
    }

    // Handle iframe loading & inject dark theme styles
    if (frame) {
        frame.addEventListener('load', function () {
            function reveal() {
                if (loading) loading.hidden = true;
                frame.style.visibility = 'visible';
            }

            try {
                var page = frame.contentDocument;
                var path = frame.contentWindow.location.pathname;

                // If redirected to login or logout, escape iframe
                if (/\/(login|dashboard|logout)\.php$/.test(path)) {
                    window.location.href = frame.contentWindow.location.href;
                    return;
                }

                syncLoadedTab();

                if (page && page.documentElement) {
                    page.documentElement.setAttribute('data-bs-theme', 'dark');
                    var css = page.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = themeUrl;
                    css.onload = reveal;
                    css.onerror = reveal;
                    if (page.head) {
                        page.head.appendChild(css);
                    } else {
                        reveal();
                    }
                } else {
                    reveal();
                }
            } catch (error) {
                reveal();
            }
        });
    }

    // Listen for hash change in browser history
    window.addEventListener('hashchange', fromHash);

    // Initial check on page load
    if (window.location.hash) {
        fromHash();
    }
})();
