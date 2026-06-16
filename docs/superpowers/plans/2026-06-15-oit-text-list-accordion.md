# OIT Text & List — Accordion Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. Also load the **protoblocks-skill:protoblocks** skill — this is a Proto-Blocks block (PHP/HTML template, not React).

**Goal:** Add an accordion presentation to the `oit-text-list` block, selectable via a sidebar toggle, where each benefit becomes a one-per-row expandable row with animated body content — without changing the existing flex layout.

**Architecture:** Enhance the existing block in place. Add a `content` wysiwyg field to the `items` repeater and two controls (`accordionLayout`, conditional `accordionSingle`). `template.php` branches between the unchanged flex `<ul>` and a new accordion built from `button` + animated panel pairs. `view.js` gains an accordion controller (GSAP height animation, single-open coordination, first-open default) and extends the existing scroll-reveal to the new rows. All styling stays inline-Tailwind (this block has no `style.css`); open accent and chevron rotation are JS-toggled Tailwind classes.

**Tech Stack:** Proto-Blocks (PHP templates), Tailwind CSS (inline utilities, `useTailwind: true`), GSAP + ScrollTrigger (already loaded by the theme), vanilla `view.js`.

**Verification reality:** These blocks render at runtime from `block.json` + `template.php` — there is **no pytest/JS unit harness**. Verification = `wp proto-blocks validate`, `wp proto-blocks cache clear`, a Tailwind recompile, and a manual editor + frontend check in the browser. Run all `wp` commands from the site's WP-CLI shell (Local → right-click site → **Open site shell**), from the WordPress root.

**Files touched (all under `wp-content/themes/optimizedit/proto-blocks/oit-text-list/`):**
- Modify: `block.json` — new field + two controls
- Modify: `template.php` — accordion render branch + attribute reads + defaults
- Modify: `view.js` — accordion controller + extended reveal

---

### Task 1: block.json — add content field and accordion controls

**Files:**
- Modify: `proto-blocks/oit-text-list/block.json`

- [ ] **Step 1: Add `content` to the `items` repeater fields**

In `block.json`, the `items.fields` object currently holds only `title`. Replace that fields object so it reads:

```json
          "fields": {
            "title": {
              "type": "text",
              "label": "Title"
            },
            "content": {
              "type": "wysiwyg",
              "label": "Content (accordion layout only)"
            }
          }
```

- [ ] **Step 2: Add the two controls**

Replace the existing `controls` object (which currently holds only `showGradient`) with:

```json
    "controls": {
      "showGradient": {
        "type": "toggle",
        "label": "Show Gradient Background",
        "default": true,
        "help": "Off uses solid white."
      },
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
    }
```

- [ ] **Step 3: Validate the schema**

Run (in the site's WP-CLI shell, from WordPress root):

```bash
wp proto-blocks validate oit-text-list
```

Expected: validation passes (no errors). A warning-free result is ideal; errors must be fixed before continuing.

- [ ] **Step 4: Clear the block cache**

```bash
wp proto-blocks cache clear
```

Expected: cache cleared confirmation.

- [ ] **Step 5: Commit**

```bash
git add proto-blocks/oit-text-list/block.json
git commit -m "feat(oit-text-list): add content field and accordion controls"
```

---

### Task 2: template.php — accordion render branch

**Files:**
- Modify: `proto-blocks/oit-text-list/template.php`

- [ ] **Step 1: Read the new attributes and extend defaults**

In `template.php`, after the existing `$items = $attributes['items'] ?? [];` line and the `if (empty($items))` default block, the defaults currently set `title` only. Replace the default `$items` array so each default has content (so the editor preview shows something in accordion mode):

```php
  if (empty($items)) {
    $items = [
      ['title' => 'Improve productivity', 'content' => '<p>Streamline workflows and remove the IT friction that slows your team down.</p>'],
      ['title' => 'Increase uptime performance', 'content' => '<p>Proactive monitoring keeps systems online so your business keeps moving.</p>'],
      ['title' => 'Make the right IT investments', 'content' => '<p>Spend on technology that maps to outcomes, not guesswork.</p>'],
      ['title' => 'Deploy continuity strategies', 'content' => '<p>Backups and disaster recovery that get you running again fast.</p>'],
      ['title' => 'Leverage the Cloud', 'content' => '<p>Scale securely with cloud infrastructure tuned to your needs.</p>'],
      ['title' => 'Network security best practices', 'content' => '<p>Layered defenses that protect your data and your reputation.</p>'],
    ];
  }
```

Then, immediately after the `$show_gradient`/`$bg_class` lines, add the accordion attribute reads (note `?? true` for `accordionSingle` to match its `"default": true`):

```php
$accordion_layout = $attributes['accordionLayout'] ?? false;
$accordion_single = $attributes['accordionSingle'] ?? true;
```

- [ ] **Step 2: Branch the repeater markup**

Replace the entire existing `<ul data-proto-repeater="items" ...> ... </ul>` block (the flex grid) with a conditional. The flex branch keeps today's markup byte-for-byte; the accordion branch is new:

```php
    <?php if ($accordion_layout): ?>
    <?php $acc_uid = wp_unique_id('oit-acc-'); ?>
    <div
      data-proto-repeater="items"
      data-acc-single="<?php echo $accordion_single ? '1' : '0'; ?>"
      class="oit-text-list__accordion flex flex-col max-w-[900px] m-0 p-0 list-none">
      <?php foreach ($items as $i => $item): ?>
      <?php $panel_id = $acc_uid . '-' . $i; $is_first = ($i === 0); ?>
      <div
        data-proto-repeater-item
        class="oit-text-list__acc-item border-b border-grey list-none">
        <button
          type="button"
          class="oit-text-list__acc-trigger w-full flex items-center justify-between gap-4 py-5 m-0 bg-transparent border-0 text-left cursor-pointer appearance-none"
          aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
          aria-controls="<?php echo esc_attr($panel_id); ?>">
          <span
            data-proto-field="title"
            class="oit-text-list__acc-title m-0 font-grotesk font-bold text-body-md leading-[1.4] <?php echo $is_first ? 'text-brand-red' : 'text-black'; ?>">
            <?php echo esc_html($item['title'] ?? ''); ?>
          </span>
          <svg
            class="oit-text-list__acc-chevron shrink-0 w-5 h-5 text-brand-red transition-transform duration-300 <?php echo $is_first ? 'rotate-180' : ''; ?>"
            viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <div
          id="<?php echo esc_attr($panel_id); ?>"
          role="region"
          class="oit-text-list__acc-panel overflow-hidden">
          <div
            data-proto-field="content"
            class="oit-text-list__acc-panel-inner pb-5 font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-4">
            <?php echo wp_kses_post($item['content'] ?? ''); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <ul
      data-proto-repeater="items"
      class="oit-text-list__items flex flex-wrap gap-9 m-0 p-0 list-none">
      <?php foreach ($items as $item): ?>
      <li
        data-proto-repeater-item
        class="oit-text-list__item grow basis-[160px] min-w-0 flex flex-col justify-between gap-3 min-h-[71px] list-none">
        <p
          data-proto-field="title"
          class="oit-text-list__item-title m-0 font-grotesk font-bold text-body-md leading-[1.4] text-black">
          <?php echo esc_html($item['title'] ?? ''); ?>
        </p>
        <div class="oit-text-list__item-rule relative w-full h-1.5" aria-hidden="true">
          <span class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-0.5 bg-grey"></span>
          <span class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-grey"></span>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
```

Rationale notes:
- Both branches keep `data-proto-repeater="items"` so the repeater stays editable in either layout (Iron Rule 4).
- The accordion panel has **no inline height** — with JS off it renders fully open and readable (the "bail = visible" fallback). `view.js` collapses non-first panels on the frontend.
- The first item's `text-brand-red` + `rotate-180` are server-rendered so the open accent is correct before JS runs.

- [ ] **Step 3: Clear the block cache and re-validate**

```bash
wp proto-blocks cache clear
wp proto-blocks validate oit-text-list
```

Expected: cache cleared; validation passes.

- [ ] **Step 4: Commit**

```bash
git add proto-blocks/oit-text-list/template.php
git commit -m "feat(oit-text-list): render accordion layout branch"
```

---

### Task 3: view.js — accordion controller + extended reveal

**Files:**
- Modify: `proto-blocks/oit-text-list/view.js`

- [ ] **Step 1: Extend the reveal to include accordion rows**

In `view.js`, rename the existing `init` function to `initReveal` (it owns the scroll-reveal). In it, change the items query so accordion rows also stagger in. Replace:

```js
    var items    = section.querySelectorAll('.oit-text-list__item');
```
with:
```js
    var items    = section.querySelectorAll('.oit-text-list__item, .oit-text-list__acc-item');
```

(The two selectors are mutually exclusive per layout, so this is safe in both modes.)

- [ ] **Step 2: Add the accordion controller function**

Add this new function above `boot()` (it is independent of GSAP availability for its core toggle behavior — it only uses GSAP to animate height, and falls back to instant show/hide otherwise):

```js
  function initAccordion(section) {
    var container = section.querySelector('.oit-text-list__accordion');
    if (!container) return;
    if (container.dataset.oitAccInit === '1') return;
    container.dataset.oitAccInit = '1';

    var single = container.getAttribute('data-acc-single') === '1';
    var items = Array.prototype.slice.call(
      container.querySelectorAll('.oit-text-list__acc-item')
    );
    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var canAnimate = !!window.gsap && !reduce;

    function triggerOf(item) { return item.querySelector('.oit-text-list__acc-trigger'); }
    function panelOf(item)   { return item.querySelector('.oit-text-list__acc-panel'); }
    function titleOf(item)   { return item.querySelector('.oit-text-list__acc-title'); }
    function chevOf(item)    { return item.querySelector('.oit-text-list__acc-chevron'); }
    function isOpen(item)    { return triggerOf(item).getAttribute('aria-expanded') === 'true'; }

    function setOpenState(item, open, animate) {
      var panel = panelOf(item), trigger = triggerOf(item),
          title = titleOf(item), chev = chevOf(item);
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      title.classList.toggle('text-brand-red', open);
      title.classList.toggle('text-black', !open);
      chev.classList.toggle('rotate-180', open);

      if (!canAnimate || !animate) {
        panel.style.height = open ? 'auto' : '0px';
        return;
      }
      if (open) {
        window.gsap.set(panel, { height: 'auto' });
        var target = panel.offsetHeight;
        window.gsap.fromTo(panel,
          { height: 0 },
          { height: target, duration: 0.4, ease: 'power3.out',
            onComplete: function () { panel.style.height = 'auto'; } });
      } else {
        var current = panel.offsetHeight;
        window.gsap.fromTo(panel,
          { height: current },
          { height: 0, duration: 0.35, ease: 'power3.out' });
      }
    }

    // Initial state: first row open, rest collapsed -- no animation on first paint.
    items.forEach(function (item, idx) { setOpenState(item, idx === 0, false); });

    items.forEach(function (item) {
      triggerOf(item).addEventListener('click', function () {
        if (isOpen(item)) { setOpenState(item, false, true); return; }
        if (single) {
          items.forEach(function (other) {
            if (other !== item && isOpen(other)) { setOpenState(other, false, true); }
          });
        }
        setOpenState(item, true, true);
      });
    });
  }
```

- [ ] **Step 3: Call both initializers from `boot()`**

Replace the existing `boot()` body so it runs the reveal and the accordion controller for each section:

```js
  function boot() {
    document.querySelectorAll('.oit-text-list').forEach(function (section) {
      initReveal(section);
      initAccordion(section);
    });
  }
```

(Leave the `DOMContentLoaded` wiring at the bottom of the file unchanged.)

- [ ] **Step 4: Clear the block cache**

```bash
wp proto-blocks cache clear
```

Expected: cache cleared.

- [ ] **Step 5: Commit**

```bash
git add proto-blocks/oit-text-list/view.js
git commit -m "feat(oit-text-list): animated accordion controller in view.js"
```

---

### Task 4: Tailwind recompile + manual verification

**Files:** none (build + verify)

- [ ] **Step 1: Recompile Tailwind**

New utility classes were introduced (`max-w-[900px]` already existed; `rotate-180`, `transition-transform`, `duration-300`, `appearance-none`, `border-0`, `bg-transparent` are standard). Recompile so they exist in the generated CSS:
- **Local / shell host:** run the theme/Proto-Blocks Tailwind compile (download the Tailwind binary if prompted), or
- **Managed host (WP Engine):** use the Proto-Blocks **Compile CSS** button in wp-admin.

Then clear the cache once more:
```bash
wp proto-blocks cache clear
```

- [ ] **Step 2: Editor verification**

Open a page using the OIT Text and List block (or insert a fresh one) in the WordPress editor and confirm:
- With **Accordion layout OFF**: the block looks identical to before (flex grid of titles + rule/dot). The `accordionSingle` control is **hidden** in the sidebar.
- Toggle **Accordion layout ON**: the `accordionSingle` control **appears**; the repeater renders one row per item; each item now exposes an editable **Content** field; rows render expanded (editable) in the canvas.

- [ ] **Step 3: Frontend verification**

View the page on the frontend (logged-out or preview) with accordion mode on, and confirm:
- First row is open (content visible, title brand-red, chevron pointing up); the rest are collapsed.
- Clicking a collapsed row animates its content open (height tween), turns its title brand-red, rotates its chevron; clicking an open row animates it closed.
- With **Open one panel at a time ON**: opening a row closes the previously open one. Turn it OFF, re-save, reload: multiple rows can stay open simultaneously.
- Open DevTools console — no JS errors from `view.js`.
- Scroll the section into view from above: rows stagger in via the existing reveal.

- [ ] **Step 4: No-JS / reduced-motion fallback check**

- In DevTools, emulate `prefers-reduced-motion: reduce` (or disable JS): all titles and their content render visible and readable; the block is not stuck collapsed. Toggling still works instantly under reduced motion.

- [ ] **Step 5: Final commit (if recompiled CSS is tracked)**

If the Tailwind build produces a tracked CSS artifact, commit it:
```bash
git add -A
git commit -m "build(oit-text-list): recompile Tailwind for accordion utilities"
```
If CSS is generated/ignored, skip this step.

---

## Self-Review notes

- **Spec coverage:** content field (T1), accordionLayout + conditional accordionSingle (T1), two render branches with flex unchanged (T2), first-open + brand-red accent + chevron (T2/T3), animated height + single-open coordination (T3), extended reveal (T3), no-JS fallback (T2 markup + T4 check), keyboard (native `<button>` triggers). All spec success criteria map to a task.
- **Naming consistency:** classes `oit-text-list__accordion`, `__acc-item`, `__acc-trigger`, `__acc-title`, `__acc-chevron`, `__acc-panel`, `__acc-panel-inner` and the `data-acc-single` attribute are used identically across `template.php` (T2) and `view.js` (T3).
- **Attribute defaults:** `accordionSingle` reads `?? true` to match its `"default": true` (controls reference, Step note).
- **No `style.css`:** intentional — this block had none; chevron/accent are JS-toggled Tailwind classes, height animation is GSAP inline.
