#!/bin/sh
# Bouwt het WordPress-thema uit wordpress/harveys/ plus de gedeelde vormgeving
# (css/, js/) en de foto's voor de startinhoud (images/).
#
# Schrijft:
#   dist/harveys/                          het thema als map (om lokaal te testen)
#   dist/harveys.zip                       met startinhoud, voor Harvey's
#   dist/harveys-zonder-startinhoud.zip    kleiner, voor een andere winkel
#
# Installeren: in WordPress bij Weergave → Thema's → Nieuw thema → Thema uploaden.
#
# Gebruik:  sh tools/maak-thema.sh

set -e

WORTEL="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$WORTEL/dist"
THEMA="$DIST/harveys"

# de map zelf blijft staan (een lokale testserver kan ze gekoppeld hebben); enkel de inhoud gaat weg
mkdir -p "$THEMA"
find "$THEMA" -mindepth 1 -delete
rm -f "$DIST"/*.zip

# de PHP-sjablonen en wat enkel bij het thema hoort
cp -R "$WORTEL/wordpress/harveys/." "$THEMA/"

# de vormgeving: dezelfde bestanden als de statische site
mkdir -p "$THEMA/assets/css" "$THEMA/assets/js"
cp "$WORTEL"/css/*.css "$THEMA/assets/css/"
cp "$WORTEL"/js/*.js "$THEMA/assets/js/"

# de foto's en het logo voor Website → Startinhoud; het thema zelf bevat niets
# van Harvey's, zodat het zonder startinhoud voor elke winkel werkt
mkdir -p "$THEMA/starter/logo"
cp -R "$WORTEL/images/collectie" "$WORTEL/images/merken" "$WORTEL/images/winkel" "$THEMA/starter/"
cp "$WORTEL/images/winkel-women.jpg" "$WORTEL/images/winkel-men.jpg" \
   "$WORTEL/images/collectie-schoenen.jpg" "$WORTEL/images/hero-poster.jpg" "$THEMA/starter/"
cp "$WORTEL/images/harveys-naam.svg" "$THEMA/starter/logo/naam.svg"
cp "$WORTEL/images/harveys-slogan.svg" "$THEMA/starter/logo/slogan.svg"
cp "$WORTEL/favicon.svg" "$THEMA/starter/logo/favicon.svg"

find "$THEMA" -name '.DS_Store' -delete

cd "$DIST"
zip -rqX harveys.zip harveys
zip -rqX harveys-zonder-startinhoud.zip harveys -x 'harveys/starter/*'

echo "Klaar:"
ls -lh "$DIST"/*.zip | awk '{ print "  " $NF "  (" $5 ")" }'
