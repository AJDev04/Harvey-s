/* Galerij in de sectie Collectie.
   - "Voor haar" en "Voor hem" worden twee tabbladen met een oranje pil die meeschuift.
   - Met een muis volgt een oranje bol met "Bekijk" de cursor boven de foto's.
   - Klik op een foto opent de lichtbak, waarin het beeld vanuit zijn plek in het
     raster naar het midden groeit; bladeren gaat binnen dezelfde groep.
   - Een video (WordPress, .galerij__item--video) speelt in het raster zonder geluid
     in een lus zodra ze in beeld is, en in de lichtbak met geluid en knoppen.
   Zonder JavaScript staan beide groepen gewoon onder elkaar in het raster; zonder
   beweging (prefers-reduced-motion) vervallen het groeien, de bol en het vanzelf
   afspelen van video's. */

(function () {
  'use strict';

  var galerij = document.getElementById('galerij');
  var lichtbak = document.getElementById('lichtbak');
  if (!galerij || !lichtbak) return;

  var groepen = Array.prototype.slice.call(galerij.querySelectorAll('.galerij__groep'));
  if (!groepen.length) return;

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var muis = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var ease = 'cubic-bezier(0.16, 1, 0.3, 1)';

  function itemsVan(groep) {
    return Array.prototype.slice.call(groep.querySelectorAll('.galerij__item'));
  }

  /* ---------- Tabbladen ---------- */
  function maakTabs() {
    if (groepen.length < 2) return;

    var tabs = document.createElement('div');
    tabs.className = 'tabs';
    tabs.setAttribute('role', 'tablist');
    tabs.setAttribute('aria-label', 'Collectie');

    var glijder = document.createElement('span');
    glijder.className = 'tabs__glijder';
    glijder.setAttribute('aria-hidden', 'true');
    tabs.appendChild(glijder);

    var knoppen = groepen.map(function (groep, i) {
      var titel = groep.querySelector('h3');
      groep.id = groep.id || 'galerij-groep-' + (i + 1);
      var knop = document.createElement('button');
      knop.type = 'button';
      knop.className = 'tabs__knop';
      knop.id = groep.id + '-tab';
      knop.setAttribute('role', 'tab');
      knop.setAttribute('aria-controls', groep.id);
      knop.textContent = titel ? titel.textContent : 'Groep ' + (i + 1);
      groep.setAttribute('role', 'tabpanel');
      groep.setAttribute('aria-labelledby', knop.id);
      tabs.appendChild(knop);
      return knop;
    });

    galerij.insertBefore(tabs, groepen[0]);
    galerij.classList.add('is-tabs');

    var actief = 0;

    // de pil schuift naar de gekozen knop en neemt haar breedte aan
    function schuif() {
      var knop = knoppen[actief];
      tabs.style.setProperty('--x', knop.offsetLeft + 'px');
      tabs.style.setProperty('--w', knop.offsetWidth + 'px');
    }

    function kies(i, metFocus) {
      var anders = i !== actief;
      actief = i;
      knoppen.forEach(function (knop, j) {
        var aan = j === i;
        knop.setAttribute('aria-selected', String(aan));
        knop.tabIndex = aan ? 0 : -1;
        groepen[j].hidden = !aan;
      });
      if (metFocus) knoppen[i].focus();
      schuif();
      if (anders && !reduced) {
        groepen[i].animate(
          [{ opacity: 0, transform: 'translateY(1.5rem)' }, { opacity: 1, transform: 'none' }],
          { duration: 700, easing: ease }
        );
      }
    }

    knoppen.forEach(function (knop, i) {
      knop.addEventListener('click', function () {
        kies(i);
      });
    });

    // Pijltjes, Home en End wisselen van tabblad (zoals bij elk tablist)
    tabs.addEventListener('keydown', function (e) {
      var n = knoppen.length;
      var doel = { ArrowRight: actief + 1, ArrowLeft: actief - 1, Home: 0, End: n - 1 }[e.key];
      if (doel === undefined) return;
      e.preventDefault();
      kies((doel + n) % n, true);
    });

    window.addEventListener('resize', schuif);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(schuif);
    kies(0);
  }

  /* ---------- Bol met "Bekijk" ---------- */
  var cursor = document.querySelector('.galerij__cursor');

  function verbergCursor() {
    if (cursor) cursor.classList.remove('is-zichtbaar');
  }

  function maakCursor() {
    if (!cursor || reduced || !muis) return;
    galerij.classList.add('heeft-cursor');
    var doelX = 0;
    var doelY = 0;
    var x = 0;
    var y = 0;
    var loopt = false;

    // de bol loopt de muis zacht achterna
    function stap() {
      x += (doelX - x) * 0.22;
      y += (doelY - y) * 0.22;
      cursor.style.transform = 'translate3d(' + x.toFixed(1) + 'px, ' + y.toFixed(1) + 'px, 0)';
      if (Math.abs(doelX - x) > 0.3 || Math.abs(doelY - y) > 0.3) requestAnimationFrame(stap);
      else loopt = false;
    }

    galerij.addEventListener('pointermove', function (e) {
      var opFoto = !!e.target.closest('.galerij__knop');
      doelX = e.clientX;
      doelY = e.clientY;
      // vanuit het niets verschijnt de bol meteen op de muis
      if (opFoto && !cursor.classList.contains('is-zichtbaar')) {
        x = doelX;
        y = doelY;
      }
      cursor.classList.toggle('is-zichtbaar', opFoto);
      if (!loopt) {
        loopt = true;
        requestAnimationFrame(stap);
      }
    });
    galerij.addEventListener('pointerleave', verbergCursor);
    window.addEventListener('scroll', verbergCursor, { passive: true });
  }

  /* ---------- Video's in het raster: spelen zonder geluid zolang ze in beeld zijn ---------- */
  var tegels = Array.prototype.slice.call(galerij.querySelectorAll('.galerij__video'));

  function speel(v) {
    var p = v.play();
    if (p && p.catch) p.catch(function () {}); // geweigerd (bv. spaarstand): dan blijft het eerste beeld staan
  }

  function maakTegels() {
    if (!tegels.length || reduced || !('IntersectionObserver' in window)) return;
    var kijker = new IntersectionObserver(function (items) {
      items.forEach(function (e) {
        e.target.inBeeld = e.isIntersecting;
        if (e.isIntersecting && !lichtbak.open) speel(e.target);
        else e.target.pause();
      });
    }, { threshold: 0.25 });
    tegels.forEach(function (v) {
      v.muted = true;
      kijker.observe(v);
    });
  }

  // terwijl de lichtbak open is, staan de video's in het raster stil
  function pauzeerTegels() {
    tegels.forEach(function (v) {
      v.pause();
    });
  }

  function speelTegels() {
    if (reduced) return;
    tegels.forEach(function (v) {
      if (v.inBeeld) speel(v);
    });
  }

  /* ---------- Lichtbak ---------- */
  var beeld = document.getElementById('lichtbak-beeld');
  var video = document.getElementById('lichtbak-video'); // ontbreekt in oudere markup: dan enkel foto's
  var actief = beeld; // wat nu groot staat: de foto of de video
  var nummer = document.getElementById('lichtbak-nummer');
  var totaal = document.getElementById('lichtbak-totaal');
  var titel = document.getElementById('lichtbak-titel');
  var groepTekst = document.getElementById('lichtbak-groep');
  var tekst = lichtbak.querySelector('.lichtbak__tekst');
  var lijst = []; // de foto's van de groep waarin geklikt werd
  var huidig = -1;

  function vulTekst(i) {
    var item = lijst[i];
    nummer.textContent = String(i + 1).padStart(2, '0');
    totaal.textContent = String(lijst.length).padStart(2, '0');
    titel.textContent = item.dataset.merk;
    groepTekst.textContent = item.dataset.groep;
  }

  // Groei van de kleine foto (in het raster) naar de grote (in de lichtbak):
  // eerst de eindstand tekenen, dan met een transform terug naar de beginstand
  // en die laten wegvallen.
  function groei(vanRect, naarEl, omgekeerd) {
    if (reduced) return Promise.resolve();
    var naar = naarEl.getBoundingClientRect();
    var dx = vanRect.left - naar.left;
    var dy = vanRect.top - naar.top;
    var sx = vanRect.width / naar.width;
    var sy = vanRect.height / naar.height;
    var klein = 'translate(' + dx + 'px, ' + dy + 'px) scale(' + sx + ', ' + sy + ')';
    var frames = omgekeerd ? [{ transform: 'none' }, { transform: klein }] : [{ transform: klein }, { transform: 'none' }];
    return naarEl.animate(frames, { duration: 650, easing: ease, fill: 'both' }).finished;
  }

  function tekstIn() {
    if (reduced) return;
    Array.prototype.forEach.call(tekst.children, function (el, i) {
      el.animate(
        [{ opacity: 0, transform: 'translateY(1rem)' }, { opacity: 1, transform: 'none' }],
        { duration: 700, delay: 250 + i * 70, easing: ease, fill: 'backwards' }
      );
    });
  }

  function isVideo(item) {
    return !!(video && item.dataset.video);
  }

  // het beeld in het raster: de video of de foto
  function bronVan(item) {
    return item.querySelector('.galerij__video') || item.querySelector('img');
  }

  // doe iets zodra het grote beeld maat heeft (een foto geladen, van een video de afmetingen)
  function alsKlaar(el, doe) {
    if (el === video) {
      if (video.readyState >= 1 && video.videoWidth) doe();
      else video.addEventListener('loadedmetadata', doe, { once: true });
    } else if (beeld.complete && beeld.naturalWidth) doe();
    else beeld.addEventListener('load', doe, { once: true });
  }

  // oude animaties weg (een vervaging bij het bladeren blijft anders hangen)
  function wis(el) {
    if (el && el.getAnimations) {
      el.getAnimations().forEach(function (a) {
        a.cancel();
      });
    }
  }

  function zetBeeld(i) {
    var item = lijst[i];
    var bron = bronVan(item);
    huidig = i;
    vulTekst(i);
    if (isVideo(item)) {
      beeld.hidden = true;
      video.hidden = false;
      if (video.dataset.bron !== item.dataset.video) {
        video.dataset.bron = item.dataset.video;
        video.poster = bron.getAttribute('poster') || '';
        video.src = item.dataset.video;
      } else {
        video.currentTime = 0;
      }
      video.setAttribute('aria-label', item.dataset.titel || item.dataset.groep + ' — ' + item.dataset.merk);
      // met geluid: de klik van de bezoeker laat dat toe; bij "minder beweging" start ze pas met de knop
      if (!reduced) speel(video);
      actief = video;
    } else {
      if (video) {
        video.pause();
        video.hidden = true;
      }
      beeld.hidden = false;
      // data-groot: de grote versie van de foto (WordPress); anders dezelfde als in het raster
      beeld.src = item.dataset.groot || bron.currentSrc || bron.src;
      // de alt-tekst van de foto zelf, anders groep en merk
      beeld.alt = bron.alt || item.dataset.groep + ' — ' + item.dataset.merk;
      actief = beeld;
    }
    return bron;
  }

  function open(item) {
    lijst = itemsVan(item.closest('.galerij__groep') || galerij);
    wis(beeld);
    wis(video);
    var bron = zetBeeld(lijst.indexOf(item));
    var van = bron.getBoundingClientRect();
    var el = actief;
    verbergCursor();
    pauzeerTegels();
    if (!lichtbak.open) lichtbak.showModal();
    lichtbak.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 400, easing: ease, fill: 'both' });
    // wachten tot het beeld maat heeft, anders klopt de eindstand niet
    alsKlaar(el, function () {
      groei(van, el, false);
      tekstIn();
    });
  }

  function sluit() {
    if (!lichtbak.open) return;
    var item = lijst[huidig];
    var naar = bronVan(item).getBoundingClientRect();
    var el = actief;
    if (video) video.pause();
    var klaar = groei(naar, el, true);
    lichtbak.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 350, delay: 200, easing: ease, fill: 'both' });
    klaar.then(function () {
      lichtbak.close();
      speelTegels();
      item.querySelector('.galerij__knop').focus({ preventScroll: true });
    });
  }

  function blader(richting) {
    var i = (huidig + richting + lijst.length) % lijst.length;
    if (reduced) {
      zetBeeld(i);
      return;
    }
    // korte overgang: oud beeld weg, nieuw beeld erin
    var weg = actief.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 200, easing: ease, fill: 'both' });
    weg.finished.then(function () {
      zetBeeld(i);
      var el = actief;
      alsKlaar(el, function () {
        el.animate([{ opacity: 0, transform: 'translateY(0.5rem)' }, { opacity: 1, transform: 'none' }], { duration: 450, easing: ease, fill: 'both' });
        tekstIn();
      });
    });
  }

  galerij.querySelectorAll('.galerij__item').forEach(function (item) {
    item.querySelector('.galerij__knop').addEventListener('click', function () {
      open(item);
    });
  });

  lichtbak.querySelector('.lichtbak__sluit').addEventListener('click', sluit);
  lichtbak.querySelectorAll('.lichtbak__pijl').forEach(function (knop) {
    knop.addEventListener('click', function () {
      blader(Number(knop.dataset.richting));
    });
  });

  // Klik naast de inhoud sluit; Escape sluit via de dialog zelf (cancel)
  lichtbak.addEventListener('click', function (e) {
    if (e.target === lichtbak) sluit();
  });
  lichtbak.addEventListener('cancel', function (e) {
    e.preventDefault();
    sluit();
  });
  lichtbak.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') blader(1);
    if (e.key === 'ArrowLeft') blader(-1);
  });

  maakTabs();
  maakCursor();
  maakTegels();
})();
