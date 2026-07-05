# Logo assets

This folder is the drop location for the final South Forsyth badge/logo
files, once they exist as real image assets. **No final logo file has been
added yet** — nothing in the theme assumes a specific filename here. See
"Brand identity" in the project root `README.md` for the full color
palette and design direction this logo should follow (circular community
badge, navy border, warm cream fill, forest-green accents, "Discover •
Connect • Volunteer" tagline).

## How the site actually uses a logo today

The theme supports WordPress's native **custom logo** feature
(`add_theme_support('custom-logo', ...)` in `inc/setup.php`). The
recommended way to set the real logo is:

1. Go to **Appearance → Customize → Site Identity** in wp-admin.
2. Upload the logo image there (WordPress stores it in the Media Library,
   not in this theme folder).
3. `template-parts/header/site-header.php` picks it up automatically via
   `has_custom_logo()` / `the_custom_logo()` — no code or template change
   needed.

Until a logo is uploaded that way, the header falls back to
`assets/icons/logo-mark.svg` (a small circular badge mark already styled to
the new brand palette) plus the site title text — see "Fallback behavior"
below.

## When would a file actually go in *this* folder?

If a source/vector version of the final logo needs to live in the repo
(e.g. for reuse in print materials, favicons, or as the bundled
`assets/icons/logo-mark.svg` fallback's replacement), drop it here using a
descriptive name, for example:

- `southforsyth-badge.svg` — preferred: a vector source is resolution
  independent and easiest to recolor or resize later.
- `southforsyth-badge@2x.png` — a raster export, if a vector isn't
  available, at 2x the largest size it'll be displayed at.

Whoever adds the real file should also update
`template-parts/header/site-header.php`'s fallback `<img>` path (or better,
just upload it via the Customizer as described above, which requires no
code change at all).

## Fallback behavior

`site-header.php` always shows the site title (`bloginfo('name')`) next to
whatever logo is present, so the header never depends on an image loading
successfully:

- **Custom logo uploaded** → WordPress's own `the_custom_logo()` output,
  circular-cropped via CSS (`.brand img, .brand .custom-logo` in
  `assets/css/main.css`) to keep the circular badge feel regardless of the
  uploaded image's original shape.
- **No custom logo uploaded** → `assets/icons/logo-mark.svg`, a small
  circular badge (navy ring, cream fill, forest-green mark, gold accent)
  already matching the brand palette.
- **Either way** → the site title and the "Discover • Connect • Volunteer"
  tagline render next to it.
