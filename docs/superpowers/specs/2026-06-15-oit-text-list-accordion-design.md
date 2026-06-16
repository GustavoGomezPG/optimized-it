# OIT Text & List — Accordion layout

**Date:** 2026-06-15
**Block:** `proto-blocks/oit-text-list`
**Status:** Approved design, pending implementation plan

## Goal

The bottom repeater of `oit-text-list` currently renders a flex-wrap grid of
short benefit titles, each capped by a grey rule + dot. The benefits now need
optional body content. Add a second presentation — an **accordion** — selectable
via a sidebar toggle, while leaving the existing flex layout (and every page
already using it) untouched.

One block, two layouts:

- **Flex (default, unchanged):** title-only grid, `flex flex-wrap`, rule + dot.
- **Accordion:** one item per row; each row is a clickable title that expands a
  body-content panel with an animated height transition.

## Decisions (locked)

- Enhance the existing block — do **not** create a new block.
- Accordion mode: **first item open** on load.
- **Single-open** (one panel at a time) is the default, exposed as a *conditional*
  control that only appears when accordion mode is on. Author may switch to
  multiple-open.
- Expand/collapse is an **animated height** transition (GSAP), not an instant
  toggle.
- When a row is open, its title turns **brand-red** (open accent) and the chevron
  rotates.

## block.json changes

### New repeater field (inside `items`)

```jsonc
"content": {
  "type": "wysiwyg",
  "label": "Content (accordion layout only)"
}
```

Sits beside the existing `title`. Ignored by the flex layout.

### New controls

```jsonc
"accordionLayout": {
  "type": "toggle",
  "label": "Accordion layout",
  "default": false,
  "help": "Off: title-only flex grid (default). On: one item per row with expandable content."
},
"accordionSingle": {
  "type": "toggle",
  "label": "Open one panel at a time",
  "default": true,
  "conditions": { "visible": { "accordionLayout": true } }
}
```

`accordionSingle` is only meaningful in accordion mode, so it is hidden until
`accordionLayout` is enabled.

## template.php changes

Intro (`h2` headline + body) and the brand-red `listLabel` (`h3`) render
**identically in both modes**. Only the repeater branches.

### Flex branch (`accordionLayout` false)

Unchanged from today: `<ul class="...flex flex-wrap gap-9...">` with each
`<li>` = title + rule/dot. The new `content` field is not rendered.

### Accordion branch (`accordionLayout` true)

- Container: `flex flex-col`, constrained width (~`max-w-[900px]`), one row per item.
- Each item renders as a **button + panel** pair (not native `<details>`, because
  animated height needs JS control):

```html
<div class="oit-text-list__acc-item" data-proto-repeater-item>
  <button type="button"
          class="oit-text-list__acc-trigger ..."
          aria-expanded="true|false"
          aria-controls="<panelId>">
    <span data-proto-field="title" class="oit-text-list__acc-title ...">…</span>
    <svg class="oit-text-list__acc-chevron ..." aria-hidden="true">…</svg>
  </button>
  <div id="<panelId>"
       role="region"
       class="oit-text-list__acc-panel ..."
       data-proto-field="content">
    <div class="oit-text-list__acc-panel-inner ...">… wysiwyg …</div>
  </div>
</div>
```

- A full-width grey rule sits at the bottom of each row (keeps the brand line
  motif; drops the dot used in flex mode).
- `panelId` is derived from the block anchor + row index for stable
  `aria-controls` / `id` pairing.
- **Progressive-enhancement fallback:** with no JS, panels render **expanded**
  (CSS default) so all content stays readable — consistent with the block's
  existing "if the script bails, the block still works" philosophy. JS collapses
  the non-first panels on init.

### Editor preview

The block renders via `template.php` in the editor too. The author sees the
selected layout live; the `content` field is editable inline in accordion mode.
Flex mode shows no content field output (it remains editable through the repeater
item's expanded controls).

## view.js changes

Extend the existing single-file view script. The current scroll-reveal stays for
both layouts (reveal accordion rows with the same staggered GSAP one-shot).

New accordion controller (runs only when accordion rows are present):

1. **On init:** open the first panel, collapse the rest. Collapse = set
   `aria-expanded="false"` on the trigger and animate the panel to height 0
   (`gsap.set` instantly on init, no animation on first paint).
2. **On trigger click:** toggle the panel with `gsap.to(panel, { height: 'auto'|0 })`
   using the measured natural height; update `aria-expanded`; toggle the open
   accent class (brand-red title) and chevron rotation.
3. **Single-open mode:** when `accordionSingle` is on (read from a
   `data-acc-single` attribute the template emits), opening one row animates the
   currently-open row closed first. Multiple-open mode skips that.
4. **Reduced motion / no GSAP:** bail to instant show/hide (set height auto/0
   without tweening), never leave a panel stuck mid-height.

Single-open vs multiple-open is communicated to JS via a `data-acc-single="1|0"`
attribute on the accordion container, set from the `accordionSingle` control.

## style.css changes

Tailwind-inline stays the norm; `style.css` only gets what utilities can't
express cleanly:

- Chevron rotation transition + rotated state.
- Open accent (brand-red title) state class.
- Trigger button focus-visible styling, cursor, list-marker reset.
- Panel `overflow:hidden` so the animated height clips content.

## Out of scope

- No change to the flex layout's markup, classes, or animation.
- No new block, no migration of existing content (toggle defaults off).
- No nested/inner-block content inside panels — `content` is a single wysiwyg field.

## Success criteria

- Existing pages using the block render byte-for-byte the same (flex, toggle off).
- Toggling accordion on shows one-per-row rows; first open, rest collapsed.
- Clicking a row animates its height open/closed; open row's title is brand-red,
  chevron rotated.
- Single-open default closes the previously-open row; switching the conditional
  control to multiple-open allows several open at once.
- The `accordionSingle` control is hidden in the sidebar until accordion mode is on.
- With JS disabled, all titles + content are visible and readable.
- Keyboard: triggers are focusable buttons, operable with Enter/Space, with
  correct `aria-expanded` / `aria-controls`.
