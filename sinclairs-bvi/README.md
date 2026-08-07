# Sinclairs (BVI) — WordPress theme

Custom classic PHP theme built from the approved design handoff
(`Sinclair's BVI Legal Website Redesign.zip`, `design_handoff_wordpress/`).
No page builder, no ACF/Contact Form 7 dependency — everything needed to
run the site is in this theme.

## Quick start

1. Copy/symlink this folder into `wp-content/themes/sinclairs-bvi`.
2. Activate it in **Appearance → Themes**. On first activation it
   automatically:
   - imports the client logo and the one supplied harbour photo,
   - creates the Home, About, Our Services, Our Articles and Contact
     pages with the correct templates, and sets Home as the site's
     front page,
   - creates the 8 practice-area posts (Corporate & Commercial, Investment
     Funds, Banking & Corporate Finance, AML/CFT & Compliance, Virtual
     Assets & FinTech, Economic Substance, Property & Private Client,
     Liquidations/Trade Marks/Notarial) with the client's copy and FAQs,
   - adds the firm's 3 real client testimonials and the 6 real articles
     from the "Legal Insights" section of the live sinclairsbvi.com
     (pulled 2026-08; see "Content sourced from the live site" below).
   This only ever *creates* missing content — reactivating the theme later
   won't overwrite anything that's since been edited.
3. Go to **Settings → Permalinks** and click **Save** once, so the
   `/services/…/` and `/articles/…/` routes take effect.

## Where content lives in wp-admin

| On the site | Edit in wp-admin |
|---|---|
| Header mega-menu, home "Choose a practice area", footer service links, Services-hub groups | **Practice Areas** (custom post type `service`) — one query feeds all four, so reordering/renaming/adding a practice area updates everywhere at once |
| A practice-area page's body copy | The post's main content editor |
| Its one-line "dek" under the H1 | The post's **Excerpt** box |
| Its mega-menu / home-panel blurb | **Practice Area Settings** box on the post |
| Its "Information & FAQs" questions | **Information & FAQs** box (add/remove/reorder rows) |
| Its rows on the Services hub | **Services Hub — Sub-service Rows** box. Each row picks which of the 3 hub groups it appears under — this is per-row, not per-page, because in the approved design one practice area (Property & Private Client) contributes rows to two different hub groups |
| Its FAQ heading ("Information & FAQs" vs "Frequently Asked Questions") | Per-post override in the same Settings box, or the site-wide default in Customizer |
| Home page hero / intro / articles-teaser / closing-CTA text | Edit the **Home** page → **Home Page Content** box |
| Home intro paragraphs | The Home page's main content editor |
| About page body | The **About** page's main content editor (first paragraph is auto-styled larger; add a Quote block for a pull-quote) |
| About portrait photo / Home articles-teaser photo | The **Secondary image** box on that page |
| Testimonials | **Testimonials** in the admin menu — title = client name, content = quote, "Matter" field |
| Articles | **Our Articles** in the admin menu — Excerpt = standfirst, Category = taxonomy |
| Site accent color, default/fallback photo, contact details, footer blurb, copyright + disclaimer text, default FAQ heading | **Appearance → Customize → Theme Options** |
| Banner photo on any page/post | That item's Featured Image |

## Design decisions made while building this out

- **No CMYK/halftone photo filter.** The design handoff itself flagged
  this as a placeholder-system decorative effect and said it would
  "likely drop for a law firm site" — done. Photos are shown clean.
- **No ACF, no Contact Form 7/WPForms dependency.** FAQs and the Services
  hub's sub-service rows use a small custom repeater field (plain post
  meta + vanilla JS row cloning); the enquiry form posts to a
  theme-provided `admin-post.php` handler that emails the firm. Swapping
  either for a plugin later is a template-level change, not a
  re-architecture.
- **Accent color is computed, not hard-coded per shade.** `style.css`
  derives hover/active/ink/on-dark shades from the one `--color-accent`
  variable with `color-mix()`, so changing the accent color in Customizer
  (default `#0e7c93`, the client's tweak from Broadsheet's placeholder
  `#0088b0`) stays visually coherent everywhere without a regenerated
  tonal ramp.
- **`/services/` and `/articles/` are real editable Pages**, while the 8
  practice areas and each article live one level under the same slug
  (`/services/corporate-commercial/`, `/articles/{slug}/`) as a custom
  post type. WordPress resolves the exact-slug Page and the
  one-level-deeper CPT rewrite without conflict; confirmed against a live
  WordPress install during development.
- **Scroll-reveal and the testimonial/practice-area crossfades degrade
  safely.** Without JavaScript (or for `prefers-reduced-motion`), all
  content is shown immediately rather than staying hidden — see the
  `.js`-gated rules at the top of `assets/css/style.css`.

## Content sourced from the live site

The design handoff's testimonial section literally said "paste from the
current site," and asked for the real "Legal Insights" articles, so
`inc/seed-content.php` seeds real content pulled from sinclairsbvi.com
(2026-08) rather than placeholders:

- **3 testimonials** — Benoit Quertemont, Michael Fitzhugh (Attorney-At-Law)
  and Matt Freeman (In-House Counsel). The live site's first testimonial has
  its name/role fields entered inconsistently (no role given); reproduced
  as-is rather than guessed at.
- **6 articles**, with categories assigned by topic since the live posts
  are all uncategorized: *What You Need Know About Registering Trade Marks
  In The BVI*, *What are trusts?*, *Trustee (Amendment) Act, 2015*,
  *Incubator Funds and Approved Funds*, *How does the Economic Substance
  (Companies and Limited Partnership) Act, 2018 Affect You?*, *Estate
  Planning and Cryptocurrencies*. None have a featured image on the live
  site (the "related articles" cards are text-only) — they use the same
  fallback photo as everything else pending real photography.
- One sentence in the live "Economic Substance" article's body is
  genuinely truncated mid-sentence on the source site ("…adequacy of
  expenditure, staff and premises in the" — nothing follows). Reproduced
  as published; worth flagging to the client rather than inventing an
  ending.
- The testimonial rotator's type size/column width and the crossfade
  stage's height were tuned in the approved design for a short 1–2 line
  placeholder quote. Both were reworked here (`assets/css/style.css`,
  `assets/js/main.js`) to fit a full multi-sentence endorsement, and the
  stage height is now measured from actual content via JS rather than a
  fixed guess, so it won't overflow into the section below regardless of
  how long a future testimonial is.

## Before this goes live — still open

- **Photography.** Only one client photo was supplied and is currently
  reused across every hero/banner/portrait/panel/article slot. Replace
  each via its Featured Image / Secondary Image field.
- **Contact details need client confirmation.** The address/phone numbers
  in Customizer → Theme Options → Contact Info come from the design
  handoff (sourced from public BVI FSC records) and read "Mill Mall, 2nd
  Floor, Unit 20." A web search while building this surfaced a business
  directory listing a different address ("2nd Floor, O'Neal Marketing
  Associates Building, Wickham's Cay II") for the same firm — directory
  listings are often stale, so this isn't necessarily more current, but
  it's a reason to confirm the address with the client rather than trust
  either source blindly.
- **One service group's copy was assembled as a stand-in.** Per the
  handoff notes, "Economic Substance" and "Liquidations, Trade Marks &
  Notarial" didn't have a dedicated source document; their copy was
  built from the Nutshell doc, and neither has FAQs yet.
- **Fonts load from Google Fonts** (`Source Serif 4`, matching the
  design). Self-host instead if that's a requirement.

## File map

```
functions.php              theme setup, enqueues, includes
inc/cpt-service.php         "service" CPT (the 8 practice areas)
inc/cpt-article.php         "article" CPT + category taxonomy
inc/cpt-testimonial.php     "testimonial" CPT
inc/repeater-field.php      reusable repeater meta-box (FAQs, hub rows)
inc/page-meta-boxes.php     Home page fields + reusable secondary-image field
inc/customizer.php          Theme Options (brand, services, contact, footer)
inc/helpers.php             page-by-template lookup, ordered service query, image helpers
inc/contact-form.php        self-contained enquiry-form handler
inc/seed-content.php        first-activation content creation (idempotent)
inc/admin-assets.php        wp-admin CSS/JS enqueue for the repeaters/image picker
header.php / footer.php     sticky header + mega-menu + mobile drawer / 4-col footer
front-page.php              Home
page-about.php               About
page-services.php            Our Services hub
single-service.php           one practice area
page-articles.php            Our Articles hub
single-article.php           one article
page-contact.php             Contact + enquiry form
page.php / single.php / 404.php / index.php   fallbacks
assets/css/style.css         all front-end styles
assets/css/admin.css         repeater/image-picker admin styles
assets/js/main.js            mega-menu, mobile drawer, practice-area panel, testimonial rotator, scroll-reveal
assets/js/admin-repeater.js  wp-admin repeater row cloning + media picker
```
