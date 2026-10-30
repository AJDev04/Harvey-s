#!/bin/sh
# Haalt de productfoto's voor "Voor haar" in de Collectie op en zet ze in
# images/collectie/. Enkel stukken die zeker in de winkel liggen:
#   - Polo Ralph Lauren donsjack (in de winkel in khaki, kleur New olive) en
#     7 For All Mankind Lotta in corduroy: nagekeken op een video uit de winkel
#   - No Secrets cardigan iconico simil pelliccia 263NS034, macaron
#   - Senso pull S2584PL/F Neela, brown
#   - Reset Felice LR026.60.263, kleur 2410 coffee bean
#     (opgezocht aan de hand van het label in de winkel)
# De foto's van Cambio en de grijze Polo Ralph Lauren komen van harveys.be en
# staan al in de map.
#
# VOORBEELDBEELDEN: deze foto's komen van de websites van de merken (de Senso-foto
# van een verkoper) en zijn enkel bedoeld om de site aan Harvey's voor te stellen.
# Vervang ze door de officiele beelden van de merken voor de site online gaat.
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

haal haar-prl-donsjack-khaki.jpg            "https://dtcralphlauren.scene7.com/is/image/PoloGSI/s7-AI211932183003_alternate10?wid=1200&hei=1500&fit=crop,1&fmt=jpeg&qlt=90"
haal haar-nosecrets-cardigan-macaron.jpg    "https://www.nosecrets.com/wp-content/uploads/2026/08/263NS034_7.jpg"
haal haar-7fam-lotta-corduroy.jpg           "https://7forallmankind.com/cdn/shop/files/7UG20V64_1LP_2.jpg?width=1200&height=1500&crop=center"
haal haar-senso-neela-brown.jpg             "https://d2j6dbq0eux0bg.cloudfront.net/images/79653804/products/859227026/6196370043.jpg"
haal haar-reset-felice-coffee-bean.jpg      "https://reset.eu/cdn/shop/files/Felice_2410_BM_F2.jpg?width=1200&height=1500&crop=top"

if [ "$fout" -eq 0 ]; then
  echo "Klaar: 5 foto's in images/collectie/"
else
  echo "Niet alles is gelukt. Controleer je internetverbinding en probeer opnieuw."
  exit 1
fi
