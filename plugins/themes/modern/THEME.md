# Modern theme for Open Monograph Press

A clean, book‑cover‑forward child theme for OMP. It **inherits everything from
the bundled Default theme** and layers a modern publishing aesthetic on top:

- Moss / paper / ink / line palette (WCAG AA on paper)
- Serif display headings (Lora) + humanist sans body (IBM Plex Sans), with
  Arabic fallbacks (IBM Plex Sans Arabic / Noto Naskh Arabic)
- Generous whitespace, strong typographic hierarchy
- Cover‑forward homepage & catalog grids, a redesigned book landing page with a
  sticky metadata sidebar and per‑format download buttons
- Sticky header, prominent language switcher
- Mobile‑first responsive + full RTL support
- Accessibility: visible focus rings, skip link, semantic landmarks

Because it is a **child theme**, it never edits core templates in `templates/`
or `lib/pkp/`, so it survives OMP upgrades.

---

## Install & activate

1. The theme lives at `plugins/themes/modern/`. If you deploy OMP from a built
   image, make sure this directory ships inside the image (it is part of the
   repo under `omp/plugins/themes/modern`).
2. In the admin UI go to **Settings → Website → Appearance → Theme** and choose
   **Modern Theme**, then **Save**.
3. Options appear below the theme picker:
   - **Accent colour** – drives header, links, buttons, highlights (default
     `#2f6b45`).
   - **Homepage hero** – toggle the hero band on the homepage.
   - All the inherited Default‑theme options (typography, homepage image as
     header, etc.) remain available.
4. **Clear the template cache** after any template change:
   ```
   rm -f cache/t_compile/*.php
   rm -f cache/_db*  # optional
   ```
   In this Docker setup:
   ```
   docker exec omp-app sh -c 'rm -f /var/www/html/cache/t_compile/*.php'
   ```
   Compiled CSS is regenerated automatically on the next request.

---

## Where the design tokens live

Tokens are defined **once as CSS custom properties** at the top of
[`styles/modern.less`](styles/modern.less) (`:root { --ink, --paper, --moss,
--line, … }`). The *same* values are fed to the **inherited Default stylesheet**
as LESS variables in [`ModernThemePlugin.php`](ModernThemePlugin.php)
(`$lessVariables`), so the recoloured Default components and the new Modern
components stay in sync.

To change a colour or font:

- **Palette / fonts globally:** edit both the `:root` block in `styles/modern.less`
  **and** the matching entry in `$lessVariables` in `ModernThemePlugin.php`.
- **Accent only, per press:** just use the *Accent colour* option in the admin —
  it overrides `@primary` / `@bg-base` on the inherited stylesheet at runtime.

Web fonts are loaded from Google Fonts in `ModernThemePlugin::init()`
(`addStyle('modernFonts', …)`). To self‑host, drop the font files into
`styles/fonts/`, add an `@font-face` block, and replace that `addStyle` call.

---

## How to override more templates

Template resolution walks the theme chain: **Modern → Default → app → lib/pkp**,
first match wins. To customise another page, copy the original into the theme
under the *same relative path* and edit only markup/classes:

```
# example: customise the catalog page
cp templates/frontend/pages/catalog.tpl \
   plugins/themes/modern/templates/frontend/pages/catalog.tpl
```

Common source locations:

| Page / part            | Original template                                         |
|------------------------|-----------------------------------------------------------|
| Homepage               | `templates/frontend/pages/index.tpl` *(overridden here)*  |
| Catalog                | `templates/frontend/pages/catalog.tpl`                    |
| Book landing           | `templates/frontend/objects/monograph_full.tpl`           |
| Catalog card           | `templates/frontend/objects/monograph_summary.tpl`        |
| Series / category      | `templates/frontend/pages/catalogSeries.tpl` / `catalogCategory.tpl` |
| Header                 | `lib/pkp/templates/frontend/components/header.tpl`        |
| Footer                 | `lib/pkp/templates/frontend/components/footer.tpl`        |

**Keep every Smarty variable, `{include}`, and `{call_hook}` intact** — change
only markup, classes and styles. Then clear `cache/t_compile`.

Currently the theme overrides **only** `templates/frontend/pages/index.tpl`
(to add the hero). Everything else is restyled purely through
`styles/modern.less`, which is the lowest‑risk way to keep upgrade safety.

---

## Files in this theme

```
plugins/themes/modern/
├── index.php                         # plugin loader (namespace autoload)
├── ModernThemePlugin.php             # extends ThemePlugin; setParent('default')
├── version.xml                       # plugin version metadata
├── settings.xml                      # default settings (enabled=true)
├── THEME.md                          # this file
├── locale/
│   ├── en/locale.po                  # English strings
│   └── ar/locale.po                  # Arabic strings
├── styles/
│   └── modern.less                   # tokens (CSS custom props) + components
└── templates/
    └── frontend/pages/index.tpl      # homepage override (adds hero)
```
