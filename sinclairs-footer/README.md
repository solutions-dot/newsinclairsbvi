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

## Attributes

```
[sinclairs_footer theme="dark"]      ink text, for a pale panel
[sinclairs_footer headings="no"]     drop the column headings
[sinclairs_footer align="center"]    centre each column's block in its
                                     cell (text stays ranged left)
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
| ≤ 600px | One centred column. The contact and hours rows stay left-aligned inside it — an icon plus two lines of text centres badly, and a centred address wraps to a ragged triangle |

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
- The `screen-reader-text` class is defined in the plugin's own CSS
  rather than relied on from the theme: without a definition the
  "Telephone:" / "Address:" labels would render as visible stray words.
