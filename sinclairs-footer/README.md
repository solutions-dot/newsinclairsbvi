# Sinclairs (BVI) — Footer

The site footer as one shortcode: navigation, opening hours and contact
details in three balanced columns, each row with an icon.

```
[sinclairs_footer]
```

Drop it into the teal footer section in place of the three separate
widgets. It does **not** include the black copyright bar underneath —
that stays as it is.

## What it gives you

- **Three columns**: Explore (the five page links), Opening Hours, Get in
  Touch. The middle column is the short one on purpose, so the two long
  columns sit either side of it rather than everything bunching to the
  left.
- **Every link resolves to a real page.** Slugs are looked up rather than
  hard-coded — the Services page has lived at both `/our-services/` and
  `/our-services2/`, so a fixed path is a 404 waiting to happen.
- **Actionable contact rows.** Phone numbers dial on a mobile, the email
  opens a mail client, the address opens a map. That is most of what a
  footer is for on a phone.
- **Icons** for hours (clock), phone, email and address, plus the arrow
  on each nav link — inline SVG, so no icon font and no flash of missing
  glyphs, and each takes its colour from the footer.
- **Same type size as the rest of the page**: the footer sets no font
  size on its body text, so it inherits whatever the page uses.

No column headings ("Explore", "Opening Hours", "Get in Touch") or the rule
under them by default — the three lists just start at the top of their
column. Add `headings="yes"` to bring them back.

## Attributes

```
[sinclairs_footer theme="dark"]      ink text, for a pale panel
[sinclairs_footer headings="yes"]    show the column headings and the
                                     rule under them (off by default)
[sinclairs_footer align="center"]    centre each column's block in its
                                     cell (text stays ranged left)
[sinclairs_footer collapse="no"]     stop the columns folding on mobile
                                     (only relevant with headings="yes" —
                                     without a heading there is nothing
                                     to tap, so the columns are always
                                     open either way)
[sinclairs_footer padding="sm"]      less space above and below; also
                                     "none", "lg", or an exact length
                                     such as padding="12px"
```

Also available as a template tag:

```php
<?php if ( function_exists( 'sinclairs_footer' ) ) { sinclairs_footer(); } ?>
```

## How it balances

| Width | Layout |
|---|---|
| Desktop | Three columns, sized to their content rather than equal thirds — the hours column is short and would leave a hole in the middle at `1fr`, while the address needs the room |
| ≤ 900px | Two columns, with the contact block spanning the full width beneath, since the address is the long one and would otherwise wrap awkwardly |
| ≤ 600px | One column. With `headings="yes"` each section folds under its heading, all three starting closed, tap to open; without a heading (the default) there is nothing to tap, so all three stay expanded. Either way each block is centred as a unit with its contents ranged left, and the three share a width so the arrows and icons line up down the page |

## Changing the content

Everything runs through filters, so nothing needs editing in the plugin:

```php
add_filter( 'sinclairs_footer_links',   function ( $links ) { …; return $links; } );
add_filter( 'sinclairs_footer_hours',   function ( $hours ) { …; return $hours; } );
add_filter( 'sinclairs_footer_contact', function ( $rows )  { …; return $rows;  } );
```

Or edit `inc/content.php` directly.

## Notes

- No background colour of its own: it takes the colour of the section it
  sits in, so the existing teal panel keeps working.
- No font registered — it inherits the theme's typeface.
- The mobile accordion only exists with `headings="yes"` — without a
  heading there is no tap target, so the default is every column simply
  expanded on a phone, same as desktop. With `headings="yes"` it is
  native `details` / `summary`: the columns carry `open` in the markup
  and the script only ever *closes* them once it has confirmed the
  viewport is narrow, so with JavaScript off every column stays open
  rather than a visitor being left with a footer they cannot open. On
  desktop the summary is inert, so a click on a heading cannot fold a
  column away where nothing would re-open it. All three start closed on
  a phone, and a section someone has opened there is not shut again by a
  resize, an on-screen keyboard, or the address bar collapsing.
- The `screen-reader-text` class is defined in the plugin's own CSS
  rather than relied on from the theme: without a definition the
  "Telephone:" / "Address:" labels would render as visible stray words.
