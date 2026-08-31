# Sinclairs (BVI) — Hero Slider

A small, self-contained replacement for Slider Revolution on sinclairsbvi.com.
Upload 1920 × 1080 images, set a focal point per slide, position the heading and
buttons independently, pick a transition, and show the slide navigation in a band
**below** the images rather than floating on top of them.

The plugin is theme-agnostic. It never loads a font of its own, so headings and
buttons inherit whatever typeface the active theme already uses and the slider
matches the rest of the site out of the box.

## Installing

1. Zip the `sinclairs-slider` folder and upload it under **Plugins → Add New → Upload Plugin**, or copy the folder into `wp-content/plugins/`.
2. Activate **Sinclairs (BVI) — Hero Slider**.
3. Add your slides under **Slider → Add Slide**.
4. Put `[sinclairs_slider]` where the old slider used to be (or insert the **Sinclairs Slider** block).
5. Once it looks right, deactivate Slider Revolution.

Nothing is deleted when the plugin is deactivated — slides are ordinary posts and
stay in the database.

## Per-slide options

| Setting | What it does |
| --- | --- |
| **Image** | Media Library picker. Upload at 1920 × 1080 (16:9). |
| **Focal point** | Click anywhere on the image to mark the spot that must stay visible. Arrow keys nudge it; Shift + arrow moves in bigger steps. Drives `object-position`, so the subject is never cropped out on a phone. |
| **Alt text** | Falls back to the Media Library alt text if left blank. |
| **Darken image** | Tint strength and colour, so light text stays readable over a photograph. |
| **Heading / sub-heading** | Press Enter for a deliberate line break. Leave blank for an image-only slide. |
| **Heading size** | Separate desktop and mobile sizes. The rendered size scales smoothly between the two, so there is no jump at the breakpoint. |
| **Position** | A 9-point grid plus an X/Y nudge in percent, for the text block. |
| **Alignment, colour, max width** | Per slide. |
| **Buttons** | Up to two per slide, each with a label, link, new-tab option and style (solid pill / outline / text link), plus size and colour. Any slide can have none. |
| **Button position** | By default the buttons sit directly under the text. Untick that and they get their own 9-point grid and nudge, so they can go anywhere on the frame independently of the heading. |
| **On phones** | Re-centres text and buttons on narrow screens so an edge-anchored desktop layout does not fall off the side. Can be turned off per slide. |

Slides play in the order shown on the **Slider** screen. Change the order with the
**Order** field under Page Attributes on each slide.

## Slider-wide settings (Slider → Settings)

- **Transition** — fade, slide, zoom fade, Ken Burns, or an instant cut; transition speed; time on each slide; autoplay, loop and pause-on-hover.
- **Navigation** — thumbnails, dots, progress bars, or none, with optional previous/next arrows. It always renders in its own band under the images.
- **Size** — full width (edge to edge, breaking out of the page container) or contained; and desktop and mobile height independently: full 16:9, a fixed pixel height cropped to the focal point, or full screen height.
- **Appearance** — the default button colour, and an optional font override for the rare case where the hero is meant to differ from the rest of the site. Leave the font blank to inherit the theme.

## Output

```
[sinclairs_slider]                    All published slides, in order
[sinclairs_slider ids="12,8,3"]       Only those slides, in that order
[sinclairs_slider width="contained"]  Box it inside the page width on this page only
```

In a theme template:

```php
<?php if ( function_exists( 'sinclairs_slider' ) ) { sinclairs_slider(); } ?>
```

## Notes

- No jQuery and no build step: one CSS file and one ~7 KB JavaScript file, loaded only on pages that actually render a slider.
- The first slide loads eagerly with `fetchpriority="high"`; the rest load lazily, so the slider does not slow down the first paint.
- Keyboard (left/right arrows), touch swipe, and screen-reader labelling are all supported, and autoplay pauses on hover, on focus, when the tab is hidden and when the slider scrolls out of view.
- Visitors who have asked their system for reduced motion get no autoplay and no transition animation.
