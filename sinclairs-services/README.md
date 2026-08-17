# Sinclairs (BVI) — Our Services

WordPress plugin that renders the whole **Our Services** page from one
shortcode: the *In a Nutshell* index, a jump-to dropdown, and each of the
ten practice areas with its own *Information & FAQs*. It also adds the
"Our Services" nav dropdown that anchor-links to each section.

> **This replaces the `sinclairs-services` plugin currently live on the
> site.** It keeps that plugin's folder name, asset paths
> (`assets/frontend.css`, `assets/frontend.js`), handles (`sc-frontend-css`,
> `sc-frontend-js`) and `sc-` class prefix, so it drops straight in.
> **Back up the existing plugin folder before overwriting it**, and see
> "Before this goes live" below — the shortcode tag needs confirming.

## Install

1. Replace `wp-content/plugins/sinclairs-services/` with this folder.
2. Activate under **Plugins** if it isn't already active.
3. Edit the **Our Services** page and make sure its Elementor Shortcode
   widget contains `[sinclairs_services]`.

## Shortcodes

| Shortcode | Renders |
|---|---|
| `[sinclairs_services]` | The whole page — intro, jump box, nutshell index, all ten sections |
| `[sinclairs_services_nutshell]` | Just the *In a Nutshell* index |
| `[sinclairs_services_menu]` | Just the jump-to dropdown |

### Attributes

```
[sinclairs_services layout="rail"]        default
[sinclairs_services layout="accordion"]   alternative arrangement
[sinclairs_services nutshell="no"]        hide the index
[sinclairs_services jump="no"]            hide the jump box
[sinclairs_services intro="no"]           hide the "Our Expertise" header
```

**`layout` is the one worth knowing about.** Both arrangements were
discussed with the client; this ships with `rail` and the other is a
one-word change so they can be compared on the live page:

- **`rail`** (default) — the short "in a nutshell" wording sits in a
  sticky side rail beside the service name; the detail prose from the Word
  documents stays visible; the Q&As collapse under one *Information &
  FAQs* heading.
- **`accordion`** — the short wording becomes a standfirst under the
  heading, and the detail prose folds into the same accordion as a leading
  *Overview* panel. More compact; the trade-off is that a visitor landing
  on a section sees only headings until they click.

## Where the content lives

All copy is in `inc/content.php`, transcribed from the client's Word
documents (28 July 2026 set, plus the amendments in *Comments to Andrew*,
10 August 2026):

| Function | Contents |
|---|---|
| `ssvc_page_intro()` | The "Our Expertise" header wording |
| `ssvc_sections()` | The ten practice areas, in page order |
| `ssvc_nutshell_groups()` | The *In a Nutshell* index, grouped |
| `ssvc_icons()` (`inc/icons.php`) | The service icons |

Each runs through a filter of the same name (`sinclairs_services_content`,
`sinclairs_services_nutshell`, `sinclairs_services_intro`,
`sinclairs_services_icons`), so wording can be amended from a child theme
without editing the plugin.

Reordering sections = reordering the array in `ssvc_sections()`. That one
array drives the section order, the numerals, the jump box and the nav
dropdown at once.

### Icons

The ten icons are the client's existing ones, lifted verbatim from the
live page's `sc-acc-icon` markup. They're 24×24 line icons using
`currentColor`, so they take their colour from CSS. Where a section was
renamed the icon carried across:

| Icon from | Now on |
|---|---|
| Compliance & Regulatory Law | AML/CFT and Regulatory Compliance |
| Digital / Virtual Assets Service Provider Advice | Virtual Assets and FinTech |
| Trademarks | Trade Marks |

## The nav dropdown

`inc/nav.php` attaches to whichever menu item points at the Services page
— matched on URL path first, falling back to the title — so it works with
the existing "primary-menu" with no theme edit. It's a plain
single-column list of names, per the reference the client supplied.

Turn it off with:

```php
add_filter( 'sinclairs_services_nav_dropdown', '__return_false' );
```

If the Services page ever moves:

```php
add_filter( 'sinclairs_services_page_url', function () {
	return home_url( '/expertise/' );
} );
```

## Colours

Taken from the live site's own CSS rather than invented — `#0C4DA2`
primary blue, `#015797` navy, `#65C8D0` teal, `#ecdbc9` sand (the tile
behind the icons, matching the existing practice boxes), `#efefef`
surface, `#202020` ink. They're declared as custom properties on
`.sc-services` at the top of `assets/frontend.css`, so changing the
palette is a five-line edit.

## Accessibility / no-JS

Everything works without JavaScript: the index links to every section, the
FAQs are native `<details>`, and the nav dropdown opens on hover and
`:focus-within` in CSS. `assets/frontend.js` only adds smooth scrolling
with the sticky-header offset, tap toggling for the dropdown on touch
devices, and opening a FAQ that a URL hash points directly at.

## Before this goes live — still open

- **Confirm the existing shortcode tag.** The page currently calls the old
  plugin through an Elementor Shortcode widget, and the tag it uses isn't
  visible in the page source. If it isn't `[sinclairs_services]`, either
  update the widget or add an alias:
  `add_shortcode( 'old_tag', 'ssvc_shortcode_services' );`
- **"About Sinclairs (BVI)" was never supplied.** It was item 2 on the
  client's list but no document arrived, so there's no About content here.
- **Residential Conveyancing has no detail copy.** It appears in the
  Nutshell document but has no section of its own, so its index row
  renders as plain text rather than a link to nowhere. It needs either a
  section of its own or removal from the index.
- **Investment Business has no client-supplied nutshell line.** The
  Nutshell document predates that copy (Adenike's note said the remaining
  services would follow separately), so the side-rail wording there is
  carried over from the approved theme copy and should be confirmed.
- **Two source typos were corrected**, both worth flagging back:
  - The Nutshell document's Banking & Corporate Finance line opens
    "(whether…" and never closes the bracket. Closed here.
  - The *Comments to Andrew* contact row edits to `+1 (284) 54523454`
    — eight digits. The footer row in the same document reads
    `545-2454` cleanly, and that matches the current site, so **545-2454**
    is what the client confirmed. (Contact details were out of scope for
    this change and have not been touched.)
- **Section order** follows the client's own numbered list, which puts
  AML/CFT before Virtual Assets — the reverse of the current live page.
  One array move in `ssvc_sections()` if they'd rather keep the old order.
