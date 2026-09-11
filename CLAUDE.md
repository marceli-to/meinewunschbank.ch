# CLAUDE.md

Read `README.md` first — it documents the architecture, the content model, the
form, the mail setup and the PHP conventions. This file covers only what the
README does not: how the front-end templates are written.

## Antlers components

Everything under `resources/views/components/` is a partial, called as
`{{ partial:components/path/name param="value" }}`. There is no Blade in the
site templates (Blade is used only for mails and the sitemap).

A partial that takes parameters documents them in a comment block at the top.
Parameters only — do not open a component with a comment explaining what it is
or why it is built that way; the components here are small enough to read.

```antlers
{{#
  @param compact - Narrow variant (78rem) instead of 96rem.
  @param class - Extra classes.
#}}
```

Conventions:

- **`class` param** — every component that could reasonably be nudged from the
  outside accepts `class` and interpolates it last, so the caller can override.
  Pass layout concerns (width, margin) in from the caller; keep the component's
  own look inside it.
- **Slots** — a component that wraps content is called as a paired tag and
  renders `{{ slot }}`. `layout/container` and `button` are the examples.
- **`layout/container`** is the only thing that sets page gutters and max
  width. Do not re-implement `mx-auto px-…` anywhere else; wrap in a container
  and pass `spacing` for the vertical rhythm.
- Icons and logos are partials, not files referenced by `<img>`, so they take
  `currentColor` from the caller's text colour.
- **Headings carry `text-balance`.** It is baked into the `headings/h1`…`h4`
  partials, so use those rather than a bare `<h1>`. Bard renders its own
  headings and cannot, so `article h2, article h3` get it from `app.css`.

## Content blocks

A page's `blocks` replicator drives everything. Adding a block means four
edits, in this order:

1. **A fieldset** at `resources/fieldsets/<handle>.yaml` holding the block's
   fields. Always a real fieldset, even for a single field and even when only
   one block uses it today — other collections may want the same block later.
   Fieldsets may import each other: `intro` pulls in `editor` for its body
   text.
2. **A set** under `blocks` in
   `resources/blueprints/collections/pages/page.yaml` that does nothing but
   `- import: <handle>`, plus `display`, `instructions` and `icon` — all
   editor-facing text in German. The blueprint stays thin; the fields live in
   the fieldset.
3. **A partial** at `resources/views/components/blocks/<handle>.antlers.html`,
   named after the set handle with underscores as hyphens (`wish_form` ->
   `wish-form.antlers.html`).
4. **A branch** in `resources/views/components/blocks.antlers.html` matching on
   `type`.

Set handle, fieldset filename and partial name always match.

Every block opens with a `layout/container`, which owns its vertical spacing.
The established rhythm is `spacing="py-20 md:py-28 lg:py-36"`.

## Styling

Tailwind 4, configured entirely in `resources/css/app.css` — there is no
`tailwind.config.js`, so new tokens go in the `@theme` block or a partial under
`resources/css/partials/`.

- **Spacing is 1:1 with pixels** — `p-16` is 16px, `gap-12` is 12px. The scale
  runs to 500. Sizes are the same (`w-232`, `size-24`).
- **Font sizes are arbitrary values for now** — write `text-[18px]`, straight
  from the design. The `text-tiny`…`text-4xl` tokens in
  `partials/font-sizes.css` are still in use in existing templates; leave them
  alone. Both will be folded into one proper scale later, so don't reach for a
  token to avoid an arbitrary value, and don't retrofit existing templates.
- **Colours** are the tokens in `app.css`: `brand` (Raiffeisen red), `snow`,
  `ink`, `blush`, `crimson`, `garnet`, `maroon`, `mist`, `silver`, `slate`,
  `charcoal`, `linen`, `sand`, `walnut`. There is no `accent` token.
- **Images are stored uncropped and cropped by CSS.** Glide has one preset per
  width (`md`/`lg`/`xl` = 768/1280/1920, plus `-webp` twins) at `fit: max`, so
  a file is never upscaled. The ratio comes from an aspect class passed to
  `media/image` along with `object-cover` — `aspect-square`, otherwise an
  arbitrary value such as `aspect-[16/7]` — varying per breakpoint where the
  design does.
- **Breakpoints are `md:` and `lg:` only** — no `xl:`, and no `sm:`. Base
  styles are mobile; `md:` and `lg:` step up from there. `max-md:` handles
  mobile-only overrides, such as the footer's reordered grid.
- Alpine handles small UI state (the menu). One Vue island handles the form.

## Language

UI copy, editor-facing labels and `aria-label`s are German.

**Code comments are always English, and brief.** A line or two saying why, not
what — the code already says what. Skip the comment when the code is plain,
which is most of the time: no header comment describing a component, and no
running commentary on markup. Comment only the line that would otherwise read
as a mistake.

Commit messages are English, imperative, sentence case, no prefix or trailing
period, and say why when the why is not obvious: *"Drop the menu's blur so it
doesn't stack with the header"*.
