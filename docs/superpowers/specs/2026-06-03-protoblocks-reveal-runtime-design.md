# Proto-Blocks `$block` Contract + `data-proto-animate` Reveal Runtime — Design

**Date:** 2026-06-03
**Status:** Approved (design)
**Repos touched:** `Proto-Blocks` plugin (separate git repo) and `optimizedit` theme (this repo, branch `development`)

## Problem

Proto-Blocks documents that templates receive `$block` (a `WP_Block` on the frontend, `null` in editor preview) so a template can detect preview via `$is_preview = ! isset($block) || $block === null;`. **The implementation never passes `$block` into the template scope** (`Renderer::executeTemplate` only extracts attributes + a `$template` helper). So `$block` is *always* null and `$is_preview` is *always* true.

Consequence: every theme block that adds `data-animate="pending"` only when **not** preview never adds it on the frontend. The GSAP/ScrollTrigger entrance animations have therefore been **dormant site-wide** — content always shows, animations never run. Naively "fixing" `$block` would activate ~16 blocks' pre-hide states at once; two of them (`oit-breadcrumbs-eyebrow-title`, `oit-text-image`) hide content via CSS but have **no reveal JS**, so they would render permanently invisible.

## Goals

1. Make the documented `$block` preview contract true (frontend = `WP_Block`, editor = `null`).
2. Turn the reveal lifecycle into a **plugin-owned convention** (`data-proto-animate`) that ships by default and is **safe by default** — content can never get stuck hidden.
3. Enable and QA entrance animations on all 16 theme blocks.
4. Document everything (code comments, this spec, skill docs, plugin CHANGELOG + version bump, animation authoring guide).

Non-goal: running entrance animations in the editor. Editor renders the **resting (revealed) state** so blocks stay editable. Frontend-only animation. (Interactive-content blocks like accordions/tabs may opt into editor JS case-by-case in the future; none exist today.)

## Decisions (from brainstorming)

- **Scope:** fix contract + safety net + polish all 16 blocks' animations.
- **Ownership:** the *convention + safety runtime* ships in the **plugin**; GSAP and per-block motion stay in the **theme**.
- **Attribute:** canonical `data-proto-animate` (consistent with `data-proto-field`/`-repeater`); the runtime also treats legacy `data-animate` as an alias. Migrate the 16 blocks to the canonical name.
- **Default motion:** none. The plugin provides only the **state machine + safety guarantees**; each block supplies its own motion (CSS transition on state, or `view.js`).
- **Verification:** PHP unit test + a render-assertion script across all blocks + live browser checks (human-driven).
- **Editor:** frontend-only animation; editor at resting state.

## Architecture

### Layer A — Plugin: `$block` threading (root fix)

Thread the real block instance down the frontend render path; pass `null` on the preview path.

- `Engine::renderBlock($attributes, $content, $block)` → call `$this->render($templatePath, $attributes, $metadata, $block)`.
- `Engine::render(..., ?\WP_Block $block = null)` → forward to `Renderer::render(..., $block)`.
- `Renderer::render(..., ?\WP_Block $block = null)` → forward to `executeTemplate(..., $block)`.
- `Renderer::renderPreview(...)` → calls `executeTemplate(...)` with **no** `$block` (defaults to `null`).
- `Renderer::executeTemplate(..., ?\WP_Block $block = null)` → preserve `$block` across `extract($variables)` (capture into a temp before extract, reassign after) so an attribute literally named `block` can't clobber it; `$block` is then in template scope.

All new params are optional with `null` defaults → backward compatible with any external callers.

### Layer B — Plugin: `data-proto-animate` reveal runtime (new, ships by default)

Frontend-only assets enqueued by the plugin (not loaded in the editor canvas):

- **`assets/reveal-runtime.js`** (vanilla, zero deps):
  - `IntersectionObserver` flips elements `[data-proto-animate="pending"]` → `"done"` when they scroll into view. **Skips** elements whose value is `"manual"`.
  - **Watchdog:** on `window.load` + a short grace delay, force any element still `"pending"` (including `"manual"`) to `"done"`. Nothing can stay hidden.
  - **Reduced motion:** if `matchMedia('(prefers-reduced-motion: reduce)')` matches, reveal all immediately on init (skip waiting for scroll).
  - **Alias:** also matches legacy `data-animate`.
  - Dispatches a `proto-blocks/reveal` (and matching `done`) event on each element so block `view.js` can hook the moment of reveal if desired.
- **`assets/reveal-runtime.css`** (safety only — no motion opinion):
  - `@media (prefers-reduced-motion: reduce)` → `[data-proto-animate]{transition:none!important;animation:none!important;}` (reveal without motion).
- **`<noscript>` net** printed on `wp_head` (frontend): `[data-proto-animate]{opacity:1!important;transform:none!important;}` so content is visible when JS is off.

The plugin ships **no** `pending` hide rule and **no** entrance motion — those belong to the block. The plugin only guarantees the state reaches `done` under every condition.

### Layer C — Theme: the 16 blocks

- Rename `data-animate` → `data-proto-animate` in each block's `template.php`, `style.css`, and `view.js`.
- Each block keeps gating the `pending` attribute on `$is_preview` (now correct): added on frontend, omitted in editor.
- The 12 GSAP blocks: mark their animated root `data-proto-animate="manual"` and keep their existing timelines; the watchdog backstops them.
- `oit-breadcrumbs-eyebrow-title` and `oit-text-image`: add reveal `view.js` matching the existing fade/slide pattern (they currently hide with no reveal).
- `oit-blog-header`, `oit-resources`: no hide CSS — migrate the attribute only.
- `oit-featured-cards`: remove the interim `REST_REQUEST`/`wp_doing_ajax` detection added during debugging; use the now-correct `$is_preview = ! isset($block) || $block === null;`.

### Editor behavior

`$is_preview` true in editor → templates omit `data-proto-animate="pending"` → content renders at resting state, fully editable, no entrance JS (the runtime isn't enqueued in the canvas anyway).

## Safety matrix

| Condition | Outcome |
|---|---|
| JS enabled, block in view | scroll-in reveal; block's own motion plays |
| `prefers-reduced-motion` | revealed immediately, transitions/animations suppressed |
| JS disabled | `<noscript>` rule reveals content |
| Script fails / block has no reveal JS / `manual` never completes | watchdog forces `done` after `window.load` + grace |
| Editor preview | resting state, always visible |

## File-level change list

**Plugin (`Proto-Blocks`, separate repo):**
- `includes/Template/Engine.php` — `render()` + `renderBlock()` thread `$block`.
- `includes/Template/Renderer.php` — `render()` + `executeTemplate()` accept and expose `$block`.
- `assets/reveal-runtime.js`, `assets/reveal-runtime.css` — new.
- Enqueue logic (frontend `wp_enqueue_scripts`) + `wp_head` `<noscript>` — new (likely a small `includes/Frontend/RevealRuntime.php` registered from `Core/Plugin.php`; follow existing asset-loading patterns).
- `proto-blocks.php` — version 2.3.0 → 2.4.0.
- `CHANGELOG.md` — new; document the `$block` fix + reveal convention.
- `docs/` (plugin) — animation authoring guide for the `data-proto-animate` convention.
- `tests/php/...` — unit test for `$block` threading.

**Theme (`optimizedit`, this repo):**
- 16 `proto-blocks/oit-*/{template.php,style.css,view.js}` — attribute migration; `manual` flags; 2 new `view.js` reveals; featured-cards cleanup.
- Render-assertion verification script (kept under a scripts/ or docs/ location, or run ad hoc).

**Skill docs (plugins cache, flag canonical source to mirror):**
- `references/templates.md` — correct the preview-detection section; document `$block` now works.
- Add the `data-proto-animate` convention to the skill (and note the canonical skill repo must be updated too, since the cache copy can be overwritten on update).

## Testing / verification

1. **PHP unit test:** `render()` exposes a `WP_Block` to the template; `renderPreview()` exposes `null`.
2. **Render-assertion script:** for every block — frontend-context render contains `data-proto-animate="pending"` (or `manual`) where expected and the editor-context render contains none; no element is left hidden without a reveal path; `<noscript>` net present on a rendered page.
3. **Browser (human-driven):** representative pages — confirm scroll reveal animates, reduced-motion reveals instantly, JS-off shows content, and editing a block in wp-admin works (resting state).

## Rollout / ordering

The reveal runtime (Layer B) must ship **in the same change set as** the `$block` threading (Layer A) so there is never a window where frontend templates emit `pending` without the safety net. Theme block migration (Layer C) follows. Clear the Proto-Blocks template cache after template edits (`wp proto-blocks cache clear`). The plugin and theme are separate git repos and get separate commits.

## Risks

- **Blast radius:** activating dormant animations across the site. Mitigated by the safety matrix (no path leaves content hidden) and per-block browser QA.
- **Asset load order:** the runtime is dependency-free and gated to `window.load` for the watchdog, so it does not depend on GSAP load timing.
- **Skill cache vs source:** the cache copy of the skill may be overwritten on update; the canonical skill repo must receive the same doc change (flagged, owner to apply).
