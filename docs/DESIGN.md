# Design System & UI/UX Redesign Direction

This document is the design counterpart to `docs/ARCHITECTURE.md`: it tells
an agent (or a human) what the visual system is and, more importantly, *why*
it's built the way it is — read this before writing CSS or HTML for the
redesign effort described below.

## Current state (baseline — do not discard)

`assets/css/style.css`'s `:root` block is the single source of truth for
color. It is also, separately, *data*: `themes`/`theme_tokens`
(`docs/ARCHITECTURE.md` §6.10) let an admin store and switch between full
palettes from the admin panel without a deploy. Four palettes already exist,
derived from the store's actual logo colors. **The redesign below extends
this system — it does not replace it.** Any new visual token (blur radius,
glass opacity, elevation level) should be added as a new CSS variable
alongside the existing color ones, and, where it's something an admin
should reasonably be able to tune per-theme, as a new `theme_tokens`
`token_group` (e.g. `'glass'` alongside the existing `'color'`) rather than
a second, parallel configuration mechanism.

```css
--color-bg, --color-surface, --color-text, --color-muted, --color-border,
--color-primary, --color-primary-dark, --color-primary-light, --color-accent,
--color-success, --color-danger
--radius-sm, --radius-md, --radius-lg
--shadow-sm, --shadow-md
```

## The direction: glass and depth, applied as an accent

The stated goal is a "3D, liquid-glass" redesign. Taken literally and
applied everywhere, that goal actively fights two other goals this project
already has: `docs/SEO.md`'s Core Web Vitals targets, and the hosting
constraints in `AGENTS.md` (a large share of this store's traffic is mobile,
on variable-quality connections, and the host has a hard 80 GB/month
bandwidth ceiling). Heavy `backdrop-filter` use across a whole page, and
especially any WebGL/3D-engine bundle, is a well-documented way to tank
mobile INP/LCP and page weight. This section resolves that tension instead
of picking a side silently: **glass and depth are an accent language,
applied to a deliberately small set of floating surfaces — not a page-wide
background treatment.**

### What "liquid glass" means here, technically

Modern CSS/SVG can produce the real effect — translucency *plus* refraction
at the surface's edge, not just a blurred rectangle — without WebGL:

```css
.glass-surface {
    background: color-mix(in srgb, var(--color-surface) 65%, transparent);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px); /* Safari requires the prefix */
    border: 1px solid color-mix(in srgb, var(--color-border) 60%, transparent);
    box-shadow:
        inset 0 1px 0 color-mix(in srgb, white 35%, transparent), /* specular top edge */
        var(--shadow-md);
}
```

`backdrop-filter` needs real content behind the panel to sample — it looks
flat over an empty background, so glass surfaces belong where there's
something visually busy underneath them (product imagery, a gradient hero),
not over a plain `--color-bg` fill. The optional next step — genuine
liquid-style refraction at the edges, via an SVG `feDisplacementMap`/
`feTurbulence` filter referenced from `backdrop-filter: url(#liquid-glass)`
— is a real, GPU-composited, no-library technique, but it's also the most
expensive part of the effect. Reserve it for one or two hero moments (the
header when scrolled, a promo modal) rather than every card, and always pair
it with the fallback below.

### Where glass belongs, and where it doesn't

| Use glass | Keep solid |
|---|---|
| Sticky header once scrolled | Product listing/admin data tables (legibility over aesthetics) |
| Modals, dialogs, the cart drawer | Long-form body text (product description, About page) |
| The announcement bar | Form inputs (glass on an input hurts contrast and affordance) |
| Floating CTAs / primary buttons on a hero image | Admin financial figures (`docs/ARCHITECTURE.md` §5.21) — these need to read unambiguously at a glance |
| Product-card hover state (a subtle lift, not full glass) | Anything where WCAG contrast can't be guaranteed against a variable background |

This mirrors why the color system already keeps `--color-primary` (solid,
contrast-checked) separate from `--color-primary-light`/`--color-accent`
(soft, decorative) — glass is another axis of the same "decorative vs.
load-bearing" split, not a new philosophy.

### "3D" — recommendation, not a mandate

Read this as the professional recommendation it is, and push back if the
actual intent is a full 3D product configurator or similar — that's a
legitimately different, larger scope than what follows.

For a premium *feel* (depth, parallax, tasteful motion), CSS 3D transforms
cost nothing extra to ship and are GPU-accelerated natively:

```css
.tilt-card {
    perspective: 1000px;
}
.tilt-card img {
    transition: transform .3s ease;
}
.tilt-card:hover img {
    transform: rotateY(6deg) rotateX(2deg) translateZ(10px);
}
```

This covers a hero product tilting on hover, layered cards with real
`translateZ` depth, and a parallax announcement/header — the "3D" feel the
brief asks for — with zero added JS payload and no bandwidth cost against
the 80 GB/month cap.

If a genuinely interactive 3D product viewer (rotate-and-inspect a specific
sock/gift box) is wanted later, the lean path is Google's `<model-viewer>`
web component, loaded only on the specific product page that uses it
(`<script type="module" src=".../model-viewer.min.js" defer>`), never
site-wide — not a hand-rolled Three.js scene. Model files (`.glb`) also
count against the 1.5 GB disk budget in `AGENTS.md`; budget for that
explicitly if this path is taken.

### Required guardrails (non-negotiable, not style preferences)

1. **Feature-detect and fall back.**
   ```css
   @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
       .glass-surface { background: var(--color-surface); }
   }
   ```
2. **Respect reduced motion and reduced transparency.**
   ```css
   @media (prefers-reduced-motion: reduce) {
       .tilt-card img, .glass-surface { transition: none; }
   }
   @media (prefers-reduced-transparency: reduce) {
       .glass-surface { backdrop-filter: none; background: var(--color-surface); }
   }
   ```
3. **`contain: layout paint style` on glass surfaces** to isolate their
   repaint cost from the rest of the page — cheap insurance against jank on
   scroll, per current browser guidance on `backdrop-filter` performance.
4. **Measure on a real mid-range Android device before shipping widely**,
   not just desktop Chrome DevTools throttling. This is where glassmorphism
   redesigns most often quietly fail in production.
5. **Never apply a heavy filter to more than a handful of simultaneously
   visible elements.** A glass product grid (every card blurred) is the
   canonical way this pattern destroys scroll performance — use it for
   floating chrome (header, modals, one hero), not for repeated list items.

### Rollout plan

Don't reskin the whole site in one change on a live store:

1. Add the new tokens (`--glass-*` variables) to `:root` alongside the
   existing ones; add a `'glass'` `theme_tokens` group so it's tunable per
   theme, defaulting to values matching the currently active palette.
2. Apply glass treatment to one low-risk, high-visibility surface first —
   the sticky header is the best candidate (isolated, easy to measure,
   highly visible). Ship it, verify real-device performance and Core Web
   Vitals didn't regress (`docs/SEO.md`), then continue.
3. Expand to modals/cart drawer, then hero/CTA treatments, in the same
   measure-before-expanding pattern. Product-grid cards, if touched at all,
   get the lightweight hover-lift version, never full blur per card.
4. Admin panel stays predominantly solid throughout — it's a data tool used
   by the store owner, not a storefront moment; legibility wins there by
   default (see the table above).
