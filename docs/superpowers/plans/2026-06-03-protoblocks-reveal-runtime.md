# Proto-Blocks `$block` Contract + `data-proto-animate` Reveal Runtime — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make Proto-Blocks pass the real `$block` to templates (so editor-vs-frontend detection works as documented), and ship a plugin-owned, safe-by-default reveal runtime (`data-proto-animate`) so entrance animations work on the frontend and content can never get stuck hidden.

**Architecture:** The plugin threads `$block` down the frontend render path (`renderBlock → render → executeTemplate`) and passes `null` on the preview path (`renderPreview`). A new frontend-only runtime (vanilla JS + safety CSS + `<noscript>` net) owns the `pending → done` lifecycle and guarantees reveal under every condition (scroll, reduced-motion, no-JS, script failure). The 16 theme blocks migrate from `data-animate` to `data-proto-animate`; 12 GSAP blocks run in `manual` mode (block owns motion, runtime backstops), 4 run in `auto` mode (runtime reveals, CSS transitions the motion).

**Tech Stack:** PHP 8 (plugin), PHPUnit, vanilla JS (IntersectionObserver), CSS, WordPress block render hooks, GSAP (theme, already enqueued).

**Repos (separate git roots — separate commits):**
- Plugin: `/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/plugins/Proto-Blocks`
- Theme: `/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/themes/optimizedit` (branch `development`)

**WP-CLI:** use the wrapper `~/.local/bin/wp` (add `export PATH="$HOME/.local/bin:$PATH"`); media/admin ops need `--user=1`.

**Execution ordering (critical):** Do Phase 1 (runtime, inert until something emits `pending` on the frontend) **before** Phase 2 (`$block` threading, which activates it). This guarantees no window where frontend content hides without the safety net — even on this live dev site. Theme migration (Phase 4) comes after both.

---

## File Structure

**Plugin — create:**
- `assets/js/reveal-runtime.js` — IntersectionObserver reveal + manual backstop + reduced-motion + watchdog.
- `assets/css/reveal-runtime.css` — reduced-motion safety (suppress transitions).
- `CHANGELOG.md` — new.
- `docs/animation.md` — `data-proto-animate` authoring guide.
- `tests/php/Template/BlockContextTest.php` — `$block` threading unit test.

**Plugin — modify:**
- `includes/Template/Renderer.php` — `render()` + `executeTemplate()` accept/expose `$block`.
- `includes/Template/Engine.php` — `render()` + `renderBlock()` thread `$block`.
- `includes/Admin/Assets.php` — enqueue reveal runtime on frontend; print `<noscript>` net.
- `includes/Core/Plugin.php` — hook `wp_head` → noscript.
- `proto-blocks.php` — version `2.3.0` → `2.4.0`.
- `tests/php/bootstrap.php` — add minimal `WP_Block` stub for tests.

**Theme — modify (16 blocks):** each block's `template.php`, `style.css`, `view.js` under `proto-blocks/oit-*`, plus 2 new `view.js`-free auto reveals and the `oit-featured-cards` `$is_preview` cleanup. Verification script under `docs/superpowers/` (ad hoc).

**Skill docs (cache) — modify:** `references/templates.md` (preview section) and add the convention; flag canonical source.

---

## Phase 1 — Plugin: reveal runtime (inert until activated)

### Task 1: Reveal runtime JS

**Files:**
- Create: `plugins/Proto-Blocks/assets/js/reveal-runtime.js`

- [ ] **Step 1: Create the file with this exact content**

```javascript
/**
 * Proto-Blocks reveal runtime (frontend only).
 *
 * Owns the data-proto-animate lifecycle and GUARANTEES content is never left
 * hidden. States: "pending" (runtime reveals on scroll-in) -> "done";
 * "manual" (a block's own view.js owns the motion; runtime only backstops).
 * Legacy "data-animate" is treated as an alias.
 */
(function () {
  'use strict';

  var ATTR = 'data-proto-animate';
  var LEGACY = 'data-animate';
  var MANUAL_GRACE = 1500; // ms a manual block gets to reveal itself after entering view
  var LOAD_BACKSTOP = 2000; // ms after window load before force-revealing in/above-viewport stragglers
  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function stateOf(el) { return el.getAttribute(ATTR) || el.getAttribute(LEGACY); }

  function setDone(el) {
    if (stateOf(el) === 'done') return;
    if (el.hasAttribute(ATTR)) el.setAttribute(ATTR, 'done');
    if (el.hasAttribute(LEGACY)) el.setAttribute(LEGACY, 'done');
    try { el.dispatchEvent(new CustomEvent('proto-blocks:reveal', { bubbles: true })); } catch (e) {}
  }

  function all() { return document.querySelectorAll('[' + ATTR + '],[' + LEGACY + ']'); }

  function revealAll() { Array.prototype.forEach.call(all(), setDone); }

  function boot() {
    // No motion possible / wanted -> reveal everything now.
    if (reduced || !('IntersectionObserver' in window)) { revealAll(); return; }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target, st = stateOf(el);
        if (st === 'pending') {
          setDone(el);
          io.unobserve(el);
        } else if (st === 'manual') {
          // Block owns the animation; only backstop if it never finishes.
          io.unobserve(el);
          window.setTimeout(function () { if (stateOf(el) !== 'done') setDone(el); }, MANUAL_GRACE);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.01 });

    Array.prototype.forEach.call(all(), function (el) {
      if (stateOf(el) !== 'done') io.observe(el);
    });

    // Final safety: anything still hidden that is in/above the viewport after load
    // (e.g. IO never fired, manual block JS missing) gets revealed. Far-below
    // content stays pending so genuine scroll reveals still happen.
    window.addEventListener('load', function () {
      window.setTimeout(function () {
        var vh = window.innerHeight || document.documentElement.clientHeight;
        Array.prototype.forEach.call(all(), function (el) {
          if (stateOf(el) === 'done') return;
          if (el.getBoundingClientRect().top < vh) setDone(el);
        });
      }, LOAD_BACKSTOP);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
```

- [ ] **Step 2: Syntax check**

Run: `node --check "plugins/Proto-Blocks/assets/js/reveal-runtime.js"` (from public/wp-content) — if `node` unavailable, skip; it's verified in the browser later.
Expected: no output (valid).

### Task 2: Reveal runtime CSS (safety only)

**Files:**
- Create: `plugins/Proto-Blocks/assets/css/reveal-runtime.css`

- [ ] **Step 1: Create the file with this exact content**

```css
/**
 * Proto-Blocks reveal runtime — safety styles only (no motion opinion).
 * Blocks own their pending/done visuals. This file only guarantees that
 * reduced-motion users get an instant, motionless reveal.
 */
@media (prefers-reduced-motion: reduce) {
  [data-proto-animate],
  [data-proto-animate] *,
  [data-animate],
  [data-animate] * {
    transition: none !important;
    animation: none !important;
  }
}
```

### Task 3: Enqueue runtime + noscript net

**Files:**
- Modify: `plugins/Proto-Blocks/includes/Admin/Assets.php` (inside `enqueueFrontendAssets()`, and add a new `printRevealNoscript()` method)
- Modify: `plugins/Proto-Blocks/includes/Core/Plugin.php` (boot hooks, near the other `wp_*` hooks)

- [ ] **Step 1: Read the current `enqueueFrontendAssets()` method**

Run: `grep -n "function enqueueFrontendAssets" plugins/Proto-Blocks/includes/Admin/Assets.php`
Then read the method body to place the new enqueue near the top (so it loads regardless of Tailwind branching).

- [ ] **Step 2: Add the runtime enqueue at the start of `enqueueFrontendAssets()`**

Insert immediately after the method's opening brace:

```php
        // Reveal runtime — frontend only, dependency-free. Owns the
        // data-proto-animate lifecycle and guarantees content reveals.
        wp_enqueue_style(
            'proto-blocks-reveal',
            PROTO_BLOCKS_URL . 'assets/css/reveal-runtime.css',
            [],
            PROTO_BLOCKS_VERSION
        );
        wp_enqueue_script(
            'proto-blocks-reveal',
            PROTO_BLOCKS_URL . 'assets/js/reveal-runtime.js',
            [],
            PROTO_BLOCKS_VERSION,
            true
        );
```

- [ ] **Step 3: Add the `printRevealNoscript()` method to the `Assets` class**

Add this method to the class (anywhere among the public methods):

```php
    /**
     * No-JS fallback: ensure data-proto-animate pre-state content is visible
     * when JavaScript is disabled. Printed in wp_head on the frontend.
     */
    public function printRevealNoscript(): void
    {
        echo '<noscript><style>'
            . '[data-proto-animate]:not([data-proto-animate="done"]),'
            . '[data-proto-animate]:not([data-proto-animate="done"]) *,'
            . '[data-animate]:not([data-animate="done"]),'
            . '[data-animate]:not([data-animate="done"]) *'
            . '{opacity:1!important;transform:none!important;visibility:visible!important;}'
            . '</style></noscript>' . "\n";
    }
```

- [ ] **Step 4: Hook the noscript in `Plugin.php` boot**

Find the line `add_action('wp_enqueue_scripts', [$this->getAssets(), 'enqueueFrontendAssets']);` and add directly beneath it:

```php
        add_action('wp_head', [$this->getAssets(), 'printRevealNoscript'], 1);
```

- [ ] **Step 5: Verify it loads on the frontend**

```bash
export PATH="$HOME/.local/bin:$PATH"
curl -s -H "Host: optimizedit.local" "http://optimizedit.local/solutions/" | grep -c "reveal-runtime.js"   # expect 1
curl -s -H "Host: optimizedit.local" "http://optimizedit.local/solutions/" | grep -c "data-proto-animate.*opacity:1"  # noscript present -> expect >=1
```
Expected: runtime script present; noscript net present. (No block emits `pending` yet, so nothing changes visually.)

- [ ] **Step 6: Commit (plugin repo)**

```bash
cd "/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/plugins/Proto-Blocks"
git add assets/js/reveal-runtime.js assets/css/reveal-runtime.css includes/Admin/Assets.php includes/Core/Plugin.php
git commit -m "feat(reveal): add frontend reveal runtime for data-proto-animate (inert until used)"
```

---

## Phase 2 — Plugin: thread `$block` (TDD), activate

### Task 4: Install dev deps so tests run

- [ ] **Step 1: Install composer deps in the plugin**

```bash
cd "/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/plugins/Proto-Blocks"
composer install
```
Expected: `vendor/bin/phpunit` now exists. (If `composer` is unavailable, install it or use the Local site shell; tests require it.)

- [ ] **Step 2: Run the existing suite to confirm green baseline**

Run: `composer test`
Expected: existing tests pass.

### Task 5: Failing test for `$block` exposure

**Files:**
- Modify: `plugins/Proto-Blocks/tests/php/bootstrap.php` (add `WP_Block` stub)
- Create: `plugins/Proto-Blocks/tests/php/Template/BlockContextTest.php`
- Create (test fixture): `plugins/Proto-Blocks/tests/php/fixtures/block-probe.php`

- [ ] **Step 1: Add a `WP_Block` stub to the test bootstrap** (only if not already defined)

Append to `tests/php/bootstrap.php`:

```php
if (!class_exists('WP_Block')) {
    /** Minimal stub: the real WP_Block isn't loaded in unit tests. */
    class WP_Block {}
}
```

- [ ] **Step 2: Create the probe fixture template**

`tests/php/fixtures/block-probe.php`:

```php
<?php
echo 'BLOCK=' . (isset($block) && $block instanceof \WP_Block ? 'instance' : ($block === null ? 'null' : 'unset'));
```

- [ ] **Step 3: Write the failing test**

`tests/php/Template/BlockContextTest.php`:

```php
<?php

declare(strict_types=1);

namespace ProtoBlocks\Tests\Template;

use PHPUnit\Framework\TestCase;
use ProtoBlocks\Template\Renderer;
use ProtoBlocks\Fields\Registry as FieldRegistry;
use ProtoBlocks\Controls\Registry as ControlRegistry;

final class BlockContextTest extends TestCase
{
    private function renderer(): Renderer
    {
        return new Renderer(new FieldRegistry(), new ControlRegistry());
    }

    private function exec(Renderer $r, ?\WP_Block $block): string
    {
        $m = new \ReflectionMethod($r, 'executeTemplate');
        $m->setAccessible(true);
        $fixture = dirname(__DIR__) . '/fixtures/block-probe.php';
        return $m->invoke($r, $fixture, [], [], $block);
    }

    public function test_block_instance_is_exposed_on_frontend(): void
    {
        $this->assertStringContainsString('BLOCK=instance', $this->exec($this->renderer(), new \WP_Block()));
    }

    public function test_block_is_null_in_preview(): void
    {
        $this->assertStringContainsString('BLOCK=null', $this->exec($this->renderer(), null));
    }
}
```

- [ ] **Step 4: Run it — expect failure**

Run: `composer test -- --filter BlockContextTest`
Expected: FAIL — `executeTemplate` currently has no `$block` parameter, so `test_block_instance_is_exposed_on_frontend` reports `BLOCK=null` (or an ArgumentCountError). This confirms the bug.

### Task 6: Thread `$block` through Renderer + Engine

**Files:**
- Modify: `plugins/Proto-Blocks/includes/Template/Renderer.php`
- Modify: `plugins/Proto-Blocks/includes/Template/Engine.php`

- [ ] **Step 1: `Renderer::render()` — accept `$block` and forward it**

Change the signature and the `executeTemplate` call:

```php
    public function render(string $templatePath, array $attributes, array $metadata = [], ?\WP_Block $block = null): string
    {
        $protoConfig = $metadata['protoBlocks'] ?? [];
        $processedAttributes = $this->processControlValues($attributes, $protoConfig);
        $html = $this->executeTemplate($templatePath, $processedAttributes, $protoConfig, $block);
```
(Leave the rest of `render()` unchanged.)

- [ ] **Step 2: `Renderer::executeTemplate()` — accept `$block` and expose it in scope**

```php
    private function executeTemplate(string $templatePath, array $attributes, array $protoConfig, ?\WP_Block $block = null): string
    {
        if (!file_exists($templatePath)) {
            throw new \RuntimeException(sprintf('Template file not found: %s', $templatePath));
        }

        ob_start();

        $variables = $this->transformAttributeKeys($attributes);

        // Preserve $block across extract() (an attribute named "block" would clobber it).
        $protoBlockInstance = $block;
        extract($variables);

        // Documented contract: $block is the WP_Block on the frontend, null in
        // the editor preview. Templates branch on it via
        //   $is_preview = ! isset($block) || $block === null;
        $block = $protoBlockInstance;
```
(Leave the `$template` helper and `include` unchanged.)

- [ ] **Step 3: `Engine::render()` — accept and forward `$block`**

```php
    public function render(string $templatePath, array $attributes, array $metadata = [], ?\WP_Block $block = null): string
    {
        return $this->getRenderer()->render($templatePath, $attributes, $metadata, $block);
    }
```

- [ ] **Step 4: `Engine::renderBlock()` — pass `$block` into `render()`**

In the `try` block, change:
```php
            return $this->render($templatePath, $attributes, $metadata, $block);
```

- [ ] **Step 5: Run the test — expect pass**

Run: `composer test -- --filter BlockContextTest`
Expected: PASS (both tests).

- [ ] **Step 6: Run the full suite — no regressions**

Run: `composer test`
Expected: all pass.

- [ ] **Step 7: Commit (plugin repo)**

```bash
cd "/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/plugins/Proto-Blocks"
git add includes/Template/Renderer.php includes/Template/Engine.php tests/php/bootstrap.php tests/php/Template/BlockContextTest.php tests/php/fixtures/block-probe.php
git commit -m "fix(render): pass \$block to templates (WP_Block on frontend, null in preview)"
```

- [ ] **Step 8: Clear template cache + sanity-check the contract live**

```bash
export PATH="$HOME/.local/bin:$PATH"
wp proto-blocks cache clear --user=1
wp eval '
$e=\ProtoBlocks\Core\Plugin::getInstance()->getEngine();
$d=get_stylesheet_directory()."/proto-blocks/oit-featured-cards";
$meta=json_decode(file_get_contents($d."/block.json"),true);
$tpl=$d."/template.php";
$wpb=new WP_Block(["blockName"=>"proto-blocks/oit-featured-cards","attrs"=>[]]);
$front=$e->render($tpl,[],$meta,$wpb); $prev=$e->render($tpl,[],$meta,null);
echo "frontend(block):".(strpos($front,"card-link")!==false?"frontend-branch":"preview-branch")."\n";
echo "preview(null):".(preg_match("/<a[^>]*card-action/",$prev)?"preview-branch":"frontend-branch")."\n";
' --user=1
```
Expected: `frontend(block):frontend-branch` and `preview(null):preview-branch`.

---

## Phase 3 — Plugin: docs + version bump

### Task 7: Animation authoring guide

**Files:**
- Create: `plugins/Proto-Blocks/docs/animation.md`

- [ ] **Step 1: Write the guide**

```markdown
# Animating a block: the `data-proto-animate` convention

Proto-Blocks ships a frontend reveal runtime that owns a simple lifecycle and
**guarantees content is never left hidden**.

## States
- `pending` — author's pre-reveal state. The runtime reveals it (sets `done`)
  when it scrolls into view. Use for CSS-only reveals.
- `manual` — your block's own `view.js` owns the motion. The runtime does NOT
  trigger it; it only backstops (force-reveals if your JS never finishes).
- `done` — revealed. The runtime sets this; your CSS/JS react to it.

## Author a CSS-only ("auto") reveal — no JS
```php
<section <?php echo get_block_wrapper_attributes(['class' => 'my-block']); ?>
  <?php echo $is_preview ? '' : 'data-proto-animate="pending"'; ?>>
```
```css
.my-block[data-proto-animate="pending"] { opacity: 0; transform: translateY(16px); }
.my-block[data-proto-animate="done"]    { opacity: 1; transform: none; transition: opacity .6s, transform .6s; }
```
`$is_preview = ! isset($block) || $block === null;` — omit the attribute in the
editor so content is visible/editable; the runtime handles the rest on the frontend.

## Author a JS ("manual") reveal — your own GSAP/anime timeline
Set the root to `manual`, hide children in CSS while not `done`, run your
timeline in `view.js`, then set `data-proto-animate="done"`.

## Guarantees (you get these for free)
- Scrolls into view → revealed.
- `prefers-reduced-motion` → revealed instantly, no motion.
- JS disabled → `<noscript>` reveals content.
- Your JS fails / never completes → watchdog reveals after a grace period.
- Editor → resting state (no `pending` added), fully editable.

Legacy `data-animate` is accepted as an alias of `data-proto-animate`.
```

### Task 8: CHANGELOG + version bump

**Files:**
- Create: `plugins/Proto-Blocks/CHANGELOG.md`
- Modify: `plugins/Proto-Blocks/proto-blocks.php` (`Version:` header + `PROTO_BLOCKS_VERSION`)

- [ ] **Step 1: Bump the version in `proto-blocks.php`**

Change the header comment `* Version: 2.3.0` → `* Version: 2.4.0` and the constant `define('PROTO_BLOCKS_VERSION', '2.3.0');` → `define('PROTO_BLOCKS_VERSION', '2.4.0');`.

- [ ] **Step 2: Create `CHANGELOG.md`**

```markdown
# Changelog

## 2.4.0 — 2026-06-03

### Fixed
- Templates now receive `$block` (the `WP_Block` instance on the frontend,
  `null` in the editor preview), so the documented preview-detection
  (`$is_preview = ! isset($block)`) works. Previously `$block` was never passed,
  so `$is_preview` was always true and any frontend-only branch never ran.

### Added
- Reveal runtime: a frontend-only, dependency-free script/style implementing the
  `data-proto-animate` lifecycle (`pending`/`manual` → `done`) with a hard
  guarantee that content is never left hidden (scroll reveal, `prefers-reduced-motion`,
  no-JS `<noscript>` fallback, and a watchdog for failed/absent block JS).
- `docs/animation.md` — authoring guide for the convention.
- Legacy `data-animate` accepted as an alias of `data-proto-animate`.
```

- [ ] **Step 3: Commit (plugin repo)**

```bash
cd "/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/plugins/Proto-Blocks"
git add proto-blocks.php CHANGELOG.md docs/animation.md
git commit -m "docs+chore: animation authoring guide, CHANGELOG, bump to 2.4.0"
```

---

## Phase 4 — Theme: migrate the 16 blocks + featured-cards cleanup

**Block categories** (decides how each migrates):
- **MANUAL (12, have GSAP `view.js`):** `oit-about-hero`, `oit-cta`, `oit-link-cards`, `oit-hero`, `oit-logo-gallery`, `oit-page-header`, `oit-single-location`, `oit-testimonial-callout`, `oit-text-services-cards`, `oit-two-col-header`, `oit-title-columns`, `oit-video-preview`.
- **AUTO (4, no/partial reveal JS):** `oit-blog-header`, `oit-breadcrumbs-eyebrow-title`, `oit-resources`, `oit-text-image`.

> Work one block at a time. Commit per block (or per small group) so a regression is easy to bisect. After each block, clear cache and check the page in the browser.

### Task 9: Migrate a MANUAL block (repeat for all 12)

For each MANUAL block directory `proto-blocks/<block>/`:

- [ ] **Step 1: `template.php` — rename attribute + set `manual`**

Replace every `data-animate` with `data-proto-animate`. Where the root currently gets `data-animate="pending"` (only when `!$is_preview`), make the value `manual`:
```php
<?php echo $is_preview ? '' : 'data-proto-animate="manual"'; ?>
```
(Keep the existing `$is_preview = ! isset($block) || $block === null;` line — it is now correct.)

- [ ] **Step 2: `style.css` — match pre-hide on "not done"**

Replace pre-state selectors `…[data-animate="pending"] …` with `…[data-proto-animate]:not([data-proto-animate="done"]) …` so children stay hidden in both `manual` and (defensively) `pending` until revealed. Update any `[data-animate="done"]` → `[data-proto-animate="done"]`.

- [ ] **Step 3: `view.js` — rename attribute reads/writes**

Replace `data-animate` with `data-proto-animate` (selectors and `setAttribute('data-proto-animate','done')`). The block keeps its own GSAP timeline/trigger; it must set the root to `done` when it animates (it already does for the legacy attribute).

- [ ] **Step 4: Clear cache + browser check**

```bash
export PATH="$HOME/.local/bin:$PATH"; wp proto-blocks cache clear --user=1
```
Then load a page using this block in the browser (Chrome tools): confirm content is hidden initially, animates in on scroll, and is fully visible after. Confirm the wp-admin editor shows the block at resting state and it's editable.

- [ ] **Step 5: Commit (theme repo)**

```bash
cd "/Users/gustavogomez/Local Sites/optimizedit/app/public/wp-content/themes/optimizedit"
git add proto-blocks/<block>
git commit -m "refactor(<block>): migrate to data-proto-animate (manual reveal)"
```

### Task 10: Migrate an AUTO block (repeat for all 4)

For `oit-blog-header`, `oit-breadcrumbs-eyebrow-title`, `oit-resources`, `oit-text-image`:

- [ ] **Step 1: `template.php` — rename attribute, keep `pending`**

Replace `data-animate` → `data-proto-animate`; the root uses `pending` (gated on `!$is_preview`):
```php
<?php echo $is_preview ? '' : 'data-proto-animate="pending"'; ?>
```

- [ ] **Step 2: `style.css` — define the auto reveal (this gives them motion with no JS)**

Ensure a pre-state and a transitioned done-state exist (create `style.css` if missing and register it via the block's `block.json` styles if the block has no stylesheet yet — follow an existing block's `style.css`/`block.json` pairing):
```css
.<root>[data-proto-animate="pending"] { opacity: 0; transform: translateY(16px); }
.<root>[data-proto-animate="done"]    { opacity: 1; transform: none; transition: opacity .6s ease, transform .6s ease; }
```
Replace any existing `[data-animate="pending"]/[="done"]` rules accordingly.

- [ ] **Step 3: No `view.js` needed** — the plugin runtime flips `pending`→`done` on scroll-in; CSS animates.

- [ ] **Step 4: Clear cache + browser check**

```bash
export PATH="$HOME/.local/bin:$PATH"; wp proto-blocks cache clear --user=1
```
Load a page with this block: confirm it fades/slides in on scroll and ends fully visible; editor shows resting state.

- [ ] **Step 5: Commit (theme repo)**

```bash
git add proto-blocks/<block>
git commit -m "feat(<block>): migrate to data-proto-animate (auto reveal)"
```

### Task 11: `oit-featured-cards` — drop the interim editor-detection hack

**Files:**
- Modify: `theme/proto-blocks/oit-featured-cards/template.php`

- [ ] **Step 1: Replace the `REST_REQUEST`/`wp_doing_ajax` detection with the contract**

Find the block beginning `// Editor preview vs. frontend.` and replace the `$is_preview = (defined('REST_REQUEST') …);` definition with:
```php
// Editor preview vs. frontend (plugin now supplies $block):
$is_preview = !isset($block) || $block === null;
```
(The existing `if ($is_preview) { chevron <a> } else { stretched link }` branch is unchanged.)

- [ ] **Step 2: Clear cache + verify both contexts**

```bash
export PATH="$HOME/.local/bin:$PATH"; wp proto-blocks cache clear --user=1
curl -s -H "Host: optimizedit.local" "http://optimizedit.local/solutions/" | grep -c "oit-featured-cards__card-link"  # frontend stretched -> expect 6
wp eval 'define("REST_REQUEST",true); $p=get_post(107); foreach(parse_blocks($p->post_content) as $b){ if(($b["blockName"]??"")==="proto-blocks/oit-featured-cards"){ echo (preg_match("/<a[^>]*card-action/",render_block($b))?"editor-chevron-ok":"FAIL"); break; } }' --user=1
```
Expected: `6` and `editor-chevron-ok`.

- [ ] **Step 3: Commit (theme repo)**

```bash
git add proto-blocks/oit-featured-cards/template.php
git commit -m "refactor(featured-cards): use \$block preview contract instead of request sniffing"
```

---

## Phase 5 — Verification & docs sync

### Task 12: Render-assertion script (all blocks)

**Files:**
- Create: `theme/docs/superpowers/verify-reveal.sh` (ad-hoc verification)

- [ ] **Step 1: Create the script**

```bash
#!/usr/bin/env bash
# Asserts: (1) frontend pages carry the reveal runtime + noscript net,
# (2) no element is left hidden without a reveal path on representative pages.
set -u
export PATH="$HOME/.local/bin:$PATH"
HOST='-H Host:optimizedit.local'
PAGES=(/ /solutions/ /industries/government/ /about/ /locations/cincinnati/ /blog/)
fail=0
for p in "${PAGES[@]}"; do
  html=$(curl -s $HOST "http://optimizedit.local$p")
  echo "$html" | grep -q "reveal-runtime.js" || { echo "MISSING runtime: $p"; fail=1; }
  echo "$html" | grep -q "noscript" || { echo "MISSING noscript: $p"; fail=1; }
  # No legacy attribute should remain in rendered theme block output:
  n=$(echo "$html" | grep -oE 'data-animate="(pending|manual)"' | wc -l | tr -d ' ')
  [ "$n" = "0" ] || echo "WARN legacy data-animate on $p: $n"
done
echo "done (fail=$fail)"; exit $fail
```

- [ ] **Step 2: Run it**

Run: `bash docs/superpowers/verify-reveal.sh`
Expected: runtime + noscript present on every page; `fail=0`. (`WARN legacy` should be 0 once all blocks migrated.)

### Task 13: Browser QA pass (human-driven by the agent)

- [ ] **Step 1:** For each of a representative page per block, open in Chrome (mcp__claude-in-chrome) and confirm: content reveals on scroll (manual blocks animate via GSAP; auto blocks fade/slide); page is fully visible at rest.
- [ ] **Step 2:** Toggle OS reduced-motion (or emulate) and reload one page → content appears immediately, no motion.
- [ ] **Step 3:** In wp-admin, edit one manual block and one auto block → both show resting state and are editable; the featured-cards link control is editable.

### Task 14: Sync skill docs

**Files:**
- Modify: `~/.claude/plugins/cache/protoblocks/protoblocks-skill/1.1.0/skills/protoblocks/references/templates.md`

- [ ] **Step 1:** In the "Detecting editor preview vs frontend" section, confirm/keep `$is_preview = ! isset($block) || $block === null;` (now actually works) and add a one-paragraph pointer to the `data-proto-animate` reveal convention (states + guarantee) with a link to the plugin `docs/animation.md`.
- [ ] **Step 2:** Add a note at the top: "As of plugin 2.4.0, the renderer passes `$block` (WP_Block on frontend, null in preview)."
- [ ] **Step 3:** Flag to the user that the **canonical** skill source repo must receive the same edit (the cache copy is overwritten on skill update). Do not commit the cache (not a repo we own here); report the diff for the user to apply upstream.

---

## Self-review notes (coverage)
- Spec Layer A (`$block` threading) → Tasks 5–6. Layer B (runtime) → Tasks 1–3. Layer C (theme) → Tasks 9–11. Safety matrix → runtime JS (Task 1) + CSS (Task 2) + noscript (Task 3) + auto/manual split (Tasks 9–10). Editor resting state → preserved by `$is_preview` gating (Tasks 9–11). Docs (code comments throughout; spec already committed; skill docs Task 14; CHANGELOG + version Task 8; authoring guide Task 7). Verification → Tasks 12–13 + the PHPUnit test (Tasks 5–6). Rollout ordering → Phase 1 before Phase 2 before Phase 4.
- Attribute name consistency: `data-proto-animate` everywhere; legacy `data-animate` only as runtime alias + noscript selector.
```
