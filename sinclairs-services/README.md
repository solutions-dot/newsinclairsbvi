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
2. **Deactivate, then activate** under **Plugins**. Activation is what
   creates the `practice-areas` child page and points the ten menu items
   at it — upgrading the files alone does not run it.
3. Edit **Our Services** in Elementor and set its Shortcode widget to
   `[sinclairs_services_index]`. (If it still says `[sinclairs_services]`
   the page renders the index *and* all ten sections together — the
   single-page arrangement, which also works, just isn't the split.)
4. **Settings → Permalinks → Save** once, so the child page's URL
   resolves.
5. Check for an admin notice — if anything is still missing it says so
   and links to the page concerned.

### What activation does, and doesn't

| | Behaviour |
|---|---|
| **Pages** | The index page and its `practice-areas` child are created **only if missing**. An existing page is **never** modified — see the warning below. |
| **Menu items** | Ten child items under the "Our Services" menu item, each linking to the detail page's anchor. |
| **Migration** | Children already pointing at the *old* single-page anchors (`/our-services/#trade-marks`) are **repointed** to the detail page rather than skipped — otherwise a menu seeded before the page split would silently break. Matched on the URL fragment, so renamed items survive. |
| **Anchors** | Not seeded — the section `id`s are emitted by the shortcode, so they exist wherever it renders. |

Both creating and repointing are idempotent: a second run changes nothing.

> **An existing Our Services page is never rewritten.** On this site that
> page is Elementor-built: its shortcode lives inside `_elementor_data`,
> not `post_content`, and Elementor replaces `post_content` wholesale when
> it renders. Writing there would be invisible at best and destructive at
> worst. So when the shortcode is missing from an Elementor page, the
> plugin says so in an admin notice and links to the page — it will not
> guess. Set the Shortcode widget to `[sinclairs_services]` by hand.

Everything is idempotent, so deactivating and reactivating is a safe way
to re-run seeding after building the menu or page.

## The two pages

The index and the detail sections live on separate pages:

| Page | Shortcode | Role |
|---|---|---|
| `/our-services/` | `[sinclairs_services_index]` | Jump box + *In a Nutshell* index. Where the nav "Our Services" link lands. Each row crosses to the detail page's anchor. |
| `/our-services/practice-areas/` | `[sinclairs_services_detail]` | The ten sections with their anchors and *Information & FAQs*. Where the nav dropdown items land. |

## Shortcodes

| Shortcode | Renders |
|---|---|
| `[sinclairs_services_index]` | Jump box + *In a Nutshell* index |
| `[sinclairs_services_detail]` | The ten sections |
| `[sinclairs_services]` | Everything on one page (single-page arrangement) |
| `[sinclairs_services_nutshell]` | Just the index |
| `[sinclairs_services_menu]` | Just the jump-to dropdown |
| `[sinclairs_services_summary]` | Alias of `_index` — the tag the previous plugin registered, kept so the home page's existing call doesn't break |

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

There are two mechanisms, and the plugin picks between them so they can
never both show.

### Seeded menu items (what you get by default)

**On activation** the plugin adds real child menu items under whichever
menu item points at the Services page — custom links to
`/our-services/#anchor`, one per practice area. They appear in
**Appearance → Menus** like any other item, so they can be reordered,
renamed or deleted, and the theme renders them as its own native submenu.

Seeding is idempotent: an item whose URL already exists under the parent
is skipped, so activating again never duplicates.

If the menu didn't exist yet at activation — or the "Our Services" item
was added afterwards — an admin notice appears with an **Add the submenu
items** button. Deactivating and reactivating works too.

`assets/frontend.css` gives the seeded submenu the look from the client's
reference (plain list, teal top rule). Those rules set appearance only —
no `display`, `visibility`, `position` or `transform` — so the theme's own
open/close behaviour is untouched. Delete that block to use the theme's
native dropdown styling instead.

### Injected dropdown (fallback)

If nothing has been seeded, `inc/nav.php` renders the panel at request
time instead, attaching to the same menu item — matched on URL path
first, falling back to the title. This needs no menu changes at all, but
the panel is invisible in wp-admin, so it can't be edited there.

Seeding sets the `ssvc_menu_seeded` option, which turns this off
automatically. Force it either way with:

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
