(function () {
  'use strict';

  // Footer year
  var yr = document.getElementById('yr');
  if (yr) yr.textContent = new Date().getFullYear();

  // Sticky nav border on scroll
  var nav = document.querySelector('.nav');
  var onScroll = function () { nav.classList.toggle('scrolled', window.scrollY > 8); };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Mobile menu
  var btn = document.querySelector('.menu-btn');
  var menu = document.getElementById('menu');
  btn.addEventListener('click', function () {
    var open = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', String(!open));
    btn.setAttribute('aria-label', open ? 'Open menu' : 'Close menu');
    menu.classList.toggle('open', !open);
  });
  menu.addEventListener('click', function (e) {
    if (e.target.tagName === 'A') {
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', 'Open menu');
      menu.classList.remove('open');
    }
  });

  // Reveal on scroll
  var items = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    items.forEach(function (el, i) {
      el.style.transitionDelay = (i % 3) * 70 + 'ms';
      io.observe(el);
    });
  } else {
    items.forEach(function (el) { el.classList.add('in'); });
  }

  // Terminal typing animation (decorative)
  var term = document.getElementById('term');
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (term) {
    var lines = [
      ['cmd', '$ sv plan --project your-idea'],
      ['', '  scope ....... agreed'],
      ['cmd', '$ sv build --secure'],
      ['', '  tests ....... <span class="ok">passing</span>'],
      ['', '  audit ....... <span class="ok">clean</span>'],
      ['cmd', '$ sv deploy --env production'],
      ['', '  <span class="ok">✓</span> live in <span class="ac">production</span>'],
      ['cmd', '$ sv support --mode always-on']
    ];
    var html = lines.map(function (l) { return '<span class="' + l[0] + '">' + l[1] + '</span>'; });
    if (reduce) {
      term.innerHTML = html.join('\n') + '\n<span class="cursor"></span>';
    } else {
      var i = 0;
      var tick = function () {
        term.innerHTML = html.slice(0, i).join('\n') + (i ? '\n' : '') + '<span class="cursor"></span>';
        if (i < html.length) { i++; setTimeout(tick, lines[i - 1][0] === 'cmd' ? 700 : 320); }
      };
      setTimeout(tick, 500);
    }
  }

  // Contact form: show result from ?sent=1 / ?error=..., and validate before submit
  var form = document.querySelector('.form');
  var notice = form.querySelector('.notice');
  // Page-load timestamp for the server-side bot timing check
  form.elements.ts.value = Math.floor(Date.now() / 1000);
  var show = function (msg, ok) {
    notice.textContent = msg;
    notice.className = 'notice ' + (ok ? 'ok' : 'err');
    notice.hidden = false;
  };
  var params = new URLSearchParams(window.location.search);
  if (params.get('sent') === '1') {
    show('Thanks — your message has been sent. We will get back to you within one business day.', true);
  } else if (params.has('error')) {
    show('Sorry, your message could not be sent. Please check the fields, or email us directly at amritanshuverma1993@gmail.com.', false);
  }
  if (params.has('sent') || params.has('error')) {
    history.replaceState(null, '', window.location.pathname + '#contact');
    document.getElementById('contact').scrollIntoView();
  }

  form.addEventListener('submit', function (e) {
    var bad = [];
    ['name', 'email', 'message'].forEach(function (n) {
      var f = form.elements[n];
      var v = f.value.trim();
      var invalid = !v || (n === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));
      f.classList.toggle('invalid', invalid);
      if (invalid) bad.push(f);
    });
    if (bad.length) {
      e.preventDefault();
      show('Please fill in your name, a valid email and a message.', false);
      bad[0].focus();
    }
  });
})();
