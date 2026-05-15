# OptimizedIT

A WordPress block theme for the OptimizedIT site.

| Field | Value |
| --- | --- |
| Author | Provisions Group |
| Requires WordPress | 6.9 |
| Tested up to | 6.9 |
| Requires PHP | 8.0 |
| License | GPL v2 or later |

---

## Required plugin

This theme depends on the **Proto-Blocks** plugin. Without it, the theme's
custom block(s) under `proto-blocks/` will not be registered and the front-end
will fall back to the underlying WordPress block scaffolding.

> **Proto-Blocks** — Create Gutenberg blocks with PHP/HTML templates instead
> of React, with built-in Tailwind v4 support.
>
> - Repo: [GustavoGomez092/Proto-Blocks](https://github.com/GustavoGomez092/Proto-Blocks)
> - Latest plugin zip: [proto-blocks.zip](https://github.com/GustavoGomez092/Proto-Blocks/releases/tag/latest)

Install order matters: **install Proto-Blocks first, activate it, then install
this theme.**

## Installation

1. Download the Proto-Blocks plugin from the [latest release](https://github.com/GustavoGomez092/Proto-Blocks/releases/tag/latest) (`proto-blocks.zip`).
2. In WP Admin, go to **Plugins → Add New → Upload Plugin**, upload the zip, then **Activate**.
3. Clone or download this theme repo into `wp-content/themes/optimizedit/`.
4. In WP Admin, go to **Appearance → Themes**, activate **OptimizedIT**.
5. (Optional) In **Appearance → Menus**, build your menu and assign it to the
   **Primary Navigation** location used by the OIT Main Navigation block.

## What's in the box

```
optimizedit/
├── functions.php          # registers primary nav, scopes Proto-Blocks
│                          # category to "OptimizedIT", enqueues theme CSS
├── style.css              # theme header + Google Fonts + .oit-font-* helpers
├── theme.json             # global FSE styles
├── parts/                 # header.html, footer.html (template parts)
├── templates/             # index.html (FSE templates)
├── screenshot.png         # theme inserter preview
├── readme.txt             # WordPress-standard readme
└── proto-blocks/
    └── oit-navigation/    # OIT Main Navigation block
        ├── block.json
        ├── template.php
        ├── style.css      # pseudo-element + animated-underline CSS only
        └── view.js        # drawer / submenu interactivity
```

## OIT Main Navigation block

Block name: `proto-blocks/oit-navigation` (category: **OptimizedIT**).

| Source | Behavior |
| --- | --- |
| Menu items + children | Pulled from the WP nav menu assigned to the `primary` theme location |
| Logo, CTA button, phone number, social links | Editable fields on the block instance |
| Show CTA / phone / social / sticky / "View All" link | Inspector toggles |

**Desktop (≥ 1024 px):** logo + horizontal menu with hover-revealed dropdowns +
CTA pill.

**Mobile (< 1024 px):** logo + MENU / CLOSE toggle; tapping it slides the
nav open as a top-collapsing accordion. Each top-level item with children
expands inline into a darker-red submenu drawer with an animated red underline
on hover.

## Customization filters

The theme adds these filters via `functions.php`:

- `proto_blocks_category_slug` — renames the Proto-Blocks block category slug to `optimizedit`
- `proto_blocks_category_title` — renames its display title to *OptimizedIT*

Override per site if you want different branding.

## Changelog

### 1.0.0
- Initial release.
- OIT Main Navigation Proto-Block (desktop dropdowns + mobile accordion).
- `primary` nav menu location registered.
- OptimizedIT block-inserter category.

## License & copyright

OptimizedIT WordPress Theme, © 2026 Provisions Group.

Distributed under the [GNU General Public License v2 or later](http://www.gnu.org/licenses/gpl-2.0.html). This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but **WITHOUT ANY WARRANTY**; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
