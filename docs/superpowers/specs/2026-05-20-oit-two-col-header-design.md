# OIT Two-Col Header — Design Spec

**Date:** 2026-05-20
**Block:** `proto-blocks/oit-two-col-header`
**Design source:** Figma node `23:333` ("Industries / Manufacturing" page header) in file `8Bk0ThqaYfXBWeztBgy56V`.

## Purpose

Section-page header used at the top of industry / sub-section pages (Manufacturing, Healthcare, Finance, etc.). Renders a breadcrumb derived from the page hierarchy, an industry icon, a headline and subtitle, a 608×400 featured image on the right, and a giant background wordmark in `#F5D6DA` at 40% opacity behind everything. Light theme only.

Closely related to the existing `oit-page-header` block, but differs in three ways that make a separate block the right choice:

- Two-column layout (content left, featured image right) vs. single-column with right-side decoration.
- No "card" chrome around the content — text sits on the page background directly.
- Industry icon stacked above the title (`oit-page-header` puts its optional icon inside a floating badge to the right).

## Block identity

| Field            | Value                                         |
|------------------|-----------------------------------------------|
| Block name       | `proto-blocks/oit-two-col-header`             |
| Title            | OIT Two-Col Header                            |
| Category         | `optimizedit`                                 |
| Icon (Dashicon)  | `align-left`                                  |
| Supports         | `anchor`, `customClassName`, `align: wide/full` |
| Tailwind v4      | `useTailwind: true`                           |

No `isLight` or theme-variant control. Light-only is a global theme directive (see memory `feedback-no-dark-mode`).

## Fields (inline-editable)

| Key             | Type     | Notes                                                                                          |
|-----------------|----------|------------------------------------------------------------------------------------------------|
| `industryIcon`  | `image`  | Sizes: `thumbnail`, `medium`. Light-grey placeholder if empty. Sits above the title.           |
| `title`         | `wysiwyg`| Main headline. Supports the `highlightWord` red-span treatment.                                |
| `subtitle`      | `wysiwyg`| Supporting line. Rendered with `[&_p]:m-0` to suppress paragraph margins.                      |
| `featuredImage` | `image`  | Sizes: `medium`, `large`, `full`. 608×400 aspect, `rounded-3xl`, `shadow-red-glow`, `object-cover`. Light-grey `#E6E6E8` placeholder when empty. |

## Controls (sidebar)

| Key             | Type   | Default | Notes                                                                                                   |
|-----------------|--------|---------|---------------------------------------------------------------------------------------------------------|
| `wordmark`      | `text` | `""`    | Giant uppercase background word. Empty string hides it. Editor types per page (e.g. "Manufacturing").   |
| `highlightWord` | `text` | `""`    | First case-insensitive occurrence in `title` is wrapped in a `text-brand-red` span.                     |

## Auto-derived

**Breadcrumb trail.** Same logic as `oit-page-header`:

- Always starts with `Home → home_url('/')`.
- Then `get_post_ancestors($current_id)` reversed (top-down).
- Final crumb is `get_the_title($current_id)`, unlinked, painted brand red.
- If no current post (editor preview / template insertion) → `Home → Current` stub.

## Deliberately omitted

- CTAs (not in the Figma).
- Light/dark toggle.
- Circuit / icon-badge / Lottie decoration toggles.
- Featured-image aspect-ratio or shadow controls.

If a future variant needs any of these, add them then — not pre-emptively.

## Shared partials (refactor)

Pull the breadcrumb and wordmark rendering out of `oit-page-header/template.php` into a new theme include so both blocks share them:

```
inc/
└── oit-header-partials.php
```

Exposed via `require_once get_stylesheet_directory() . '/inc/oit-header-partials.php'` from `functions.php`.

Two functions, both `if (!function_exists(...))`-guarded so the file is safe to load twice:

- `oit_render_breadcrumb(): void` — derives the trail from `get_the_ID()` ancestors, prints the `<nav class="oit-page-header__breadcrumb">...</nav>` markup with the existing chevron SVG and per-crumb class names. Class names stay as `oit-page-header__breadcrumb` etc. because the existing block's CSS and `view.js` key off them — renaming would force a three-file rewrite for zero visual gain. The new block reuses the same selectors.
- `oit_render_wordmark(string $text): void` — prints the `<div class="oit-page-header__wordmark">…<span class="oit-page-header__wordmark-text">…</span></div>` markup. Empty string → nothing rendered. The `clamp(56px, 13vw, 186px)` font-size rule already lives in `oit-page-header/style.css`; that CSS keeps working because the partial emits the same class names.

`oit-page-header/template.php` is updated to call these helpers in place of its inline breadcrumb/wordmark blocks. Behavior must be byte-identical to today (see Verification below).

## Layout

### Desktop (≥1024px)

```
┌─────────────────────────────────────────────────────────────────────┐
│  section: max-w-[1440px], mx-auto, px-6 lg:px-20, py-10 lg:py-16    │
│                                                                     │
│   Home › Industries Overview › Manufacturing      (breadcrumb)      │
│                                                                     │
│   ┌─────────────────────────────┐   ┌────────────────────────────┐  │
│   │ [industry icon ≈75×82]      │   │                            │  │
│   │                             │   │  featured image            │  │
│   │ IT Services For             │   │  608×400, rounded-3xl,     │  │
│   │ Manufacturing  (red span)   │   │  shadow-red-glow,          │  │
│   │                             │   │  object-cover              │  │
│   │ Every minute of downtime…   │   │                            │  │
│   └─────────────────────────────┘   └────────────────────────────┘  │
│                                                                     │
│              ▒▒ MANUFACTURING ▒▒  (wordmark, behind cols, z-0)      │
└─────────────────────────────────────────────────────────────────────┘
```

- Two-column CSS grid: `grid-cols-2 gap-16`. Both columns vertically aligned to top (`items-start`).
- Left column: vertical `flex flex-col gap-5`, items in order — industry icon, title, subtitle.
- Right column: fixed `aspect-[608/400]` container (aspect-ratio rather than fixed height so the mobile full-width stack scales proportionally without a media query), `rounded-3xl`, `shadow-red-glow`, `overflow-clip`. Image is `object-cover w-full h-full`. Empty state is a `bg-light-grey` block at the same aspect.
- Wordmark is absolute at the section level, behind both columns (`z-0`, `pointer-events-none`, `select-none`). Sits at the bottom of the section so it spills below the columns the way the Figma frame shows ("MANUFACTU" clipped at the right edge).

### Mobile (<1024px)

Single-column stack, in this order:

1. Breadcrumb
2. Industry icon
3. Title
4. Subtitle
5. Featured image — full width, same aspect ratio

`gap-6` between stack items. Image keeps `rounded-3xl` and `shadow-red-glow`. Wordmark stays as a background watermark sized by the existing `clamp(56px, 13vw, 186px)` rule.

## Typography & color

| Element             | Token / Tailwind                                                   |
|---------------------|--------------------------------------------------------------------|
| Breadcrumb crumbs   | `font-grotesk font-medium text-body-sm leading-[1.3]`, `text-black` (current crumb: `text-brand-red`) |
| Title               | Global `h1` scale (Space Grotesk Bold 36px mobile / 56px desktop). Headline color is `text-black`. Highlighted word and any period accent: `text-brand-red`. |
| Subtitle            | `font-dm font-medium text-body-sm lg:text-body-md leading-[1.5]`, `text-black`, `max-w-[820px]` |
| Wordmark            | `font-grotesk font-bold uppercase`, color `#F5D6DA`, opacity `0.4`, fluid `clamp(56px, 13vw, 186px)` |
| Image placeholder   | `bg-light-grey` (`#E6E6E8`)                                        |

All colors above are existing tokens in `tailwind-theme.css` — no new variables needed.

## Motion (scroll-triggered reveal)

Personality matches `oit-page-header`: corporate / decelerated, ease `power3.out`, total runtime ~1.4 s, fires once when the section's top crosses 80% of the viewport from below.

| Offset | Target            | Animation                                            | Duration  |
|--------|-------------------|------------------------------------------------------|-----------|
| 0.00 s | Section (whole)   | `opacity 0→1, y +20→0`                               | 0.7 s     |
| 0.10 s | Featured image    | `opacity 0→1, scale 0.96→1`, transformOrigin center  | 0.7 s     |
| 0.20 s | Wordmark chars    | `opacity 0→1, y +60→0`, stagger 0.04 s               | 0.5 s/ch  |
| 0.30 s | Breadcrumb crumbs | `opacity 0→1, y +8→0`, stagger 0.05 s                | 0.4 s     |
| 0.40 s | Industry icon     | `opacity 0→1, y +12→0`                               | 0.5 s     |
| 0.50 s | Title words       | SplitText words, `opacity 0→1, y +20→0`, stagger 0.04 s | 0.55 s |
| 0.80 s | Subtitle          | `opacity 0→1, y +12→0`                               | 0.5 s     |

### Safety nets

- `prefers-reduced-motion: reduce` → flip `data-animate="done"` immediately, no animation runs.
- GSAP / ScrollTrigger missing → same static reveal path.
- SplitText missing → animate the whole title and the whole wordmark wrappers instead of per-word / per-char.
- Editor preview (`$block === null`) → skip `data-animate="pending"` so the canvas isn't blank.

### Period accent

Reuse the `wrapPeriods()` walker from `oit-page-header/view.js`: any `.` inside the title is wrapped in `<span class="text-brand-red">.</span>`. Idempotent via a `data-` flag, runs before SplitText so the spans become part of their parent word.

## File layout

```
inc/
└── oit-header-partials.php          # NEW: oit_render_breadcrumb(), oit_render_wordmark($text)

functions.php                         # MODIFIED: require_once inc/oit-header-partials.php

proto-blocks/
├── oit-page-header/
│   └── template.php                  # MODIFIED: calls oit_render_breadcrumb() and
│                                     #           oit_render_wordmark($wordmark) instead of
│                                     #           inlining that markup.
└── oit-two-col-header/               # NEW DIRECTORY
    ├── block.json                    # fields + controls + protoBlocks meta
    ├── template.php                  # markup; calls the two shared partials
    ├── style.css                     # data-animate="pending" pre-state only
    └── view.js                       # GSAP timeline w/ the 7 beats above + wrapPeriods()
```

## Risks & edge cases

- **Refactor regression on `oit-page-header`.** The two helpers must produce byte-identical markup to today's inlined output so existing CSS selectors and `view.js` keep working unchanged. Plan: extract carefully, then load the home page (which uses `oit-page-header`) and verify breadcrumb + wordmark still render and the reveal still plays.
- **Image without intrinsic dimensions.** Editor picks an image whose attachment meta lacks `width`/`height` → CLS. Mitigation: emit `width` and `height` attributes from the attachment meta when available, and always wrap in the fixed-aspect container so the layout box is reserved.
- **Mobile wordmark overlap.** At very narrow viewports the wordmark could overrun the image. The existing `clamp(56px, 13vw, 186px)` rule + the existing negative-top-margin `clamp` already handle this for `oit-page-header`; reusing the same partial means same behavior.
- **Editor canvas blank flash.** `data-animate="pending"` hides content pre-JS. Guard with `$is_preview = !isset($block) || $block === null` and only emit the attribute on the front end, matching `oit-page-header`.
- **Empty `featuredImage`.** Falls back to a `bg-light-grey` block at the same aspect ratio, so the layout doesn't collapse and the editor sees a clear "pick an image here" affordance.
- **Empty `industryIcon`.** Same pattern: a placeholder element at the icon's reserved dimensions so the column doesn't reflow when the icon is added.

## Verification (acceptance criteria)

1. Activate the theme. Insert *OIT Two-Col Header* on a page that has at least one ancestor. Verify breadcrumb auto-derives the trail and the current crumb is brand red.
2. Set `wordmark` to "Manufacturing". Verify the giant `#F5D6DA @ 0.4 opacity` wordmark renders behind both columns and clips at the right edge on wide viewports.
3. Pick an industry icon and a featured image. Verify:
   - Desktop: two columns side by side, image is `608×400` rounded with red glow.
   - Mobile: stacked, image full-width at the same aspect ratio.
4. Set `highlightWord` to a word in the title. Verify the first occurrence renders in brand red.
5. Scroll past the section from below: verify the 7-beat reveal plays once and the title's words stagger.
6. Toggle `prefers-reduced-motion: reduce` in DevTools and reload: verify the section appears in its final state with no animation.
7. Existing `oit-page-header` on the home page still renders and animates exactly as before (no regression from the partials extraction).
