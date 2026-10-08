/* Gedrag van de site: menu, header, hero, de reveal-animaties en de kleine interacties.
   Alle inhoud staat in index.html; hier staat enkel interactie. */

(function () {
  'use strict';

  document.documentElement.classList.remove('no-js');

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  // Muis-effecten (spotlight, magneet, kantelen) enkel met een echte muis
  var muis = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  // Roept fn hoogstens één keer per frame op, hoe vaak het event ook komt
  function perFrame(fn) {
    var wacht = false;
    return function () {
      if (wacht) return;
      wacht = true;
      requestAnimationFrame(function () {
        wacht = false;
        fn();
      });
    };
  }

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

    // Menu sluit na een klik op een link
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) zet(false);
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

  /* ---------- Header ----------
     Eens gescrold krijgt de header een vlak. Naar beneden scrollen schuift ze weg,
     naar boven scrollen haalt ze terug; kleine bewegingen tellen niet. */
  function setupHeader() {
    var header = document.querySelector('.header');
    if (!header) return;
    var vorige = window.scrollY;

    var werkBij = perFrame(function () {
      var y = window.scrollY;
      header.classList.toggle('is-gescrold', y > 8);
      if (Math.abs(y - vorige) < 6) return;
      header.classList.toggle('is-weg', y > vorige && y > window.innerHeight * 0.3);
      vorige = y;
    });

    window.addEventListener('scroll', werkBij, { passive: true });
    // Met het toetsenbord in de header: altijd in beeld
    header.addEventListener('focusin', function () {
      header.classList.remove('is-weg');
    });
    werkBij();
  }

  /* ---------- Hero ----------
     --p loopt van 0 (klein videovenster onder het logo) tot 1 (video over het hele
     scherm) terwijl de hero blijft staan; css/components.css maakt er een clip-path
     van. Boven het oranje volgt een zachte spotlight de muis (--mx/--my). */
  function setupHero() {
    var hero = document.querySelector('.hero');
    if (!hero) return;
    var plak = hero.querySelector('.hero__plak');

    if (!reduced && muis && plak) {
      var px = 0;
      var py = 0;
      var spot = perFrame(function () {
        var r = plak.getBoundingClientRect();
        plak.style.setProperty('--mx', (((px - r.left) / r.width) * 100).toFixed(2) + '%');
        plak.style.setProperty('--my', (((py - r.top) / r.height) * 100).toFixed(2) + '%');
      });
      plak.addEventListener('pointermove', function (e) {
        px = e.clientX;
        py = e.clientY;
        spot();
      });
    }

    if (reduced) return;

    var werkBij = perFrame(function () {
      var r = hero.getBoundingClientRect();
      var weg = hero.offsetHeight - window.innerHeight;
      // het venster is open na 80% van de weg; de rest blijft de video staan
      var p = weg > 0 ? Math.min(1, Math.max(0, -r.top / (weg * 0.8))) : 0;
      hero.style.setProperty('--p', p.toFixed(4));
      hero.classList.toggle('is-donker', p > 0.45);
    });

    window.addEventListener('scroll', werkBij, { passive: true });
    window.addEventListener('resize', werkBij);
    werkBij();
  }

  /* ---------- Hero-video ----------
     Cloudflare Stream levert HLS. Safari speelt dat zelf af; andere browsers
     krijgen hls.js. Bij 'minder beweging' blijft enkel de poster staan. Uit beeld
     pauzeert de video. */
  function setupHeroVideo() {
    var video = document.querySelector('.hero__video');
    if (!video) return;
    var src = video.dataset.hls;
    if (!src) return;

    if (reduced) {
      video.removeAttribute('autoplay');
      return;
    }

    function play() {
      var p = video.play();
      if (p && p.catch) p.catch(function () {});
    }

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!video.currentSrc && !video.src) return;
          if (entry.isIntersecting) play();
          else video.pause();
        });
      }).observe(video.closest('.hero') || video);
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

  /* ---------- Band onder de hero ----------
     Twee rijen grote tekst die traag voorbijschuiven, in tegengestelde richting.
     Scrollen geeft ze een duw en bepaalt de richting. De tekst staat zo vaak in de
     rij als nodig om het scherm te vullen; één tekstbreedte verschuiven is één lus. */
  function setupBand() {
    var band = document.querySelector('.band');
    if (!band || reduced) return;
    var tempo = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--band-tempo')) || 60;
    var rijen = [];

    function vul() {
      rijen = Array.prototype.map.call(band.querySelectorAll('.band__rij'), function (rij) {
        var teksten = rij.querySelectorAll('.band__tekst');
        var breedte = teksten[0].offsetWidth;
        var nodig = Math.ceil((window.innerWidth * 2) / breedte) + 1;
        for (var k = teksten.length; k < nodig; k++) rij.appendChild(teksten[0].cloneNode(true));
        return { el: rij, breedte: breedte, richting: Number(rij.dataset.richting) || -1, x: 0 };
      });
    }

    var loopt = false;
    var vorigeT = 0;
    var vorigeY = window.scrollY;
    var zin = 1; // 1: naar beneden gescrold, -1: naar boven
    var duw = 0;

    function stap(t) {
      if (!loopt) return;
      var dt = vorigeT ? Math.min(64, t - vorigeT) / 1000 : 0;
      vorigeT = t;
      var y = window.scrollY;
      var dy = y - vorigeY;
      vorigeY = y;
      if (dy) zin = dy > 0 ? 1 : -1;
      // de snelheid van het scrollen geeft een duw die langzaam uitdooft
      var snelheid = dt ? Math.abs(dy) / dt : 0;
      duw += (snelheid * 0.4 - duw) * 0.08;
      rijen.forEach(function (r) {
        r.x += r.richting * zin * (tempo + duw) * dt;
        r.x = ((r.x % r.breedte) - r.breedte) % r.breedte;
        r.el.style.transform = 'translate3d(' + r.x.toFixed(2) + 'px, 0, 0)';
      });
      requestAnimationFrame(stap);
    }

    function start() {
      if (loopt) return;
      loopt = true;
      vorigeT = 0;
      vorigeY = window.scrollY;
      requestAnimationFrame(stap);
    }

    // pas meten als Michroma geladen is, anders klopt de breedte niet
    var klaar = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
    klaar.then(function () {
      vul();
      window.addEventListener('resize', vul);
      if (!('IntersectionObserver' in window)) return start();
      new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) start();
          else loopt = false;
        });
      }).observe(band);
    });
  }

  /* ---------- Titels ----------
     Elke .kop wordt in woorden en letters gesplitst; de letters komen één voor
     één omhoog als de titel in beeld komt (css/beweging.css). Schermlezers lezen
     de titel als één geheel. */
  function setupKoppen() {
    if (reduced || !('IntersectionObserver' in window)) return;
    var koppen = document.querySelectorAll('.kop');
    if (!koppen.length) return;

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-in');
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: '0px 0px -8% 0px', threshold: 0 }
    );

    koppen.forEach(function (kop) {
      var tekst = kop.textContent.trim();
      var leesbaar = document.createElement('span');
      leesbaar.className = 'visually-hidden';
      leesbaar.textContent = tekst;
      var beeld = document.createElement('span');
      beeld.setAttribute('aria-hidden', 'true');
      var i = 0;
      tekst.split(/\s+/).forEach(function (woord, w) {
        if (w > 0) beeld.appendChild(document.createTextNode(' '));
        var vak = document.createElement('span');
        vak.className = 'kop__woord';
        Array.from(woord).forEach(function (letter) {
          var l = document.createElement('span');
          l.className = 'kop__letter';
          l.style.setProperty('--i', i++);
          l.textContent = letter;
          vak.appendChild(l);
        });
        beeld.appendChild(vak);
      });
      kop.textContent = '';
      kop.append(leesbaar, beeld);
      kop.classList.add('is-gesplitst');
      observer.observe(kop);
    });
  }

  /* ---------- Statement (Over Harvey's) ----------
     De woorden lichten één voor één op terwijl de uitspraak door het scherm
     schuift: elk woord krijgt --aan van 0 tot 1. */
  function setupStatement() {
    if (reduced) return;
    document.querySelectorAll('.statement').forEach(function (st) {
      var woorden = [];
      (function splits(el) {
        Array.prototype.slice.call(el.childNodes).forEach(function (node) {
          if (node.nodeType === 1) return splits(node);
          if (node.nodeType !== 3) return;
          var stuk = document.createDocumentFragment();
          node.textContent.split(/(\s+)/).forEach(function (deel) {
            if (!deel) return;
            if (/^\s+$/.test(deel)) return stuk.appendChild(document.createTextNode(deel));
            var s = document.createElement('span');
            s.className = 'statement__woord';
            s.textContent = deel;
            woorden.push(s);
            stuk.appendChild(s);
          });
          el.replaceChild(stuk, node);
        });
      })(st);
      st.classList.add('is-gesplitst');

      var werkBij = perFrame(function () {
        var r = st.getBoundingClientRect();
        var h = window.innerHeight;
        // begint als de bovenkant op 90% van het scherm staat, klaar als de
        // onderkant op 50% staat
        var p = Math.min(1, Math.max(0, (h * 0.9 - r.top) / (r.height + h * 0.4)));
        var n = woorden.length;
        woorden.forEach(function (w, i) {
          var aan = Math.min(1, Math.max(0, p * n - i)).toFixed(2);
          if (w.dataset.aan !== aan) {
            w.dataset.aan = aan;
            w.style.setProperty('--aan', aan);
          }
        });
      });
      window.addEventListener('scroll', werkBij, { passive: true });
      window.addEventListener('resize', werkBij);
      werkBij();
    });
  }

  /* ---------- Magneet ----------
     Knoppen met .magneet trekken licht naar de muis toe (--mx/--my). */
  function setupMagneet() {
    if (reduced || !muis) return;
    document.querySelectorAll('.magneet').forEach(function (el) {
      var ex = 0;
      var ey = 0;
      var trek = perFrame(function () {
        var r = el.getBoundingClientRect();
        el.style.setProperty('--mx', ((ex - (r.left + r.width / 2)) * 0.25).toFixed(1) + 'px');
        el.style.setProperty('--my', ((ey - (r.top + r.height / 2)) * 0.35).toFixed(1) + 'px');
      });
      el.addEventListener('pointermove', function (e) {
        ex = e.clientX;
        ey = e.clientY;
        trek();
      });
      el.addEventListener('pointerleave', function () {
        el.style.removeProperty('--mx');
        el.style.removeProperty('--my');
      });
    });
  }

  /* ---------- Klantenkaart ----------
     De kaart kantelt naar de muis (--rx/--ry) en de glans volgt (--gx/--gy). */
  function setupKaart() {
    var vak = document.querySelector('.klantenkaart');
    var kaart = vak && vak.querySelector('.klantenkaart__kaart');
    if (!kaart || reduced || !muis) return;
    var ex = 0;
    var ey = 0;
    var kantel = perFrame(function () {
      var r = kaart.getBoundingClientRect();
      var x = Math.min(1, Math.max(0, (ex - r.left) / r.width));
      var y = Math.min(1, Math.max(0, (ey - r.top) / r.height));
      kaart.style.setProperty('--ry', ((x - 0.5) * 16).toFixed(2) + 'deg');
      kaart.style.setProperty('--rx', ((0.5 - y) * 12).toFixed(2) + 'deg');
      kaart.style.setProperty('--gx', (x * 100).toFixed(1) + '%');
      kaart.style.setProperty('--gy', (y * 100).toFixed(1) + '%');
    });
    vak.addEventListener('pointermove', function (e) {
      ex = e.clientX;
      ey = e.clientY;
      kantel();
    });
    vak.addEventListener('pointerleave', function () {
      ['--rx', '--ry', '--gx', '--gy'].forEach(function (p) {
        kaart.style.removeProperty(p);
      });
    });
  }

  /* ---------- Open of gesloten ----------
     Openingsuren: maandag tot zaterdag 10–18 uur, zondag en feestdagen gesloten.
     Rekent in Belgische tijd en kent de Belgische wettelijke feestdagen. */
  function setupStatus() {
    var el = document.querySelector('.status');
    if (!el || !window.Intl || !Intl.DateTimeFormat.prototype.formatToParts) return;
    var tekst = el.querySelector('.status__tekst');
    var dagen = ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'];
    var DAG = 86400000;

    // Paaszondag (gregoriaans, "anonieme" methode), als UTC-tijdstip
    function pasen(j) {
      var a = j % 19;
      var b = Math.floor(j / 100);
      var c = j % 100;
      var d = Math.floor(b / 4);
      var e = b % 4;
      var f = Math.floor((b + 8) / 25);
      var g = Math.floor((b - f + 1) / 3);
      var h = (19 * a + b - d - g + 15) % 30;
      var i = Math.floor(c / 4);
      var k = c % 4;
      var l = (32 + 2 * e + 2 * i - h - k) % 7;
      var m = Math.floor((a + 11 * h + 22 * l) / 451);
      var n = h + l - 7 * m + 114;
      return Date.UTC(j, Math.floor(n / 31) - 1, (n % 31) + 1);
    }

    // Nieuwjaar, paasmaandag, 1 mei, Hemelvaart, pinkstermaandag, 21 juli,
    // 15 augustus, 1 november, 11 november, Kerstmis
    function feestdag(t) {
      var datum = new Date(t);
      var md = datum.getUTCMonth() + 1 + '-' + datum.getUTCDate();
      if (['1-1', '5-1', '7-21', '8-15', '11-1', '11-11', '12-25'].indexOf(md) !== -1) return true;
      var p = pasen(datum.getUTCFullYear());
      return t === p + DAG || t === p + 39 * DAG || t === p + 50 * DAG;
    }

    function openDag(t) {
      return new Date(t).getUTCDay() !== 0 && !feestdag(t);
    }

    var formaat = new Intl.DateTimeFormat('en-GB', {
      timeZone: 'Europe/Brussels',
      year: 'numeric',
      month: 'numeric',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric',
      hourCycle: 'h23'
    });

    function werkBij() {
      var d = {};
      formaat.formatToParts(new Date()).forEach(function (deel) {
        d[deel.type] = Number(deel.value);
      });
      var vandaag = Date.UTC(d.year, d.month - 1, d.day);
      var uur = d.hour + d.minute / 60;
      var vandaagOpen = openDag(vandaag);
      var isOpen = vandaagOpen && uur >= 10 && uur < 18;
      var zin = null;
      if (isOpen) zin = 'Nu open · tot 18 uur';
      else if (vandaagOpen && uur < 10) zin = 'Gesloten · opent vandaag om 10 uur';
      else {
        for (var k = 1; k <= 7 && !zin; k++) {
          var t = vandaag + k * DAG;
          if (openDag(t)) zin = 'Gesloten · opent ' + (k === 1 ? 'morgen' : dagen[new Date(t).getUTCDay()]) + ' om 10 uur';
        }
      }
      if (!zin) return;
      el.classList.toggle('is-open', isOpen);
      tekst.textContent = zin;
      el.hidden = false;
    }

    werkBij();
    setInterval(werkBij, 60000);
  }

  /* ---------- Nieuwsbrief ---------- */
  function setupNewsletter() {
    var form = document.querySelector('.nieuwsbrief__form');
    if (!form) return;
    var input = form.querySelector('input[type="email"]');
    var message = form.querySelector('.nieuwsbrief__bericht');
    if (!input || !message) return;

    input.addEventListener('input', function () {
      input.removeAttribute('aria-invalid');
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!input.checkValidity()) {
        message.textContent = 'Vul een geldig e-mailadres in.';
        input.setAttribute('aria-invalid', 'true');
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
     gewoon meteen op zijn plaats. Titels (.kop) en de statement hebben hun eigen
     beweging en doen hier niet mee. */
  function setupTekst() {
    if (reduced || !('IntersectionObserver' in window)) return;

    // Kandidaten: koppen, alinea's, kaarten en losse regels. Een blok dat al in
    // een gekozen blok zit, doet niet nog eens mee.
    var selector = [
      '.reveal h3', '.reveal p:not(.statement)', '.reveal figure:not(.carrousel__slide)',
      '.reveal time', '.reveal li:not(.merk)', '.reveal address', '.reveal .beeld:not(.beeld--onthul)',
      '.reveal .carrousel', '.reveal .merkband', '.reveal .klantenkaart', '.reveal .sectie__nr',
      '.reveal .winkel__nr', '.reveal .nieuwsbrief__form', '.reveal .tabs',
      '.footer__boven > *', '.footer__logo', '.footer__meta > *'
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
      toon(blokken.filter(function (el) {
        return !el.classList.contains('is-in') && el.getClientRects().length;
      }));
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
  setupHeader();
  setupHero();
  setupHeroVideo();
  setupBand();
  setupKoppen();
  setupStatement();
  setupMagneet();
  setupKaart();
  setupStatus();
  setupNewsletter();
  setupTekst();
  setupMerkbanden();
  setupCarrousel();
  setupReveal();
})();
