#!/bin/sh
# Haalt de productfoto's voor "Voor haar" in de Collectie op en zet ze in
# images/collectie/ (haar-*.jpg, 1200 x 1500 px, al bijgesneden tot 4:5).
# Enkel merken die op de site staan, enkel de wintercollectie.
# De Cambio-foto (haar-cambio-1.jpg) komt van harveys.be en staat al in de map.
#
# VOORBEELDBEELDEN: deze foto's komen van de websites van de merken en zijn
# enkel bedoeld om de site aan Harvey's voor te stellen. Vervang ze door de
# officiele beelden van de merken voor de site online gaat.
#
# Gebruik, vanuit de map Harvey-s:  sh tools/haal-collectiefotos.sh

cd "$(dirname "$0")/.." || exit 1
mkdir -p images/collectie
fout=0

haal() {
  if curl -fsSL -A "Mozilla/5.0" -o "images/collectie/$1" "$2"; then
    echo "ok    $1"
  else
    echo "FOUT  $1"; fout=1
  fi
}

haal haar-prl-kabeltrui-blauw.jpg       "https://dtcralphlauren.scene7.com/is/image/PoloGSI/s7-AI211971865004_alternate10?wid=1200&hei=1500&fit=crop,1&fmt=jpeg&qlt=90"
haal haar-rinopelle-lias.jpg            "https://rino-pelle.com/cdn/shop/files/Lias.7002611_Mocha-Latte_01.jpg?width=1200&height=1500&crop=top"
haal haar-7fam-wollen-jas.jpg           "https://7forallmankind.com/cdn/shop/files/7NEX0H58_3VY_3.jpg?width=1200&height=1500&crop=center"
haal haar-prl-kabeltrui-halve-rits.jpg  "https://dtcralphlauren.scene7.com/is/image/PoloGSI/s7-AI211972130003_alternate10?wid=1200&hei=1500&fit=crop,1&fmt=jpeg&qlt=90"
haal haar-prl-donsjack.jpg              "https://dtcralphlauren.scene7.com/is/image/PoloGSI/s7-AI211932183002_alternate10?wid=1200&hei=1500&fit=crop,1&fmt=jpeg&qlt=90"
haal haar-rinopelle-ambra.jpg           "https://rino-pelle.com/cdn/shop/files/Ambra.7002611_Birch_01_06fb6d71-e457-4529-a73d-2908d5ac378c.jpg?width=1200&height=1500&crop=top"
haal haar-prl-polo-bear.jpg             "https://dtcralphlauren.scene7.com/is/image/PoloGSI/s7-AI211B23917001_alternate10?wid=1200&hei=1500&fit=crop,1&fmt=jpeg&qlt=90"
haal haar-rinopelle-nonna.jpg           "https://rino-pelle.com/cdn/shop/files/Nonna.7002611_Mocha-latte_01.jpg?width=1200&height=1500&crop=top"
haal haar-7fam-lotta-corduroy.jpg       "https://7forallmankind.com/cdn/shop/files/7UG20V64_1LP_2.jpg?width=1200&height=1500&crop=center"

if [ "$fout" -eq 0 ]; then
  echo "Klaar: 9 foto's in images/collectie/"
else
  echo "Niet alles is gelukt. Controleer je internetverbinding en probeer opnieuw."
  exit 1
fi
