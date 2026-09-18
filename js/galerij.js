/* Galerij in de sectie Collectie: klik op een foto opent de lichtbak, waarin
   het beeld vanuit zijn plek in het raster naar het midden groeit. Zonder
   JavaScript staan de foto's gewoon in het raster; zonder beweging
   (prefers-reduced-motion) vervalt het groeien. */

(function () {
  'use strict';

  var galerij = document.getElementById('galerij');
  var lichtbak = document.getElementById('lichtbak');
  if (!galerij || !lichtbak) return;

  var items = Array.prototype.slice.call(galerij.querySelectorAll('.galerij__item'));
  if (!items.length) return;

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var ease = 'cubic-bezier(0.16, 1, 0.3, 1)';

  /* ---------- Lichtbak ---------- */
  var beeld = document.getElementById('lichtbak-beeld');
  var nummer = document.getElementById('lichtbak-nummer');
  var totaal = document.getElementById('lichtbak-totaal');
  var titel = document.getElementById('lichtbak-titel');
  var groep = document.getElementById('lichtbak-groep');
  var tekst = lichtbak.querySelector('.lichtbak__tekst');
  var huidig = -1;

  totaal.textContent = String(items.length).padStart(2, '0');

  function vulTekst(i) {
    var item = items[i];
    nummer.textContent = String(i + 1).padStart(2, '0');
    titel.textContent = item.dataset.merk;
    groep.textContent = item.dataset.groep;
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

  function open(i) {
    huidig = i;
    var item = items[i];
    var bron = item.querySelector('img');
    vulTekst(i);
    beeld.src = bron.currentSrc || bron.src;
    beeld.alt = item.dataset.groep + ' — ' + item.dataset.merk;
    var van = bron.getBoundingClientRect();
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
    var item = items[huidig];
    var naar = item.querySelector('img').getBoundingClientRect();
    var klaar = groei(naar, beeld, true);
    lichtbak.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 350, delay: 200, easing: ease, fill: 'both' });
    klaar.then(function () {
      lichtbak.close();
      item.querySelector('.galerij__knop').focus({ preventScroll: true });
    });
  }

  function blader(richting) {
    var i = (huidig + richting + items.length) % items.length;
    if (reduced) {
      open(i);
      return;
    }
    // korte overgang: oud beeld weg, nieuw beeld erin
    var weg = beeld.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 200, easing: ease, fill: 'both' });
    weg.finished.then(function () {
      var item = items[i];
      var bron = item.querySelector('img');
      huidig = i;
      vulTekst(i);
      beeld.src = bron.currentSrc || bron.src;
      beeld.alt = item.dataset.groep + ' — ' + item.dataset.merk;
      var toon = function () {
        beeld.animate([{ opacity: 0, transform: 'translateY(0.5rem)' }, { opacity: 1, transform: 'none' }], { duration: 450, easing: ease, fill: 'both' });
        tekstIn();
      };
      if (beeld.complete && beeld.naturalWidth) toon();
      else beeld.addEventListener('load', toon, { once: true });
    });
  }

  items.forEach(function (item, i) {
    item.querySelector('.galerij__knop').addEventListener('click', function () {
      open(i);
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

})();
