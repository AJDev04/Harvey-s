# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Shoppers in and around Aalst**, women and men, "zowel jong als oud". They come to the
  site to decide whether to visit: which brands Harvey's carries, what the collection looks
  like right now, where the stores are, whether they are open, and how to get a gift voucher.
  Many are on a phone, often on the way or just before going.
- **The shop itself** (Harvey's, run by DEVLO NV). Staff manage photos, news, brands, texts,
  opening hours and the logo in the WordPress admin, without touching code.
- **Another independent shop** that installs the same WordPress theme without Harvey's
  content and fills in its own.

## Product Purpose

Bring people into the two stores. There is no webshop: the site shows the brands, the
collection and the atmosphere, then makes finding, calling and visiting effortless. Success
means store visits, phone calls and gift vouchers bought in store. Newsletter sign-ups are
secondary.

## Positioning

A family-run boutique in Aalst with two stores facing each other on the Kattestraat, Harvey's
Women at number 38 and Harvey's Men at 49. In the shop's own words: "méér dan zomaar een winkel,
het is een begrip in de regio". It stands for relaxed shopping in a family atmosphere, personal
service and professional advice, and hip brands "hand in hand met de meer klassiekere merken".
The collection grows every week, and there are accessories (shoes, jewellery, handbags, belts).
Loyalty-card holders get 5% off.

## Operating Context

- Two physical stores: Kattestraat 38 (Women, 053 80 92 84) and Kattestraat 49 (Men,
  053 70 14 55), 9300 Aalst.
- Opening hours: Monday to Saturday, 10 to 18 without a break; closed on Sundays and public
  holidays. The open/closed label uses Belgian time and Belgian public holidays.
- Gift voucher: a physical folded card sold in the store, valid for one year, only in the
  store named on it, not valid during the sales.
- Legal entity: DEVLO NV, BTW BE 0455.889.607, devlo@skynet.be. The disclaimer and privacy
  texts are the shop's own.
- Hosting: the static site is meant for Cloudflare Pages. The WordPress theme replaces the
  current WordPress site on the shop's hosting.

## Capabilities and Constraints

- Two deliverables share one design: a static site (plain HTML, CSS and JS, no build step)
  and a WordPress theme (`wordpress/harveys/`, built by `tools/maak-thema.sh`). Both use the
  same `css/` and `js/` files unchanged, so every change to `css/` or `js/` applies to both.
- **The theme must stay shop-neutral.** Everything shop-specific lives in the WordPress admin
  or the optional starter content, never in theme code. A second shop must be able to run it
  with its own logo, colour, texts, brands, stores and hours. Sections without content hide
  themselves, including their menu items.
- The newsletter is not connected in the static site; it only shows a confirmation. In
  WordPress it stores sign-ups with a CSV export.
- The logo is never live text. `tools/maak-logo.py` turns it into SVG paths from
  `tools/logo-bron.svg`. The licensed font is not shipped.
- Uploaded SVGs are sanitised (`wordpress/harveys/inc/svg.php`).
- Language: Dutch (Belgium). The copy addresses visitors formally with "u".
- Works without JavaScript and with reduced motion: content stays in place and readable.

## Brand Commitments

- Names: Harvey's, Harvey's Women, Harvey's Men. Slogan: "Casual Luxury", the only new line
  in the new identity.
- The new identity board (`new style harveys's.svg`, not in this repository) is the source
  of truth for the identity. The logo design that ships is `tools/logo-bron.svg`, and the
  visual rules live in DESIGN.md.
- **Content comes strictly from Harvey's.** Texts, facts, photos and brands come from
  harveys.be, from the shop and from its gift voucher, taken over as literally as possible.
  New copy is written only after the shop approves it.
- Voice: the shop's own Dutch. Warm, plain, formal "u", never pushy or sale-like.

## Evidence on Hand

- Texts from harveys.be: home page, collection pages, the Nathan-Baume news item,
  disclaimer and privacy.
- Photos: 38 collection photos (`images/collectie/`, prefixed `haar-` or `hem-` plus the
  brand), store interiors (`images/winkel/`), both facades (`images/winkel-women.jpg`,
  `images/winkel-men.jpg`), the news photo (`images/collectie-schoenen.jpg`), and the hero
  video on Cloudflare Stream with `images/hero-poster.jpg`.
- Brand logos in `images/merken/`. No logo yet for No Secrets, Freebird, Senso, Reset,
  Nathan-Baume, Beaumont and Hox; their names stand in as text.
- The shop's real gift voucher, which the voucher section reproduces.
- **Absent, so never fabricate:** reviews or testimonials, prices, an online shop, staff names
  or photos, founding year or history, press, awards, and opening hours or offers that the shop
  has not stated.

## Product Principles

1. **The store is the destination.** Every surface should make the next real-world step
   obvious: find, call, check the hours, visit.
2. **Only Harvey's own truth.** If the shop didn't say it, the site doesn't say it.
3. **Specifics belong in the admin.** Anything one shop would change is a setting, not code.
4. **Brands and atmosphere do the selling.** The collection, the brands and the stores carry
   the message, not claims about them.
5. **Comfortable for every age.** Visitors range from young to old, so content stays
   legible, calm and usable on a phone.

## Accessibility & Inclusion

The audience spans every age, including older shoppers. The repository practises WCAG AA
contrast for text, visible keyboard focus, labelled controls, alt text on content photos,
44px touch targets, and full fallbacks for reduced motion and no JavaScript. Keep that
standard.
