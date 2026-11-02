/* Admin, Website: foto's en SVG's kiezen uit de mediabibliotheek, een kleur
   kiezen, en galerijen met slepen in volgorde zetten. Bewaard wordt pas met de
   knop Bewaren. */

jQuery(function ($) {
  'use strict';

  /* ---------- Eén foto of SVG ---------- */
  $('.harveys-media').each(function () {
    var $blok = $(this);
    var $veld = $blok.find('input[type="hidden"]');
    var $voorbeeld = $blok.find('.harveys-media__voorbeeld');
    var svg = $blok.data('type') === 'svg';
    var kader;

    $blok.on('click', '.harveys-media__kies', function (e) {
      e.preventDefault();
      if (!kader) {
        kader = wp.media({
          title: svg ? 'Kies een SVG' : 'Kies een foto',
          button: { text: 'Gebruiken' },
          library: { type: svg ? 'image/svg+xml' : 'image' },
          multiple: false
        });
        kader.on('select', function () {
          var a = kader.state().get('selection').first().toJSON();
          var url = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
          $veld.val(a.id);
          $voorbeeld.empty().append($('<img alt="">').attr('src', url));
          $blok.addClass('heeft-waarde');
        });
      }
      kader.open();
    });

    $blok.on('click', '.harveys-media__weg', function (e) {
      e.preventDefault();
      $veld.val('');
      $voorbeeld.empty();
      $blok.removeClass('heeft-waarde');
    });
  });

  /* ---------- Kleur: de code ernaast volgt mee, en terug naar de standaard ---------- */
  $('.harveys-kleur').on('input change', function () {
    $(this).nextAll('code').first().text(this.value);
  });

  $('.harveys-kleur__terug').on('click', function (e) {
    e.preventDefault();
    $(this).prevAll('.harveys-kleur').first().val($(this).data('kleur')).trigger('change');
    $(this).remove();
  });

  /* ---------- Galerij: meerdere foto's (en in de Collectie video's), met een onderschrift per foto ---------- */
  var videotypes = ['video/mp4', 'video/webm', 'video/quicktime'];

  $('.harveys-galerij').each(function () {
    var $blok = $(this);
    var $lijst = $blok.find('.harveys-galerij__lijst');
    var $veld = $blok.find('.harveys-galerij__ids');
    var $aantal = $blok.find('.harveys-galerij__aantal');
    var $melding = $blok.find('.harveys-galerij__melding');
    var label = $blok.data('bijschrift') || 'Merk';
    var metVideo = String($blok.data('video')) === '1';
    var kader;

    function meervoud(n, een, veel) {
      return n === 1 ? '1 ' + een : n + ' ' + veel;
    }

    function bewaar() {
      var ids = $lijst.children().map(function () {
        return $(this).data('id');
      }).get();
      var videos = $lijst.children('[data-soort="video"]').length;
      var tekst = meervoud(ids.length - videos, 'foto', "foto's");
      if (videos) tekst += ', ' + meervoud(videos, 'video', "video's");
      $veld.val(ids.join(','));
      $aantal.text(tekst);
    }

    function item(a) {
      var voorbeeld;
      if (a.type === 'video') {
        // het eerste beeld van de video, of het stilstaand beeld als dat al gekozen is
        voorbeeld = $('<video muted playsinline preload="metadata">').attr('src', a.url + '#t=0.5');
        if (a.image && a.image.src && a.image.src.indexOf('/wp-includes/') === -1) voorbeeld.attr('poster', a.image.src);
      } else {
        var maat = a.sizes && (a.sizes.thumbnail || a.sizes.medium);
        voorbeeld = $('<img alt="">').attr('src', maat ? maat.url : a.url);
      }
      // een video is een beeld van in de winkel: daar hoort geen merk bij
      var veldje = a.type === 'video'
        ? $('<span class="harveys-galerij__soort">').text('Video in de winkel')
        : $('<input type="text">').attr({ name: 'harveys_bijschrift[' + a.id + ']', placeholder: label, 'aria-label': label }).val(a.caption || '');
      return $('<li class="harveys-galerij__item">')
        .attr({ 'data-id': a.id, 'data-soort': a.type === 'video' ? 'video' : 'foto' })
        .append(
          voorbeeld,
          veldje,
          $('<button type="button" class="harveys-galerij__weg" aria-label="Weghalen">×</button>')
        );
    }

    $lijst.sortable({ update: bewaar, tolerance: 'pointer', cancel: 'input, button' });

    $blok.on('click', '.harveys-galerij__toevoegen', function (e) {
      e.preventDefault();
      if (!kader) {
        kader = wp.media({
          title: metVideo ? "Foto's en video's toevoegen" : "Foto's toevoegen",
          button: { text: 'Toevoegen' },
          library: { type: metVideo ? ['image', 'video'] : 'image' },
          multiple: 'add'
        });
        kader.on('open', function () {
          kader.state().get('selection').reset();
        });
        kader.on('select', function () {
          var geweigerd = [];
          kader.state().get('selection').each(function (m) {
            var a = m.toJSON();
            // enkel video's die elke browser afspeelt
            if (a.type === 'video' && videotypes.indexOf(a.mime) === -1) {
              geweigerd.push(a.filename || a.title);
              return;
            }
            if (!$lijst.children('[data-id="' + a.id + '"]').length) $lijst.append(item(a));
          });
          if (geweigerd.length) {
            $melding.text('Niet toegevoegd: ' + geweigerd.join(', ') + '. Gebruik een video in mp4, webm of mov.').prop('hidden', false);
          } else {
            $melding.prop('hidden', true);
          }
          bewaar();
        });
      }
      kader.open();
    });

    $blok.on('click', '.harveys-galerij__weg', function () {
      $(this).closest('li').remove();
      bewaar();
    });
  });
});
