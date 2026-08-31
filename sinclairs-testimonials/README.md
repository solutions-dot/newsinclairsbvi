# Sinclairs (BVI) — Testimonials

A testimonials carousel: the client's **name and title at the top**, their
words **below in quotes**. One shortcode.

```
[sinclairs_testimonials]
```

## What it does differently from the old one

- **The card fits the testimonial.** The previous carousel sat at a fixed
  height, so short quotes left a large empty gap underneath. Here the
  height follows whichever testimonial is showing and animates between
  them.
- **Name and title first, quote after** — the order asked for.
- **Arrows and dots sit under the card**, not overlapping the text.
- **Mobile**: swipe to move between testimonials, 44px touch targets,
  and type and padding that scale down rather than being pinned to
  desktop sizes.

## Attributes

```
[sinclairs_testimonials heading="What clients say"]   heading above the card
[sinclairs_testimonials autoplay="yes"]               advance automatically
[sinclairs_testimonials delay="12000"]                ms per testimonial
[sinclairs_testimonials arrows="no"]                  hide previous / next
[sinclairs_testimonials dots="no"]                    hide the position dots
```

Autoplay is **off by default**. Reading speed varies far more than a
timer can allow for, so a testimonial that moves on by itself while
someone is mid-sentence is usually an annoyance rather than a feature.
When it is on, it pauses on hover, on keyboard focus, when the tab is
hidden and when the carousel scrolls out of view, and is suppressed
entirely for visitors who have asked their system for reduced motion.

Also available as a template tag:

```php
<?php if ( function_exists( 'sinclairs_testimonials' ) ) { sinclairs_testimonials(); } ?>
```

## The content

The five testimonials are transcribed verbatim from the client's Word
document (*Testimonials for Website for Sinclairs BVI*, v03 / v02
250825) and live in `inc/content.php`. The attribution that trailed each
quote — "– Benoit Q. Asset Management Director" — is split into a name
and a title so the carousel can set them above the quote, and the curly
quotation marks around each one are dropped because the styling supplies
them.

To change the wording, edit `inc/content.php`, or filter it without
touching the plugin:

```php
add_filter( 'sinclairs_testimonials', function ( $items ) {
    $items[] = array(
        'name'  => 'A. Client',
        'role'  => 'Director',
        'quote' => array( 'First paragraph.', 'Second paragraph.' ),
    );
    return $items;
} );
```

## Notes

- No font is registered: the carousel inherits the theme's typeface, so
  it matches the rest of the site. Colours are the site's existing
  palette — the same values the Our Services plugin uses, including the
  teal of the current carousel's arrows and dots.
- Vanilla JS, no jQuery, no build step. Assets load only on pages that
  actually render the shortcode.
- Every testimonial is real text in the page, so search engines and
  find-in-page see all of them, not just the visible one. With
  JavaScript off they stack down the page and stay readable.
- The quotation marks are generated in CSS, so they wrap the whole quote
  however many paragraphs it runs to, and are never read out as stray
  characters by a screen reader.
