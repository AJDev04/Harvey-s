/* Gedrag van de site: mobiel menu, hero-video, nieuwsbriefformulier en de reveal-animaties.
   Alle inhoud staat in index.html; hier staat enkel interactie. */

(function () {
  'use strict';

  document.documentElement.classList.remove('no-js');

  /* ---------- Mobiel menu ---------- */
  function setupNav() {
    var nav = document.querySelector('.nav');
    var toggle = document.querySelector('.nav__toggle');
    var list = document.querySelector('.nav__lijst');
    if (!nav || !toggle || !list) return;

    function zet(open) {
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      document.documentElement.classList.toggle('menu-open', open);
    }

    function isOpen() {
      return nav.classList.contains('is-open');
    }

    toggle.addEventListener('click', function () {
      zet(!isOpen());
    });

    // Menu sluit na een klik op een link, of op het doek naast het paneel
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a') || e.target === nav) zet(false);
    });

    // Escape sluit het menu en zet focus terug op de knop
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen()) {
        zet(false);
        toggle.focus();
      }
    });

    // Wordt het scherm breed genoeg voor het gewone menu, dan gaat het paneel dicht
    window.matchMedia('(min-width: 48em)').addEventListener('change', function (e) {
      if (e.matches) zet(false);
    });
  }

  /* ---------- Hero-video ----------
     Cloudflare Stream levert HLS. Safari speelt dat zelf af; andere browsers
     krijgen hls.js. Bij 'minder beweging' blijft enkel de poster staan. */
  function setupHeroVideo() {
    var video = document.querySelector('.hero__video');
    if (!video) return;
    var src = video.dataset.hls;
    if (!src) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      video.removeAttribute('autoplay');
      return;
    }

    function play() {
      var p = video.play();
      if (p && p.catch) p.catch(function () {});
    }

    if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = src;
      video.addEventListener('loadedmetadata', play, { once: true });
      return;
    }

    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';
    script.async = true;
    script.onload = function () {
      if (!window.Hls || !window.Hls.isSupported()) return;
      var hls = new window.Hls({ capLevelToPlayerSize: true });
      hls.loadSource(src);
      hls.attachMedia(video);
      hls.on(window.Hls.Events.MANIFEST_PARSED, play);
    };
    document.head.appendChild(script);
  }

  /* ---------- Nieuwsbrief ---------- */
  function setupNewsletter() {
    var form = document.querySelector('.nieuwsbrief__form');
    if (!form) return;
    var input = form.querySelector('input[type="email"]');
    var message = form.querySelector('.nieuwsbrief__bericht');
    if (!input || !message) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!input.checkValidity()) {
        message.textContent = 'Vul een geldig e-mailadres in.';
        input.focus();
        return;
      }
      // Mockup: er wordt nog niets verstuurd. Koppel hier later een echte dienst.
      message.textContent = 'Bedankt. U hoort binnenkort van ons.';
      form.reset();
    });
  }

  /* ---------- Tekst-reveal ----------
     Elk tekstblok onder de hero schuift omhoog en wordt zichtbaar zodra het
     in beeld komt. Wat samen in beeld komt, volgt elkaar op met een kleine
     vertraging. Zonder beweging (of zonder IntersectionObserver) staat alles
     gewoon meteen op zijn plaats. */
  function setupTekst() {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || !('IntersectionObserver' in window)) return;

    // Kandidaten: koppen, alinea's, kaarten en losse regels. Een blok dat al in
    // een gekozen blok zit, doet niet nog eens mee.
    var selector = [
      '.reveal h1', '.reveal h2', '.reveal h3', '.reveal p', '.reveal figure:not(.carrousel__slide)',
      '.reveal time', '.reveal li:not(.merk)', '.reveal address', '.reveal .beeld',
      '.reveal .carrousel', '.reveal .merkband', '.reveal .kaart-callout',
      '.reveal .nieuwsbrief__form', '.footer__logo', '.footer__meta > *'
    ].join(',');
    var blokken = Array.prototype.filter.call(document.querySelectorAll(selector), function (el) {
      return !el.parentElement.closest(selector);
    });
    if (!blokken.length) return;

    var stap = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--stap')) || 60;
    var maxVertraging = 900;

    blokken.forEach(function (el) {
      el.classList.add('tekst');
    });

    function toon(lijst) {
      lijst
        .sort(function (a, b) { return a.compareDocumentPosition(b) & 2 ? 1 : -1; })
        .forEach(function (el, i) {
          el.style.transitionDelay = Math.min(i * stap, maxVertraging) + 'ms';
          el.classList.add('is-in');
          observer.unobserve(el);
        });
    }

    var observer = new IntersectionObserver(
      function (entries) {
        // Alles wat in deze ronde in beeld komt, in documentvolgorde na elkaar
        toon(
          entries
            .filter(function (e) { return e.isIntersecting; })
            .map(function (e) { return e.target; })
        );
      },
      // Een blok telt als "in beeld" zodra het net boven de onderrand komt;
      // de beweging zelf is traag genoeg om op te vallen.
      { rootMargin: '0px 0px -6% 0px', threshold: 0 }
    );

    blokken.forEach(function (el) {
      observer.observe(el);
    });

    // Onderaan de pagina kan er niet verder gescrold worden; wat daar nog
    // onder de marge zit (de footer), tonen we dan alsnog.
    function onderaan() {
      if (window.innerHeight + window.scrollY < document.documentElement.scrollHeight - 2) return;
      toon(blokken.filter(function (el) { return !el.classList.contains('is-in'); }));
      window.removeEventListener('scroll', onderaan);
      window.removeEventListener('resize', onderaan);
    }
    window.addEventListener('scroll', onderaan, { passive: true });
    window.addEventListener('resize', onderaan);
    // Korte pagina's (disclaimer, privacy) kunnen soms helemaal niet scrollen
    window.addEventListener('load', onderaan);
  }

  /* ---------- Merkbanden ----------
     Voor een naadloze lus staat elke merkenlijst tweemaal in het spoor; de
     kopie is decoratief. Zonder beweging blijft er één lijst, die dan gewoon
     omloopt in een raster (css/beweging.css doet de rest). */
  function setupMerkbanden() {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('.merkband__spoor').forEach(function (spoor) {
      var lijst = spoor.querySelector('.merklijst');
      if (!lijst) return;
      spoor.style.setProperty('--n', lijst.children.length);
      if (reduced) {
        spoor.classList.add('is-stil');
        return;
      }
      var kopie = lijst.cloneNode(true);
      kopie.setAttribute('aria-hidden', 'true');
      kopie.querySelectorAll('img').forEach(function (img) {
        img.removeAttribute('loading');
      });
      spoor.appendChild(kopie);
    });
  }

  /* ---------- Carrousel (Over Harvey's) ----------
     Het spoor scrolt met scroll-snap, dus vegen en slepen werken vanzelf.
     Hier komen de pijlen, de teller, en het vanzelf doorschuiven met de
     voortgangslijn bij. Bij "minder beweging" schuift er niets vanzelf. */
  function setupCarrousel() {
    var wortel = document.getElementById('over-carrousel');
    if (!wortel) return;
    var spoor = wortel.querySelector('.carrousel__spoor');
    var slides = Array.prototype.slice.call(spoor.children);
    var teller = wortel.querySelector('.carrousel__nu');
    var voortgang = wortel.querySelector('.carrousel__voortgang');
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var interval = 6000;
    var huidig = 0;
    var timer = null;
    var stil = false; // muis erop, focus erin of aan het slepen: niet doorschuiven

    function index() {
      var links = spoor.scrollLeft;
      var beste = 0;
      var afstand = Infinity;
      slides.forEach(function (slide, i) {
        var d = Math.abs(slide.offsetLeft - links);
        if (d < afstand) { afstand = d; beste = i; }
      });
      return beste;
    }

    var knoppen = wortel.querySelectorAll('.carrousel__knoppen button');

    // Geen lus: bij de laatste foto stopt het
    function toon(i) {
      huidig = Math.max(0, Math.min(slides.length - 1, i));
      spoor.scrollTo({ left: slides[huidig].offsetLeft, behavior: reduced ? 'instant' : 'smooth' });
      werkTellerBij();
    }

    function werkTellerBij() {
      teller.textContent = String(huidig + 1).padStart(2, '0');
      knoppen.forEach(function (knop) {
        var doel = huidig + Number(knop.dataset.richting);
        knop.disabled = doel < 0 || doel >= slides.length;
      });
    }

    function herstartVoortgang() {
      if (reduced) return;
      voortgang.classList.remove('is-bezig');
      void voortgang.offsetWidth; // herstart de transitie
      voortgang.style.setProperty('--tijd', interval + 'ms');
      if (!stil) voortgang.classList.add('is-bezig');
    }

    function plan() {
      clearTimeout(timer);
      voortgang.classList.remove('is-bezig');
      if (reduced || huidig >= slides.length - 1) return; // laatste foto: klaar
      herstartVoortgang();
      if (stil) return;
      timer = setTimeout(function () {
        toon(huidig + 1);
      }, interval);
    }

    // Na scrollen (pijl, veeg of sleep) de teller bijwerken en opnieuw plannen
    var wachtScroll;
    spoor.addEventListener('scroll', function () {
      clearTimeout(wachtScroll);
      wachtScroll = setTimeout(function () {
        var i = index();
        if (i !== huidig) {
          huidig = i;
          werkTellerBij();
        }
        plan();
      }, 120);
    }, { passive: true });

    knoppen.forEach(function (knop) {
      knop.addEventListener('click', function () {
        toon(huidig + Number(knop.dataset.richting));
      });
    });

    spoor.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); toon(huidig + 1); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); toon(huidig - 1); }
    });

    function pauzeer() { stil = true; clearTimeout(timer); voortgang.classList.remove('is-bezig'); }
    function hervat() { stil = false; plan(); }
    wortel.addEventListener('pointerenter', pauzeer);
    wortel.addEventListener('pointerleave', hervat);
    wortel.addEventListener('focusin', pauzeer);
    wortel.addEventListener('focusout', function (e) {
      if (!wortel.contains(e.relatedTarget)) hervat();
    });

    // Pas doorschuiven als de carrousel in beeld is
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) plan();
          else { clearTimeout(timer); voortgang.classList.remove('is-bezig'); }
        });
      }, { threshold: 0.4 }).observe(wortel);
    } else {
      plan();
    }

    werkTellerBij();
  }

  /* ---------- Reveal van secties ---------- */
  function setupReveal() {
    var targets = document.querySelectorAll('.reveal');
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function show(el) {
      el.classList.add('is-visible');
    }

    if (reduced || !('IntersectionObserver' in window)) {
      targets.forEach(show);
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            show(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { rootMargin: '0px 0px -40px 0px', threshold: 0 }
    );

    targets.forEach(function (el) {
      observer.observe(el);
    });

    // Vangnet: wat na het laden al (deels) in beeld staat, toont meteen.
    targets.forEach(function (el) {
      if (el.getBoundingClientRect().top < window.innerHeight) show(el);
    });
  }

  setupNav();
  setupHeroVideo();
  setupNewsletter();
  setupTekst();
  setupMerkbanden();
  setupCarrousel();
  setupReveal();
})();
