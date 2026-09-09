# Meine Wunschbank – Raiffeisenbank Weissenstein

Statamic 6 / Laravel 13 site. Visitors submit a *Herzenswunsch* (a wish) with a
photo; submissions land unpublished in the Control Panel for review.

## Setup

```bash
composer setup          # install, .env, key, migrate, npm install + build
php artisan statamic:make:user --super
herd secure             # https://meinewunschbank.ch.test
```

`npm run dev` for the front-end watcher, `npm run cp:dev` for Control Panel assets.

## How it fits together

- **Content model** — `pages` (structured, routable) and `wishes` (submissions,
  no route). Blueprints in `resources/blueprints`, fieldsets reduced to
  `editor` and `seo`.
- **Templates** — `resources/views/layout.antlers.html` composes the page from
  `components/layout/*`. Page content is a replicator; each set maps to a
  partial in `components/blocks/` via `components/blocks.antlers.html`.
- **The form** — a Vue island (`resources/js/components/WishForm.vue`) mounted
  on the `wish_form` block. It posts multipart to `POST /api/wishes`
  (`SubmitWishController`), which validates via `SubmitWishRequest`, creates an
  **unpublished** entry in `wishes` and mails `MAIL_NOTIFY`.
- **Photos** are stored in the private `wishes` asset container
  (`storage/app/private/wishes`, outside the web root). Moderators view them
  through `GET /cp/wishes/{path}/photo`, which requires a CP session.

## Conventions

- Spacing utilities are 1:1 with pixels (`p-16` = 16px) — see
  `resources/css/partials/spacing.css`.
- Type scale tokens live in `resources/css/partials/font-sizes.css`.
- Outside production all mail is redirected to `MAIL_TO`.
- Submissions (`content/collections/wishes/*`) are **not** versioned — they hold
  personal data and this repository is public.
