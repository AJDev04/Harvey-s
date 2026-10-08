/* Galerij in de sectie Collectie.
   - "Voor haar" en "Voor hem" worden twee tabbladen met een oranje pil die meeschuift.
   - Met een muis volgt een oranje bol met "Bekijk" de cursor boven de foto's.
   - Klik op een foto opent de lichtbak, waarin het beeld vanuit zijn plek in het
     raster naar het midden groeit; bladeren gaat binnen dezelfde groep.
   Zonder JavaScript staan beide groepen gewoon onder elkaar in het raster; zonder
   beweging (prefers-reduced-motion) vervallen het groeien en de bol. */

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
      knop.appendChild(document.createTextNode(titel ? titel.textContent : 'Groep ' + (i + 1)));
      var aantal = document.createElement('span');
      aantal.className = 'tabs__aantal';
      aantal.textContent = itemsVan(groep).length;
      knop.appendChild(aantal);
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

  /* ---------- Lichtbak ---------- */
  var beeld = document.getElementById('lichtbak-beeld');
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

  function zetBeeld(i) {
    var item = lijst[i];
    var bron = item.querySelector('img');
    huidig = i;
    vulTekst(i);
    beeld.src = bron.currentSrc || bron.src;
    beeld.alt = item.dataset.groep + ' — ' + item.dataset.merk;
    return bron;
  }

  function open(item) {
    lijst = itemsVan(item.closest('.galerij__groep') || galerij);
    var bron = zetBeeld(lijst.indexOf(item));
    var van = bron.getBoundingClientRect();
    verbergCursor();
    if (!lichtbak.open) lichtbak.showModal();
    lichtbak.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 400, easing: ease, fill: 'both' });
    // wachten tot het beeld maat heeft, anders klopt de eindstand niet
    var start = function () {
      groei(van, beeld, false);
      tekstIn();
    };
    if (beeld.complete && beeld.naturalWidth) start();
    else beeld.addEventListener('load', start, { once: true });
  }

  function sluit() {
    if (!lichtbak.open) return;
    var item = lijst[huidig];
    var naar = item.querySelector('img').getBoundingClientRect();
    var klaar = groei(naar, beeld, true);
    lichtbak.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 350, delay: 200, easing: ease, fill: 'both' });
    klaar.then(function () {
      lichtbak.close();
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
    var weg = beeld.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 200, easing: ease, fill: 'both' });
    weg.finished.then(function () {
      zetBeeld(i);
      var toon = function () {
        beeld.animate([{ opacity: 0, transform: 'translateY(0.5rem)' }, { opacity: 1, transform: 'none' }], { duration: 450, easing: ease, fill: 'both' });
        tekstIn();
      };
      if (beeld.complete && beeld.naturalWidth) toon();
      else beeld.addEventListener('load', toon, { once: true });
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
})();
