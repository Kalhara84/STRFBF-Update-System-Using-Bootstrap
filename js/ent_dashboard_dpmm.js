/* Keep payment pages in the dashboard workspace, including form submissions. */
(function () {
    'use strict';
    var frame = document.getElementById('paymentFrame');
    var title = document.getElementById('paymentTitle');
    var loading = document.getElementById('paymentLoading');
    var tabs = document.querySelectorAll('.payment-tab');
    var themeUrl = new URL('css/payment-content.css', window.location.href).href;

    function selectTab(tab, navigate) {
        for (var i = 0; i < tabs.length; i++) {
            var active = tabs[i] === tab;
            tabs[i].classList.toggle('active', active);
            if (active) tabs[i].setAttribute('aria-current', 'page');
            else tabs[i].removeAttribute('aria-current');
        }
        title.textContent = tab.textContent;
        frame.title = tab.textContent;
        document.title = 'Direct Payments ? ' + tab.textContent;
        if (navigate) {
            loading.hidden = false;
            frame.style.visibility = 'hidden';
            frame.src = tab.href;
        }
    }

    function fromHash() {
        var section = window.location.hash.slice(1) || 'add';
        for (var i = 0; i < tabs.length; i++) {
            if (tabs[i].getAttribute('data-section') === section) {
                selectTab(tabs[i], true);
                return;
            }
        }
        selectTab(tabs[0], true);
    }

    for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function (event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            var hash = '#' + this.getAttribute('data-section');
            if (window.location.hash === hash) return;
            window.location.hash = hash;
        });
    }

    frame.addEventListener('load', function () {
        function reveal() {
            loading.hidden = true;
            frame.style.visibility = 'visible';
        }
        try {
            var page = frame.contentDocument;
            var path = frame.contentWindow.location.pathname;
            if (/\/(login|dashboard|logout)\.php$/.test(path)) {
                window.location.href = frame.contentWindow.location.href;
                return;
            }
            page.documentElement.setAttribute('data-bs-theme', 'dark');
            var css = page.createElement('link');
            css.rel = 'stylesheet';
            css.href = themeUrl;
            css.onload = reveal;
            css.onerror = reveal;
            page.head.appendChild(css);
        } catch (error) {
            reveal();
        }
    });
    window.addEventListener('hashchange', fromHash);
    if (window.location.hash) fromHash();
})();
