#!/bin/sh
# Haalt de productfoto's voor "Voor haar" in de Collectie op en zet ze in
# images/collectie/ (1200 x 1500 px, al bijgesneden tot 4:5).
# Enkel stukken die zeker in de winkel liggen (nagekeken op de video van
# Harvey's Women): het donsjack van Polo Ralph Lauren (op de etalagepop) en de
# Lotta in corduroy van 7 For All Mankind (label "Lotta" op de tafel).
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

haal haar-prl-donsjack-navy.jpg     "https://dtcralphlauren.scene7.com/is/image/PoloGSI/s7-AI211932183001_alternate10?wid=1200&hei=1500&fit=crop,1&fmt=jpeg&qlt=90"
haal haar-7fam-lotta-corduroy.jpg   "https://7forallmankind.com/cdn/shop/files/7UG20V64_1LP_2.jpg?width=1200&height=1500&crop=center"

if [ "$fout" -eq 0 ]; then
  echo "Klaar: 2 foto's in images/collectie/"
else
  echo "Niet alles is gelukt. Controleer je internetverbinding en probeer opnieuw."
  exit 1
fi
