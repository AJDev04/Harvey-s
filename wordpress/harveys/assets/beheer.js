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

  /* ---------- Galerij: meerdere foto's, met een onderschrift per foto ---------- */
  $('.harveys-galerij').each(function () {
    var $blok = $(this);
    var $lijst = $blok.find('.harveys-galerij__lijst');
    var $veld = $blok.find('.harveys-galerij__ids');
    var $aantal = $blok.find('.harveys-galerij__aantal');
    var label = $blok.data('bijschrift') || 'Merk';
    var kader;

    function bewaar() {
      var ids = $lijst.children().map(function () {
        return $(this).data('id');
      }).get();
      $veld.val(ids.join(','));
      $aantal.text(ids.length === 1 ? '1 foto' : ids.length + " foto's");
    }

    function item(a) {
      var maat = a.sizes && (a.sizes.thumbnail || a.sizes.medium);
      return $('<li class="harveys-galerij__item">')
        .attr('data-id', a.id)
        .append(
          $('<img alt="">').attr('src', maat ? maat.url : a.url),
          $('<input type="text">').attr({ name: 'harveys_bijschrift[' + a.id + ']', placeholder: label, 'aria-label': label }).val(a.caption || ''),
          $('<button type="button" class="harveys-galerij__weg" aria-label="Foto weghalen">×</button>')
        );
    }

    $lijst.sortable({ update: bewaar, tolerance: 'pointer', cancel: 'input, button' });

    $blok.on('click', '.harveys-galerij__toevoegen', function (e) {
      e.preventDefault();
      if (!kader) {
        kader = wp.media({
          title: "Foto's toevoegen",
          button: { text: 'Toevoegen' },
          library: { type: 'image' },
          multiple: 'add'
        });
        kader.on('open', function () {
          kader.state().get('selection').reset();
        });
        kader.on('select', function () {
          kader.state().get('selection').each(function (m) {
            var a = m.toJSON();
            if (!$lijst.children('[data-id="' + a.id + '"]').length) $lijst.append(item(a));
          });
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
